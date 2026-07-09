<?php
/** @var array<string,mixed> $article */
$categories = [
    'general' => 'Général',
    'resultat' => 'Résultat',
    'recrutement' => 'Recrutement',
    'evenement' => 'Événement',
    'partenariat' => 'Partenariat',
    'rapport' => "Rapports d'activité",
    'coupe du monde' => 'Coupe du monde'
];

$pubDate = !empty($article['published_at'])
    ? date('Y-m-d', strtotime((string) $article['published_at']))
    : '';
?>

<div class="max-w-5xl">
    <a href="<?= url('/admin/actualites') ?>" class="text-white/50 text-sm hover:text-white font-montserrat">← Retour</a>

    <form method="POST"
          action="<?= url('/admin/actualites/' . $article['id']) ?>"
          enctype="multipart/form-data"
          class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-5">
        <?= csrf_field() ?>
        <?= method_field('PUT') ?>

        <div>
            <label class="form-label">Titre *</label>
            <input name="title" required value="<?= e((string) $article['title']) ?>" class="form-input w-full">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Catégorie</label>
                <select name="category" class="form-input w-full">
                    <?php foreach ($categories as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= (($article['category'] ?? '') === $k) ? 'selected' : '' ?>>
                            <?= e($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Date de publication</label>
                <input type="date" name="published_at" value="<?= e($pubDate) ?>" class="form-input w-full">
            </div>

            <div class="sm:col-span-2">
                <label class="form-label">Image de couverture</label>
                <input type="file" name="cover_image" accept="image/*" class="form-input w-full">
            </div>
        </div>

        <div>
            <label class="form-label">Extrait</label>
            <textarea name="excerpt" rows="3" class="form-input w-full"><?= e((string) ($article['excerpt'] ?? '')) ?></textarea>
        </div>

        <div>
            <label class="form-label">Contenu</label>
            <textarea
                name="content"
                id="article-content"
                rows="18"
                class="form-input w-full"
            ><?= e((string) ($article['content'] ?? '')) ?></textarea>

            <p class="text-xs text-white/40 mt-2 font-montserrat">
                Mise en forme disponible : titres, paragraphes, gras, italique, souligné, alignements, listes, liens, tableaux, citations et images.
            </p>
        </div>

        <label class="flex items-center gap-2 text-sm font-montserrat text-white/80">
            <input type="checkbox" name="is_published" value="1" <?= !empty($article['is_published']) ? 'checked' : '' ?>>
            Publié
        </label>

        <button type="submit" class="btn-atlex">Mettre à jour</button>
    </form>
</div>