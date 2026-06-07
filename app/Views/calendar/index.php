<?php
/**
 * @var int $year
 * @var int $month
 * @var array<int,array<string,mixed>> $events
 * @var array<int,array<string,mixed>> $categories
 * @var string|null $activeCategory
 */

/**
 * Retourne le SVG inline de l'icône d'une catégorie.
 */
function category_icon_svg(string $icon, string $color): string
{
    $icons = [
        'basketball' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20M12 2a10 10 0 0 0 0 20M2 12h20M6.3 4.8a14 14 0 0 1 11.4 0M6.3 19.2a14 14 0 0 0 11.4 0"/></svg>',
        'handball'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><circle cx="12" cy="12" r="10"/><path d="M8 8h.01M12 7h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M10 16h.01M14 16h.01"/></svg>',
        'arts-martiaux' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><path d="M12 2L8 7l4 2 4-2-4-5zM8 7l-4 6h16L16 7M4 13l2 7h12l2-7"/></svg>',
        'trophy'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><path d="M6 9H4a2 2 0 0 1-2-2V5h4M18 9h2a2 2 0 0 0 2-2V5h-4"/><path d="M6 2h12v7a6 6 0 0 1-12 0V2z"/><path d="M12 15v4M9 20h6"/></svg>',
        'academique' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        'social'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-8 h-8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ];
    return $icons[$icon] ?? $icons['trophy'];
}
?>

<!-- ===== SECTION CATÉGORIES ===== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Événements</h1>
    <p class="text-white/60 font-montserrat mb-12">Explorez tous les rendez-vous d'ATLEX - Sport par catégorie</p>

    <!-- Grille des catégories (style image de référence) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6 mb-14">
        <!-- Toutes les catégories -->
        <a href="<?= url('/evenements') ?>"
           class="group flex flex-col items-center gap-3 text-center transition-transform hover:-translate-y-1">
            <div class="w-20 h-20 rounded-full flex items-center justify-center transition-opacity <?= $activeCategory === null ? 'opacity-100 ring-4 ring-white/30' : 'opacity-70 group-hover:opacity-100' ?>"
                 style="background-color: #4B5563;">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" class="w-8 h-8">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </div>
            <span class="font-montserrat font-bold text-sm text-white/90">Tout voir</span>
        </a>

        <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/evenements?categorie=' . urlencode((string) $cat['slug'])) ?>"
               class="group flex flex-col items-center gap-3 text-center transition-transform hover:-translate-y-1">
                <div class="w-20 h-20 rounded-full flex items-center justify-center transition-opacity <?= $activeCategory === $cat['slug'] ? 'opacity-100 ring-4 ring-white/30' : 'opacity-70 group-hover:opacity-100' ?>"
                     style="background-color: <?= e($cat['color']) ?>;">
                    <span style="color: white;">
                        <?= category_icon_svg((string) $cat['icon'], (string) $cat['color']) ?>
                    </span>
                </div>
                <div>
                    <span class="font-montserrat font-bold text-sm text-white/90 block"><?= e($cat['name']) ?></span>
                    <span class="text-white/40 text-xs font-montserrat leading-tight block mt-0.5"><?= e(mb_strimwidth((string) $cat['description'], 0, 40, '…')) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Titre de section filtré -->
    <?php if ($activeCategory): ?>
        <?php $activeCat = null;
        foreach ($categories as $c) { if ($c['slug'] === $activeCategory) { $activeCat = $c; break; } } ?>
        <?php if ($activeCat): ?>
        <div class="flex items-center gap-3 mb-8">
            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: <?= e($activeCat['color']) ?>;">
                <span style="color:white; display:flex;">
                    <?= category_icon_svg((string) $activeCat['icon'], (string) $activeCat['color']) ?>
                </span>
            </div>
            <h2 class="font-bebas text-3xl tracking-wider"><?= e($activeCat['name']) ?></h2>
            <span class="text-white/40 font-montserrat text-sm ml-2"><?= e($activeCat['description']) ?></span>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Calendrier + Liste -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Calendrier interactif -->
        <div class="lg:col-span-2 bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="flex items-center justify-between mb-6">
                <button id="cal-prev" class="px-3 py-1.5 rounded bg-white/5 hover:bg-white/10 font-montserrat text-sm">←</button>
                <h2 id="cal-title" class="font-bebas text-2xl tracking-wider"></h2>
                <button id="cal-next" class="px-3 py-1.5 rounded bg-white/5 hover:bg-white/10 font-montserrat text-sm">→</button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-montserrat uppercase text-white/40 mb-2">
                <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
            </div>
            <div id="cal-grid" class="grid grid-cols-7 gap-1"></div>

            <!-- Légende des catégories -->
            <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap gap-3">
                <?php foreach ($categories as $cat): ?>
                <span class="flex items-center gap-1.5 text-xs font-montserrat text-white/50">
                    <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: <?= e($cat['color']) ?>;"></span>
                    <?= e($cat['name']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Liste événements du mois -->
        <aside>
            <h3 class="font-bebas text-2xl tracking-wider mb-4">Événements du mois</h3>
            <div id="cal-events" class="space-y-3">
                <p class="text-white/40 text-sm font-montserrat">Sélectionnez une date pour voir les détails.</p>
            </div>

            <!-- Prochains événements -->
            <?php if (!empty($events)): ?>
            <h3 class="font-bebas text-xl tracking-wider mt-8 mb-3">Prochains rendez-vous</h3>
            <div class="space-y-2">
                <?php foreach (array_slice($events, 0, 5) as $ev): ?>
                <div class="flex items-start gap-3 bg-atlex-dark rounded-lg p-3 border border-white/5">
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center"
                         style="background-color: <?= e($ev['category_color'] ?? '#4B5563') ?>;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="w-4 h-4">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-montserrat font-semibold text-sm text-white truncate"><?= e($ev['title']) ?></p>
                        <p class="text-white/40 text-xs font-montserrat"><?= e(format_date_fr($ev['start_datetime'])) ?></p>
                        <?php if (!empty($ev['category_name'])): ?>
                        <span class="text-xs font-montserrat px-1.5 py-0.5 rounded mt-1 inline-block"
                              style="background-color: <?= e($ev['category_color'] ?? '#4B5563') ?>22; color: <?= e($ev['category_color'] ?? '#9CA3AF') ?>;">
                            <?= e($ev['category_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<script>
    window.ATLEX_CALENDAR = {
        year: <?= (int) $year ?>,
        month: <?= (int) $month ?>,
        apiBase: '<?= url('/api/events') ?>',
        activeCategory: <?= json_encode($activeCategory) ?>
    };
</script>
<script src="<?= asset('js/calendar.js') ?>" defer></script>
