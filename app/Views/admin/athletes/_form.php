<?php
/**
 * Formulaire partagé création / édition d'un athlète.
 *
 * @var array<string,mixed>|null $athlete  null en création
 * @var bool                     $isEdit
 * @var string                   $action    URL de soumission
 */
$a = $athlete ?? [];

/** Valeur d'un champ principal : enregistrement existant, sinon old(), sinon défaut. */
$val = static function (string $key, string $default = '') use ($a, $isEdit) {
    if ($isEdit) {
        return (string) ($a[$key] ?? $default);
    }
    return (string) old($key, $default);
};

$achievements = $a['achievements'] ?? [];
$results      = $a['results'] ?? [];
$videos       = $a['videos'] ?? [];
$isPublished  = $isEdit ? (int) ($a['is_published'] ?? 1) === 1 : true;
?>
<div class="max-w-3xl">
    <a href="<?= url('/admin/athletes') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la liste</a>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="mt-4 space-y-6">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <!-- Identité -->
        <fieldset class="bg-atlex-dark rounded-xl border border-white/5 p-6 space-y-4">
            <legend class="font-bebas text-xl tracking-wider px-2">Identité</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="form-label">Prénom *</label><input name="first_name" required value="<?= e($val('first_name')) ?>" class="form-input w-full"></div>
                <div><label class="form-label">Nom *</label><input name="last_name" required value="<?= e($val('last_name')) ?>" class="form-input w-full"></div>

                <div><label class="form-label">Discipline *</label>
                    <select name="discipline" required class="form-input w-full">
                        <option value="">—</option>
                        <?php foreach (ATLEX_DISCIPLINES as $k => $label): ?>
                            <option value="<?= e($k) ?>" <?= $val('discipline') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Catégorie</label><input name="category" value="<?= e($val('category')) ?>" placeholder="Senior, U17, Ceinture noire…" class="form-input w-full"></div>

                <div class="sm:col-span-2"><label class="form-label">Classement actuel</label><input name="ranking" value="<?= e($val('ranking')) ?>" placeholder="ex : 3e au championnat national 2025" class="form-input w-full"></div>
            </div>

            <div>
                <label class="form-label">Photo</label>
                <?php if ($isEdit && !empty($a['photo'])): ?>
                    <img src="<?= url($a['photo']) ?>" alt="" class="w-24 h-24 rounded-lg object-cover mb-2">
                    <p class="text-white/40 text-xs mb-1">Laissez vide pour conserver la photo actuelle.</p>
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" class="form-input w-full">
            </div>

            <div><label class="form-label">Biographie</label><textarea name="bio" rows="4" class="form-input w-full"><?= e($val('bio')) ?></textarea></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="form-label">Ordre d'affichage</label><input type="number" name="sort_order" value="<?= e($val('sort_order', '0')) ?>" class="form-input w-full"></div>
                <label class="flex items-center gap-2 mt-7 font-montserrat text-sm">
                    <input type="checkbox" name="is_published" value="1" <?= $isPublished ? 'checked' : '' ?> class="w-4 h-4 accent-atlex-red">
                    Publier sur le site public
                </label>
            </div>
        </fieldset>

        <!-- Palmarès -->
        <fieldset class="bg-atlex-dark rounded-xl border border-white/5 p-6">
            <legend class="font-bebas text-xl tracking-wider px-2">Palmarès</legend>
            <div data-repeat="ach" class="space-y-2">
                <?php foreach ($achievements as $row): ?>
                    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
                        <input name="ach_year[]" value="<?= e($row['year'] ?? '') ?>" placeholder="Année" class="form-input col-span-2">
                        <input name="ach_title[]" value="<?= e($row['title'] ?? '') ?>" placeholder="Titre / distinction" class="form-input col-span-6">
                        <input name="ach_position[]" value="<?= e($row['position'] ?? '') ?>" placeholder="Rang" class="form-input col-span-3">
                        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="repeat-add btn-atlex-outline text-xs mt-3" data-target="ach">+ Ajouter une ligne</button>
        </fieldset>

        <!-- Résultats -->
        <fieldset class="bg-atlex-dark rounded-xl border border-white/5 p-6">
            <legend class="font-bebas text-xl tracking-wider px-2">Résultats</legend>
            <div data-repeat="res" class="space-y-2">
                <?php foreach ($results as $row): ?>
                    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
                        <input type="date" name="res_date[]" value="<?= e($row['result_date'] ?? '') ?>" class="form-input col-span-3">
                        <input name="res_competition[]" value="<?= e($row['competition'] ?? '') ?>" placeholder="Compétition / rencontre" class="form-input col-span-5">
                        <input name="res_result[]" value="<?= e($row['result'] ?? '') ?>" placeholder="Résultat" class="form-input col-span-3">
                        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="repeat-add btn-atlex-outline text-xs mt-3" data-target="res">+ Ajouter une ligne</button>
        </fieldset>

        <!-- Vidéos -->
        <fieldset class="bg-atlex-dark rounded-xl border border-white/5 p-6">
            <legend class="font-bebas text-xl tracking-wider px-2">Vidéos</legend>
            <p class="text-white/40 text-xs mb-3">Collez un lien YouTube (les vidéos YouTube sont intégrées ; les autres liens s'affichent en bouton).</p>
            <div data-repeat="vid" class="space-y-2">
                <?php foreach ($videos as $row): ?>
                    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
                        <input name="vid_title[]" value="<?= e($row['title'] ?? '') ?>" placeholder="Titre" class="form-input col-span-4">
                        <input name="vid_url[]" value="<?= e($row['url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=…" class="form-input col-span-7">
                        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="repeat-add btn-atlex-outline text-xs mt-3" data-target="vid">+ Ajouter une ligne</button>
        </fieldset>

        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>
</div>

<!-- Gabarits de lignes (clonés en JS) -->
<template id="tpl-ach">
    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
        <input name="ach_year[]" placeholder="Année" class="form-input col-span-2">
        <input name="ach_title[]" placeholder="Titre / distinction" class="form-input col-span-6">
        <input name="ach_position[]" placeholder="Rang" class="form-input col-span-3">
        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
    </div>
</template>
<template id="tpl-res">
    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
        <input type="date" name="res_date[]" class="form-input col-span-3">
        <input name="res_competition[]" placeholder="Compétition / rencontre" class="form-input col-span-5">
        <input name="res_result[]" placeholder="Résultat" class="form-input col-span-3">
        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
    </div>
</template>
<template id="tpl-vid">
    <div class="repeat-row grid grid-cols-12 gap-2 items-center">
        <input name="vid_title[]" placeholder="Titre" class="form-input col-span-4">
        <input name="vid_url[]" placeholder="https://youtube.com/watch?v=…" class="form-input col-span-7">
        <button type="button" class="repeat-remove text-atlex-red text-xl col-span-1" aria-label="Retirer">×</button>
    </div>
</template>

<script nonce="<?= \App\Core\Security::nonce() ?>">
document.addEventListener('click', function (e) {
    const add = e.target.closest('.repeat-add');
    if (add) {
        const key = add.dataset.target;
        const tpl = document.getElementById('tpl-' + key);
        const container = document.querySelector('[data-repeat="' + key + '"]');
        if (tpl && container) {
            container.appendChild(tpl.content.cloneNode(true));
        }
        return;
    }
    const remove = e.target.closest('.repeat-remove');
    if (remove) {
        const row = remove.closest('.repeat-row');
        if (row) { row.remove(); }
    }
});
</script>
