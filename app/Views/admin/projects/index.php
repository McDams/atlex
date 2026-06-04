<?php
/**
 * @var array<int,array<string,mixed>> $projects
 * @var array<string,int>              $stats
 */
$statusColors = [
    'planifie' => 'bg-white/10 text-white/60',
    'en_cours' => 'bg-blue-600/20 text-blue-300',
    'en_pause' => 'bg-yellow-600/20 text-yellow-300',
    'termine'  => 'bg-green-600/20 text-green-300',
    'annule'   => 'bg-atlex-red/20 text-red-300',
];
$cards = [
    'en_cours' => 'En cours',
    'planifie' => 'Planifiés',
    'termine'  => 'Terminés',
];
?>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
        <div class="font-bebas text-4xl text-atlex-red leading-none"><?= e(array_sum($stats)) ?></div>
        <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Projets</div>
    </div>
    <?php foreach ($cards as $key => $label): ?>
        <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
            <div class="font-bebas text-4xl leading-none"><?= e($stats[$key] ?? 0) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1"><?= e($label) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="flex items-center justify-between mb-6">
    <p class="text-white/50 text-sm font-montserrat">Gestion interne des projets de l'association.</p>
    <a href="<?= url('/admin/projets/nouveau') ?>" class="btn-atlex text-sm">+ Nouveau projet</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Projet</th>
                <th class="px-5 py-3">Discipline</th>
                <th class="px-5 py-3">Échéance</th>
                <th class="px-5 py-3 text-right">Budget visé</th>
                <th class="px-5 py-3 text-right">Financé</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr><td colspan="7" class="px-5 py-6 text-white/40">Aucun projet. Créez le premier.</td></tr>
            <?php else: ?>
                <?php foreach ($projects as $p): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold">
                            <?= e($p['title']) ?>
                            <span class="block text-white/40 text-xs font-normal">
                                <?php if (!empty($p['theme'])): ?><?= e($p['theme']) ?><?php endif; ?>
                                <?php if (!empty($p['lead'])): ?><?= !empty($p['theme']) ? ' · ' : '' ?><?= e($p['lead']) ?><?php endif; ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-white/60"><?= e(discipline_label($p['discipline'])) ?></td>
                        <td class="px-5 py-3 text-white/60 whitespace-nowrap"><?= e(format_date_fr($p['end_date'] ?? null)) ?: '—' ?></td>
                        <td class="px-5 py-3 text-right text-white/70 whitespace-nowrap"><?= e(format_fcfa($p['budget_target'])) ?></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap min-w-[140px]">
                            <span class="text-green-300"><?= e(format_fcfa($p['funding_obtained'])) ?></span>
                            <?php if ((int) $p['funding_count'] > 0): ?><span class="block text-white/30 text-xs"><?= e($p['funding_count']) ?> financement(s)</span><?php endif; ?>
                            <?php $tgt = (float) $p['budget_target']; if ($tgt > 0): ?>
                                <?php $pp = min(100, (int) round((float) $p['funding_obtained'] / $tgt * 100)); ?>
                                <div class="h-1.5 rounded-full bg-white/10 overflow-hidden mt-1.5">
                                    <div class="h-full bg-green-500" style="width: <?= e($pp) ?>%"></div>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-1 rounded <?= $statusColors[$p['status']] ?? 'bg-white/10' ?>"><?= e(project_status_label($p['status'])) ?></span></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/projets/' . $p['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/projets/' . $p['id']) ?>" class="inline" data-confirm="Supprimer ce projet ?">
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
