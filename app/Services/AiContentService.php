<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Wrapper générique pour l'API Groq (modèles ouverts type Llama, format
 * compatible OpenAI Chat Completions) : rédige un texte à partir d'une
 * consigne (voix de marque, contraintes de format) et d'un contexte factuel
 * fourni par l'appelant. L'IA met en forme des faits réels — elle ne les
 * invente jamais ; c'est à l'appelant de ne fournir que du contenu vérifié
 * (article, événement, score de match...) dans $context.
 *
 * Authentification : GROQ_API_KEY (.env), clé gratuite (sans carte
 * bancaire) sur https://console.groq.com/keys
 */
final class AiContentService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';

    private string $apiKey;

    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey !== ''
            ? $apiKey
            : (string) ($_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: '');
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
            throw new RuntimeException('Clé API Groq manquante. Configurez GROQ_API_KEY.');
        }

        $payload = json_encode([
            'model'      => self::MODEL,
            'max_tokens' => $maxTokens,
            'messages'   => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $context],
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new RuntimeException('Impossible de sérialiser la requête vers l\'API Groq.');
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
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (API Groq) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(
                sprintf('Réponse API Groq invalide (HTTP %d).', $httpCode)
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $decoded['error']['message'] ?? "Erreur HTTP $httpCode";
            throw new RuntimeException('API Groq — ' . $errorMsg);
        }

        $text = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Réponse API Groq vide ou inattendue.');
        }

        return trim($text);
    }
}
