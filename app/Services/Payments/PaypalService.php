<?php

declare(strict_types=1);

namespace App\Services\Payments;

use RuntimeException;

/**
 * Intégration PayPal — Orders API v2 (Checkout) + vérification de webhook.
 *
 * Documentation : https://developer.paypal.com/docs/api/orders/v2/
 * Authentification : PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET, PAYPAL_ENV
 * ("sandbox" ou "live"), PAYPAL_WEBHOOK_ID (créé lors de l'enregistrement
 * du webhook dans le dashboard développeur PayPal) — .env.
 */
final class PaypalService
{
    private string $clientId;
    private string $clientSecret;
    private string $webhookId;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) ($_ENV['PAYPAL_CLIENT_ID'] ?? getenv('PAYPAL_CLIENT_ID') ?: '');
        $this->clientSecret = (string) ($_ENV['PAYPAL_CLIENT_SECRET'] ?? getenv('PAYPAL_CLIENT_SECRET') ?: '');
        $this->webhookId = (string) ($_ENV['PAYPAL_WEBHOOK_ID'] ?? getenv('PAYPAL_WEBHOOK_ID') ?: '');

        $env = (string) ($_ENV['PAYPAL_ENV'] ?? getenv('PAYPAL_ENV') ?: 'sandbox');
        $this->baseUrl = $env === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '';
    }

    /**
     * Crée une commande PayPal et retourne son identifiant (order ID), à
     * transmettre au SDK Boutons côté client pour l'approbation du donateur.
     */
    public function createOrder(string $amount, string $currency, string $description): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PayPal non configuré (PAYPAL_CLIENT_ID / PAYPAL_CLIENT_SECRET).');
        }

        $payload = json_encode([
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'amount'      => ['currency_code' => $currency, 'value' => $amount],
                'description' => mb_substr($description, 0, 127),
            ]],
        ], JSON_UNESCAPED_UNICODE);

        $response = $this->call('POST', '/v2/checkout/orders', $payload, true);

        if ($response['httpCode'] < 200 || $response['httpCode'] >= 300) {
            throw new RuntimeException('PayPal — échec de création de la commande (HTTP ' . $response['httpCode'] . ').');
        }

        $decoded = json_decode($response['body'], true);
        $orderId = is_array($decoded) ? ($decoded['id'] ?? null) : null;

        if (!is_string($orderId) || $orderId === '') {
            throw new RuntimeException('PayPal — identifiant de commande manquant dans la réponse.');
        }

        return $orderId;
    }

    /**
     * Capture le paiement d'une commande approuvée par le donateur.
     *
     * @return array{status:string,captureId:?string}
     */
    public function captureOrder(string $orderId): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PayPal non configuré.');
        }

        $response = $this->call('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', '', true);

        if ($response['httpCode'] < 200 || $response['httpCode'] >= 300) {
            throw new RuntimeException('PayPal — échec de capture du paiement (HTTP ' . $response['httpCode'] . ').');
        }

        $decoded = json_decode($response['body'], true);
        $status = is_array($decoded) ? (string) ($decoded['status'] ?? 'UNKNOWN') : 'UNKNOWN';
        $captureId = is_array($decoded)
            ? ($decoded['purchase_units'][0]['payments']['captures'][0]['id'] ?? null)
            : null;

        return ['status' => $status, 'captureId' => is_string($captureId) ? $captureId : null];
    }

    /**
     * Vérifie qu'un webhook reçu provient réellement de PayPal, avant de lui
     * faire confiance pour mettre à jour un don.
     *
     * @param array<string,string> $headers en-têtes PAYPAL-TRANSMISSION-* de la requête reçue
     */
    public function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        if (!$this->isConfigured() || $this->webhookId === '') {
            return false;
        }

        $payload = json_encode([
            'transmission_id'   => $headers['transmission_id'] ?? '',
            'transmission_time' => $headers['transmission_time'] ?? '',
            'cert_url'          => $headers['cert_url'] ?? '',
            'auth_algo'         => $headers['auth_algo'] ?? '',
            'transmission_sig'  => $headers['transmission_sig'] ?? '',
            'webhook_id'        => $this->webhookId,
            'webhook_event'     => json_decode($rawBody, true),
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return false;
        }

        $response = $this->call('POST', '/v1/notifications/verify-webhook-signature', $payload, true);

        if ($response['httpCode'] < 200 || $response['httpCode'] >= 300) {
            return false;
        }

        $decoded = json_decode($response['body'], true);

        return is_array($decoded) && ($decoded['verification_status'] ?? '') === 'SUCCESS';
    }

    private function getAccessToken(): string
    {
        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_USERPWD        => $this->clientId . ':' . $this->clientSecret,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('PayPal — erreur réseau cURL : ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('PayPal — échec d\'authentification (HTTP ' . $httpCode . ').');
        }

        $decoded = json_decode((string) $response, true);
        $token = is_array($decoded) ? ($decoded['access_token'] ?? null) : null;

        if (!is_string($token) || $token === '') {
            throw new RuntimeException('PayPal — jeton d\'accès manquant dans la réponse.');
        }

        return $token;
    }

    /**
     * @return array{httpCode:int,body:string}
     */
    private function call(string $method, string $path, string $body, bool $withAuth): array
    {
        $headers = ['Content-Type: application/json'];
        if ($withAuth) {
            $headers[] = 'Authorization: Bearer ' . $this->getAccessToken();
        }

        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('PayPal — erreur réseau cURL : ' . $curlError);
        }

        return ['httpCode' => (int) $httpCode, 'body' => (string) $response];
    }
}
