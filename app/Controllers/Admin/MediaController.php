<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Validator;
use App\Models\PressCoverage;
use App\Models\PressKitItem;
use App\Models\PressRelease;
use App\Models\Setting;
use RuntimeException;

/**
 * Gestion du Centre média (Espace SG) : communiqués, kit presse,
 * revue de presse et contact presse.
 */
final class MediaController extends Controller
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    /**
     * Tableau de bord du Centre média (kit, revue, contact + liste communiqués).
     */
    public function index(): void
    {
        $contact = (new Setting())->getMany([
            'press_contact_name',
            'press_contact_email',
            'press_contact_phone',
        ]);

        $this->render('admin/media/index', [
            'title'    => 'Centre média — Espace SG',
            'releases' => (new PressRelease())->allOrdered(),
            'kit'      => (new PressKitItem())->allOrdered(),
            'coverage' => (new PressCoverage())->allOrdered(),
            'contact'  => $contact,
        ], 'layouts/admin');
    }

    // -------------------------------------------------------------------------
    // Communiqués de presse
    // -------------------------------------------------------------------------

    public function createRelease(): void
    {
        $this->render('admin/media/release_form', [
            'title'   => 'Nouveau communiqué — Espace SG',
            'release' => null,
            'isEdit'  => false,
            'action'  => url('/admin/media/communiques'),
        ], 'layouts/admin');
    }

    public function storeRelease(): void
    {
        $this->verifyCsrf();
        $data = $this->releasePayload();

        if (!$this->validateRelease($data, 'admin/media/communiques/nouveau')) {
            return;
        }

        $model = new PressRelease();
        $data['slug'] = $model->uniqueSlug($data['title']);
        $data['file'] = $this->handleUpload('file', FileUpload::DOCUMENT_TYPES, 15_728_640);

        $model->create($data);
        flash('success', 'Communiqué créé.');
        $this->redirect('admin/media');
    }

    public function editRelease(string $id): void
    {
        $release = (new PressRelease())->find((int) $id);
        if ($release === null) {
            flash('error', 'Communiqué introuvable.');
            $this->redirect('admin/media');
        }

        $this->render('admin/media/release_form', [
            'title'   => 'Modifier un communiqué — Espace SG',
            'release' => $release,
            'isEdit'  => true,
            'action'  => url('/admin/media/communiques/' . $id),
        ], 'layouts/admin');
    }

    public function updateRelease(string $id): void
    {
        $this->verifyCsrf();
        $model = new PressRelease();

        if ($this->input('toggle') === '1') {
            $model->togglePublished((int) $id);
            flash('success', 'Statut de publication mis à jour.');
            $this->redirect('admin/media');
        }

        if ($model->find((int) $id) === null) {
            flash('error', 'Communiqué introuvable.');
            $this->redirect('admin/media');
        }

        $data = $this->releasePayload();
        if (!$this->validateRelease($data, 'admin/media/communiques/' . $id . '/edit')) {
            return;
        }

        $data['slug'] = $model->uniqueSlug($data['title'], (int) $id);
        $uploaded = $this->handleUpload('file', FileUpload::DOCUMENT_TYPES, 15_728_640);
        if ($uploaded !== null) {
            $data['file'] = $uploaded;
        }

        $model->update((int) $id, $data);
        flash('success', 'Communiqué mis à jour.');
        $this->redirect('admin/media');
    }

    public function destroyRelease(string $id): void
    {
        $this->verifyCsrf();
        (new PressRelease())->delete((int) $id);
        flash('success', 'Communiqué supprimé.');
        $this->redirect('admin/media');
    }

    // -------------------------------------------------------------------------
    // Kit presse
    // -------------------------------------------------------------------------

    public function storeKit(): void
    {
        $this->verifyCsrf();

        $data = [
            'title'       => (string) $this->input('title'),
            'description' => $this->input('description') ?: null,
            'category'    => $this->input('category') ?: 'autre',
            'sort_order'  => $this->input('sort_order') !== '' ? (int) $this->input('sort_order') : 0,
        ];

        $validator = new Validator($data);
        $validator->validate([
            'title'    => 'required|max:200',
            'category' => 'in:logo,charte,photo,dossier,autre',
        ]);
        if ($validator->fails()) {
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect('admin/media');
        }

        $allowed = array_merge(FileUpload::IMAGE_TYPES, FileUpload::DOCUMENT_TYPES);
        $file = $this->handleUpload('file', $allowed, 15_728_640);
        if ($file === null) {
            flash('error', 'Un fichier est requis pour une ressource du kit presse.');
            $this->redirect('admin/media');
        }
        $data['file'] = $file;

        (new PressKitItem())->create($data);
        flash('success', 'Ressource ajoutée au kit presse.');
        $this->redirect('admin/media');
    }

    public function destroyKit(string $id): void
    {
        $this->verifyCsrf();
        (new PressKitItem())->delete((int) $id);
        flash('success', 'Ressource supprimée.');
        $this->redirect('admin/media');
    }

    // -------------------------------------------------------------------------
    // Revue de presse
    // -------------------------------------------------------------------------

    public function storeCoverage(): void
    {
        $this->verifyCsrf();

        $data = [
            'title'          => (string) $this->input('title'),
            'media_name'     => $this->input('media_name') ?: null,
            'url'            => (string) $this->input('url'),
            'published_date' => $this->input('published_date') ?: null,
            'sort_order'     => $this->input('sort_order') !== '' ? (int) $this->input('sort_order') : 0,
        ];

        $validator = new Validator($data);
        $validator->validate(['title' => 'required|max:250', 'url' => 'required|max:500']);
        if ($validator->fails() || safe_url($data['url']) === null) {
            flash('error', $validator->fails()
                ? implode(' ', $validator->flatErrors())
                : "Le lien doit être une URL http(s) valide.");
            $this->redirect('admin/media');
        }

        (new PressCoverage())->create($data);
        flash('success', 'Article ajouté à la revue de presse.');
        $this->redirect('admin/media');
    }

    public function destroyCoverage(string $id): void
    {
        $this->verifyCsrf();
        (new PressCoverage())->delete((int) $id);
        flash('success', 'Article supprimé.');
        $this->redirect('admin/media');
    }

    // -------------------------------------------------------------------------
    // Contact presse
    // -------------------------------------------------------------------------

    public function saveContact(): void
    {
        $this->verifyCsrf();
        $setting = new Setting();
        $setting->set('press_contact_name', (string) $this->input('press_contact_name'));
        $setting->set('press_contact_email', (string) $this->input('press_contact_email'));
        $setting->set('press_contact_phone', (string) $this->input('press_contact_phone'));
        flash('success', 'Contact presse enregistré.');
        $this->redirect('admin/media');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    private function releasePayload(): array
    {
        $data = [
            'title'        => (string) $this->input('title'),
            'reference'    => $this->input('reference') ?: null,
            'excerpt'      => $this->input('excerpt') ?: null,
            'content'      => $this->input('content') ?: null,
            'is_published' => $this->input('is_published') ? 1 : 0,
        ];

        $date = trim((string) $this->input('published_at'));
        if ($date !== '') {
            $ts = strtotime($date);
            if ($ts !== false) {
                $data['published_at'] = date('Y-m-d H:i:s', $ts);
            }
        } elseif ($data['is_published']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validateRelease(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate(['title' => 'required|max:250']);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect($redirectPath);
        }

        clear_old();
        return true;
    }

    /**
     * @param array<string,string> $allowed
     */
    private function handleUpload(string $field, array $allowed, int $maxSize): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }

        try {
            $uploader = new FileUpload(ROOT . '/public/uploads', $allowed, $maxSize);
            $result = $uploader->store($_FILES[$field]);
            return 'uploads/' . $result['filename'];
        } catch (RuntimeException $e) {
            flash('error', 'Fichier : ' . $e->getMessage());
            return null;
        }
    }
}
