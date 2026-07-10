<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Wrapper générique pour l'API Anthropic (Claude) : rédige un texte à partir
 * d'une consigne (voix de marque, contraintes de format) et d'un contexte
 * factuel fourni par l'appelant. L'IA met en forme des faits réels — elle
 * ne les invente jamais ; c'est à l'appelant de ne fournir que du contenu
 * vérifié (article, événement, score de match...) dans $context.
 *
 * Authentification : ANTHROPIC_API_KEY (.env), compte gratuit à créer sur
 * https://console.anthropic.com
 */
final class AiContentService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    /** Modèle par défaut. claude-haiku-4-5 est une alternative moins chère si le volume grandit. */
    private const MODEL = 'claude-sonnet-5';

    private string $apiKey;

    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey !== ''
            ? $apiKey
            : (string) ($_ENV['ANTHROPIC_API_KEY'] ?? getenv('ANTHROPIC_API_KEY') ?: '');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Génère un texte à partir d'une consigne système (voix de marque,
     * contraintes de longueur/format) et d'un contexte factuel.
     *
     * @throws RuntimeException si la clé API manque ou si l'appel échoue.
     */
    public function draft(string $systemPrompt, string $context, int $maxTokens = 600): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Clé API Anthropic manquante. Configurez ANTHROPIC_API_KEY.');
        }

        $payload = json_encode([
            'model'      => self::MODEL,
            'max_tokens' => $maxTokens,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $context],
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new RuntimeException('Impossible de sérialiser la requête vers l\'API Anthropic.');
        }

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::API_VERSION,
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (API Anthropic) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(
                sprintf('Réponse API Anthropic invalide (HTTP %d).', $httpCode)
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $decoded['error']['message'] ?? "Erreur HTTP $httpCode";
            throw new RuntimeException('API Anthropic — ' . $errorMsg);
        }

        $text = $decoded['content'][0]['text'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Réponse API Anthropic vide ou inattendue.');
        }

        return trim($text);
    }
}
