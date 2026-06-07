<?php
/** @var array<int,array<string,mixed>> $categories */
$iconOptions = [
    'basketball'    => 'Basket-ball',
    'handball'      => 'Handball',
    'arts-martiaux' => 'Arts Martiaux',
    'trophy'        => 'Trophée / Compétition',
    'academique'    => 'Formation / Académique',
    'social'        => 'Social / Communauté',
];
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-bebas text-3xl tracking-wider">Catégories d'événements</h2>
        <p class="text-white/50 font-montserrat text-sm"><?= count($categories) ?> catégorie(s)</p>
    </div>
    <button onclick="document.getElementById('modal-new-cat').classList.remove('hidden')"
            class="btn-atlex text-sm">+ Nouvelle catégorie</button>
</div>

<!-- Grille de prévisualisation (style public) -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6 mb-10 bg-atlex-dark rounded-xl p-6 border border-white/5">
    <?php foreach ($categories as $cat): ?>
    <div class="flex flex-col items-center gap-2 text-center">
        <div class="w-16 h-16 rounded-full flex items-center justify-center <?= $cat['is_active'] ? '' : 'opacity-30' ?>"
             style="background-color: <?= e($cat['color']) ?>;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" class="w-7 h-7">
                <path d="M6 9H4a2 2 0 0 1-2-2V5h4M18 9h2a2 2 0 0 0 2-2V5h-4M6 2h12v7a6 6 0 0 1-12 0V2z"/><path d="M12 15v4M9 20h6"/>
            </svg>
        </div>
        <span class="font-montserrat font-bold text-xs text-white/90"><?= e($cat['name']) ?></span>
        <span class="text-white/30 text-xs"><?= (int)$cat['event_count'] ?> événement(s)</span>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tableau de gestion -->
<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Catégorie</th>
                <th class="px-5 py-3">Description</th>
                <th class="px-5 py-3">Couleur</th>
                <th class="px-5 py-3">Événements</th>
                <th class="px-5 py-3">Actif</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr><td colspan="6" class="px-5 py-6 text-white/40 font-montserrat">Aucune catégorie.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr class="border-b border-white/5 hover:bg-white/5">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex-shrink-0" style="background-color: <?= e($cat['color']) ?>;"></div>
                            <span class="font-montserrat font-semibold"><?= e($cat['name']) ?></span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-white/50 max-w-xs truncate"><?= e($cat['description']) ?></td>
                    <td class="px-5 py-3">
                        <span class="font-mono text-xs px-2 py-1 rounded bg-white/5"><?= e($cat['color']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-white/60"><?= (int)$cat['event_count'] ?></td>
                    <td class="px-5 py-3"><?= $cat['is_active'] ? '<span class="text-green-400">●</span>' : '<span class="text-white/30">○</span>' ?></td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="<?= url('/admin/evenements/categories/' . $cat['id'] . '/edit') ?>"
                           class="text-atlex-beige hover:underline font-montserrat text-xs">Éditer</a>
                        <form method="POST" action="<?= url('/admin/evenements/categories/' . $cat['id']) ?>"
                              class="inline" data-confirm="Supprimer cette catégorie ?">
                            <?= csrf_field() ?><?= method_field('DELETE') ?>
                            <button type="submit" class="text-atlex-red hover:underline font-montserrat text-xs ml-3">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal nouvelle catégorie -->
<div id="modal-new-cat" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="bg-[#0f1629] border border-white/10 rounded-2xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bebas text-2xl tracking-wider">Nouvelle catégorie</h3>
            <button onclick="document.getElementById('modal-new-cat').classList.add('hidden')"
                    class="text-white/40 hover:text-white text-xl">✕</button>
        </div>
        <form method="POST" action="<?= url('/admin/evenements/categories') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom *</label>
                    <input name="name" required class="form-input w-full" placeholder="Ex : Football">
                </div>
                <div>
                    <label class="form-label">Slug *</label>
                    <input name="slug" required class="form-input w-full" placeholder="ex : football">
                </div>
                <div>
                    <label class="form-label">Couleur *</label>
                    <input type="color" name="color" value="#E53935" class="form-input w-full h-10 p-1 cursor-pointer">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Description</label>
                    <input name="description" class="form-input w-full" placeholder="Courte description de la catégorie">
                </div>
                <div>
                    <label class="form-label">Icône</label>
                    <select name="icon" class="form-input w-full">
                        <?php foreach ($iconOptions as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="0" min="0" class="form-input w-full">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm font-montserrat">
                <input type="checkbox" name="is_active" value="1" checked> Catégorie active
            </label>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-atlex flex-1">Créer</button>
                <button type="button" onclick="document.getElementById('modal-new-cat').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-white/10 font-montserrat text-sm hover:bg-white/5">Annuler</button>
            </div>
        </form>
    </div>
</div>
