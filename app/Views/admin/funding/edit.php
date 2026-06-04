<?php
/** @var array<string,mixed> $opportunity */
/** @var array<int,array<string,mixed>> $projects */
/** @var array<int,array<string,mixed>> $checklist */
$isEdit = true;
$action = url('/admin/financements/' . $opportunity['id']);
require __DIR__ . '/_form.php';

$oid = $opportunity['id'];
$done = count(array_filter($checklist, static fn ($c) => (int) $c['is_done'] === 1));
$total = count($checklist);
?>
<div class="max-w-2xl mt-6">
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bebas text-xl tracking-wider">Démarches à suivre</h2>
            <?php if ($total > 0): ?>
                <span class="text-sm text-white/50 font-montserrat"><?= e($done) ?>/<?= e($total) ?> faites</span>
            <?php endif; ?>
        </div>

        <?php if ($total > 0): ?>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden mb-4">
                <div class="h-full bg-green-500" style="width: <?= e($total > 0 ? (int) round($done / $total * 100) : 0) ?>%"></div>
            </div>
            <ul class="space-y-2 mb-4">
                <?php foreach ($checklist as $item): ?>
                    <li class="flex items-center gap-3 bg-atlex-bg rounded-lg px-4 py-2">
                        <form method="POST" action="<?= url('/admin/financements/checklist/' . $item['id']) ?>" class="flex items-center">
                            <?= csrf_field() ?><?= method_field('PUT') ?>
                            <button type="submit" aria-label="Basculer" class="w-5 h-5 rounded border <?= $item['is_done'] ? 'bg-green-500 border-green-500' : 'border-white/30' ?> grid place-items-center text-xs text-atlex-bg">
                                <?= $item['is_done'] ? '✓' : '' ?>
                            </button>
                        </form>
                        <span class="flex-1 font-montserrat text-sm <?= $item['is_done'] ? 'line-through text-white/40' : '' ?>"><?= e($item['label']) ?></span>
                        <form method="POST" action="<?= url('/admin/financements/checklist/' . $item['id']) ?>" data-confirm="Supprimer cette étape ?">
                            <?= csrf_field() ?><?= method_field('DELETE') ?>
                            <button type="submit" class="text-atlex-red text-lg leading-none" aria-label="Supprimer">×</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-white/40 text-sm mb-4">
                Aucune démarche.
                <form method="POST" action="<?= url('/admin/financements/' . $oid . '/checklist/seed') ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-atlex-beige hover:underline">Générer les démarches types</button>
                </form>
            </p>
        <?php endif; ?>

        <!-- Ajouter une étape -->
        <form method="POST" action="<?= url('/admin/financements/' . $oid . '/checklist') ?>" class="flex gap-2">
            <?= csrf_field() ?>
            <input name="label" required placeholder="Ajouter une démarche…" class="form-input flex-1 text-sm">
            <button type="submit" class="btn-atlex-outline text-sm">Ajouter</button>
        </form>
    </div>
</div>
