<?php

declare(strict_types=1);

namespace App\Services\Social;

use RuntimeException;

/**
 * Publie un post approuvé sur un compte Instagram Business (Graph API).
 *
 * Contrainte de la plateforme (pas du code) : Instagram n'accepte pas de
 * publication texte seul — une image est obligatoire. Si social_posts.media_path
 * est vide, publish() échoue avec un message clair plutôt que d'appeler l'API.
 *
 * $account['access_token'] : jeton d'accès de la Page Facebook liée au compte IG.
 * $account['account_ref']  : Instagram Business Account ID.
 */
final class InstagramPublisher
{
    private const API_VERSION = 'v19.0';

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $account
     * @return string identifiant du post publié (pour external_post_id)
     */
    public function publish(array $post, array $account): string
    {
        $imageUrl = trim((string) ($post['media_path'] ?? ''));
        if ($imageUrl === '') {
            throw new RuntimeException(
                'Instagram nécessite une image : ajoutez-en une à ce brouillon avant de publier.'
            );
        }

        $igUserId = (string) $account['account_ref'];
        $token = (string) $account['access_token'];

        $containerId = $this->createMediaContainer($igUserId, $token, $imageUrl, (string) $post['content_text']);

        return $this->publishContainer($igUserId, $token, $containerId);
    }

    private function createMediaContainer(string $igUserId, string $token, string $imageUrl, string $caption): string
    {
        $url = sprintf('https://graph.facebook.com/%s/%s/media', self::API_VERSION, rawurlencode($igUserId));

        $decoded = $this->post($url, [
            'image_url'    => $imageUrl,
            'caption'      => $caption,
            'access_token' => $token,
        ]);

        $containerId = $decoded['id'] ?? null;
        if (!is_string($containerId) || $containerId === '') {
            throw new RuntimeException("Instagram n'a pas renvoyé de conteneur média.");
        }

        return $containerId;
    }

    private function publishContainer(string $igUserId, string $token, string $containerId): string
    {
        $url = sprintf('https://graph.facebook.com/%s/%s/media_publish', self::API_VERSION, rawurlencode($igUserId));

        $decoded = $this->post($url, [
            'creation_id'  => $containerId,
            'access_token' => $token,
        ]);

        $postId = $decoded['id'] ?? null;
        if (!is_string($postId) || $postId === '') {
            throw new RuntimeException("Instagram n'a pas renvoyé d'identifiant de post.");
        }

        return $postId;
    }

    /**
     * @param array<string,string> $params
     * @return array<string,mixed>
     */
    private function post(string $url, array $params): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (Instagram) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Réponse Instagram invalide (HTTP %d).', $httpCode));
        }

        if ($httpCode < 200 || $httpCode >= 300 || isset($decoded['error'])) {
            $errorMsg = $decoded['error']['message'] ?? "Erreur HTTP $httpCode";
            throw new RuntimeException('Instagram — ' . $errorMsg);
        }

        return $decoded;
    }
}
