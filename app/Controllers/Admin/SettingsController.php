<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use PDO;

/**
 * Paramètres de l'espace d'administration :
 * profil de l'utilisateur connecté, mot de passe et paramètres du site.
 */
final class SettingsController extends Controller
{
    /** Clés de paramètres gérées par la section « Paramètres du site ». */
    private const SITE_KEYS = [
        'site_name',
        'site_description',
        'contact_email',
        'contact_phone',
        'contact_address',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'maintenance_mode',
    ];

    private User $users;
    private PDO $db;

    public function __construct()
    {
        Auth::requireAuth();
        $this->users = new User();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $user = $this->users->find((int) Auth::id());

        $this->render('admin/settings/index', [
            'title'    => 'Paramètres — Espace SG',
            'user'     => $user ?? ['name' => '', 'email' => ''],
            'settings' => $this->loadSiteSettings(),
        ], 'layouts/admin');
    }

    public function updateProfile(): void
    {
        $this->verifyCsrf();

        $id = (int) Auth::id();
        $name = (string) $this->input('name');
        $email = (string) $this->input('email');

        if ($name === '') {
            flash('error', 'Le nom complet est obligatoire.');
            $this->redirect('admin/settings');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            flash('error', 'Adresse email invalide.');
            $this->redirect('admin/settings');
        }

        $existing = $this->users->findByEmail($email);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            flash('error', 'Cette adresse email est déjà utilisée par un autre compte.');
            $this->redirect('admin/settings');
        }

        $this->users->update($id, ['name' => $name, 'email' => $email]);

        // Maintient la session cohérente avec les nouvelles valeurs.
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_email'] = $email;

        flash('success', 'Profil mis à jour.');
        $this->redirect('admin/settings');
    }

    public function updatePassword(): void
    {
        $this->verifyCsrf();

        $id = (int) Auth::id();
        $current = (string) $this->input('current_password');
        $new = (string) $this->input('new_password');
        $confirm = (string) $this->input('confirm_password');

        $user = $this->users->find($id);
        if ($user === null || !password_verify($current, (string) $user['password'])) {
            flash('error', 'Le mot de passe actuel est incorrect.');
            $this->redirect('admin/settings');
        }

        if (strlen($new) < 8) {
            flash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            $this->redirect('admin/settings');
        }

        if ($new !== $confirm) {
            flash('error', 'La confirmation ne correspond pas au nouveau mot de passe.');
            $this->redirect('admin/settings');
        }

        $this->users->update($id, [
            'password' => password_hash($new, PASSWORD_BCRYPT),
        ]);

        flash('success', 'Mot de passe mis à jour.');
        $this->redirect('admin/settings');
    }

    public function updateSite(): void
    {
        $this->verifyCsrf();

        $stmt = $this->db->prepare(
            'INSERT INTO site_settings (setting_key, setting_value)
             VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = :value2'
        );

        foreach (self::SITE_KEYS as $key) {
            if ($key === 'maintenance_mode') {
                $value = $this->input('maintenance_mode') ? '1' : '0';
            } else {
                $value = (string) $this->input($key);
            }

            $stmt->execute([
                'key'    => $key,
                'value'  => $value,
                'value2' => $value,
            ]);
        }

        flash('success', 'Paramètres du site enregistrés.');
        $this->redirect('admin/settings');
    }

    /**
     * Charge tous les paramètres du site sous forme clé => valeur.
     *
     * @return array<string,string>
     */
    private function loadSiteSettings(): array
    {
        $rows = $this->db
            ->query('SELECT setting_key, setting_value FROM site_settings')
            ->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = (string) $row['setting_value'];
        }

        // Garantit que chaque clé attendue existe pour la vue.
        foreach (self::SITE_KEYS as $key) {
            $settings[$key] ??= '';
        }

        return $settings;
    }
}
