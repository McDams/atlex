<?php
/**
 * @var array<int,array<string,mixed>> $athletes
 */
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <p class="text-white/50 text-sm font-montserrat">
        Profils publics affichés sur <a href="<?= url('/athletes') ?>" class="text-atlex-beige hover:underline" target="_blank">la page Athlètes</a>.
    </p>
    <a href="<?= url('/admin/athletes/nouveau') ?>" class="btn-atlex text-sm">+ Nouvel athlète</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Athlète</th>
                <th class="px-5 py-3">Discipline</th>
                <th class="px-5 py-3">Classement</th>
                <th class="px-5 py-3">Publié</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($athletes)): ?>
                <tr><td colspan="5" class="px-5 py-6 text-white/40">Aucun athlète. Créez la première carte de présentation.</td></tr>
            <?php else: ?>
                <?php foreach ($athletes as $a): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($a['photo'])): ?>
                                    <img src="<?= url($a['photo']) ?>" alt="" class="w-9 h-9 rounded-full object-cover">
                                <?php else: ?>
                                    <span class="w-9 h-9 rounded-full bg-white/10 grid place-items-center font-bebas text-white/60"><?= e(strtoupper(mb_substr((string) $a['first_name'], 0, 1))) ?></span>
                                <?php endif; ?>
                                <span class="font-montserrat font-semibold"><?= e($a['last_name']) ?> <?= e($a['first_name']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3"><?= e(discipline_label($a['discipline'])) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($a['ranking'] ?: '—') ?></td>
                        <td class="px-5 py-3">
                            <form method="POST" action="<?= url('/admin/athletes/' . $a['id']) ?>" class="inline">
                                <?= csrf_field() ?><?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $a['is_published'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>">
                                    <?= $a['is_published'] ? 'En ligne' : 'Masqué' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/athletes/' . $a['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/athletes/' . $a['id']) ?>" class="inline" data-confirm="Supprimer cet athlète et son profil ?">
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
