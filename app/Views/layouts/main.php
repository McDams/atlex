<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?></title>
    <meta name="description" content="ATLÉX-SPORT — Association sportive béninoise. Football, Basketball, Handball, Arts Martiaux. Là où l'énergie devient passion.">

    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CDN Play (dev) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'atlex-red': '#E53935',
                        'atlex-blue': '#003366',
                        'atlex-dark': '#001a3d',
                        'atlex-bg': '#0a0e1a',
                        'atlex-beige': '#D7B899',
                    },
                    fontFamily: {
                        bebas: ['"Bebas Neue"', 'sans-serif'],
                        montserrat: ['Montserrat', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
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
