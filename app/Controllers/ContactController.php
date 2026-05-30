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

        $data = [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'email'      => $this->input('email'),
            'phone'      => $this->input('phone'),
            'message'    => $this->input('message'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:150',
            'message'    => 'required|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
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
        flash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');
        $this->redirect('contact#contact');
    }

    /**
     * Traite une demande d'inscription.
     */
    public function register(): void
    {
        $this->verifyCsrf();

        $data = [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'email'      => $this->input('email'),
            'phone'      => $this->input('phone'),
            'age'        => $this->input('age'),
            'gender'     => $this->input('gender'),
            'discipline' => $this->input('discipline'),
            'message'    => $this->input('message'),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:150',
            'phone'      => 'required|max:30',
            'discipline' => 'required|in:football,basketball,handball,arts_martiaux',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
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
            'is_read'    => 0,
        ]);

        $this->notify('Nouvelle demande d\'inscription', $data);

        clear_old();
        flash('success', 'Votre demande d\'inscription a été enregistrée. Bienvenue dans la famille ATLÉX !');
        $this->redirect('contact#inscription');
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
