<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\NewsArticle;
use App\Models\SocialPost;
use Throwable;

/**
 * Génère des brouillons de posts réseaux sociaux à partir du contenu réel
 * déjà publié sur le site (actualités, événements à venir). Ne publie
 * jamais rien : crée uniquement des lignes social_posts en statut
 * « brouillon », à valider manuellement dans l'admin.
 *
 * Note : les résultats/palmarès d'athlètes ne sont pas encore une source
 * ici (accès relationnel plus complexe via Athlete::findWithRelations) —
 * extension naturelle à ajouter par la suite.
 */
final class SocialContentGeneratorService
{
    private const PLATFORMS = ['facebook', 'instagram', 'linkedin'];

    private NewsArticle $news;
    private Event $events;
    private SocialPost $posts;
    private AiContentService $ai;

    public function __construct(?AiContentService $ai = null)
    {
        $this->news = new NewsArticle();
        $this->events = new Event();
        $this->posts = new SocialPost();
        $this->ai = $ai ?? new AiContentService();
    }

    /**
     * @return array{created:int,skipped:int,errors:int}
     */
    public function generate(int $newsLimit = 5, int $eventsLimit = 5): array
    {
        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($this->news->latest($newsLimit) as $article) {
            $this->proposeAcrossPlatforms('news', (int) $article['id'], $this->newsContext($article), $stats);
        }

        foreach ($this->events->upcoming($eventsLimit) as $event) {
            $this->proposeAcrossPlatforms('event', (int) $event['id'], $this->eventContext($event), $stats);
        }

        return $stats;
    }

    /**
     * @param array{created:int,skipped:int,errors:int} $stats
     */
    private function proposeAcrossPlatforms(string $sourceType, int $sourceId, string $context, array &$stats): void
    {
        foreach (self::PLATFORMS as $platform) {
            if ($this->posts->alreadyProposed($sourceType, $sourceId, $platform)) {
                $stats['skipped']++;
                continue;
            }

            try {
                $text = $this->ai->draft($this->systemPromptFor($platform), $context);

                $this->posts->create([
                    'platform'     => $platform,
                    'status'       => 'brouillon',
                    'content_text' => $text,
                    'source_type'  => $sourceType,
                    'source_id'    => $sourceId,
                    'created_by'   => 'ia',
                ]);

                $stats['created']++;
            } catch (Throwable $e) {
                error_log('[SocialContentGenerator] ' . $e->getMessage());
                $stats['errors']++;
            }
        }
    }

    private function systemPromptFor(string $platform): string
    {
        $base = 'Tu rédiges des publications pour les réseaux sociaux de ATLEX - Sport, '
            . 'une association sportive à Cotonou (Bénin) : football, basketball, handball, arts martiaux. '
            . 'Ton chaleureux, fier, direct, jamais ampoulé, toujours en français. '
            . "N'invente aucun fait, chiffre, date ou citation : utilise uniquement les informations "
            . 'fournies ci-dessous. Donne uniquement le texte du post, sans titre ni guillemets englobants.';

        return match ($platform) {
            'instagram' => $base . ' Format Instagram : court (2 à 4 phrases), percutant, 3 à 5 hashtags pertinents à la fin.',
            'linkedin'  => $base . ' Format LinkedIn : ton plus institutionnel (orienté partenaires/sponsors), 4 à 6 phrases, emojis rares.',
            default     => $base . ' Format Facebook : 3 à 5 phrases, chaleureux et accessible, 1 à 2 emojis maximum, pas de mur de hashtags.',
        };
    }

    /**
     * @param array<string,mixed> $article
     */
    private function newsContext(array $article): string
    {
        $excerpt = trim((string) ($article['excerpt'] ?? ''));
        $content = trim(strip_tags((string) ($article['content'] ?? '')));

        $lines = ['Actualité ATLEX à annoncer :', 'Titre : ' . $article['title']];
        if ($excerpt !== '') {
            $lines[] = 'Résumé : ' . $excerpt;
        }
        if ($content !== '') {
            $lines[] = 'Contenu : ' . mb_substr($content, 0, 800);
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string,mixed> $event
     */
    private function eventContext(array $event): string
    {
        $lines = ['Événement ATLEX à venir :', 'Nom : ' . $event['title']];

        $start = (string) ($event['start_datetime'] ?? '');
        if ($start !== '') {
            $lines[] = 'Date : ' . $start;
        }
        if (!empty($event['location'])) {
            $lines[] = 'Lieu : ' . $event['location'];
        }
        if (!empty($event['description'])) {
            $lines[] = 'Description : ' . mb_substr(strip_tags((string) $event['description']), 0, 500);
        }

        return implode("\n", $lines);
    }
}
