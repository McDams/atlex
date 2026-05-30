<?php
/**
 * @var array<int,array<string,mixed>> $members
 * @var string|null $search
 */
$statusColors = ['actif' => 'bg-green-600/20 text-green-300', 'inactif' => 'bg-white/10 text-white/60', 'suspendu' => 'bg-atlex-red/20 text-red-300'];
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <form method="GET" action="<?= url('/admin/membres') ?>" class="flex gap-2">
        <input name="q" value="<?= e($search ?? '') ?>" placeholder="Rechercher un membre…" class="form-input">
        <button type="submit" class="btn-atlex-outline text-sm">Rechercher</button>
    </form>
    <a href="<?= url('/admin/membres/nouveau') ?>" class="btn-atlex text-sm">+ Nouveau membre</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Nom</th>
                <th class="px-5 py-3">Discipline</th>
                <th class="px-5 py-3">Contact</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr><td colspan="5" class="px-5 py-6 text-white/40">Aucun membre trouvé.</td></tr>
            <?php else: ?>
                <?php foreach ($members as $m): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($m['last_name']) ?> <?= e($m['first_name']) ?></td>
                        <td class="px-5 py-3"><?= e(discipline_label($m['discipline'])) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($m['email'] ?: $m['phone'] ?: '—') ?></td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-1 rounded <?= $statusColors[$m['status']] ?? 'bg-white/10' ?>"><?= e($m['status']) ?></span></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/membres/' . $m['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/membres/' . $m['id']) ?>" class="inline" data-confirm="Supprimer ce membre ?">
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
