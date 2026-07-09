<?php
/**
 * @var int $year
 * @var int $month
 * @var array<int,array<string,mixed>> $events
 * @var array<int,array<string,mixed>> $categories
 * @var string|null $activeCategory
 */

function category_icon_svg(string $icon): string
{
    $icons = [
        'basketball'    => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20M12 2a10 10 0 0 0 0 20M2 12h20M6.3 4.8a14 14 0 0 1 11.4 0M6.3 19.2a14 14 0 0 0 11.4 0"/></svg>',
        'handball'      => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><circle cx="12" cy="12" r="10"/><circle cx="9" cy="9" r="1.5" fill="white"/><circle cx="15" cy="9" r="1.5" fill="white"/><circle cx="9" cy="15" r="1.5" fill="white"/><circle cx="15" cy="15" r="1.5" fill="white"/><circle cx="12" cy="12" r="1.5" fill="white"/></svg>',
        'arts-martiaux' => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>',
        'trophy'        => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><path d="M6 9H4a2 2 0 0 1-2-2V5h4M18 9h2a2 2 0 0 0 2-2V5h-4"/><path d="M6 2h12v7a6 6 0 0 1-12 0V2z"/><path d="M12 15v4M9 20h6"/></svg>',
        'academique'    => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        'social'        => '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ];
    return $icons[$icon] ?? $icons['trophy'];
}
?>

<!-- ===== SECTION CATÉGORIES ===== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Événements</h1>
    <p class="text-white/60 font-montserrat mb-12">Explorez tous les rendez-vous d'ATLEX - Sport par catégorie</p>

    <!-- Grille des catégories -->
    <div style="display:grid; grid-template-columns: repeat(<?= min(7, count($categories) + 1) ?>, 1fr); gap:1.5rem; margin-bottom:3.5rem;">

        <!-- Tout voir -->
        <a href="<?= url('/evenements') ?>"
           style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; text-align:center; text-decoration:none; transition:transform .2s;"
           onmouseover="this.style.transform='translateY(-4px)'"
           onmouseout="this.style.transform='translateY(0)'">
            <div style="width:80px; height:80px; border-radius:50%; background:#4B5563; display:flex; align-items:center; justify-content:center; flex-shrink:0; <?= $activeCategory === null ? 'outline:3px solid rgba(255,255,255,0.3); outline-offset:3px;' : 'opacity:.7;' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" width="32" height="32">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </div>
            <div>
                <span style="font-family:var(--font-montserrat,sans-serif); font-weight:700; font-size:.875rem; color:rgba(255,255,255,.9); display:block;">Tout voir</span>
            </div>
        </a>

        <?php foreach ($categories as $cat): ?>
        <a href="<?= url('/evenements?categorie=' . urlencode((string) $cat['slug'])) ?>"
           style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; text-align:center; text-decoration:none; transition:transform .2s;"
           onmouseover="this.style.transform='translateY(-4px)'"
           onmouseout="this.style.transform='translateY(0)'">
            <div style="width:80px; height:80px; border-radius:50%; background:<?= e($cat['color']) ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0; <?= $activeCategory === $cat['slug'] ? 'outline:3px solid rgba(255,255,255,0.3); outline-offset:3px;' : 'opacity:.8;' ?>">
                <?= category_icon_svg((string) $cat['icon']) ?>
            </div>
            <div>
                <span style="font-family:var(--font-montserrat,sans-serif); font-weight:700; font-size:.875rem; color:rgba(255,255,255,.9); display:block; line-height:1.2;"><?= e($cat['name']) ?></span>
                <span style="font-family:var(--font-montserrat,sans-serif); font-size:.75rem; color:rgba(255,255,255,.4); display:block; margin-top:.25rem; line-height:1.3;"><?= e(mb_strimwidth((string) $cat['description'], 0, 38, '…')) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($activeCategory): ?>
        <?php $activeCat = null;
        foreach ($categories as $c) { if ($c['slug'] === $activeCategory) { $activeCat = $c; break; } } ?>
        <?php if ($activeCat): ?>
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:2rem;">
            <div style="width:36px; height:36px; border-radius:50%; background:<?= e($activeCat['color']) ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <?= category_icon_svg((string) $activeCat['icon']) ?>
            </div>
            <h2 class="font-bebas text-3xl tracking-wider"><?= e($activeCat['name']) ?></h2>
            <span style="color:rgba(255,255,255,.4); font-size:.875rem; margin-left:.5rem;"><?= e($activeCat['description']) ?></span>
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
            <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,.05); display:flex; flex-wrap:wrap; gap:.75rem;">
                <?php foreach ($categories as $cat): ?>
                <span style="display:flex; align-items:center; gap:.375rem; font-size:.75rem; font-family:var(--font-montserrat,sans-serif); color:rgba(255,255,255,.5);">
                    <span style="width:10px; height:10px; border-radius:50%; display:inline-block; background:<?= e($cat['color']) ?>;"></span>
                    <?= e($cat['name']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar événements / Cliquer sur un evenement pour voir les détails -->
        <aside>
            <h3 class="font-bebas text-2xl tracking-wider mb-4">Événements du mois</h3>
            <div id="cal-events" class="space-y-3">
                <p class="text-white/40 text-sm font-montserrat">Sélectionnez une date pour voir les détails.</p>
            </div>

            <?php if (!empty($events)): ?>
            <h3 class="font-bebas text-xl tracking-wider mt-8 mb-3">Prochains rendez-vous</h3>
            <div class="space-y-2">
                <?php foreach (array_slice($events, 0, 5) as $ev): ?>
                <a href="<?= url('/evenements/' . (int) $ev['id']) ?>" style="display:flex; align-items:flex-start; gap:.75rem; background:rgba(255,255,255,.04); border-radius:.75rem; padding:.75rem; border:1px solid rgba(255,255,255,.05); text-decoration:none; transition:background .2s, border-color .2s;"
                   onmouseover="this.style.background='rgba(255,255,255,.08)'; this.style.borderColor='<?= e($ev['category_color'] ?? '#4B5563') ?>'"
                   onmouseout="this.style.background='rgba(255,255,255,.04)'; this.style.borderColor='rgba(255,255,255,.05)'">
                    <div style="width:36px; height:36px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:<?= e($ev['category_color'] ?? '#4B5563') ?>;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" width="16" height="16">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div style="min-width:0;">
                        <p style="font-family:var(--font-montserrat,sans-serif); font-weight:600; font-size:.875rem; color:white; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e($ev['title']) ?></p>
                        <p style="color:rgba(255,255,255,.4); font-size:.75rem; font-family:var(--font-montserrat,sans-serif);"><?= e(format_date_fr($ev['start_datetime'])) ?></p>
                        <?php if (!empty($ev['category_name'])): ?>
                        <span style="font-size:.7rem; font-family:var(--font-montserrat,sans-serif); padding:.15rem .5rem; border-radius:.25rem; margin-top:.25rem; display:inline-block; background:<?= e($ev['category_color'] ?? '#4B5563') ?>33; color:<?= e($ev['category_color'] ?? '#9CA3AF') ?>;">
                            <?= e($ev['category_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<script nonce="<?= \App\Core\Security::nonce() ?>">
    window.ATLEX_CALENDAR = {
        year: <?= (int) $year ?>,
        month: <?= (int) $month ?>,
        apiBase: '<?= url('/api/events') ?>',
        activeCategory: <?= json_encode($activeCategory) ?>
    };
</script>
<script src="<?= asset('js/calendar.min.js') ?>" defer></script>
