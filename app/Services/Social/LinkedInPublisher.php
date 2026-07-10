<?php

declare(strict_types=1);

namespace App\Services\Social;

use RuntimeException;

/**
 * Publie un post approuvé sur une page Organisation LinkedIn (UGC Posts API).
 *
 * $account['access_token'] : jeton d'accès (member ou organization) avec le
 *                             scope w_organization_social.
 * $account['account_ref']  : URN de l'organisation, ex: "urn:li:organization:12345678".
 */
final class LinkedInPublisher
{
    private const API_URL = 'https://api.linkedin.com/v2/ugcPosts';

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $account
     * @return string identifiant du post publié (pour external_post_id)
     */
    public function publish(array $post, array $account): string
    {
        $authorUrn = (string) $account['account_ref'];
        $token = (string) $account['access_token'];
        $text = (string) $post['content_text'];

        $payload = json_encode([
            'author'         => $authorUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary'    => ['text' => $text],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new RuntimeException('Impossible de sérialiser la requête vers LinkedIn.');
        }

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true, // les en-têtes de réponse contiennent x-restli-id
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'X-Restli-Protocol-Version: 2.0.0',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (LinkedIn) : ' . $curlError);
        }

        $rawHeaders = substr((string) $response, 0, $headerSize);
        $body = substr((string) $response, $headerSize);

        if ($httpCode < 200 || $httpCode >= 300) {
            $decoded = json_decode($body, true);
            $errorMsg = is_array($decoded) ? ($decoded['message'] ?? "Erreur HTTP $httpCode") : "Erreur HTTP $httpCode";
            throw new RuntimeException('LinkedIn — ' . $errorMsg);
        }

        if (preg_match('/^x-restli-id:\s*(.+)$/mi', $rawHeaders, $matches) === 1) {
            return trim($matches[1]);
        }

        throw new RuntimeException("LinkedIn n'a pas renvoyé d'identifiant de post (en-tête x-restli-id absent).");
    }
}
