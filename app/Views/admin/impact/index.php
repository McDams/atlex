<?php
/**
 * @var int   $beneficiaries
 * @var int   $activeMembers
 * @var int   $athleteCount
 * @var int   $projectOngoing
 * @var int   $projectDone
 * @var int   $projectTotal
 * @var float $fundingObtained
 * @var float $fundingPipeline
 * @var int   $partnerCount
 * @var int   $eventTotal
 * @var int   $reportCount
 * @var array<string,int> $membersByDiscipline
 * @var array<string,int> $athletesByDiscipline
 * @var array<string,int> $projectStatus
 * @var array<int,array<string,mixed>> $manual
 */
$nb = static fn ($v): string => number_format((float) $v, 0, ',', ' ');

$kpis = [
    ['label' => 'Bénéficiaires touchés', 'value' => $nb($beneficiaries), 'accent' => 'text-atlex-red'],
    ['label' => 'Membres actifs',        'value' => $nb($activeMembers), 'accent' => 'text-white'],
    ['label' => 'Athlètes accompagnés',  'value' => $nb($athleteCount), 'accent' => 'text-white'],
    ['label' => 'Projets en cours',      'value' => $nb($projectOngoing), 'accent' => 'text-white', 'sub' => $nb($projectTotal) . ' au total'],
    ['label' => 'Projets terminés',      'value' => $nb($projectDone), 'accent' => 'text-white'],
    ['label' => 'Financements obtenus',  'value' => format_fcfa($fundingObtained), 'accent' => 'text-green-300', 'small' => true],
    ['label' => 'Partenaires mobilisés', 'value' => $nb($partnerCount), 'accent' => 'text-white'],
    ['label' => 'Événements organisés',  'value' => $nb($eventTotal), 'accent' => 'text-white'],
    ['label' => "Rapports d'activité",   'value' => $nb($reportCount), 'accent' => 'text-white'],
];
$disciplineRows = [];
foreach (ATLEX_DISCIPLINES as $key => $label) {
    $disciplineRows[$label] = [
        'members'  => $membersByDiscipline[$key] ?? 0,
        'athletes' => $athletesByDiscipline[$key] ?? 0,
    ];
}
$statusLabels = ['planifie' => 'Planifiés', 'en_cours' => 'En cours', 'en_pause' => 'En pause', 'termine' => 'Terminés', 'annule' => 'Annulés'];
?>
<p class="text-white/50 text-sm font-montserrat mb-6">Indicateurs consolidés de l'activité et de l'impact de l'association.</p>

<!-- Chiffres-clés -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
    <?php foreach ($kpis as $k): ?>
        <div class="bg-atlex-dark rounded-xl p-5 border border-white/5">
            <div class="font-bebas <?= e($k['accent']) ?> leading-none <?= !empty($k['small']) ? 'text-2xl' : 'text-4xl' ?>"><?= e($k['value']) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2"><?= e($k['label']) ?></div>
            <?php if (!empty($k['sub'])): ?><div class="text-white/30 text-xs mt-1"><?= e($k['sub']) ?></div><?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
    <!-- Répartition par discipline -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5"><h2 class="font-bebas text-xl tracking-wider">Par discipline</h2></div>
        <table class="w-full text-sm">
            <thead class="text-left text-white/40 font-montserrat uppercase text-xs">
                <tr><th class="px-6 py-2">Discipline</th><th class="px-6 py-2 text-right">Membres</th><th class="px-6 py-2 text-right">Athlètes</th></tr>
            </thead>
            <tbody>
                <?php foreach ($disciplineRows as $label => $row): ?>
                    <tr class="border-t border-white/5">
                        <td class="px-6 py-2.5 font-montserrat"><?= e($label) ?></td>
                        <td class="px-6 py-2.5 text-right text-white/70"><?= e($nb($row['members'])) ?></td>
                        <td class="px-6 py-2.5 text-right text-white/70"><?= e($nb($row['athletes'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Projets par statut + financements -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <h2 class="font-bebas text-xl tracking-wider mb-4">Projets & financements</h2>
        <div class="flex flex-wrap gap-2 mb-5">
            <?php foreach ($statusLabels as $key => $label): ?>
                <?php if (!empty($projectStatus[$key])): ?>
                    <span class="text-xs px-3 py-1.5 rounded-full bg-white/5 text-white/70 font-montserrat">
                        <?= e($label) ?> : <strong class="text-white"><?= e($nb($projectStatus[$key])) ?></strong>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (array_sum($projectStatus) === 0): ?>
                <span class="text-white/40 text-sm">Aucun projet enregistré.</span>
            <?php endif; ?>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-atlex-bg rounded-lg p-4">
                <div class="font-bebas text-2xl text-green-300"><?= e(format_fcfa($fundingObtained)) ?></div>
                <div class="text-white/50 text-xs uppercase tracking-widest mt-1">Obtenus</div>
            </div>
            <div class="bg-atlex-bg rounded-lg p-4">
                <div class="font-bebas text-2xl text-atlex-beige"><?= e(format_fcfa($fundingPipeline)) ?></div>
                <div class="text-white/50 text-xs uppercase tracking-widest mt-1">En cours (pipeline)</div>
            </div>
        </div>
    </div>
</div>

<!-- Indicateurs manuels -->
<div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
    <h2 class="font-bebas text-xl tracking-wider mb-1">Indicateurs complémentaires</h2>
    <p class="text-white/40 text-xs mb-5">Indicateurs saisis manuellement (non dérivés de la base).</p>

    <?php if (!empty($manual)): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
            <?php foreach ($manual as $ind): ?>
                <div class="bg-atlex-bg rounded-lg p-4 relative group">
                    <div class="font-bebas text-3xl text-atlex-red leading-none"><?= e($ind['value']) ?></div>
                    <div class="font-montserrat text-xs text-white/70 mt-1"><?= e($ind['label']) ?><?php if (!empty($ind['unit'])): ?> <span class="text-white/40">(<?= e($ind['unit']) ?>)</span><?php endif; ?></div>
                    <form method="POST" action="<?= url('/admin/impact/indicateurs/' . $ind['id']) ?>" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity" data-confirm="Supprimer cet indicateur ?">
                        <?= csrf_field() ?><?= method_field('DELETE') ?>
                        <button type="submit" class="text-atlex-red text-lg leading-none" aria-label="Supprimer">×</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Ajouter un indicateur -->
    <form method="POST" action="<?= url('/admin/impact/indicateurs') ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
        <?= csrf_field() ?>
        <div class="sm:col-span-5"><label class="form-label">Libellé</label><input name="label" required placeholder="ex : Communes touchées" class="form-input w-full"></div>
        <div class="sm:col-span-3"><label class="form-label">Valeur</label><input name="value" required placeholder="ex : 8" class="form-input w-full"></div>
        <div class="sm:col-span-2"><label class="form-label">Unité</label><input name="unit" placeholder="ex : communes" class="form-input w-full"></div>
        <div class="sm:col-span-2"><button type="submit" class="btn-atlex w-full text-sm">Ajouter</button></div>
    </form>
</div>
