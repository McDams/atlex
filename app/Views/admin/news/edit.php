<?php
/** @var array<string,mixed> $article */
$categories = ['general' => 'Général', 'resultat' => 'Résultat', 'recrutement' => 'Recrutement', 'evenement' => 'Événement', 'partenariat' => 'Partenariat'];
?>
<div class="max-w-3xl">
    <a href="<?= url('/admin/actualites') ?>" class="text-white/50 text-sm hover:text-white">← Retour</a>
    <form method="POST" action="<?= url('/admin/actualites/' . $article['id']) ?>" enctype="multipart/form-data" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?><?= method_field('PUT') ?>
        <div><label class="form-label">Titre *</label><input name="title" required value="<?= e($article['title']) ?>" class="form-input w-full"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Catégorie</label><select name="category" class="form-input w-full"><?php foreach ($categories as $k => $v): ?><option value="<?= e($k) ?>" <?= $article['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label">Image de couverture</label><input type="file" name="cover_image" accept="image/*" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Extrait</label><textarea name="excerpt" rows="2" class="form-input w-full"><?= e($article['excerpt']) ?></textarea></div>
        <div><label class="form-label">Contenu</label><textarea name="content" rows="10" class="form-input w-full"><?= e($article['content']) ?></textarea></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" <?= $article['is_published'] ? 'checked' : '' ?>> Publié</label>
        <button type="submit" class="btn-atlex">Mettre à jour</button>
    </form>
</div>
