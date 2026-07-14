<?php
/**
 * @var array<int,array<string,mixed>> $donations
 * @var array<int,array{currency:string,total:string,count:int}> $totals
 * @var string $filterStatus
 * @var string|null $filterMethod
 */
$statusTabs = ['tous' => 'Tous', 'pending' => 'En attente', 'completed' => 'Confirmés', 'failed' => 'Échoués', 'cancelled' => 'Annulés'];
$statusLabels = ['pending' => 'En attente', 'completed' => 'Confirmé', 'failed' => 'Échoué', 'cancelled' => 'Annulé'];
$statusColors = [
    'pending'   => 'bg-white/10 text-white/60',
    'completed' => 'bg-green-600/20 text-green-300',
    'failed'    => 'bg-atlex-red/20 text-atlex-red',
    'cancelled' => 'bg-white/10 text-white/40',
];
$methodLabels = ['momo' => 'MTN MoMo', 'paypal' => 'PayPal'];
?>

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="font-bebas text-4xl tracking-wider text-white">Dons</h1>
    <div class="flex flex-wrap gap-3">
        <?php foreach ($totals as $t): ?>
            <div class="bg-atlex-dark rounded-xl border border-white/5 px-5 py-3">
                <div class="font-bebas text-2xl text-atlex-red leading-none">
                    <?= e(number_format((float) $t['total'], 0, ',', ' ')) ?> <?= e($t['currency']) ?>
                </div>
                <div class="text-white/40 text-xs font-montserrat mt-1"><?= (int) $t['count'] ?> don(s) confirmé(s)</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap gap-2">
        <?php foreach ($statusTabs as $key => $label): ?>
            <a href="<?= url('/admin/dons?status=' . $key . ($filterMethod ? '&method=' . $filterMethod : '')) ?>"
               class="px-4 py-1.5 rounded-full text-sm font-montserrat transition-colors <?= $filterStatus === $key ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <form method="GET" action="<?= url('/admin/dons') ?>">
        <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
        <select name="method" class="form-input text-sm" onchange="this.form.submit()">
            <option value="">Toutes les méthodes</option>
            <option value="momo" <?= $filterMethod === 'momo' ? 'selected' : '' ?>>MTN MoMo</option>
            <option value="paypal" <?= $filterMethod === 'paypal' ? 'selected' : '' ?>>PayPal</option>
        </select>
    </form>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Donateur</th>
                <th class="px-5 py-3">Méthode</th>
                <th class="px-5 py-3">Montant</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($donations)): ?>
                <tr><td colspan="6" class="px-5 py-6 text-white/40">Aucun don dans cette vue.</td></tr>
            <?php else: ?>
                <?php foreach ($donations as $d): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3">
                            <div class="font-montserrat font-semibold"><?= e($d['donor_name']) ?></div>
                            <div class="text-white/40 text-xs"><?= e($d['donor_email']) ?></div>
                            <?php if (!empty($d['donor_phone'])): ?>
                                <div class="text-white/40 text-xs"><?= e($d['donor_phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-white/70"><?= e($methodLabels[$d['method']] ?? $d['method']) ?></td>
                        <td class="px-5 py-3 font-semibold text-white">
                            <?= e(number_format((float) $d['amount'], 2, ',', ' ')) ?> <?= e($d['currency']) ?>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs <?= e($statusColors[$d['status']] ?? 'bg-white/10 text-white/60') ?>">
                                <?= e($statusLabels[$d['status']] ?? $d['status']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-white/50 text-xs"><?= e(format_date_fr($d['created_at'])) ?></td>
                        <td class="px-5 py-3 text-right">
                            <?php if ($d['status'] === 'pending'): ?>
                                <form method="POST" action="<?= url('/admin/dons/' . $d['id'] . '/confirmer') ?>"
                                      onsubmit="return confirm('Confirmer ce don uniquement si vous avez vérifié sa réception dans votre compte marchand ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs text-atlex-beige hover:underline">Marquer confirmé</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
