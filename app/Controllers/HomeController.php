<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\Member;
use App\Models\NewsArticle;
use App\Models\Sponsor;
use App\Services\GoogleNewsService;

/**
 * Page d'accueil publique.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $news = new NewsArticle();
        $events = new Event();
        $gallery = new GalleryPhoto();
        $sponsors = new Sponsor();
        $members = new Member();

        $this->render('home/index', [
            'title' => 'ATLEX - Sport | Association sportive à Cotonou',
            'description' => 'ATLEX - Sport est une association sportive à Cotonou dédiée à la jeunesse : football, basketball, handball, arts martiaux, compétitions et formation.',
            'canonical' => url('/'),
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'latestNews' => $news->latest(3),
            'upcoming' => $events->upcoming(3),
            'photos' => $gallery->published(null, 6),
            'sponsors' => $sponsors->active(),
            'memberCount' => $members->countActive(),
            'eventCount' => $events->countUpcoming(),
            'tickerNews' => $this->buildTicker($news),
        ]);
    }

    /**
     * @return array<int,array{title:string,url:string,external:bool}>
     */
    private function buildTicker(NewsArticle $news): array
    {
        $ticker = [];

        foreach ((new GoogleNewsService())->headlines(10) as $headline) {
            if ($headline['url'] === '') {
                continue;
            }

            $ticker[] = [
                'title' => $headline['title'],
                'url' => $headline['url'],
                'external' => true,
            ];
        }

        if ($ticker === []) {
            foreach ($news->latest(6) as $article) {
                $ticker[] = [
                    'title' => (string) $article['title'],
                    'url' => url('/actualites/' . $article['slug']),
                    'external' => false,
                ];
            }
        }

        return $ticker;
    }
}