<?php
/**
 * Partial hero réutilisable.
 *
 * @var string $heroTitle
 * @var string $heroSubtitle
 * @var string $heroImage  chemin asset relatif
 */
$heroImage = $heroImage ?? 'images/hero-bg.png';
?>
<section class="relative h-[42vh] min-h-[320px] flex items-center justify-center overflow-hidden">
    <img src="<?= asset($heroImage) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/70 via-atlex-bg/60 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="font-bebas text-5xl sm:text-7xl tracking-wider text-white"><?= e($heroTitle ?? '') ?></h1>
        <?php if (!empty($heroSubtitle)): ?>
            <p class="mt-3 font-montserrat uppercase tracking-widest text-atlex-beige"><?= e($heroSubtitle) ?></p>
        <?php endif; ?>
    </div>
</section>
