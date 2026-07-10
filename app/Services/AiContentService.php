<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Wrapper générique pour l'API Google Gemini : rédige un texte à partir
 * d'une consigne (voix de marque, contraintes de format) et d'un contexte
 * factuel fourni par l'appelant. L'IA met en forme des faits réels — elle
 * ne les invente jamais ; c'est à l'appelant de ne fournir que du contenu
 * vérifié (article, événement, score de match...) dans $context.
 *
 * Authentification : GEMINI_API_KEY (.env), clé gratuite (sans carte
 * bancaire) sur https://aistudio.google.com/apikey
 */
final class AiContentService
{
    private const MODEL = 'gemini-2.0-flash';
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODEL . ':generateContent';

    private string $apiKey;

    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey !== ''
            ? $apiKey
            : (string) ($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '');
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
            throw new RuntimeException('Clé API Gemini manquante. Configurez GEMINI_API_KEY.');
        }

        $payload = json_encode([
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $context]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new RuntimeException('Impossible de sérialiser la requête vers l\'API Gemini.');
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
                'x-goog-api-key: ' . $this->apiKey,
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (API Gemini) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(
                sprintf('Réponse API Gemini invalide (HTTP %d).', $httpCode)
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $decoded['error']['message'] ?? "Erreur HTTP $httpCode";
            throw new RuntimeException('API Gemini — ' . $errorMsg);
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            $finishReason = $decoded['candidates'][0]['finishReason'] ?? null;
            throw new RuntimeException(
                'Réponse API Gemini vide ou inattendue.'
                . ($finishReason !== null ? " (finishReason: $finishReason)" : '')
            );
        }

        return trim($text);
    }
}
