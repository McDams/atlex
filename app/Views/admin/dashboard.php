<?php
/**
 * @var int $memberCount
 * @var int $eventCount
 * @var int $unreadCount
 * @var int $pendingInscriptions
 * @var int $taskCount
 * @var array<int,array<string,mixed>> $recentTasks
 * @var array<int,array<string,mixed>> $recentContact
 */
$kpis = [
    ['label' => 'Membres actifs', 'value' => $memberCount, 'href' => '/admin/membres'],
    ['label' => 'Inscriptions en attente', 'value' => $pendingInscriptions, 'href' => '/admin/inscriptions'],
    ['label' => 'Événements à venir', 'value' => $eventCount, 'href' => '/admin/evenements'],
    ['label' => 'Tâches en cours', 'value' => $taskCount, 'href' => '/admin/taches'],
];
$statusLabels = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <?php foreach ($kpis as $kpi): ?>
        <a href="<?= url($kpi['href']) ?>" class="bg-atlex-dark rounded-xl p-6 border border-white/5 hover:border-atlex-red/40 transition-colors">
            <div class="font-bebas text-5xl text-atlex-red leading-none"><?= e($kpi['value']) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2"><?= e($kpi['label']) ?></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Tâches récentes -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="font-bebas text-xl tracking-wider">Tâches récentes</h2>
            <a href="<?= url('/admin/taches') ?>" class="text-atlex-red text-sm font-montserrat">Voir tout →</a>
        </div>
        <table class="w-full text-sm">
            <tbody>
                <?php if (empty($recentTasks)): ?>
                    <tr><td class="px-6 py-4 text-white/40">Aucune tâche.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentTasks as $task): ?>
                        <tr class="border-b border-white/5">
                            <td class="px-6 py-3 font-montserrat"><?= e($task['title']) ?></td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-xs px-2 py-1 rounded bg-white/5 text-white/70"><?= e($statusLabels[$task['status']] ?? $task['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Soumissions récentes -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="font-bebas text-xl tracking-wider">Soumissions récentes</h2>
        </div>
        <table class="w-full text-sm">
            <tbody>
                <?php if (empty($recentContact)): ?>
                    <tr><td class="px-6 py-4 text-white/40">Aucune soumission.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentContact as $sub): ?>
                        <tr class="border-b border-white/5">
                            <td class="px-6 py-3 font-montserrat">
                                <?= e(trim(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? ''))) ?>
                                <span class="block text-white/40 text-xs"><?= e($sub['email']) ?></span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-xs px-2 py-1 rounded bg-atlex-red/20 text-atlex-red"><?= e($sub['type']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
