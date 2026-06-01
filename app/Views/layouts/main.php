<?php
// --- SEO : valeurs par page (surchargées via les données de la vue) ---
$pageTitle       = $title ?? APP_NAME;
$pageDescription = $description ?? 'ATLEX - Sport — Association sportive béninoise à Cotonou. Football, Basketball, Handball et Arts Martiaux. Là où l\'énergie devient passion.';
$pageImage       = asset($ogImage ?? 'images/hero-bg.png');
$currentPath     = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalUrl    = $canonical ?? (rtrim(APP_URL, '/') . $currentPath);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Open Graph / réseaux sociaux -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ATLEX - Sport">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">

    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/app.min.css') ?>">
</head>
<body class="bg-atlex-bg text-white font-poppins antialiased <?= e($bodyClass ?? '') ?>">

    <?php require VIEWS_PATH . '/partials/nav.php'; ?>

    <?php if ($msg = flash('success')): ?>
        <div class="fixed top-24 right-4 z-50 bg-green-600 text-white px-5 py-3 rounded shadow-lg" data-flash>
            <?= e($msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="fixed top-24 right-4 z-50 bg-atlex-red text-white px-5 py-3 rounded shadow-lg" data-flash>
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require VIEWS_PATH . '/partials/footer.php'; ?>

    <script src="<?= asset('js/app.js') ?>" defer></script>
    <?php foreach (($scripts ?? []) as $script): ?>
        <script src="<?= asset('js/' . $script) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
