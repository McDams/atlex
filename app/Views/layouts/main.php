<?php
// --- SEO : valeurs dynamiques par page ---
$pageTitle = $title ?? APP_NAME;

$pageDescription = $description
    ?? 'ATLEX - Sport — Association sportive béninoise à Cotonou. Football, Basketball, Handball et Arts Martiaux. Là où l\'énergie devient passion.';

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalUrl = $canonical ?? (rtrim(APP_URL, '/') . $currentPath);

$ogType = $ogType ?? 'website';
$metaRobots = $metaRobots ?? 'index, follow';

$defaultOgImage = 'images/hero-bg.png';
$pageImage = asset($ogImage ?? $defaultOgImage);

// Sécurisation légère des longueurs pour éviter des tags trop longs
$seoTitle = trim($pageTitle);
$seoDescription = trim($pageDescription);

if (function_exists('mb_substr')) {
    $seoTitle = mb_substr($seoTitle, 0, 65);
    $seoDescription = mb_substr($seoDescription, 0, 160);
} else {
    $seoTitle = substr($seoTitle, 0, 65);
    $seoDescription = substr($seoDescription, 0, 160);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDescription) ?>">
    <meta name="robots" content="<?= e($metaRobots) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:site_name" content="ATLEX - Sport">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e($seoTitle) ?>">
    <meta property="og:locale" content="fr_FR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDescription) ?>">
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