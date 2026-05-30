<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Models\Document;
use RuntimeException;

/**
 * Gestion des documents internes du SG.
 */
final class DocumentsController extends Controller
{
    private Document $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Document();
    }

    public function index(): void
    {
        $this->render('admin/documents/index', [
            'title'     => 'Documents — Espace SG',
            'documents' => $this->model->allWithUploader(),
        ], 'layouts/admin');
    }

    public function upload(): void
    {
        $this->verifyCsrf();

        $title = $this->input('title');
        if (!is_string($title) || trim($title) === '') {
            flash('error', 'Le titre du document est obligatoire.');
            $this->redirect('admin/documents');
        }

        try {
            $uploader = new FileUpload(
                ROOT . '/storage/uploads',
                FileUpload::DOCUMENT_TYPES,
                20_971_520
            );
            $result = $uploader->store($_FILES['document'] ?? []);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('admin/documents');
        }

        $this->model->create([
            'title'       => $title,
            'filename'    => $result['filename'],
            'file_type'   => $result['mime'],
            'file_size'   => $result['size'],
            'category'    => $this->input('category') ?: 'autre',
            'uploaded_by' => Auth::id(),
        ]);

        flash('success', 'Document téléversé.');
        $this->redirect('admin/documents');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();

        $doc = $this->model->find((int) $id);
        if ($doc !== null) {
            $path = ROOT . '/storage/uploads/' . $doc['filename'];
            if (is_file($path)) {
                @unlink($path);
            }
            $this->model->delete((int) $id);
        }

        flash('success', 'Document supprimé.');
        $this->redirect('admin/documents');
    }
}
