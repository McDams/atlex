<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FundingLead;
use App\Models\FundingSource;

/**
 * Veille de financements : interroge les sources curées (flux RSS ou requêtes
 * Google Actualités ciblées), parse les résultats et enregistre les nouvelles
 * opportunités dans la file de veille (funding_leads), sans doublon.
 *
 * Réutilise la même technique que GoogleNewsService (curl + parsing RSS).
 */
final class FundingWatchService
{
    /** Gabarit de requête Google Actualités (région Bénin, français, 60 derniers jours). */
    private const GNEWS_TEMPLATE =
        'https://news.google.com/rss/search?q=%s+when:60d&hl=fr&gl=BJ&ceid=BJ:fr';

    private FundingSource $sources;

    private FundingLead $leads;

    public function __construct()
    {
        $this->sources = new FundingSource();
        $this->leads = new FundingLead();
    }

    /**
     * Rafraîchit toutes les sources actives.
     *
     * @return array{sources:int,fetched:int,new:int} bilan d'exécution
     */
    public function refresh(): array
    {
        $sources = $this->sources->active();
        $fetched = 0;
        $new = 0;

        foreach ($sources as $source) {
            $items = $this->fetchSource($source);
            if ($items === null) {
                continue;
            }

            $fetched += count($items);
            foreach ($items as $item) {
                if ($item['url'] === '' || $item['title'] === '') {
                    continue;
                }
                $created = $this->leads->insertIfNew([
                    'source_id'    => (int) $source['id'],
                    'title'        => $item['title'],
                    'url'          => $item['url'],
                    'summary'      => $item['summary'],
                    'source_name'  => $item['source'] !== '' ? $item['source'] : $source['name'],
                    'published_at' => $item['published_at'],
                ]);
                if ($created) {
                    $new++;
                }
            }

            $this->sources->touchFetched((int) $source['id']);
        }

        return ['sources' => count($sources), 'fetched' => $fetched, 'new' => $new];
    }

    /**
     * Résout l'URL du flux d'une source puis fetch + parse.
     *
     * @param array<string,mixed> $source
     * @return array<int,array{title:string,url:string,summary:string,source:string,published_at:?string}>|null
     */
    private function fetchSource(array $source): ?array
    {
        $type = (string) $source['type'];

        if ($type === 'google_news') {
            $query = trim((string) ($source['query'] ?? ''));
            if ($query === '') {
                return null;
            }
            $url = sprintf(self::GNEWS_TEMPLATE, rawurlencode($query));
        } else {
            $url = trim((string) ($source['url'] ?? ''));
            if ($url === '') {
                return null;
            }
        }

        $xml = $this->fetch($url);

        return $xml === null ? null : $this->parse($xml);
    }

    /**
     * Parse un flux RSS 2.0.
     *
     * @return array<int,array{title:string,url:string,summary:string,source:string,published_at:?string}>|null
     */
    private function parse(string $xml): ?array
    {
        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($feed === false || !isset($feed->channel->item)) {
            return null;
        }

        $items = [];
        foreach ($feed->channel->item as $item) {
            $pubDate = trim((string) ($item->pubDate ?? ''));
            $ts = $pubDate !== '' ? strtotime($pubDate) : false;

            $summary = trim(strip_tags((string) ($item->description ?? '')));

            $items[] = [
                'title'        => trim((string) $item->title),
                'url'          => trim((string) $item->link),
                'summary'      => mb_substr($summary, 0, 500),
                'source'       => isset($item->source) ? trim((string) $item->source) : '',
                'published_at' => $ts !== false ? date('Y-m-d H:i:s', $ts) : null,
            ];
        }

        return $items;
    }

    /**
     * GET HTTP du flux. Retourne null en cas d'erreur réseau/HTTP.
     */
    private function fetch(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
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
