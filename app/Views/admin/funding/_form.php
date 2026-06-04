<?php
/**
 * Formulaire partagé création / édition d'une opportunité de financement.
 *
 * @var array<string,mixed>|null       $opportunity null en création
 * @var array<int,array<string,mixed>> $projects
 * @var bool                           $isEdit
 * @var string                         $action
 */
$f = $opportunity ?? [];
$val = static function (string $key, string $default = '') use ($f, $isEdit) {
    return $isEdit ? (string) ($f[$key] ?? $default) : (string) old($key, $default);
};
$types = ['subvention' => 'Subvention', 'appel_projet' => 'Appel à projets', 'sponsoring' => 'Sponsoring', 'crowdfunding' => 'Crowdfunding / dons communautaires', 'don' => 'Don', 'bourse' => 'Bourse', 'prix' => 'Prix', 'autre' => 'Autre'];
$statuses = ['identifie' => 'Identifié', 'en_preparation' => 'En préparation', 'depose' => 'Déposé', 'obtenu' => 'Obtenu', 'refuse' => 'Refusé'];
$currentProject = $val('project_id');
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/financements') ?>" class="text-white/50 text-sm hover:text-white">← Retour au suivi</a>

    <form method="POST" action="<?= e($action) ?>" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div><label class="form-label">Intitulé de l'opportunité *</label><input name="name" required value="<?= e($val('name')) ?>" placeholder="ex : Appel à projets jeunesse 2026" class="form-input w-full"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Bailleur / organisme</label><input name="funder" value="<?= e($val('funder')) ?>" placeholder="ex : Ministère des Sports" class="form-input w-full"></div>
            <div><label class="form-label">Type</label>
                <select name="type" class="form-input w-full">
                    <?php foreach ($types as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $val('type', 'subvention') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Montant (FCFA)</label><input type="number" step="1" min="0" name="amount" value="<?= e($val('amount')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Date limite</label><input type="date" name="deadline" value="<?= e($val('deadline')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Statut de candidature</label>
                <select name="status" class="form-input w-full">
                    <?php foreach ($statuses as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $val('status', 'identifie') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Projet rattaché</label>
                <select name="project_id" class="form-input w-full">
                    <option value="">— Aucun —</option>
                    <?php foreach ($projects as $proj): ?>
                        <option value="<?= e($proj['id']) ?>" <?= $currentProject === (string) $proj['id'] ? 'selected' : '' ?>><?= e($proj['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div><label class="form-label">Lien vers l'appel / le dossier</label><input type="url" name="application_url" value="<?= e($val('application_url')) ?>" placeholder="https://…" class="form-input w-full"></div>
        <div><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-input w-full"><?= e($val('notes')) ?></textarea></div>

        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>
</div>
