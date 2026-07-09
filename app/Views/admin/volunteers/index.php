<?php
/**
 * @var array<int,array<string,mixed>> $requests
 * @var array<string,int>              $stats
 * @var array<string,string>           $missions
 */

/** Badge coloré pour un statut. */
$statusBadge = static function (string $status): string {
    $map = [
        'nouveau'  => ['Nouveau', 'bg-blue-600/20 text-blue-300'],
        'en_cours' => ['En cours', 'bg-orange-600/20 text-orange-300'],
        'accepte'  => ['Accepté', 'bg-green-600/20 text-green-300'],
        'refuse'   => ['Refusé', 'bg-atlex-red/20 text-red-300'],
    ];
    [$label, $classes] = $map[$status] ?? ['Inconnu', 'bg-white/10 text-white/60'];
    return '<span class="text-xs px-2.5 py-1 rounded-full font-montserrat font-semibold ' . $classes . '">' . e($label) . '</span>';
};

$statCards = [
    ['Total', $stats['total'] ?? 0, 'text-white'],
    ['Nouveaux', $stats['nouveau'] ?? 0, 'text-blue-300'],
    ['En cours', $stats['en_cours'] ?? 0, 'text-orange-300'],
    ['Acceptés', $stats['accepte'] ?? 0, 'text-green-300'],
];
?>

<!-- Statistiques -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ($statCards as [$label, $value, $color]): ?>
        <div class="bg-atlex-dark rounded-xl border border-white/5 px-5 py-4">
            <p class="text-white/40 text-xs uppercase tracking-wider font-montserrat"><?= e($label) ?></p>
            <p class="font-bebas text-3xl tracking-wider mt-1 <?= $color ?>"><?= e($value) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
    <div class="px-6 py-4 border-b border-white/5">
        <h2 class="font-bebas text-xl tracking-wider">Candidatures bénévoles</h2>
    </div>

    <?php if (empty($requests)): ?>
        <p class="px-6 py-6 text-white/40 text-sm">Aucune candidature pour le moment.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-white/40 text-xs uppercase tracking-wider">
                <tr class="border-b border-white/5">
                    <th class="px-6 py-3 text-left font-montserrat">Candidat</th>
                    <th class="px-6 py-3 text-left font-montserrat">Téléphone</th>
                    <th class="px-6 py-3 text-left font-montserrat">Âge</th>
                    <th class="px-6 py-3 text-left font-montserrat">Email</th>
                    <th class="px-6 py-3 text-left font-montserrat">Missions</th>
                    <th class="px-6 py-3 text-left font-montserrat">Reçue le</th>
                    <th class="px-6 py-3 text-left font-montserrat">Statut</th>
                    <th class="px-6 py-3 text-right font-montserrat">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <?php
                    $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                    $chosen = json_decode((string) ($r['missions'] ?? '[]'), true) ?: [];
                    ?>
                    <tr class="border-b border-white/5 align-top">
                        <td class="px-6 py-4 font-montserrat"><?= e($name) ?></td>
                        <td class="px-6 py-4 text-white/60 text-xs"><?= e($r['phone'] ?? '') ?></td>
                        <td class="px-6 py-4 text-white/60 text-xs"><?= e($r['age'] ?? '') ?></td>
                        <td class="px-6 py-4 text-white/60 text-xs"><?= e($r['email'] ?? '') ?: '—' ?></td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                <?php foreach ($chosen as $m): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-white/10 text-white/70"><?= e($missions[$m] ?? $m) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/50 text-xs"><?= e(format_date_fr($r['created_at'] ?? null)) ?></td>
                        <td class="px-6 py-4"><?= $statusBadge((string) ($r['status'] ?? 'nouveau')) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2 justify-end items-center">
                                <a href="<?= url('/admin/benevoles/' . $r['id']) ?>" class="text-xs font-montserrat font-semibold uppercase tracking-wide px-3 py-1.5 rounded bg-white/10 hover:bg-white/20 text-white transition-colors">Voir</a>
                                <form method="POST" action="<?= url('/admin/benevoles/' . $r['id'] . '/delete') ?>"
                                      onsubmit="return confirm('Supprimer définitivement cette candidature ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs font-montserrat font-semibold uppercase tracking-wide px-3 py-1.5 rounded bg-white/10 hover:bg-atlex-red text-white transition-colors">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
