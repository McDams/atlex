<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service d'intégration à l'API REST Hostinger.
 *
 * Fournit les méthodes permettant de récupérer les abonnements,
 * les domaines et de calculer les alertes d'expiration.
 *
 * Documentation API : https://api.hostinger.com
 * Authentification  : Bearer token (HOSTINGER_API_TOKEN dans .env)
 */
final class HostingerService
{
    /** URL de base de l'API Hostinger. */
    private const BASE_URL = 'https://api.hostinger.com';

    /** Délai avant expiration (jours) déclenchant une alerte. */
    private const ALERT_THRESHOLD_DAYS = 30;

    /** Token Bearer pour l'authentification API. */
    private string $apiToken;

    public function __construct(string $apiToken = '')
    {
        // Priorité : paramètre fourni > variable d'environnement
        $this->apiToken = $apiToken !== ''
            ? $apiToken
            : ($_ENV['HOSTINGER_API_TOKEN'] ?? getenv('HOSTINGER_API_TOKEN') ?: '');
    }

    // -------------------------------------------------------------------------
    // Méthodes publiques
    // -------------------------------------------------------------------------

    /**
     * Retourne la liste des abonnements Hostinger avec statut et dates.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSubscriptions(): array
    {
        $data = $this->callApi('/api/billing/v1/subscriptions');

        $subscriptions = [];
        $items = $data['data'] ?? $data ?? [];

        foreach ($items as $item) {
            $expiresAt   = $item['expires_at'] ?? $item['expiry_date'] ?? null;
            $startsAt    = $item['starts_at']  ?? $item['created_at']  ?? null;
            $daysLeft    = $expiresAt ? $this->daysUntil($expiresAt) : null;

            $subscriptions[] = [
                'id'             => $item['id'] ?? null,
                'name'           => $item['name'] ?? $item['plan_name'] ?? 'Plan inconnu',
                'status'         => $item['status'] ?? 'unknown',
                'starts_at'      => $startsAt,
                'expires_at'     => $expiresAt,
                'days_left'      => $daysLeft,
                'price'          => $item['price'] ?? $item['amount'] ?? null,
                'currency'       => $item['currency'] ?? 'EUR',
                'billing_period' => $item['billing_period'] ?? $item['period'] ?? null,
                'auto_renew'     => $item['auto_renew'] ?? $item['is_auto_renew'] ?? false,
            ];
        }

        return $subscriptions;
    }

    /**
     * Retourne la liste des domaines enregistrés avec leurs dates d'expiration.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDomains(): array
    {
        $data = $this->callApi('/api/domains/v1/portfolio');

        $domains = [];
        $items   = $data['data'] ?? $data ?? [];

        foreach ($items as $item) {
            $expiresAt = $item['expires_at'] ?? $item['expiry_date'] ?? null;
            $daysLeft  = $expiresAt ? $this->daysUntil($expiresAt) : null;

            $domains[] = [
                'domain'      => $item['domain'] ?? $item['name'] ?? '',
                'status'      => $item['status'] ?? 'unknown',
                'expires_at'  => $expiresAt,
                'days_left'   => $daysLeft,
                'auto_renew'  => $item['auto_renew'] ?? $item['is_auto_renew'] ?? false,
                'locked'      => $item['locked'] ?? false,
                'nameservers' => $item['nameservers'] ?? [],
            ];
        }

        return $domains;
    }

    /**
     * Retourne les détails d'un domaine précis.
     *
     * @return array<string, mixed>
     */
    public function getDomainDetails(string $domain): array
    {
        $domain = ltrim(trim($domain), '/');
        $data   = $this->callApi('/api/domains/v1/portfolio/' . rawurlencode($domain));

        $item      = $data['data'] ?? $data ?? [];
        $expiresAt = $item['expires_at'] ?? $item['expiry_date'] ?? null;

        return [
            'domain'      => $item['domain'] ?? $item['name'] ?? $domain,
            'status'      => $item['status'] ?? 'unknown',
            'expires_at'  => $expiresAt,
            'days_left'   => $expiresAt ? $this->daysUntil($expiresAt) : null,
            'auto_renew'  => $item['auto_renew'] ?? $item['is_auto_renew'] ?? false,
            'locked'      => $item['locked'] ?? false,
            'nameservers' => $item['nameservers'] ?? [],
            'contacts'    => $item['contacts'] ?? [],
            'raw'         => $item,
        ];
    }

    /**
     * Calcule les alertes pour les abonnements et domaines expirant
     * dans les prochains ALERT_THRESHOLD_DAYS jours.
     *
     * @return array{subscriptions: array<int, array<string, mixed>>, domains: array<int, array<string, mixed>>}
     */
    public function getAlerts(): array
    {
        $alertSubscriptions = [];
        $alertDomains       = [];

        // Abonnements
        foreach ($this->getSubscriptions() as $sub) {
            if ($sub['days_left'] !== null && $sub['days_left'] <= self::ALERT_THRESHOLD_DAYS) {
                $alertSubscriptions[] = $sub;
            }
        }

        // Domaines
        foreach ($this->getDomains() as $domain) {
            if ($domain['days_left'] !== null && $domain['days_left'] <= self::ALERT_THRESHOLD_DAYS) {
                $alertDomains[] = $domain;
            }
        }

        return [
            'subscriptions' => $alertSubscriptions,
            'domains'       => $alertDomains,
        ];
    }

    /**
     * Teste la connexion à l'API Hostinger.
     * Retourne un tableau avec le statut et un message.
     *
     * @return array{success: bool, message: string, data?: array<string, mixed>}
     */
    public function testConnection(): array
    {
        if ($this->apiToken === '') {
            return ['success' => false, 'message' => 'Token API non configuré.'];
        }

        try {
            $data = $this->callApi('/api/billing/v1/subscriptions');
            return [
                'success' => true,
                'message' => 'Connexion réussie à l\'API Hostinger.',
                'data'    => ['count' => count($data['data'] ?? $data ?? [])],
            ];
        } catch (\RuntimeException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Méthode privée : appel API via cURL
    // -------------------------------------------------------------------------

    /**
     * Effectue un appel GET vers l'API Hostinger.
     *
     * @param  string              $endpoint  Chemin relatif (ex: /api/billing/v1/subscriptions)
     * @return array<string, mixed>            Réponse JSON décodée
     * @throws \RuntimeException               En cas d'erreur réseau ou HTTP
     */
    private function callApi(string $endpoint): array
    {
        if ($this->apiToken === '') {
            throw new \RuntimeException('Token API Hostinger manquant. Configurez HOSTINGER_API_TOKEN.');
        }

        $url = self::BASE_URL . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Erreur réseau cURL
        if ($response === false) {
            throw new \RuntimeException('Erreur réseau cURL : ' . $curlError);
        }

        // Décodage JSON
        $decoded = json_decode((string) $response, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException(
                sprintf('Réponse API invalide (HTTP %d) : %s', $httpCode, substr((string) $response, 0, 200))
            );
        }

        // Erreur HTTP
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $decoded['message'] ?? $decoded['error'] ?? "Erreur HTTP $httpCode";
            throw new \RuntimeException("API Hostinger — $errorMsg (HTTP $httpCode)");
        }

        return $decoded;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Calcule le nombre de jours entre maintenant et une date cible.
     * Retourne null si la date est invalide.
     */
    private function daysUntil(string $dateString): ?int
    {
        try {
            $target = new \DateTimeImmutable($dateString);
            $now    = new \DateTimeImmutable('today');
            $diff   = $now->diff($target);

            // Retourne un nombre négatif si la date est déjà passée
            return $diff->invert ? -$diff->days : $diff->days;
        } catch (\Exception) {
            return null;
        }
    }
}
