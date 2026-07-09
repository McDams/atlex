<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Models\ContactSubmission;
use App\Models\VolunteerRequest;

/**
 * Formulaires de contact et d'inscription.
 */
final class ContactController extends Controller
{
    /** Missions de bénévolat proposées (clé technique => libellé). */
    public const VOLUNTEER_MISSIONS = [
        'organisation_evenements' => 'Organisation d\'événements sportifs',
        'entrainement_jeunes'     => 'Encadrement / Entraînement des jeunes',
        'communication'           => 'Communication & Réseaux sociaux',
        'logistique'              => 'Logistique & Transport',
        'administration'          => 'Tâches administratives',
        'arbitrage'               => 'Arbitrage & Officiel de table',
        'accueil'                 => 'Accueil & Orientation',
        'collecte_fonds'          => 'Collecte de fonds & Partenariats',
        'sante'                   => 'Assistance médicale / Premiers secours',
        'autre'                   => 'Autre mission',
    ];

    /** Nombre maximal de soumissions par IP sur la fenêtre. */
    private const MAX_SUBMISSIONS = 8;

    /** Fenêtre de limitation (secondes) — 15 minutes. */
    private const SUBMISSION_WINDOW = 900;

    public function index(): void
    {
        $this->render('contact/index', [
            'title' => 'Contact et inscription | ' . APP_NAME,
            'description' => 'Contactez ATLEX - Sport à Cotonou pour une inscription, une demande d’information, du bénévolat ou un accompagnement sportif.',
            'canonical' => url('/contact'),
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
        ]);
    }

    /**
     * Traite un message de contact.
     */
    public function send(): void
    {
        $this->verifyCsrf();
        $this->guardSubmissionRate('contact');

        if ($this->isSpam()) {
            flash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');
            $this->redirect('contact#contact');
        }

        $data = [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'email'      => $this->input('email'),
            'phone'      => $this->input('phone'),
            'message'    => $this->input('message'),
            'consent'    => $this->input('consent'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:150',
            'message'    => 'required|min:10|max:2000',
            'consent'    => 'required',
        ], [
            'consent.required' => 'Vous devez accepter la politique de confidentialité.',
        ]);

        if ($validator->fails()) {
            set_old($data);
            set_errors($validator->errors());
            flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->redirect('contact#contact');
        }

        (new ContactSubmission())->create([
            'type'       => 'contact',
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'message'    => $data['message'],
            'is_read'    => 0,
        ]);

        $this->notify('Nouveau message de contact', $data);

        clear_old();
        clear_errors();
        flash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');
        $this->redirect('contact#contact');
    }

    /**
     * Traite une demande d'inscription.
     */
    public function register(): void
    {
        $this->verifyCsrf();
        $this->guardSubmissionRate('inscription');

        if ($this->isSpam()) {
            flash('success', 'Votre demande d\'inscription a été enregistrée. Bienvenue dans la famille ATLEX !');
            $this->redirect('contact#inscription');
        }

        $data = [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'email'      => $this->input('email'),
            'phone'      => $this->input('phone'),
            'age'        => $this->input('age'),
            'gender'     => $this->input('gender'),
            'discipline' => $this->input('discipline'),
            'message'    => $this->input('message'),
            'consent'    => $this->input('consent'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:150',
            'phone'      => 'required|max:30',
            'discipline' => 'required|in:football,basketball,handball,arts_martiaux',
            'consent'    => 'required',
        ], [
            'consent.required' => 'Vous devez accepter la politique de confidentialité.',
        ]);

        if ($validator->fails()) {
            set_old($data);
            set_errors($validator->errors());
            flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->redirect('contact#inscription');
        }

        (new ContactSubmission())->create([
            'type'       => 'inscription',
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'age'        => $data['age'] !== '' ? (int) $data['age'] : null,
            'gender'     => in_array($data['gender'], ['M', 'F', 'Autre'], true) ? $data['gender'] : null,
            'discipline' => $data['discipline'],
            'message'    => $data['message'],
            'status'     => 'nouveau',
            'is_read'    => 0,
        ]);

        $this->notify('Nouvelle demande d\'inscription', $data);
        $this->acknowledgeRegistration($data);

        clear_old();
        clear_errors();
        flash('success', 'Votre demande d\'inscription a été enregistrée. Vous recevrez un email de confirmation après validation.');
        $this->redirect('contact#inscription');
    }

    /**
     * Traite une candidature bénévole.
     */
    public function submitVolunteer(): void
    {
        $this->verifyCsrf();
        $this->guardSubmissionRate('benevol');

        if ($this->isSpam()) {
            flash('success', 'Merci ! Votre candidature a bien été reçue. Nous vous contacterons prochainement.');
            $this->redirect('contact#benevol');
        }

        $rawMissions = $_POST['missions'] ?? [];
        $missions = [];

        if (is_array($rawMissions)) {
            foreach ($rawMissions as $mission) {
                if (is_string($mission) && isset(self::VOLUNTEER_MISSIONS[$mission])) {
                    $missions[] = $mission;
                }
            }
        }

        $data = [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'phone'      => $this->input('phone'),
            'email'      => $this->input('email'),
            'message'    => $this->input('message'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'phone'      => 'required|max:30',
            'email'      => 'email|max:150',
        ]);

        $errors = $validator->errors();

        if ($missions === []) {
            $errors['missions'][] = 'Veuillez sélectionner au moins une mission.';
        }

        if ($errors !== []) {
            set_old($data + ['missions' => $missions]);
            set_errors($errors);
            flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->redirect('contact#benevol');
        }

        (new VolunteerRequest())->create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'] !== '' ? $data['email'] : null,
            'missions'   => json_encode($missions, JSON_UNESCAPED_UNICODE),
            'message'    => $data['message'] !== '' ? $data['message'] : null,
            'status'     => 'nouveau',
        ]);

        $labels = array_map(static fn (string $m): string => self::VOLUNTEER_MISSIONS[$m], $missions);
        $this->notify('Nouvelle candidature bénévole', $data + ['missions' => implode(', ', $labels)]);

        clear_old();
        clear_errors();
        flash('success', 'Merci ! Votre candidature a bien été reçue. Nous vous contacterons prochainement.');
        $this->redirect('contact#benevol');
    }

    /**
     * Limite le nombre de soumissions par IP (anti-spam / anti-flood),
     * en complément du honeypot. Redirige avec un message si le seuil est atteint.
     */
    private function guardSubmissionRate(string $anchor): void
    {
        $limiter = new RateLimiter();
        $key = 'form:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if ($limiter->tooManyAttempts($key, self::MAX_SUBMISSIONS)) {
            $minutes = (int) ceil($limiter->availableIn($key) / 60);
            flash('error', "Trop d'envois en peu de temps. Réessayez dans {$minutes} minute(s).");
            $this->redirect('contact#' . $anchor);
        }

        $limiter->hit($key, self::SUBMISSION_WINDOW);
    }

    /**
     * Détecte un envoi automatisé via le champ-piège (honeypot).
     * Un humain ne remplit jamais ce champ caché ; un bot, souvent.
     */
    private function isSpam(): bool
    {
        return (string) $this->input('website', '') !== '';
    }

    /**
     * Envoie un accusé de réception au candidat (demande en cours d'étude).
     *
     * @param array<string,mixed> $data
     */
    private function acknowledgeRegistration(array $data): void
    {
        $email = (string) ($data['email'] ?? '');
        if ($email === '') {
            return;
        }

        $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $body = email_template(
            'Demande d\'inscription bien reçue',
            '<p>Bonjour <strong>' . e($name) . '</strong>,</p>'
            . '<p>Nous avons bien reçu votre demande d\'inscription en <strong>'
            . e(discipline_label($data['discipline'] ?? null)) . '</strong>.</p>'
            . '<p>Notre équipe va l\'étudier et reviendra vers vous très prochainement '
            . 'avec un email de confirmation.</p>'
            . '<p>Sportivement,<br>Le Secrétariat Général — ATLEX - Sport</p>'
        );

        (new Mailer())->send($email, 'Demande d\'inscription reçue — ATLEX - Sport', $body, $name);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function notify(string $subject, array $data): void
    {
        $body = '<h2>' . e($subject) . '</h2><ul>';

        foreach ($data as $key => $value) {
            $body .= '<li><strong>' . e($key) . ' :</strong> ' . e((string) $value) . '</li>';
        }

        $body .= '</ul>';

        (new Mailer())->send('contact@atlex-sport.com', $subject, $body);
    }
}