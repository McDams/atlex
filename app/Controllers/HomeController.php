<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\Member;
use App\Models\NewsArticle;
use App\Models\Sponsor;

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
            'title'       => APP_NAME . ' — Là où l\'énergie devient passion',
            'latestNews'  => $news->latest(3),
            'upcoming'    => $events->upcoming(3),
            'photos'      => $gallery->published(null, 6),
            'sponsors'    => $sponsors->active(),
            'memberCount' => $members->countActive(),
            'eventCount'  => $events->countUpcoming(),
            'tickerNews'  => $news->latest(6),
        ]);
    }
}
