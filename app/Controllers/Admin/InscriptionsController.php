<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\ContactSubmission;
use App\Models\Member;

/**
 * Traitement des demandes d'inscription soumises depuis le site public.
 *
 * Valider : crée le membre + envoie un email de confirmation au candidat.
 * Refuser : marque la demande refusée + envoie un email poli.
 */
final class InscriptionsController extends Controller
{
    private ContactSubmission $submissions;

    public function __construct()
    {
        Auth::requireAuth();
        $this->submissions = new ContactSubmission();
    }

    public function index(): void
    {
        $this->render('admin/inscriptions/index', [
            'title'   => 'Demandes d\'inscription — Espace SG',
            'pending' => $this->submissions->inscriptions('nouveau'),
            'valides' => $this->submissions->inscriptions('valide'),
            'refuses' => $this->submissions->inscriptions('refuse'),
        ], 'layouts/admin');
    }

    /**
     * Valide une demande : crée le membre et notifie le candidat.
     */
    public function approve(string $id): void
    {
        $this->verifyCsrf();
        $submission = $this->guardSubmission($id);

        (new Member())->create([
            'first_name' => $submission['first_name'],
            'last_name'  => $submission['last_name'],
            'email'      => $submission['email'] ?: null,
            'phone'      => $submission['phone'] ?: null,
            'age'        => $submission['age'] !== null ? (int) $submission['age'] : null,
            'gender'     => $submission['gender'] ?: null,
            'discipline' => $submission['discipline'] ?: null,
            'status'     => 'actif',
            'joined_at'  => date('Y-m-d'),
            'notes'      => 'Adhésion validée depuis le formulaire d\'inscription en ligne.',
        ]);

        $this->submissions->update((int) $id, [
            'status'       => 'valide',
            'processed_at' => date('Y-m-d H:i:s'),
            'is_read'      => 1,
        ]);

        $this->sendDecision($submission, true);

        flash('success', 'Demande validée : le membre a été créé et un email de confirmation envoyé.');
        $this->redirect('admin/inscriptions');
    }

    /**
     * Refuse une demande et notifie le candidat.
     */
    public function reject(string $id): void
    {
        $this->verifyCsrf();
        $submission = $this->guardSubmission($id);

        $this->submissions->update((int) $id, [
            'status'       => 'refuse',
            'processed_at' => date('Y-m-d H:i:s'),
            'is_read'      => 1,
        ]);

        $this->sendDecision($submission, false);

        flash('success', 'Demande refusée : un email a été envoyé au candidat.');
        $this->redirect('admin/inscriptions');
    }

    /**
     * Récupère une demande d'inscription encore en attente, ou redirige.
     *
     * @return array<string,mixed>
     */
    private function guardSubmission(string $id): array
    {
        $submission = $this->submissions->find((int) $id);

        if ($submission === null || ($submission['type'] ?? '') !== 'inscription') {
            flash('error', 'Demande d\'inscription introuvable.');
            $this->redirect('admin/inscriptions');
        }

        if (($submission['status'] ?? 'nouveau') !== 'nouveau') {
            flash('error', 'Cette demande a déjà été traitée.');
            $this->redirect('admin/inscriptions');
        }

        return $submission;
    }

    /**
     * Envoie l'email de décision (validation ou refus) au candidat.
     *
     * @param array<string,mixed> $submission
     */
    private function sendDecision(array $submission, bool $approved): void
    {
        $email = (string) ($submission['email'] ?? '');
        if ($email === '') {
            return;
        }

        $name       = trim(($submission['first_name'] ?? '') . ' ' . ($submission['last_name'] ?? ''));
        $discipline = discipline_label($submission['discipline'] ?? null);

        if ($approved) {
            $subject = 'Votre inscription est validée — ATLEX - Sport';
            $body = email_template(
                'Bienvenue dans la famille ATLEX - Sport ! 🎉',
                '<p>Bonjour <strong>' . e($name) . '</strong>,</p>'
                . '<p>Bonne nouvelle : votre demande d\'inscription en <strong>' . e($discipline)
                . '</strong> a bien été <strong>validée</strong>.</p>'
                . '<p>Notre équipe vous contactera très prochainement pour finaliser votre adhésion '
                . '(horaires d\'entraînement, documents à fournir et cotisation).</p>'
                . '<p>À très bientôt sur le terrain,<br>Le Secrétariat Général — ATLEX - Sport</p>'
            );
        } else {
            $subject = 'Suite à votre demande d\'inscription — ATLEX - Sport';
            $body = email_template(
                'À propos de votre demande d\'inscription',
                '<p>Bonjour <strong>' . e($name) . '</strong>,</p>'
                . '<p>Nous vous remercions de l\'intérêt que vous portez à ATLEX - Sport et de votre '
                . 'demande d\'inscription en ' . e($discipline) . '.</p>'
                . '<p>Après étude, nous ne sommes malheureusement pas en mesure d\'y donner une suite '
                . 'favorable pour le moment. N\'hésitez pas à nous recontacter ultérieurement.</p>'
                . '<p>Sportivement,<br>Le Secrétariat Général — ATLEX - Sport</p>'
            );
        }

        (new Mailer())->send($email, $subject, $body, $name);
    }
}
