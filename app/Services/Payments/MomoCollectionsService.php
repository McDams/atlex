<?php

declare(strict_types=1);

namespace App\Services\Payments;

use RuntimeException;

/**
 * Intégration MTN Mobile Money — API "Collections" (Request to Pay).
 *
 * Documentation : https://momodeveloper.mtn.com
 * Authentification : MOMO_SUBSCRIPTION_KEY, MOMO_API_USER, MOMO_API_KEY,
 * MOMO_TARGET_ENVIRONMENT (ex: "sandbox" ou l'environnement fourni par MTN
 * en production, ex: "mtnbenin"), MOMO_ENV ("sandbox" ou "production") — .env.
 *
 * L'API User + API Key sont provisionnés une seule fois (voir
 * bin/momo-provision-sandbox.php pour le bac à sable) — ce service ne fait
 * que les utiliser, jamais leur création.
 */
final class MomoCollectionsService
{
    private string $subscriptionKey;
    private string $apiUser;
    private string $apiKey;
    private string $targetEnvironment;
    private string $baseUrl;

    public function __construct()
    {
        $this->subscriptionKey = (string) ($_ENV['MOMO_SUBSCRIPTION_KEY'] ?? getenv('MOMO_SUBSCRIPTION_KEY') ?: '');
        $this->apiUser = (string) ($_ENV['MOMO_API_USER'] ?? getenv('MOMO_API_USER') ?: '');
        $this->apiKey = (string) ($_ENV['MOMO_API_KEY'] ?? getenv('MOMO_API_KEY') ?: '');
        $this->targetEnvironment = (string) ($_ENV['MOMO_TARGET_ENVIRONMENT'] ?? getenv('MOMO_TARGET_ENVIRONMENT') ?: 'sandbox');

        $env = (string) ($_ENV['MOMO_ENV'] ?? getenv('MOMO_ENV') ?: 'sandbox');
        $this->baseUrl = $env === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    public function isConfigured(): bool
    {
        return $this->subscriptionKey !== '' && $this->apiUser !== '' && $this->apiKey !== '';
    }

    /**
     * Déclenche une demande de paiement (le payeur reçoit une notification
     * USSD sur son téléphone pour confirmer). Retourne l'identifiant de
     * référence à utiliser pour interroger le statut ensuite.
     */
    public function requestToPay(string $amount, string $currency, string $phone, string $externalId, string $message): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('MTN MoMo non configuré (MOMO_SUBSCRIPTION_KEY / MOMO_API_USER / MOMO_API_KEY).');
        }

        $token = $this->getAccessToken();
        $referenceId = $this->generateUuid();

        $payload = json_encode([
            'amount'       => $amount,
            'currency'     => $currency,
            'externalId'   => $externalId,
            'payer'        => [
                'partyIdType' => 'MSISDN',
                'partyId'     => $this->normalizePhone($phone),
            ],
            'payerMessage' => mb_substr($message, 0, 160),
            'payeeNote'    => mb_substr($message, 0, 160),
        ], JSON_UNESCAPED_UNICODE);

        $response = $this->call(
            'POST',
            '/collection/v1_0/requesttopay',
            $payload,
            [
                'Authorization: Bearer ' . $token,
                'X-Reference-Id: ' . $referenceId,
                'X-Target-Environment: ' . $this->targetEnvironment,
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
                'Content-Type: application/json',
            ]
        );

        if ($response['httpCode'] !== 202) {
            throw new RuntimeException(
                'MTN MoMo — échec de la demande de paiement (HTTP ' . $response['httpCode'] . ').'
            );
        }

        return $referenceId;
    }

    /**
     * @return array<string,mixed> réponse MTN (contient au minimum 'status').
     */
    public function getTransactionStatus(string $referenceId): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('MTN MoMo non configuré.');
        }

        $token = $this->getAccessToken();

        $response = $this->call(
            'GET',
            '/collection/v1_0/requesttopay/' . rawurlencode($referenceId),
            null,
            [
                'Authorization: Bearer ' . $token,
                'X-Target-Environment: ' . $this->targetEnvironment,
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
            ]
        );

        if ($response['httpCode'] < 200 || $response['httpCode'] >= 300) {
            throw new RuntimeException('MTN MoMo — statut introuvable (HTTP ' . $response['httpCode'] . ').');
        }

        $decoded = json_decode($response['body'], true);

        return is_array($decoded) ? $decoded : ['status' => 'UNKNOWN'];
    }

    private function getAccessToken(): string
    {
        $response = $this->call(
            'POST',
            '/collection/token/',
            '',
            [
                'Authorization: Basic ' . base64_encode($this->apiUser . ':' . $this->apiKey),
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
            ]
        );

        if ($response['httpCode'] < 200 || $response['httpCode'] >= 300) {
            throw new RuntimeException('MTN MoMo — échec d\'authentification (HTTP ' . $response['httpCode'] . ').');
        }

        $decoded = json_decode($response['body'], true);
        $token = is_array($decoded) ? ($decoded['access_token'] ?? null) : null;

        if (!is_string($token) || $token === '') {
            throw new RuntimeException('MTN MoMo — jeton d\'accès manquant dans la réponse.');
        }

        return $token;
    }

    /**
     * @param array<int,string> $headers
     * @return array{httpCode:int,body:string}
     */
    private function call(string $method, string $path, ?string $body, array $headers): array
    {
        $ch = curl_init($this->baseUrl . $path);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('MTN MoMo — erreur réseau cURL : ' . $curlError);
        }

        return ['httpCode' => (int) $httpCode, 'body' => (string) $response];
    }

    /**
     * Normalise un numéro béninois (+229 01 XX XX XX XX ou variantes) au
     * format MSISDN attendu par MoMo (indicatif pays sans "+", sans espaces).
     *
     * Depuis la réforme de numérotation de 2021, les numéros locaux béninois
     * comptent 10 chiffres (préfixe "01" inclus, ex: 01 92 57 33 33) — ce
     * préfixe est conservé tel quel après l'indicatif pays, jamais retiré.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (!str_starts_with($digits, '229') && strlen($digits) === 10) {
            $digits = '229' . $digits;
        }

        return $digits;
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
