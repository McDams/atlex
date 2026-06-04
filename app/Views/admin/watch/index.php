<?php
/**
 * @var array<int,array<string,mixed>> $leads
 * @var array<int,array<string,mixed>> $sources
 * @var string|null $filterStatus
 * @var int|null    $filterSource
 */
$statusTabs = ['nouveau' => 'Nouvelles', 'promu' => 'Ajoutées au suivi', 'ignore' => 'Ignorées', 'tous' => 'Toutes'];
$activeSources = array_filter($sources, static fn ($s) => (int) $s['is_active'] === 1);
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-white/50 text-sm font-montserrat">
            Opportunités détectées automatiquement depuis <strong class="text-white/70"><?= e(count($activeSources)) ?></strong> source(s) active(s).
            <a href="<?= url('/admin/veille/sources') ?>" class="text-atlex-beige hover:underline">Gérer les sources →</a>
        </p>
    </div>
    <form method="POST" action="<?= url('/admin/veille/refresh') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn-atlex text-sm">↻ Rafraîchir maintenant</button>
    </form>
</div>

<!-- Onglets de statut + filtre source -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap gap-2">
        <?php foreach ($statusTabs as $key => $label): ?>
            <a href="<?= url('/admin/veille?status=' . $key . ($filterSource ? '&source=' . $filterSource : '')) ?>"
               class="px-4 py-1.5 rounded-full text-sm font-montserrat transition-colors <?= ($filterStatus ?? 'nouveau') === $key ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <form method="GET" action="<?= url('/admin/veille') ?>">
        <input type="hidden" name="status" value="<?= e($filterStatus ?? 'nouveau') ?>">
        <select name="source" class="form-input text-sm" onchange="this.form.submit()">
            <option value="">Toutes les sources</option>
            <?php foreach ($sources as $s): ?>
                <option value="<?= e($s['id']) ?>" <?= $filterSource === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($leads)): ?>
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-8 text-center text-white/40">
        Aucune opportunité dans cette vue.
        <?php if (empty($activeSources)): ?>
            <br>Commencez par <a href="<?= url('/admin/veille/sources') ?>" class="text-atlex-beige hover:underline">ajouter des sources</a>, puis cliquez sur « Rafraîchir ».
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($leads as $lead): ?>
            <div class="bg-atlex-dark rounded-xl border border-white/5 p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <?php if ($link = safe_url($lead['url'])): ?>
                            <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" class="font-montserrat font-semibold hover:text-atlex-beige">
                                <?= e($lead['title']) ?> ↗
                            </a>
                        <?php else: ?>
                            <span class="font-montserrat font-semibold"><?= e($lead['title']) ?></span>
                        <?php endif; ?>
                        <div class="text-white/40 text-xs mt-1">
                            <?= e($lead['source_label'] ?? $lead['source_name'] ?? 'Source') ?>
                            <?php if (!empty($lead['published_at'])): ?> · <?= e(format_date_fr($lead['published_at'])) ?><?php endif; ?>
                        </div>
                        <?php if (!empty($lead['summary'])): ?>
                            <p class="text-white/60 text-sm mt-2 line-clamp-2"><?= e($lead['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <?php if ($lead['status'] === 'nouveau'): ?>
                            <form method="POST" action="<?= url('/admin/veille/' . $lead['id'] . '/promouvoir') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-atlex text-xs">+ Ajouter au suivi</button>
                            </form>
                            <form method="POST" action="<?= url('/admin/veille/' . $lead['id'] . '/ignorer') ?>" data-confirm="Ignorer cette opportunité ?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-atlex-outline text-xs">Ignorer</button>
                            </form>
                        <?php elseif ($lead['status'] === 'promu'): ?>
                            <span class="text-xs px-2 py-1 rounded bg-green-600/20 text-green-300">Au suivi</span>
                        <?php else: ?>
                            <span class="text-xs px-2 py-1 rounded bg-white/10 text-white/50">Ignorée</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
