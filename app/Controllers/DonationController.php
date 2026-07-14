<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Models\Donation;
use App\Services\Payments\MomoCollectionsService;
use App\Services\Payments\PaypalService;
use Throwable;

/**
 * Dons en ligne (MTN MoMo + PayPal). Endpoints appelés en AJAX depuis
 * l'onglet « Faire un don » de /contact — jamais de rendu de page complète.
 */
final class DonationController extends Controller
{
    /** Tentatives MoMo max par IP sur la fenêtre — un peu de marge en cas de faux numéro. */
    private const MAX_MOMO_ATTEMPTS = 5;

    /** Fenêtre de limitation (secondes) — 15 minutes. */
    private const MOMO_WINDOW = 900;

    private Donation $donations;

    public function __construct()
    {
        $this->donations = new Donation();
    }

    // -------------------------------------------------------------------------
    // MTN MoMo
    // -------------------------------------------------------------------------

    public function initiateMomo(): void
    {
        $this->verifyCsrf();

        if ($this->isSpam()) {
            // Un bot a rempli le champ-piège : on répond comme si tout allait
            // bien, sans jamais appeler l'API MoMo ni créer de don.
            $this->json(['reference' => bin2hex(random_bytes(16))]);
        }

        $this->guardMomoRate();

        $data = [
            'donor_name'  => trim((string) $this->input('donor_name')),
            'donor_email' => trim((string) $this->input('donor_email')),
            'donor_phone' => trim((string) $this->input('donor_phone')),
            'amount'      => (string) $this->input('amount'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'donor_name'  => 'required|max:150',
            'donor_email' => 'required|email|max:190',
            'donor_phone' => 'required|max:30',
            'amount'      => 'required',
        ]);

        if ($validator->fails()) {
            $this->json(['error' => 'Veuillez vérifier les informations saisies.'], 422);
        }

        $amount = $this->normalizedAmount($data['amount']);
        if ($amount === null) {
            $this->json(['error' => 'Montant invalide.'], 422);
        }

        $service = new MomoCollectionsService();
        if (!$service->isConfigured()) {
            $this->json(['error' => 'Le paiement Mobile Money n\'est pas encore configuré.'], 503);
        }

        $reference = $this->generateReference();

        $id = $this->donations->create([
            'reference'   => $reference,
            'method'      => 'momo',
            'amount'      => $amount,
            'currency'    => 'XOF',
            'donor_name'  => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'donor_phone' => $data['donor_phone'],
            'status'      => 'pending',
        ]);

        try {
            $externalReference = $service->requestToPay(
                (string) $amount,
                'XOF',
                $data['donor_phone'],
                $reference,
                'Don ATLEX Sport'
            );
            $this->donations->update($id, ['external_reference' => $externalReference]);
        } catch (Throwable $e) {
            $this->donations->updateStatus($id, 'failed', null, $e->getMessage());
            $this->json(['error' => "Échec de l'envoi de la demande de paiement. Vérifiez le numéro et réessayez."], 502);
        }

        $this->json(['reference' => $reference]);
    }

    public function momoStatus(string $reference): void
    {
        $donation = $this->donations->findByReference($reference);
        if ($donation === null) {
            $this->json(['error' => 'Don introuvable.'], 404);
        }

        if (in_array($donation['status'], ['completed', 'failed', 'cancelled'], true)) {
            $this->json(['status' => $donation['status']]);
        }

        $service = new MomoCollectionsService();
        if (!$service->isConfigured() || empty($donation['external_reference'])) {
            $this->json(['status' => 'pending']);
        }

        try {
            $result = $service->getTransactionStatus((string) $donation['external_reference']);
            $status = $this->mapMomoStatus((string) ($result['status'] ?? 'PENDING'));

            if ($status !== 'pending') {
                $this->donations->updateStatus((int) $donation['id'], $status, null, json_encode($result));
            }

            $this->json(['status' => $status]);
        } catch (Throwable) {
            // On ne fait pas échouer le don sur une simple erreur réseau
            // ponctuelle — le donateur reverra "en attente" et le polling réessaiera.
            $this->json(['status' => 'pending']);
        }
    }

    /**
     * Callback serveur-à-serveur MTN (best effort) — la source de vérité
     * reste le polling actif via momoStatus(), qui interroge MTN directement.
     */
    public function momoCallback(): void
    {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->json(['received' => true]);
        }

        $externalId = (string) ($payload['externalId'] ?? '');
        $donation = $externalId !== '' ? $this->donations->findByReference($externalId) : null;

        if ($donation !== null) {
            $status = $this->mapMomoStatus((string) ($payload['status'] ?? 'PENDING'));
            if ($status !== 'pending') {
                $this->donations->updateStatus((int) $donation['id'], $status, null, $raw);
            }
        }

        $this->json(['received' => true]);
    }

    // -------------------------------------------------------------------------
    // PayPal
    // -------------------------------------------------------------------------

    public function createPaypalOrder(): void
    {
        $this->verifyCsrf();

        if ($this->isSpam()) {
            $this->json(['error' => 'Requête invalide.'], 422);
        }

        $data = [
            'donor_name'  => trim((string) $this->input('donor_name')),
            'donor_email' => trim((string) $this->input('donor_email')),
            'amount'      => (string) $this->input('amount'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'donor_name'  => 'required|max:150',
            'donor_email' => 'required|email|max:190',
            'amount'      => 'required',
        ]);

        if ($validator->fails()) {
            $this->json(['error' => 'Veuillez vérifier les informations saisies.'], 422);
        }

        $amount = $this->normalizedAmount($data['amount']);
        if ($amount === null) {
            $this->json(['error' => 'Montant invalide.'], 422);
        }

        $service = new PaypalService();
        if (!$service->isConfigured()) {
            $this->json(['error' => 'Le paiement PayPal n\'est pas encore configuré.'], 503);
        }

        $reference = $this->generateReference();
        $amountFormatted = number_format($amount, 2, '.', '');

        $id = $this->donations->create([
            'reference'   => $reference,
            'method'      => 'paypal',
            'amount'      => $amount,
            'currency'    => 'EUR',
            'donor_name'  => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'status'      => 'pending',
        ]);

        try {
            $orderId = $service->createOrder($amountFormatted, 'EUR', 'Don ATLEX Sport');
            $this->donations->update($id, ['external_reference' => $orderId]);
        } catch (Throwable $e) {
            $this->donations->updateStatus($id, 'failed', null, $e->getMessage());
            $this->json(['error' => 'Échec de création de la commande PayPal.'], 502);
        }

        $this->json(['orderID' => $orderId]);
    }

    public function capturePaypalOrder(): void
    {
        $this->verifyCsrf();

        $orderId = (string) $this->input('orderID');
        if ($orderId === '') {
            $this->json(['error' => 'Commande manquante.'], 422);
        }

        $donation = $this->donations->findByExternalReference($orderId);
        if ($donation === null) {
            $this->json(['error' => 'Don introuvable.'], 404);
        }

        if ($donation['status'] === 'completed') {
            $this->json(['status' => 'completed']);
        }

        $service = new PaypalService();

        try {
            $result = $service->captureOrder($orderId);
            $status = $result['status'] === 'COMPLETED' ? 'completed' : 'failed';
            $this->donations->updateStatus((int) $donation['id'], $status, $result['captureId'], json_encode($result));

            $this->json(['status' => $status]);
        } catch (Throwable $e) {
            $this->donations->updateStatus((int) $donation['id'], 'failed', null, $e->getMessage());
            $this->json(['error' => 'Échec de la capture du paiement.'], 502);
        }
    }

    /**
     * Filet de sécurité si la capture côté client est interrompue. N'agit
     * jamais sans signature PayPal valide.
     */
    public function paypalWebhook(): void
    {
        $raw = file_get_contents('php://input') ?: '';

        $headers = [
            'transmission_id'   => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
            'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
            'cert_url'          => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
            'auth_algo'         => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
            'transmission_sig'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
        ];

        $service = new PaypalService();
        if (!$service->verifyWebhookSignature($headers, $raw)) {
            $this->json(['received' => false], 400);
        }

        $event = json_decode($raw, true);
        $eventType = is_array($event) ? (string) ($event['event_type'] ?? '') : '';
        $orderId = is_array($event)
            ? (string) ($event['resource']['supplementary_data']['related_ids']['order_id'] ?? '')
            : '';

        if ($orderId !== '') {
            $donation = $this->donations->findByExternalReference($orderId);
            if ($donation !== null) {
                if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
                    $this->donations->updateStatus((int) $donation['id'], 'completed', null, $raw);
                } elseif ($eventType === 'PAYMENT.CAPTURE.DENIED') {
                    $this->donations->updateStatus((int) $donation['id'], 'failed', null, $raw);
                }
            }
        }

        $this->json(['received' => true]);
    }

    // -------------------------------------------------------------------------
    // Aides communes
    // -------------------------------------------------------------------------

    private function guardMomoRate(): void
    {
        $limiter = new RateLimiter();
        $key = 'donation-momo:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if ($limiter->tooManyAttempts($key, self::MAX_MOMO_ATTEMPTS)) {
            $minutes = (int) ceil($limiter->availableIn($key) / 60);
            $this->json(['error' => "Trop de tentatives. Réessayez dans {$minutes} minute(s)."], 429);
        }

        $limiter->hit($key, self::MOMO_WINDOW);
    }

    /**
     * Détecte un envoi automatisé via le champ-piège (honeypot) — même
     * principe que ContactController::isSpam().
     */
    private function isSpam(): bool
    {
        return (string) $this->input('website', '') !== '';
    }

    private function normalizedAmount(string $raw): ?float
    {
        $raw = str_replace(',', '.', trim($raw));
        if (!is_numeric($raw)) {
            return null;
        }

        $amount = (float) $raw;
        if ($amount <= 0 || $amount > 5_000_000) {
            return null;
        }

        return round($amount, 2);
    }

    private function mapMomoStatus(string $providerStatus): string
    {
        return match (strtoupper($providerStatus)) {
            'SUCCESSFUL' => 'completed',
            'FAILED', 'REJECTED' => 'failed',
            default => 'pending',
        };
    }

    private function generateReference(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
