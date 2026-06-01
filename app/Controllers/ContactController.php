<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Validator;
use App\Models\ContactSubmission;

/**
 * Formulaires de contact et d'inscription.
 */
final class ContactController extends Controller
{
    public function index(): void
    {
        $this->render('contact/index', [
            'title' => 'Contact & Inscription — ' . APP_NAME,
        ]);
    }

    /**
     * Traite un message de contact.
     */
    public function send(): void
    {
        $this->verifyCsrf();

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

        (new Mailer())->send('contact@atlexsport.com', $subject, $body);
    }
}
