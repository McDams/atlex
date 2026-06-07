<?php
/**
 * Navigation publique : icônes avec libellé affiché au survol (desktop),
 * icône + libellé (menu mobile). Chaque entrée : href => [label, icône SVG].
 */
$links = [
    '/'             => ['Accueil',    'M3 9.5l9-6.5 9 6.5M5 9v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9M9 21v-6h6v6'],
    '/clubs'        => ['Clubs',      'M12 3l8 3v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6z'],
    '/athletes'     => ['Athlètes',   'M12 14a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM9 13l-1.5 8L12 18l4.5 3L15 13'],
    '/actualites'   => ['Actualités', 'M19 20H5a2 2 0 0 1-2-2V5a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v13a2 2 0 0 0 2 2 2 2 0 0 0 2-2V8h-3M7 8h6M7 12h6M7 16h4'],
    '/galerie'      => ['Galerie',    'M3 5h18v14H3zM3 16l4-4 3 3 5-5 5 5M8.5 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z'],
    '/calendrier'   => ['Calendrier', 'M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
    '/a-propos'     => ['Notre Association',   'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 11v5M12 7.5h.01'],
    '/sponsors'     => ['Sponsors',   'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 1 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z'],
    '/centre-media' => ['Presse',     'M11 5L6 9H2v6h4l5 4zM15.5 8.5a5 5 0 0 1 0 7M19 5a9 9 0 0 1 0 14'],
    '/contact'      => ['Contact',    'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM3.5 6.5 12 13l8.5-6.5'],
];
?>
<nav class="fixed top-0 inset-x-0 z-40 bg-atlex-dark/95 backdrop-blur border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-20">

        <!-- Logo -->
        <a href="<?= url('/') ?>" style="display:flex; align-items:center; gap:10px; text-decoration:none;" aria-label="ATLEX - Sport accueil">
            <img src="<?= asset('images/LOGO.jpeg') ?>" alt="ATLEX Sport" style="height:48px; width:auto; object-fit:contain; background:white; border-radius:6px; padding:4px;">
            <span style="font-family:'Bebas Neue',sans-serif; font-size:1.4rem; letter-spacing:0.05em; color:white; line-height:1;">ATLEX<br><span style="font-size:0.9rem; color:#E53935;">Sport</span></span>
        </a>

        <!-- Icônes desktop (libellé affiché au survol) -->
        <ul class="hidden lg:flex items-center gap-4">
            <?php foreach ($links as $href => [$label, $icon]): ?>
                <li class="relative group">
                    <a href="<?= url($href) ?>"
                       class="nav-link block p-2 text-white/80 hover:text-white transition-colors"
                       data-path="<?= e($href) ?>"
                       aria-label="<?= e($label) ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="<?= $icon ?>"/>
                        </svg>
                        <span class="pointer-events-none absolute left-1/2 -translate-x-1/2 top-full mt-2 px-2 py-1 rounded bg-atlex-dark border border-white/10 text-white text-xs font-montserrat uppercase tracking-wide whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
                            <?= e($label) ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- CTA + hamburger -->
        <div class="flex items-center gap-3">
            <a href="<?= url('/contact#inscription') ?>" class="btn-atlex hidden sm:inline-block text-sm">Rejoindre</a>
            <button type="button" class="nav-hamburger lg:hidden text-white p-2" aria-label="Menu">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu mobile (icône + libellé) -->
    <div class="nav-mobile-menu hidden lg:hidden bg-atlex-dark border-t border-white/5">
        <ul class="px-4 py-4 space-y-1 font-montserrat uppercase text-sm font-semibold">
            <?php foreach ($links as $href => [$label, $icon]): ?>
                <li>
                    <a href="<?= url($href) ?>" class="flex items-center gap-3 py-2 text-white/80 hover:text-white">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= $icon ?>"/></svg>
                        <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="pt-2">
                <a href="<?= url('/contact#inscription') ?>" class="btn-atlex w-full text-center text-sm">Rejoindre</a>
            </li>
        </ul>
    </div>
</nav>
<div class="h-20"></div>
