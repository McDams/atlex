<?php
/**
 * @var array<int,array<string,mixed>> $releases
 * @var array<int,array<string,mixed>> $kit
 * @var array<int,array<string,mixed>> $coverage
 * @var array<string,string|null> $contact
 */
$kitCategories = ['logo' => 'Logos', 'charte' => 'Charte graphique', 'photo' => 'Photos officielles', 'dossier' => 'Dossiers', 'autre' => 'Autres'];
?>
<p class="text-white/50 text-sm font-montserrat mb-6">
    Gestion de l'espace presse public : <a href="<?= url('/centre-media') ?>" target="_blank" class="text-atlex-beige hover:underline">voir la page Centre média →</a>
</p>

<!-- Communiqués -->
<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
        <h2 class="font-bebas text-xl tracking-wider">Communiqués de presse</h2>
        <a href="<?= url('/admin/media/communiques/nouveau') ?>" class="btn-atlex text-sm">+ Nouveau communiqué</a>
    </div>
    <table class="w-full text-sm">
        <tbody>
            <?php if (empty($releases)): ?>
                <tr><td class="px-6 py-4 text-white/40">Aucun communiqué.</td></tr>
            <?php else: ?>
                <?php foreach ($releases as $r): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-6 py-3 font-montserrat font-semibold">
                            <?= e($r['title']) ?>
                            <span class="block text-white/40 text-xs font-normal"><?= e(format_date_fr($r['published_at'] ?? $r['created_at'])) ?><?php if (!empty($r['reference'])): ?> · <?= e($r['reference']) ?><?php endif; ?><?php if (!empty($r['file'])): ?> · PDF<?php endif; ?></span>
                        </td>
                        <td class="px-6 py-3">
                            <form method="POST" action="<?= url('/admin/media/communiques/' . $r['id']) ?>" class="inline">
                                <?= csrf_field() ?><?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $r['is_published'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>"><?= $r['is_published'] ? 'Publié' : 'Brouillon' ?></button>
                            </form>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/media/communiques/' . $r['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/media/communiques/' . $r['id']) ?>" class="inline" data-confirm="Supprimer ce communiqué ?">
                                <?= csrf_field() ?><?= method_field('DELETE') ?>
                                <button type="submit" class="text-atlex-red hover:underline ml-3">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Kit presse -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <h2 class="font-bebas text-xl tracking-wider mb-4">Kit presse</h2>
        <?php if (!empty($kit)): ?>
            <ul class="space-y-2 mb-5">
                <?php foreach ($kit as $item): ?>
                    <li class="flex items-center gap-3 bg-atlex-bg rounded-lg px-4 py-2.5">
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10 text-white/60 whitespace-nowrap"><?= e($kitCategories[$item['category']] ?? $item['category']) ?></span>
                        <a href="<?= url($item['file']) ?>" target="_blank" class="flex-1 min-w-0 font-montserrat text-sm hover:text-atlex-beige truncate"><?= e($item['title']) ?></a>
                        <form method="POST" action="<?= url('/admin/media/kit/' . $item['id']) ?>" data-confirm="Supprimer cette ressource ?">
                            <?= csrf_field() ?><?= method_field('DELETE') ?>
                            <button type="submit" class="text-atlex-red text-lg leading-none" aria-label="Supprimer">×</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-white/40 text-sm mb-5">Aucune ressource.</p>
        <?php endif; ?>
        <form method="POST" action="<?= url('/admin/media/kit') ?>" enctype="multipart/form-data" class="space-y-3 border-t border-white/5 pt-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-2">
                <input name="title" required placeholder="Titre *" class="form-input text-sm">
                <select name="category" class="form-input text-sm">
                    <?php foreach ($kitCategories as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </div>
            <input name="description" placeholder="Description (optionnel)" class="form-input text-sm w-full">
            <input type="file" name="file" required class="form-input text-sm w-full">
            <button type="submit" class="btn-atlex-outline text-sm">Ajouter au kit</button>
        </form>
    </div>

    <!-- Revue de presse -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <h2 class="font-bebas text-xl tracking-wider mb-4">Revue de presse</h2>
        <?php if (!empty($coverage)): ?>
            <ul class="space-y-2 mb-5">
                <?php foreach ($coverage as $c): ?>
                    <li class="flex items-center gap-3 bg-atlex-bg rounded-lg px-4 py-2.5">
                        <span class="flex-1 min-w-0">
                            <?php if ($link = safe_url($c['url'])): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" class="font-montserrat text-sm hover:text-atlex-beige truncate"><?= e($c['title']) ?> ↗</a>
                            <?php else: ?>
                                <span class="font-montserrat text-sm"><?= e($c['title']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($c['media_name'])): ?><span class="block text-white/40 text-xs"><?= e($c['media_name']) ?></span><?php endif; ?>
                        </span>
                        <form method="POST" action="<?= url('/admin/media/revue/' . $c['id']) ?>" data-confirm="Supprimer cet article ?">
                            <?= csrf_field() ?><?= method_field('DELETE') ?>
                            <button type="submit" class="text-atlex-red text-lg leading-none" aria-label="Supprimer">×</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-white/40 text-sm mb-5">Aucun article.</p>
        <?php endif; ?>
        <form method="POST" action="<?= url('/admin/media/revue') ?>" class="space-y-3 border-t border-white/5 pt-4">
            <?= csrf_field() ?>
            <input name="title" required placeholder="Titre de l'article *" class="form-input text-sm w-full">
            <div class="grid grid-cols-2 gap-2">
                <input name="media_name" placeholder="Média" class="form-input text-sm">
                <input type="date" name="published_date" class="form-input text-sm">
            </div>
            <input type="url" name="url" required placeholder="https://… *" class="form-input text-sm w-full">
            <button type="submit" class="btn-atlex-outline text-sm">Ajouter à la revue</button>
        </form>
    </div>
</div>

<!-- Contact presse -->
<div class="bg-atlex-dark rounded-xl border border-white/5 p-6 max-w-2xl">
    <h2 class="font-bebas text-xl tracking-wider mb-4">Contact presse</h2>
    <form method="POST" action="<?= url('/admin/media/contact') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        <?= csrf_field() ?>
        <div><label class="form-label">Référent</label><input name="press_contact_name" value="<?= e($contact['press_contact_name'] ?? '') ?>" class="form-input w-full"></div>
        <div><label class="form-label">Email</label><input type="email" name="press_contact_email" value="<?= e($contact['press_contact_email'] ?? '') ?>" class="form-input w-full"></div>
        <div><label class="form-label">Téléphone</label><input name="press_contact_phone" value="<?= e($contact['press_contact_phone'] ?? '') ?>" class="form-input w-full"></div>
        <div class="sm:col-span-3"><button type="submit" class="btn-atlex text-sm">Enregistrer le contact</button></div>
    </form>
</div>
