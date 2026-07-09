<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\NewsArticle;

/**
 * Génère dynamiquement le plan du site (sitemap.xml) pour le référencement.
 */
final class SitemapController extends Controller
{
    public function index(): void
    {
        $base = rtrim(APP_URL, '/');
        $urls = [];

        // Pages statiques publiques
        $staticPages = [
            '/',
            '/clubs',
            '/actualites',
            '/galerie',
            '/calendrier',
            '/sponsors',
            '/contact',
            '/politique-de-confidentialite',
        ];

        foreach ($staticPages as $path) {
            $urls[] = [
                'loc' => $base . $path,
                'lastmod' => null,
            ];
        }

        // Disciplines
        foreach (['football', 'basketball', 'handball', 'arts-martiaux'] as $slug) {
            $urls[] = [
                'loc' => $base . '/clubs/' . $slug,
                'lastmod' => null,
            ];
        }

        // Articles publiés
        foreach ((new NewsArticle())->latest(1000) as $article) {
            if (empty($article['slug'])) {
                continue;
            }

            $lastmod = null;

            if (!empty($article['published_at'])) {
                $timestamp = strtotime((string) $article['published_at']);
                if ($timestamp !== false) {
                    $lastmod = date('Y-m-d', $timestamp);
                }
            } elseif (!empty($article['created_at'])) {
                $timestamp = strtotime((string) $article['created_at']);
                if ($timestamp !== false) {
                    $lastmod = date('Y-m-d', $timestamp);
                }
            }

            $urls[] = [
                'loc' => $base . '/actualites/' . $article['slug'],
                'lastmod' => $lastmod,
            ];
        }

        // Événements publiés
        foreach ((new Event())->allOrdered() as $event) {
            if (empty($event['is_published']) || empty($event['slug'])) {
                continue;
            }

            $lastmod = null;

            if (!empty($event['start_datetime'])) {
                $timestamp = strtotime((string) $event['start_datetime']);
                if ($timestamp !== false) {
                    $lastmod = date('Y-m-d', $timestamp);
                }
            }

            $urls[] = [
                'loc' => $base . '/evenements/' . $event['slug'],
                'lastmod' => $lastmod,
            ];
        }

        // Athlètes publiés
        foreach ((new Athlete())->published() as $athlete) {
            if (empty($athlete['slug'])) {
                continue;
            }

            $urls[] = [
                'loc' => $base . '/athletes/' . $athlete['slug'],
                'lastmod' => null,
            ];
        }

        header('Content-Type: application/xml; charset=UTF-8');

        echo $this->buildXml($urls);
        exit;
    }

    /**
     * @param array<int,array{loc:string,lastmod:?string}> $urls
     */
    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";

            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}