<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;

/**
 * Présentation des disciplines / clubs.
 */
final class ClubsController extends Controller
{
    /**
     * Données statiques des disciplines (complétées par les stats DB).
     *
     * @return array<string,array<string,mixed>>
     */
    private function disciplines(): array
    {
        return [
            'football' => [
                'slug' => 'football',
                'name' => 'Football',
                'image' => 'images/hero-bg.png',
                'tagline' => 'Le cœur battant de l\'association',
                'description' => 'Notre section football rassemble joueurs et passionnés autour du sport roi. Entraînements techniques, matchs et tournois rythment la saison.',
                'schedule' => ['Lundi 17h-19h', 'Mercredi 17h-19h', 'Samedi 9h-12h'],
                'coach' => 'Coach principal : ATLEX Staff',
            ],
            'basketball' => [
                'slug' => 'basketball',
                'name' => 'Basketball',
                'image' => 'images/basket-hero.png',
                'tagline' => 'Vitesse, adresse et collectif',
                'description' => 'La section basketball développe l\'esprit d\'équipe et le dépassement de soi. Du mini-basket aux compétitions seniors.',
                'schedule' => ['Mardi 18h-20h', 'Jeudi 18h-20h', 'Samedi 14h-16h'],
                'coach' => 'Coach principal : ATLEX Staff',
            ],
            'handball' => [
                'slug' => 'handball',
                'name' => 'Handball',
                'image' => 'images/handball-hero.png',
                'tagline' => 'Intensité et stratégie',
                'description' => 'Le handball ATLEX privilégie l\'engagement physique et la cohésion. Une discipline complète pour tous les âges.',
                'schedule' => ['Lundi 18h-20h', 'Vendredi 18h-20h'],
                'coach' => 'Coach principal : ATLEX Staff',
            ],
            'arts_martiaux' => [
                'slug' => 'arts-martiaux',
                'key' => 'arts_martiaux',
                'name' => 'Arts Martiaux',
                'image' => 'images/martial-arts-hero.png',
                'tagline' => 'Discipline, respect, maîtrise',
                'description' => 'Notre section arts martiaux enseigne la maîtrise de soi, la concentration et le respect. Self-défense et compétition.',
                'schedule' => ['Mercredi 18h-20h', 'Samedi 10h-12h'],
                'coach' => 'Coach principal : ATLEX Staff',
            ],
        ];
    }

    public function index(): void
    {
        $stats = (new Member())->statsByDiscipline();
        $disciplines = $this->disciplines();

        foreach ($disciplines as $key => &$d) {
            $statKey = $d['key'] ?? $key;
            $d['member_count'] = $stats[$statKey] ?? 0;
        }
        unset($d);

        $this->render('clubs/index', [
            'title' => 'Disciplines sportives | ' . APP_NAME,
            'description' => 'Découvrez les disciplines sportives proposées par ATLEX - Sport à Cotonou : football, basketball, handball et arts martiaux.',
            'canonical' => url('/clubs'),
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'disciplines' => $disciplines,
        ]);
    }

    public function show(string $slug): void
    {
        $disciplines = $this->disciplines();
        $discipline = null;

        foreach ($disciplines as $key => $d) {
            if ($d['slug'] === $slug) {
                $discipline = $d;
                $discipline['key'] = $d['key'] ?? $key;
                break;
            }
        }

        if ($discipline === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'Discipline introuvable | ' . APP_NAME,
                'description' => 'La discipline demandée est introuvable.',
                'canonical' => url('/clubs'),
                'ogImage' => 'images/hero-bg.png',
                'ogType' => 'website',
                'metaRobots' => 'noindex, nofollow',
            ]);
            return;
        }

        $discipline['member_count'] = (new Member())->statsByDiscipline()[$discipline['key']] ?? 0;

        $description = $discipline['description']
            . ' Rejoignez la section '
            . $discipline['name']
            . ' de '
            . APP_NAME
            . ' à Cotonou.';

        $this->render('clubs/show', [
            'title' => $discipline['name'] . ' à Cotonou | ' . APP_NAME,
            'description' => $description,
            'canonical' => url('/clubs/' . $discipline['slug']),
            'ogImage' => $discipline['image'] ?? 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'discipline' => $discipline,
        ]);
    }
}