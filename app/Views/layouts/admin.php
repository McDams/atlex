<?php
use App\Core\Auth;
use App\Models\ContactSubmission;

$isLoginPage = !Auth::isLoggedIn();
$adminUser = Auth::user();

// Nombre de demandes d'inscription en attente (pour le badge de menu).
$pendingInscriptions = 0;
if (!$isLoginPage) {
    try {
        $pendingInscriptions = (new ContactSubmission())->countPendingInscriptions();
    } catch (\Throwable) {
        $pendingInscriptions = 0;
    }
}

$navItems = [
    '/admin'            => ['Tableau de bord', 'M3 13h8V3H3zM13 21h8V3h-8zM3 21h8v-6H3z'],
    '/admin/impact'     => ['Impact', 'M3 3v18h18M7 14l4-4 3 3 5-6'],
    '/admin/membres'    => ['Membres', 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
    '/admin/athletes'   => ['Athlètes', 'M13 2 L3 14h7l-1 8 10-12h-7z'],
    '/admin/inscriptions' => ['Inscriptions', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM19 8v6M22 11h-6'],
    '/admin/evenements' => ['Événements', 'M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
    '/admin/actualites' => ['Actualités', 'M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9l5 5v9a2 2 0 0 1-2 2z'],
    '/admin/projets'    => ['Projets', 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
    '/admin/financements' => ['Financements', 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
    '/admin/veille'     => ['Veille financements', 'M21 21l-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16z'],
    '/admin/partenaires' => ['Partenaires', 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z'],
    '/admin/documents'  => ['Documents', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'],
    '/admin/taches'     => ['Tâches', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
    '/admin/hostinger'  => ['Hébergement', 'M3 15a4 4 0 0 0 4 4h9a5 5 0 1 0-.1-9.999 5.002 5.002 0 0 0-9.78 2.096A4.001 4.001 0 0 0 3 15z'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Administration') ?> — ATLEX - Sport</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="bg-atlex-bg text-white font-poppins min-h-screen">

<?php if ($isLoginPage): ?>
    <?php if ($msg = flash('error')): ?>
        <div class="fixed top-4 inset-x-0 mx-auto w-fit z-50 bg-atlex-red px-5 py-3 rounded shadow-lg" data-flash><?= e($msg) ?></div>
    <?php endif; ?>
    <?= $content ?>
<?php else: ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-atlex-dark border-r border-white/5 flex-shrink-0 flex flex-col">
            <div class="h-20 flex items-center gap-2 px-6 border-b border-white/5">
                <svg width="30" height="30" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                    <path d="M20 3 L37 34 H3 Z" fill="#E53935"/><path d="M20 14 L28 30 H12 Z" fill="#001a3d"/>
                </svg>
                <span class="font-bebas text-lg tracking-wider">ATL<span class="text-atlex-beige">É</span>X·SG</span>
            </div>
            <nav class="flex-1 px-3 py-5 space-y-1">
                <?php foreach ($navItems as $href => [$label, $icon]): ?>
                    <a href="<?= url($href) ?>"
                       class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-montserrat text-white/70 hover:bg-white/5 hover:text-white transition-colors"
                       data-path="<?= e($href) ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icon ?>"/></svg>
                        <span class="flex-1"><?= e($label) ?></span>
                        <?php if ($href === '/admin/inscriptions' && $pendingInscriptions > 0): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-atlex-red text-white font-semibold"><?= e($pendingInscriptions) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="p-3 border-t border-white/5">
                <form method="POST" action="<?= url('/admin/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-montserrat text-white/70 hover:bg-atlex-red hover:text-white transition-colors">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        <!-- Zone principale -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-20 bg-atlex-dark/60 border-b border-white/5 flex items-center justify-between px-6">
                <h1 class="font-bebas text-2xl tracking-wider text-white"><?= e($title ?? 'Administration') ?></h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-white/60 font-montserrat"><?= e($adminUser['name'] ?? '') ?></span>
                    <div class="w-9 h-9 rounded-full bg-atlex-red grid place-items-center font-bebas">
                        <?= e(strtoupper(mb_substr((string) ($adminUser['name'] ?? 'A'), 0, 1))) ?>
                    </div>
                </div>
            </header>

            <?php if ($msg = flash('success')): ?>
                <div class="m-6 mb-0 bg-green-600/20 border border-green-600/40 text-green-300 px-4 py-3 rounded" data-flash><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="m-6 mb-0 bg-atlex-red/20 border border-atlex-red/40 text-red-300 px-4 py-3 rounded" data-flash><?= e($msg) ?></div>
            <?php endif; ?>

            <main class="flex-1 p-6 overflow-x-auto">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php endif; ?>

    <script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
