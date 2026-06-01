<?php
$links = [
    '/'           => 'Accueil',
    '/clubs'      => 'Clubs',
    '/actualites' => 'Actualités',
    '/galerie'    => 'Galerie',
    '/calendrier' => 'Calendrier',
    '/a-propos'   => 'À propos',
    '/sponsors'   => 'Sponsors',
    '/contact'    => 'Contact',
];
?>
<nav class="fixed top-0 inset-x-0 z-40 bg-atlex-dark/95 backdrop-blur border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-20">

        <!-- Logo SVG inline -->
        <a href="<?= url('/') ?>" class="flex items-center gap-2" aria-label="ATLEX - Sport accueil">
            <svg width="38" height="38" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 3 L37 34 H3 Z" fill="#E53935"/>
                <path d="M20 14 L28 30 H12 Z" fill="#0a0e1a"/>
            </svg>
            <span class="font-bebas text-2xl tracking-wider leading-none">
                <span class="text-white">ATL</span><span class="text-atlex-beige">É</span><span class="text-white">X·SPORT</span>
            </span>
        </a>

        <!-- Liens desktop -->
        <ul class="hidden lg:flex items-center gap-6 font-montserrat text-sm font-semibold uppercase tracking-wide">
            <?php foreach ($links as $href => $label): ?>
                <li>
                    <a href="<?= url($href) ?>"
                       class="nav-link text-white/80 hover:text-white transition-colors"
                       data-path="<?= e($href) ?>">
                        <?= e($label) ?>
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

    <!-- Menu mobile -->
    <div class="nav-mobile-menu hidden lg:hidden bg-atlex-dark border-t border-white/5">
        <ul class="px-4 py-4 space-y-2 font-montserrat uppercase text-sm font-semibold">
            <?php foreach ($links as $href => $label): ?>
                <li>
                    <a href="<?= url($href) ?>" class="block py-2 text-white/80 hover:text-white">
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
