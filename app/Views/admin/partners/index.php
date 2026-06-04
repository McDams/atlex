<?php
/**
 * @var array<int,array<string,mixed>> $partners
 */
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <p class="text-white/50 text-sm font-montserrat">
        Affichés sur <a href="<?= url('/sponsors') ?>" class="text-atlex-beige hover:underline" target="_blank">la page Partenaires</a> publique.
    </p>
    <a href="<?= url('/admin/partenaires/nouveau') ?>" class="btn-atlex text-sm">+ Nouveau partenaire</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Partenaire</th>
                <th class="px-5 py-3">Niveau</th>
                <th class="px-5 py-3">Site web</th>
                <th class="px-5 py-3">Visible</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($partners)): ?>
                <tr><td colspan="5" class="px-5 py-6 text-white/40">Aucun partenaire. Ajoutez le premier.</td></tr>
            <?php else: ?>
                <?php foreach ($partners as $p): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($p['logo'])): ?>
                                    <img src="<?= url($p['logo']) ?>" alt="" class="w-10 h-10 rounded object-contain bg-white/5 p-1">
                                <?php else: ?>
                                    <span class="w-10 h-10 rounded bg-white/10 grid place-items-center font-bebas text-white/60"><?= e(strtoupper(mb_substr((string) $p['name'], 0, 1))) ?></span>
                                <?php endif; ?>
                                <span class="font-montserrat font-semibold"><?= e($p['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-white/60"><?= e(sponsor_tier_label($p['tier'])) ?></td>
                        <td class="px-5 py-3 text-white/60">
                            <?php if ($link = safe_url($p['website_url'])): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" class="text-atlex-beige hover:underline">Lien ↗</a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="<?= url('/admin/partenaires/' . $p['id']) ?>" class="inline">
                                <?= csrf_field() ?><?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $p['is_active'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>">
                                    <?= $p['is_active'] ? 'Visible' : 'Masqué' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/partenaires/' . $p['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/partenaires/' . $p['id']) ?>" class="inline" data-confirm="Supprimer ce partenaire ?">
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
