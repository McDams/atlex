<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\Social\FacebookPublisher;
use App\Services\Social\InstagramPublisher;
use App\Services\Social\LinkedInPublisher;
use App\Services\SocialContentGeneratorService;
use App\Services\SportsResultsService;
use RuntimeException;
use Throwable;

/**
 * File de brouillons de posts réseaux sociaux générés par l'IA — chaque
 * action de cette file (approuver, publier) est un clic humain explicite ;
 * rien ne part automatiquement.
 */
final class SocialMediaController extends Controller
{
    private SocialPost $posts;

    public function __construct()
    {
        Auth::requireAuth();
        $this->posts = new SocialPost();
    }

    public function index(): void
    {
        $status = $this->input('status') ?: 'brouillon';
        $platform = $this->input('platform');
        $platform = is_string($platform) && $platform !== '' ? $platform : null;

        $this->render('admin/social/index', [
            'title'          => 'Réseaux sociaux — Espace SG',
            'posts'          => $this->posts->filtered(is_string($status) ? $status : 'brouillon', $platform),
            'counts'         => [
                'brouillon' => $this->posts->countByStatus('brouillon'),
                'approuve'  => $this->posts->countByStatus('approuve'),
                'publie'    => $this->posts->countByStatus('publie'),
                'echec'     => $this->posts->countByStatus('echec'),
            ],
            'filterStatus'   => $status,
            'filterPlatform' => $platform,
        ], 'layouts/admin');
    }

    /**
     * Génère des brouillons à partir des actualités/événements récents.
     */
    public function generate(): void
    {
        $this->verifyCsrf();

        try {
            $report = (new SocialContentGeneratorService())->generate();
            flash('success', sprintf(
                '%d brouillon(s) généré(s), %d déjà proposé(s), %d erreur(s).',
                $report['created'],
                $report['skipped'],
                $report['errors']
            ));
        } catch (Throwable $e) {
            flash('error', 'Échec de la génération : ' . $e->getMessage());
        }

        $this->redirect('admin/social');
    }

    /**
     * Génère des brouillons de résumés à partir des matchs terminés.
     */
    public function generateMatches(): void
    {
        $this->verifyCsrf();

        $service = new SportsResultsService();
        if (!$service->isConfigured()) {
            flash('error', 'Clé Sofascore non configurée (SOFASCORE_API_KEY dans .env).');
            $this->redirect('admin/social');
        }

        try {
            $report = $service->checkFinishedMatches();
            flash('success', sprintf(
                '%d compétition(s) vérifiée(s), %d match(s) trouvé(s), %d brouillon(s) créé(s), %d article(s) rédigé(s).',
                $report['competitions'],
                $report['matches'],
                $report['created'],
                $report['articles']
            ));
        } catch (Throwable $e) {
            flash('error', 'Échec de la vérification des résultats : ' . $e->getMessage());
        }

        $this->redirect('admin/social');
    }

    /**
     * Modifie le texte/média d'un brouillon avant approbation.
     */
    public function update(string $id): void
    {
        $this->verifyCsrf();

        $text = trim((string) $this->input('content_text'));
        if ($text === '') {
            flash('error', 'Le texte du post ne peut pas être vide.');
            $this->redirect('admin/social');
        }

        $this->posts->update((int) $id, [
            'content_text' => $text,
            'media_path'   => trim((string) ($this->input('media_path') ?? '')) ?: null,
        ]);

        flash('success', 'Brouillon mis à jour.');
        $this->redirect('admin/social');
    }

    /**
     * Approuve un brouillon, avec programmation optionnelle.
     */
    public function approve(string $id): void
    {
        $this->verifyCsrf();

        $data = ['status' => 'approuve'];

        $scheduledAt = trim((string) ($this->input('scheduled_at') ?? ''));
        if ($scheduledAt !== '') {
            $ts = strtotime($scheduledAt);
            if ($ts !== false) {
                $data['scheduled_at'] = date('Y-m-d H:i:s', $ts);
            }
        }

        $this->posts->update((int) $id, $data);
        flash('success', 'Brouillon approuvé.');
        $this->redirect('admin/social');
    }

    /**
     * Publie réellement un post approuvé sur la plateforme cible.
     */
    public function publish(string $id): void
    {
        $this->verifyCsrf();

        $post = $this->posts->find((int) $id);
        if ($post === null) {
            flash('error', 'Post introuvable.');
            $this->redirect('admin/social');
        }

        $platform = (string) $post['platform'];
        $account = (new SocialAccount())->findByPlatform($platform);

        if ($account === null || !$account['is_active']) {
            flash('error', "Aucun compte {$platform} connecté ou actif. Configurez-le dans Comptes réseaux sociaux.");
            $this->redirect('admin/social');
        }

        try {
            $externalId = match ($platform) {
                'facebook'  => (new FacebookPublisher())->publish($post, $account),
                'instagram' => (new InstagramPublisher())->publish($post, $account),
                'linkedin'  => (new LinkedInPublisher())->publish($post, $account),
                default     => throw new RuntimeException('Plateforme inconnue.'),
            };

            $this->posts->markPublished((int) $id, $externalId);
            flash('success', 'Publié avec succès sur ' . ucfirst($platform) . '.');
        } catch (Throwable $e) {
            $this->posts->markFailed((int) $id, $e->getMessage());
            flash('error', 'Échec de la publication : ' . $e->getMessage());
        }

        $this->redirect('admin/social');
    }

    public function ignore(string $id): void
    {
        $this->verifyCsrf();
        $this->posts->setStatus((int) $id, 'ignore');
        flash('success', 'Brouillon ignoré.');
        $this->redirect('admin/social');
    }
}
