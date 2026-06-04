<?php
/**
 * @var array<int,array<string,mixed>> $opportunities
 * @var array{count:int,obtained:float,pipeline:float,by_status:array<string,int>} $dashboard
 * @var array<int,array<string,mixed>> $projects
 * @var string|null $filterStatus
 * @var int|null    $filterProject
 */
$statusColors = [
    'identifie' => 'bg-white/10 text-white/60', 'en_preparation' => 'bg-yellow-600/20 text-yellow-300',
    'depose' => 'bg-blue-600/20 text-blue-300', 'obtenu' => 'bg-green-600/20 text-green-300', 'refuse' => 'bg-atlex-red/20 text-red-300',
];
$statusFilters = ['identifie', 'en_preparation', 'depose', 'obtenu', 'refuse'];
?>
<!-- Indicateurs -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
        <div class="font-bebas text-3xl text-green-300 leading-none"><?= e(format_fcfa($dashboard['obtained'])) ?></div>
        <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Financements obtenus</div>
    </div>
    <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
        <div class="font-bebas text-3xl text-atlex-beige leading-none"><?= e(format_fcfa($dashboard['pipeline'])) ?></div>
        <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">En cours (pipeline)</div>
    </div>
    <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
        <div class="font-bebas text-3xl text-atlex-red leading-none"><?= e($dashboard['count']) ?></div>
        <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Opportunités suivies</div>
    </div>
</div>

<!-- Filtres + action -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <form method="GET" action="<?= url('/admin/financements') ?>" class="flex flex-wrap gap-2">
        <select name="status" class="form-input text-sm" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <?php foreach ($statusFilters as $s): ?>
                <option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e(funding_status_label($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="projet" class="form-input text-sm" onchange="this.form.submit()">
            <option value="">Tous les projets</option>
            <?php foreach ($projects as $proj): ?>
                <option value="<?= e($proj['id']) ?>" <?= $filterProject === (int) $proj['id'] ? 'selected' : '' ?>><?= e($proj['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn-atlex-outline text-sm">Filtrer</button></noscript>
    </form>
    <a href="<?= url('/admin/financements/nouveau') ?>" class="btn-atlex text-sm">+ Nouvelle opportunité</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Opportunité</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Projet</th>
                <th class="px-5 py-3">Échéance</th>
                <th class="px-5 py-3 text-right">Montant</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($opportunities)): ?>
                <tr><td colspan="7" class="px-5 py-6 text-white/40">Aucune opportunité de financement.</td></tr>
            <?php else: ?>
                <?php foreach ($opportunities as $f): ?>
                    <?php
                    $late = !empty($f['deadline']) && $f['deadline'] < date('Y-m-d')
                        && !in_array($f['status'], ['obtenu', 'refuse'], true);
                    ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold">
                            <?php if (!empty($f['application_url'])): ?>
                                <a href="<?= e($f['application_url']) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-atlex-beige"><?= e($f['name']) ?> ↗</a>
                            <?php else: ?>
                                <?= e($f['name']) ?>
                            <?php endif; ?>
                            <?php if (!empty($f['funder'])): ?><span class="block text-white/40 text-xs font-normal"><?= e($f['funder']) ?></span><?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-white/60"><?= e(funding_type_label($f['type'])) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($f['project_title'] ?: '—') ?></td>
                        <td class="px-5 py-3 whitespace-nowrap <?= $late ? 'text-red-300' : 'text-white/60' ?>">
                            <?= e(format_date_fr($f['deadline'] ?? null)) ?: '—' ?><?= $late ? ' ⚠' : '' ?>
                        </td>
                        <td class="px-5 py-3 text-right text-white/70 whitespace-nowrap"><?= e(format_fcfa($f['amount'])) ?></td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-1 rounded <?= $statusColors[$f['status']] ?? 'bg-white/10' ?>"><?= e(funding_status_label($f['status'])) ?></span></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/financements/' . $f['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/financements/' . $f['id']) ?>" class="inline" data-confirm="Supprimer cette opportunité ?">
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
