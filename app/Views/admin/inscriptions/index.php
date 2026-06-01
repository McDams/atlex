<?php
/**
 * @var array<int,array<string,mixed>> $pending
 * @var array<int,array<string,mixed>> $valides
 * @var array<int,array<string,mixed>> $refuses
 */
$renderContact = static function (array $s): string {
    $name = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
    $html = '<span class="font-montserrat">' . e($name) . '</span>';
    if (!empty($s['email'])) {
        $html .= '<span class="block text-white/40 text-xs">' . e($s['email']) . '</span>';
    }
    if (!empty($s['phone'])) {
        $html .= '<span class="block text-white/40 text-xs">' . e($s['phone']) . '</span>';
    }
    return $html;
};
?>

<!-- Demandes en attente -->
<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
        <h2 class="font-bebas text-xl tracking-wider">Demandes en attente</h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-atlex-red text-white font-montserrat font-semibold"><?= count($pending) ?></span>
    </div>

    <?php if (empty($pending)): ?>
        <p class="px-6 py-6 text-white/40 text-sm">Aucune demande en attente. 🎉</p>
    <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-white/40 text-xs uppercase tracking-wider">
                <tr class="border-b border-white/5">
                    <th class="px-6 py-3 text-left font-montserrat">Candidat</th>
                    <th class="px-6 py-3 text-left font-montserrat">Discipline</th>
                    <th class="px-6 py-3 text-left font-montserrat">Profil</th>
                    <th class="px-6 py-3 text-left font-montserrat">Reçue le</th>
                    <th class="px-6 py-3 text-right font-montserrat">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $s): ?>
                    <tr class="border-b border-white/5 align-top">
                        <td class="px-6 py-4"><?= $renderContact($s) ?></td>
                        <td class="px-6 py-4 font-montserrat"><?= e(discipline_label($s['discipline'] ?? null)) ?></td>
                        <td class="px-6 py-4 text-white/60 text-xs">
                            <?php if (!empty($s['age'])): ?><?= e($s['age']) ?> ans<?php endif; ?>
                            <?php if (!empty($s['gender'])): ?> · <?= e($s['gender']) ?><?php endif; ?>
                            <?php if (!empty($s['message'])): ?>
                                <span class="block mt-1 italic text-white/40">« <?= e($s['message']) ?> »</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-white/50 text-xs"><?= e(format_date_fr($s['created_at'] ?? null)) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2 justify-end">
                                <form method="POST" action="<?= url('/admin/inscriptions/' . $s['id'] . '/valider') ?>"
                                      onsubmit="return confirm('Valider cette inscription ? Le membre sera créé et un email de confirmation envoyé.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs font-montserrat font-semibold uppercase tracking-wide px-3 py-1.5 rounded bg-green-600 hover:bg-green-500 text-white transition-colors">Valider</button>
                                </form>
                                <form method="POST" action="<?= url('/admin/inscriptions/' . $s['id'] . '/refuser') ?>"
                                      onsubmit="return confirm('Refuser cette demande ? Un email sera envoyé au candidat.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs font-montserrat font-semibold uppercase tracking-wide px-3 py-1.5 rounded bg-white/10 hover:bg-atlex-red text-white transition-colors">Refuser</button>
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

<!-- Historique -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php
    $historyBlocks = [
        ['title' => 'Validées', 'rows' => $valides, 'badge' => 'bg-green-600/20 text-green-300', 'label' => 'Validée'],
        ['title' => 'Refusées', 'rows' => $refuses, 'badge' => 'bg-atlex-red/20 text-red-300', 'label' => 'Refusée'],
    ];
    foreach ($historyBlocks as $block): ?>
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider"><?= e($block['title']) ?></h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($block['rows'])): ?>
                        <tr><td class="px-6 py-4 text-white/40 text-sm">Aucune.</td></tr>
                    <?php else: ?>
                        <?php foreach ($block['rows'] as $s): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3"><?= $renderContact($s) ?></td>
                                <td class="px-6 py-3 font-montserrat text-white/60 text-xs"><?= e(discipline_label($s['discipline'] ?? null)) ?></td>
                                <td class="px-6 py-3 text-right text-white/40 text-xs"><?= e(format_date_fr($s['processed_at'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>
