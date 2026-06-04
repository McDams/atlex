<?php
/**
 * Formulaire partagé création / édition d'un partenaire.
 *
 * @var array<string,mixed>|null $partner null en création
 * @var bool                     $isEdit
 * @var string                   $action
 */
$p = $partner ?? [];
$val = static function (string $key, string $default = '') use ($p, $isEdit) {
    return $isEdit ? (string) ($p[$key] ?? $default) : (string) old($key, $default);
};
$tiers = ['officiel' => 'Partenaire officiel', 'associe' => 'Partenaire associé', 'media' => 'Partenaire média'];
$isActive = $isEdit ? (int) ($p['is_active'] ?? 1) === 1 : true;
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/partenaires') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la liste</a>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div><label class="form-label">Nom du partenaire *</label><input name="name" required value="<?= e($val('name')) ?>" class="form-input w-full"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Niveau</label>
                <select name="tier" class="form-input w-full">
                    <?php foreach ($tiers as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $val('tier', 'associe') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Ordre d'affichage</label><input type="number" name="sort_order" value="<?= e($val('sort_order', '0')) ?>" class="form-input w-full"></div>
        </div>

        <div><label class="form-label">Site web</label><input type="url" name="website_url" value="<?= e($val('website_url')) ?>" placeholder="https://…" class="form-input w-full"></div>

        <div>
            <label class="form-label">Logo</label>
            <?php if ($isEdit && !empty($p['logo'])): ?>
                <img src="<?= url($p['logo']) ?>" alt="" class="w-24 h-24 rounded-lg object-contain bg-white/5 p-2 mb-2">
                <p class="text-white/40 text-xs mb-1">Laissez vide pour conserver le logo actuel.</p>
            <?php endif; ?>
            <input type="file" name="logo" accept="image/*" class="form-input w-full">
        </div>

        <div><label class="form-label">Description</label><textarea name="description" rows="3" class="form-input w-full"><?= e($val('description')) ?></textarea></div>

        <label class="flex items-center gap-2 font-montserrat text-sm">
            <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> class="w-4 h-4 accent-atlex-red">
            Visible sur la page publique
        </label>

        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>
</div>
