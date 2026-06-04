<?php
/**
 * @var array<string,mixed>|null $release
 * @var bool   $isEdit
 * @var string $action
 */
$r = $release ?? [];
$val = static function (string $key, string $default = '') use ($r, $isEdit) {
    return $isEdit ? (string) ($r[$key] ?? $default) : (string) old($key, $default);
};
$pubDate = '';
if ($isEdit && !empty($r['published_at'])) {
    $pubDate = date('Y-m-d', strtotime((string) $r['published_at']));
} elseif (!$isEdit) {
    $pubDate = (string) old('published_at', date('Y-m-d'));
}
$isPublished = $isEdit ? (int) ($r['is_published'] ?? 0) === 1 : false;
?>
<div class="max-w-3xl">
    <a href="<?= url('/admin/media') ?>" class="text-white/50 text-sm hover:text-white">← Retour au Centre média</a>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

        <div><label class="form-label">Titre *</label><input name="title" required value="<?= e($val('title')) ?>" class="form-input w-full"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Référence</label><input name="reference" value="<?= e($val('reference')) ?>" placeholder="ex : CP-2026-001" class="form-input w-full"></div>
            <div><label class="form-label">Date de publication</label><input type="date" name="published_at" value="<?= e($pubDate) ?>" class="form-input w-full"></div>
        </div>

        <div><label class="form-label">Chapô / extrait</label><textarea name="excerpt" rows="2" class="form-input w-full"><?= e($val('excerpt')) ?></textarea></div>
        <div><label class="form-label">Contenu</label><textarea name="content" rows="10" class="form-input w-full"><?= e($val('content')) ?></textarea></div>

        <div>
            <label class="form-label">Communiqué PDF</label>
            <?php if ($isEdit && !empty($r['file'])): ?>
                <p class="text-white/50 text-xs mb-1"><a href="<?= url($r['file']) ?>" target="_blank" class="text-atlex-beige hover:underline">Fichier actuel</a> — laissez vide pour le conserver.</p>
            <?php endif; ?>
            <input type="file" name="file" accept=".pdf,.doc,.docx" class="form-input w-full">
        </div>

        <label class="flex items-center gap-2 font-montserrat text-sm">
            <input type="checkbox" name="is_published" value="1" <?= $isPublished ? 'checked' : '' ?> class="w-4 h-4 accent-atlex-red">
            Publier sur le Centre média
        </label>

        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>
</div>
