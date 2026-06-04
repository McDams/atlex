<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Validator;
use App\Models\Athlete;
use RuntimeException;

/**
 * CRUD des profils numériques d'athlètes (photo + palmarès, résultats, vidéos).
 */
final class AthletesController extends Controller
{
    private Athlete $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Athlete();
    }

    public function index(): void
    {
        $this->render('admin/athletes/index', [
            'title'    => 'Athlètes — Espace SG',
            'athletes' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/athletes/create', [
            'title'   => 'Nouvel athlète — Espace SG',
            'athlete' => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/athletes/nouveau')) {
            return;
        }

        $data['slug']  = $this->model->uniqueSlug($data['first_name'], $data['last_name']);
        $data['photo'] = $this->handleUpload();

        $id = $this->model->create($data);
        $this->model->syncRelations($id, ...$this->relations());

        flash('success', 'Athlète ajouté avec succès.');
        $this->redirect('admin/athletes');
    }

    public function edit(string $id): void
    {
        $athlete = $this->model->findWithRelations((int) $id);
        if ($athlete === null) {
            flash('error', 'Athlète introuvable.');
            $this->redirect('admin/athletes');
        }

        $this->render('admin/athletes/edit', [
            'title'   => 'Modifier un athlète — Espace SG',
            'athlete' => $athlete,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        // Bascule rapide de l'état de publication.
        if ($this->input('toggle') === '1') {
            $this->model->togglePublished((int) $id);
            flash('success', 'Statut de publication mis à jour.');
            $this->redirect('admin/athletes');
        }

        $existing = $this->model->find((int) $id);
        if ($existing === null) {
            flash('error', 'Athlète introuvable.');
            $this->redirect('admin/athletes');
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/athletes/' . $id . '/edit')) {
            return;
        }

        $data['slug'] = $this->model->uniqueSlug($data['first_name'], $data['last_name'], (int) $id);

        $uploaded = $this->handleUpload();
        if ($uploaded !== null) {
            $data['photo'] = $uploaded;
        }

        $this->model->update((int) $id, $data);
        $this->model->syncRelations((int) $id, ...$this->relations());

        flash('success', 'Athlète mis à jour.');
        $this->redirect('admin/athletes');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id); // collections supprimées en cascade (FK)
        flash('success', 'Athlète supprimé.');
        $this->redirect('admin/athletes');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        return [
            'first_name'   => (string) $this->input('first_name'),
            'last_name'    => (string) $this->input('last_name'),
            'discipline'   => $this->input('discipline') ?: null,
            'category'     => $this->input('category') ?: null,
            'ranking'      => $this->input('ranking') ?: null,
            'bio'          => $this->input('bio') ?: null,
            'is_published' => $this->input('is_published') ? 1 : 0,
            'sort_order'   => $this->input('sort_order') !== '' ? (int) $this->input('sort_order') : 0,
        ];
    }

    /**
     * Normalise les collections liées issues des tableaux du formulaire.
     *
     * @return array{0:array<int,array<string,?string>>,1:array<int,array<string,?string>>,2:array<int,array<string,?string>>}
     */
    private function relations(): array
    {
        $achievements = $this->rows([
            'year'     => 'ach_year',
            'title'    => 'ach_title',
            'position' => 'ach_position',
        ]);

        $results = $this->rows([
            'result_date' => 'res_date',
            'competition' => 'res_competition',
            'result'      => 'res_result',
        ]);

        $videos = $this->rows([
            'title' => 'vid_title',
            'url'   => 'vid_url',
        ]);

        return [$achievements, $results, $videos];
    }

    /**
     * Recompose des lignes à partir de tableaux POST parallèles.
     *
     * @param array<string,string> $map champ logique => nom du tableau POST
     * @return array<int,array<string,?string>>
     */
    private function rows(array $map): array
    {
        $columns = [];
        $maxLen = 0;

        foreach ($map as $field => $postName) {
            $values = $_POST[$postName] ?? [];
            $values = is_array($values) ? array_values($values) : [];
            $columns[$field] = $values;
            $maxLen = max($maxLen, count($values));
        }

        $rows = [];
        for ($i = 0; $i < $maxLen; $i++) {
            $row = [];
            foreach (array_keys($map) as $field) {
                $value = $columns[$field][$i] ?? '';
                $row[$field] = is_string($value) ? trim($value) : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['photo']['name'])) {
            return null;
        }

        try {
            $uploader = new FileUpload(ROOT . '/public/uploads');
            $result = $uploader->store($_FILES['photo']);
            return 'uploads/' . $result['filename'];
        } catch (RuntimeException $e) {
            flash('error', 'Photo : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'discipline' => 'required|in:football,basketball,handball,arts_martiaux',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect($redirectPath);
        }

        clear_old();
        return true;
    }
}
