<?php
/** @var array<int,array<string,mixed>> $articles */
?>
<div class="flex items-center justify-between mb-6">
    <p class="text-white/50 font-montserrat text-sm"><?= count($articles) ?> article(s)</p>
    <a href="<?= url('/admin/actualites/nouveau') ?>" class="btn-atlex text-sm">+ Nouvel article</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Titre</th>
                <th class="px-5 py-3">Catégorie</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles)): ?>
                <tr><td colspan="5" class="px-5 py-6 text-white/40">Aucun article.</td></tr>
            <?php else: ?>
                <?php foreach ($articles as $a): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($a['title']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($a['category']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e(format_date_fr($a['published_at'] ?? $a['created_at'])) ?></td>
                        <td class="px-5 py-3">
                            <form method="POST" action="<?= url('/admin/actualites/' . $a['id']) ?>" class="inline js-toggle-publish" data-id="<?= (int) $a['id'] ?>">
                                <?= csrf_field() ?><?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $a['is_published'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>">
                                    <?= $a['is_published'] ? 'Publié' : 'Brouillon' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/actualites/' . $a['slug']) ?>" target="_blank" class="text-atlex-beige hover:underline">Voir</a>
                            <form method="POST" action="<?= url('/admin/actualites/' . $a['id']) ?>" class="inline" data-confirm="Supprimer cet article ?">
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
