<?php

declare(strict_types=1);

namespace App\Services\Social;

use RuntimeException;

/**
 * Publie un post approuvé sur une Page Facebook (Graph API).
 *
 * $account['access_token'] : jeton d'accès longue durée de la Page.
 * $account['account_ref']  : identifiant de la Page Facebook.
 */
final class FacebookPublisher
{
    private const API_VERSION = 'v19.0';

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $account
     * @return string identifiant du post publié (pour external_post_id)
     */
    public function publish(array $post, array $account): string
    {
        $pageId = (string) $account['account_ref'];
        $token = (string) $account['access_token'];
        $message = (string) $post['content_text'];

        $url = sprintf('https://graph.facebook.com/%s/%s/feed', self::API_VERSION, rawurlencode($pageId));

        $params = ['message' => $message, 'access_token' => $token];
        if (!empty($post['media_path'])) {
            // Une seule image : Facebook accepte 'link' pour une image publique,
            // ou l'endpoint /photos pour un upload direct (non géré en v1).
            $params['link'] = (string) $post['media_path'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (Facebook) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Réponse Facebook invalide (HTTP %d).', $httpCode));
        }

        if ($httpCode < 200 || $httpCode >= 300 || isset($decoded['error'])) {
            $errorMsg = $decoded['error']['message'] ?? "Erreur HTTP $httpCode";
            throw new RuntimeException('Facebook — ' . $errorMsg);
        }

        $postId = $decoded['id'] ?? null;
        if (!is_string($postId) || $postId === '') {
            throw new RuntimeException("Facebook n'a pas renvoyé d'identifiant de post.");
        }

        return $postId;
    }
}
