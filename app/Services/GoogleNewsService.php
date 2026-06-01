<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Récupère les titres de Google Actualités (rubrique Sport) via son flux RSS.
 *
 * Le flux est mis en cache sur disque (storage/cache) pour ne pas appeler
 * Google à chaque chargement de page et éviter tout throttling. En cas de
 * coupure réseau, le dernier cache (même périmé) est réutilisé.
 *
 * L'URL du flux est surchargeable via la variable d'environnement
 * NEWS_FEED_URL (ex. pour cibler une autre région : gl=BJ&ceid=BJ:fr).
 */
final class GoogleNewsService
{
    /** Flux RSS Google Actualités — rubrique Sport, en français. */
    private const DEFAULT_FEED_URL =
        'https://news.google.com/rss/headlines/section/topic/SPORTS?hl=fr&gl=FR&ceid=FR:fr';

    /** Durée de vie du cache, en secondes (30 minutes). */
    private const CACHE_TTL = 1800;

    private string $feedUrl;

    private string $cacheFile;

    public function __construct(?string $feedUrl = null)
    {
        $this->feedUrl = $feedUrl
            ?? ($_ENV['NEWS_FEED_URL'] ?? getenv('NEWS_FEED_URL') ?: self::DEFAULT_FEED_URL);

        $this->cacheFile = ROOT . '/storage/cache/google_news_sport.json';
    }

    /**
     * Retourne les derniers titres Sport.
     *
     * @return array<int,array{title:string,url:string,source:string}>
     */
    public function headlines(int $limit = 10): array
    {
        $items = $this->readCache();

        if ($items === null) {
            $fetched = $this->fetchAndParse();

            if ($fetched !== null) {
                $this->writeCache($fetched);
                $items = $fetched;
            } else {
                // Échec réseau : on retombe sur le cache périmé s'il existe.
                $items = $this->readCache(true) ?? [];
            }
        }

        return array_slice($items, 0, max(1, $limit));
    }

    /**
     * @return array<int,array{title:string,url:string,source:string}>|null
     */
    private function readCache(bool $ignoreTtl = false): ?array
    {
        if (!is_file($this->cacheFile)) {
            return null;
        }

        $raw = json_decode((string) file_get_contents($this->cacheFile), true);
        if (!is_array($raw) || !isset($raw['items']) || !is_array($raw['items'])) {
            return null;
        }

        if (!$ignoreTtl && (int) ($raw['fetched_at'] ?? 0) + self::CACHE_TTL < time()) {
            return null;
        }

        return $raw['items'];
    }

    /**
     * @param array<int,array{title:string,url:string,source:string}> $items
     */
    private function writeCache(array $items): void
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        file_put_contents(
            $this->cacheFile,
            json_encode(['fetched_at' => time(), 'items' => $items], JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    /**
     * Télécharge et parse le flux RSS.
     *
     * @return array<int,array{title:string,url:string,source:string}>|null  null si échec
     */
    private function fetchAndParse(): ?array
    {
        $xml = $this->fetch($this->feedUrl);
        if ($xml === null) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($feed === false || !isset($feed->channel->item)) {
            return null;
        }

        $items = [];
        foreach ($feed->channel->item as $item) {
            $title = trim((string) $item->title);
            if ($title === '') {
                continue;
            }

            $items[] = [
                'title'  => $title,
                'url'    => trim((string) $item->link),
                'source' => isset($item->source) ? trim((string) $item->source) : '',
            ];
        }

        return $items;
    }

    /**
     * Effectue le GET HTTP du flux. Retourne null en cas d'erreur réseau/HTTP.
     */
    private function fetch(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; ATLEX-SportBot/1.0)',
            CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/xml, text/xml'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        return (string) $response;
    }
}
