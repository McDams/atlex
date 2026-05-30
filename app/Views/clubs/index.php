<?php
/** @var array<string,array<string,mixed>> $disciplines */
?>
<section class="relative h-[40vh] min-h-[300px] flex items-center justify-center overflow-hidden">
    <img src="<?= asset('images/team-photo.png') ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/75 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="font-bebas text-5xl sm:text-7xl tracking-wider">Nos clubs</h1>
        <p class="font-montserrat uppercase tracking-widest text-atlex-beige mt-2">Quatre disciplines d'excellence</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
    <h2 class="font-bebas text-4xl tracking-wider text-center mb-12">Toutes nos disciplines</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach ($disciplines as $d): ?>
            <div class="group relative rounded-xl overflow-hidden h-80 reveal">
                <img src="<?= asset($d['image']) ?>" alt="<?= e($d['name']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-atlex-bg via-atlex-bg/40 to-transparent"></div>
                <div class="absolute bottom-0 p-6 w-full">
                    <h3 class="font-bebas text-4xl tracking-wider"><?= e($d['name']) ?></h3>
                    <p class="text-atlex-beige font-montserrat text-sm mb-3"><?= e($d['tagline']) ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-white/70 text-sm font-montserrat">
                            <strong class="text-atlex-red text-lg"><?= e($d['member_count']) ?></strong> membres actifs
                        </span>
                        <a href="<?= url('/clubs/' . $d['slug']) ?>" class="btn-atlex text-xs">Découvrir</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
