<?php
/** @var array<string,mixed> $discipline */
?>
<section class="relative h-[55vh] min-h-[360px] flex items-end overflow-hidden">
    <?= responsive_image($discipline['image'], $discipline['name'], 'absolute inset-0 w-full h-full object-cover', ['eager' => true]) ?>
    <div class="absolute inset-0 bg-gradient-to-t from-atlex-bg via-atlex-bg/40 to-transparent"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 pb-12 w-full">
        <a href="<?= url('/clubs') ?>" class="text-white/70 hover:text-white text-sm font-montserrat">← Toutes les disciplines</a>
        <h1 class="font-bebas text-6xl sm:text-8xl tracking-wider mt-2"><?= e($discipline['name']) ?></h1>
        <p class="text-atlex-beige font-montserrat text-lg"><?= e($discipline['tagline']) ?></p>
    </div>
</section>

<section class="max-w-5xl mx-auto px-4 sm:px-6 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-atlex-dark rounded-xl p-6 text-center border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red"><?= e($discipline['member_count']) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Membres actifs</div>
        </div>
        <div class="bg-atlex-dark rounded-xl p-6 text-center border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red"><?= e(count($discipline['schedule'])) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Créneaux / semaine</div>
        </div>
        <div class="bg-atlex-dark rounded-xl p-6 text-center border border-white/5">
            <div class="font-bebas text-2xl text-atlex-red mt-3">ATLEX</div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Encadrement</div>
        </div>
    </div>

    <h2 class="font-bebas text-3xl tracking-wider mb-4">Présentation</h2>
    <p class="text-white/70 font-poppins leading-relaxed mb-10"><?= e($discipline['description']) ?></p>

    <h2 class="font-bebas text-3xl tracking-wider mb-4">Horaires d'entraînement</h2>
    <ul class="space-y-3 mb-10">
        <?php foreach ($discipline['schedule'] as $slot): ?>
            <li class="flex items-center gap-3 bg-atlex-dark rounded-lg px-5 py-3 border border-white/5">
                <span class="w-2 h-2 rounded-full bg-atlex-red"></span>
                <span class="font-montserrat"><?= e($slot) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="text-center">
        <a href="<?= url('/contact#inscription') ?>" class="btn-atlex">S'inscrire à cette discipline</a>
    </div>
</section>
