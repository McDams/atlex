<?php
/**
 * @var array<string,mixed> $category
 */
$iconOptions = [
    'basketball'    => 'Basket-ball',
    'handball'      => 'Handball',
    'arts-martiaux' => 'Arts Martiaux',
    'trophy'        => 'Trophée / Compétition',
    'academique'    => 'Formation / Académique',
    'social'        => 'Social / Communauté',
];
?>
<div class="max-w-xl">
    <a href="<?= url('/admin/evenements/categories') ?>"
       class="text-white/50 text-sm hover:text-white font-montserrat">← Retour aux catégories</a>

    <h2 class="font-bebas text-3xl tracking-wider mt-4 mb-6">Modifier la catégorie</h2>

    <form method="POST" action="<?= url('/admin/evenements/categories/' . $category['id']) ?>"
          class="bg-atlex-dark rounded-xl border border-white/5 p-6 space-y-4">
        <?= csrf_field() ?><?= method_field('PUT') ?>

        <!-- Aperçu -->
        <div class="flex items-center gap-4 mb-2 p-4 bg-white/5 rounded-lg">
            <div id="preview-circle" class="w-14 h-14 rounded-full flex-shrink-0" style="background-color: <?= e($category['color']) ?>;"></div>
            <div>
                <p id="preview-name" class="font-bebas text-xl"><?= e($category['name']) ?></p>
                <p id="preview-desc" class="text-white/40 text-xs font-montserrat"><?= e($category['description']) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="form-label">Nom *</label>
                <input name="name" id="inp-name" required value="<?= e($category['name']) ?>"
                       class="form-input w-full" oninput="document.getElementById('preview-name').textContent=this.value">
            </div>
            <div>
                <label class="form-label">Slug *</label>
                <input name="slug" required value="<?= e($category['slug']) ?>" class="form-input w-full">
            </div>
            <div>
                <label class="form-label">Couleur *</label>
                <input type="color" name="color" id="inp-color" value="<?= e($category['color']) ?>"
                       class="form-input w-full h-10 p-1 cursor-pointer"
                       oninput="document.getElementById('preview-circle').style.backgroundColor=this.value">
            </div>
            <div class="col-span-2">
                <label class="form-label">Description</label>
                <input name="description" id="inp-desc" value="<?= e($category['description']) ?>"
                       class="form-input w-full"
                       oninput="document.getElementById('preview-desc').textContent=this.value">
            </div>
            <div>
                <label class="form-label">Icône</label>
                <select name="icon" class="form-input w-full">
                    <?php foreach ($iconOptions as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $category['icon'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="sort_order" value="<?= (int)$category['sort_order'] ?>" min="0" class="form-input w-full">
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-montserrat">
            <input type="checkbox" name="is_active" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
            Catégorie active
        </label>

        <button type="submit" class="btn-atlex w-full">Enregistrer les modifications</button>
    </form>
</div>
