<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
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
        foreach (['/', '/clubs', '/actualites', '/galerie', '/calendrier', '/a-propos', '/sponsors', '/contact', '/confidentialite'] as $path) {
            $urls[] = $base . $path;
        }
        foreach (['football', 'basketball', 'handball', 'arts-martiaux'] as $slug) {
            $urls[] = $base . '/clubs/' . $slug;
        }
        foreach ((new NewsArticle())->paginate(1, 200) as $article) {
            $urls[] = $base . '/actualites/' . $article['slug'];
        }

        header('Content-Type: application/xml; charset=UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . htmlspecialchars($url, ENT_XML1, 'UTF-8') . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        echo $xml;
        exit;
    }
}
