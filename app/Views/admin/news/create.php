<?php
$categories = [
    'general' => 'Général',
    'resultat' => 'Résultat',
    'recrutement' => 'Recrutement',
    'evenement' => 'Événement',
    'partenariat' => 'Partenariat',
    'rapport' => "Rapports d'activité",
    'coupe du monde' => 'Coupe du monde'
];
?>

<div class="max-w-3xl">
    <a href="<?= url('/admin/actualites') ?>" class="text-white/50 text-sm hover:text-white">← Retour</a>

    <form method="POST"
          action="<?= url('/admin/actualites') ?>"
          enctype="multipart/form-data"
          class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="form-label">Titre *</label>
            <input name="title" required value="<?= e(old('title')) ?>" class="form-input w-full">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Catégorie</label>
                <select name="category" class="form-input w-full">
                    <?php foreach ($categories as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= old('category') === $k ? 'selected' : '' ?>>
                            <?= e($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Date de publication</label>
                <input type="date"
                       name="published_at"
                       value="<?= e(old('published_at', date('Y-m-d'))) ?>"
                       class="form-input w-full">
            </div>

            <div class="sm:col-span-2">
                <label class="form-label">Image de couverture</label>
                <input type="file" name="cover_image" accept="image/*" class="form-input w-full">
            </div>
        </div>

        <div>
            <label class="form-label">Extrait</label>
            <textarea name="excerpt" rows="2" class="form-input w-full"><?= e(old('excerpt')) ?></textarea>
        </div>

        <div>
            <label class="form-label">Contenu</label>
            <textarea
                name="content"
                id="article-content"
                rows="12"
                class="form-input w-full"
            ><?= e(old('content')) ?></textarea>
            <p class="text-white/40 text-xs mt-2">
                Mise en forme disponible : titres, paragraphes, gras, italique, souligné, alignements, listes, liens, tableaux, citations et images.
            </p>
        </div>

        <label class="flex items-center gap-2 text-sm text-white/80">
            <input type="checkbox" name="is_published" value="1" <?= old('is_published') ? 'checked' : '' ?>>
            Publier immédiatement
        </label>

        <button type="submit" class="btn-atlex">Créer l'article</button>
    </form>
</div>