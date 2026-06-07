<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Member;

/**
 * CRUD des membres de l'association.
 */
final class MembersController extends Controller
{
    private Member $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Member();
    }

    public function index(): void
    {
        $search = $this->input('q');
        $members = is_string($search) && $search !== ''
            ? $this->model->search($search)
            : $this->model->findAll('created_at', 'DESC');

        $this->render('admin/members/index', [
            'title'   => 'Membres — Espace SG',
            'members' => $members,
            'search'  => $search,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/members/create', [
            'title' => 'Nouveau membre — Espace SG',
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/membres/nouveau')) {
            return;
        }

        $this->model->create($data);
        flash('success', 'Membre ajouté avec succès.');
        $this->redirect('admin/membres');
    }

    public function edit(string $id): void
    {
        $member = $this->model->find((int) $id);
        if ($member === null) {
            flash('error', 'Membre introuvable.');
            $this->redirect('admin/membres');
        }

        $this->render('admin/members/edit', [
            'title'  => 'Modifier un membre — Espace SG',
            'member' => $member,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/membres/' . $id . '/edit')) {
            return;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Membre mis à jour.');
        $this->redirect('admin/membres');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Membre supprimé.');
        $this->redirect('admin/membres');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        return [
            'first_name' => $this->input('first_name'),
            'last_name'  => $this->input('last_name'),
            'email'      => $this->input('email') ?: null,
            'phone'      => $this->input('phone') ?: null,
            'age'        => $this->input('age') !== '' ? (int) $this->input('age') : null,
            'gender'     => $this->input('gender') ?: null,
            'role'       => $this->input('role') ?: null,
            'discipline' => $this->input('discipline') ?: null,
            'status'     => $this->input('status') ?: 'actif',
            'joined_at'  => $this->input('joined_at') ?: null,
            'notes'      => $this->input('notes') ?: null,
        ];
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
            'email'      => 'email|max:150',
            'role'       => 'required|in:benevole,bureau,president,secretaire_general,tresorier,responsable_technique,autre',
            'discipline' => 'in:football,basketball,handball,arts_martiaux',
            'status'     => 'in:actif,inactif,suspendu',
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
