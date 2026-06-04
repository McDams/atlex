<?php
/**
 * Formulaire partagé création / édition d'un projet.
 *
 * @var array<string,mixed>|null            $project null en création
 * @var array<int,array<string,mixed>>      $funding opportunités rattachées (édition)
 * @var bool                                $isEdit
 * @var string                              $action
 */
$p = $project ?? [];
$val = static function (string $key, string $default = '') use ($p, $isEdit) {
    return $isEdit ? (string) ($p[$key] ?? $default) : (string) old($key, $default);
};
$statuses = ['planifie' => 'Planifié', 'en_cours' => 'En cours', 'en_pause' => 'En pause', 'termine' => 'Terminé', 'annule' => 'Annulé'];
$fundingStatusColors = [
    'identifie' => 'bg-white/10 text-white/60', 'en_preparation' => 'bg-yellow-600/20 text-yellow-300',
    'depose' => 'bg-blue-600/20 text-blue-300', 'obtenu' => 'bg-green-600/20 text-green-300', 'refuse' => 'bg-atlex-red/20 text-red-300',
];
?>
<div class="max-w-3xl">
    <a href="<?= url('/admin/projets') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la liste</a>

    <form method="POST" action="<?= e($action) ?>" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div><label class="form-label">Intitulé du projet *</label><input name="title" required value="<?= e($val('title')) ?>" class="form-input w-full"></div>
        <div><label class="form-label">Description</label><textarea name="description" rows="4" class="form-input w-full"><?= e($val('description')) ?></textarea></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Discipline</label>
                <select name="discipline" class="form-input w-full">
                    <option value="tous" <?= $val('discipline', 'tous') === 'tous' ? 'selected' : '' ?>>Toutes disciplines</option>
                    <?php foreach (ATLEX_DISCIPLINES as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $val('discipline') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Thématique</label><input name="theme" value="<?= e($val('theme')) ?>" placeholder="ex : Insertion des jeunes par le sport" class="form-input w-full"></div>
            <div><label class="form-label">Statut</label>
                <select name="status" class="form-input w-full">
                    <?php foreach ($statuses as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $val('status', 'planifie') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Responsable</label><input name="lead" value="<?= e($val('lead')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Budget visé (FCFA)</label><input type="number" step="1" min="0" name="budget_target" value="<?= e($val('budget_target')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Date de début</label><input type="date" name="start_date" value="<?= e($val('start_date')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Échéance</label><input type="date" name="end_date" value="<?= e($val('end_date')) ?>" class="form-input w-full"></div>
        </div>

        <!-- Bénéficiaires & impact (redevabilité) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2"><label class="form-label">Bénéficiaires</label><input name="beneficiaries" value="<?= e($val('beneficiaries')) ?>" placeholder="ex : jeunes de 12 à 18 ans du quartier" class="form-input w-full"></div>
            <div><label class="form-label">Nombre de bénéficiaires</label><input type="number" min="0" name="beneficiary_count" value="<?= e($val('beneficiary_count')) ?>" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Impact attendu</label><textarea name="expected_impact" rows="3" class="form-input w-full"><?= e($val('expected_impact')) ?></textarea></div>

        <!-- Partenaires -->
        <div>
            <label class="form-label">Partenaires</label>
            <div data-repeat="pa" class="space-y-2">
                <?php foreach ($partners as $row): ?>
                    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
                        <input name="pa_name[]" value="<?= e($row['name'] ?? '') ?>" placeholder="Nom du partenaire" class="form-input col-span-6">
                        <input name="pa_role[]" value="<?= e($row['role'] ?? '') ?>" placeholder="Rôle (optionnel)" class="form-input col-span-5">
                        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="repeat-add btn-atlex-outline text-xs mt-2" data-target="pa">+ Ajouter un partenaire</button>
        </div>

        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>

    <?php if ($isEdit): ?>
        <?php
        $collected = 0.0;
        foreach ($funding as $fRow) {
            if (($fRow['status'] ?? '') === 'obtenu') {
                $collected += (float) $fRow['amount'];
            }
        }
        $target = (float) ($p['budget_target'] ?? 0);
        $pct = $target > 0 ? min(100, (int) round($collected / $target * 100)) : 0;
        ?>
        <!-- Financements rattachés -->
        <div class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bebas text-xl tracking-wider">Financements rattachés</h2>
                <a href="<?= url('/admin/financements/nouveau') ?>" class="btn-atlex-outline text-xs">+ Ajouter</a>
            </div>

            <!-- Progression de collecte -->
            <?php if ($target > 0): ?>
                <div class="mb-5">
                    <div class="flex justify-between text-sm font-montserrat mb-1">
                        <span class="text-green-300"><?= e(format_fcfa($collected)) ?> collectés</span>
                        <span class="text-white/50">Objectif <?= e(format_fcfa($target)) ?> · <?= e($pct) ?>%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-white/10 overflow-hidden">
                        <div class="h-full bg-green-500" style="width: <?= e($pct) ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (empty($funding)): ?>
                <p class="text-white/40 text-sm">Aucune opportunité de financement rattachée à ce projet.</p>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($funding as $f): ?>
                        <li class="flex items-center gap-3 bg-atlex-bg rounded-lg px-4 py-3">
                            <span class="flex-1 font-montserrat text-sm">
                                <?= e($f['name']) ?>
                                <?php if (!empty($f['funder'])): ?><span class="text-white/40">— <?= e($f['funder']) ?></span><?php endif; ?>
                            </span>
                            <span class="text-white/70 text-sm whitespace-nowrap"><?= e(format_fcfa($f['amount'])) ?></span>
                            <span class="text-xs px-2 py-1 rounded <?= $fundingStatusColors[$f['status']] ?? 'bg-white/10' ?>"><?= e(funding_status_label($f['status'])) ?></span>
                            <a href="<?= url('/admin/financements/' . $f['id'] . '/edit') ?>" class="text-atlex-beige hover:underline text-sm">Éditer</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Gabarit de ligne partenaire (cloné en JS) -->
<template id="tpl-pa">
    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
        <input name="pa_name[]" placeholder="Nom du partenaire" class="form-input col-span-6">
        <input name="pa_role[]" placeholder="Rôle (optionnel)" class="form-input col-span-5">
        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
    </div>
</template>

<script>
document.addEventListener('click', function (e) {
    const add = e.target.closest('.repeat-add');
    if (add) {
        const key = add.dataset.target;
        const tpl = document.getElementById('tpl-' + key);
        const container = document.querySelector('[data-repeat="' + key + '"]');
        if (tpl && container) { container.appendChild(tpl.content.cloneNode(true)); }
        return;
    }
    const remove = e.target.closest('.repeat-remove');
    if (remove) {
        const row = remove.closest('.repeat-row');
        if (row) { row.remove(); }
    }
});
</script>
