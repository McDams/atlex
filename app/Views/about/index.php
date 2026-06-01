<?php
/**
 * @var array<int,array{year:string,label:string}> $timeline
 * @var array<int,array{title:string,text:string}> $values
 */
?>
<section class="relative h-[45vh] min-h-[320px] flex items-center justify-center overflow-hidden">
    <img src="<?= asset('images/about-team.png') ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/75 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="font-bebas text-5xl sm:text-7xl tracking-wider">À propos</h1>
        <p class="font-montserrat uppercase tracking-widest text-atlex-beige mt-2">ATLANTIS EXPERTISE SPORT</p>
    </div>
</section>

<!-- Histoire -->
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <h2 class="font-bebas text-4xl tracking-wider mb-6">Notre histoire</h2>
    <p class="text-white/70 leading-relaxed mb-4">
        Fondée le 26 août 2023 à Cotonou, l'association ATLANTIS EXPERTISE SPORT (ATLEX - Sport)
        rassemble des passionnés autour de quatre disciplines : le football, le basketball,
        le handball et les arts martiaux.
    </p>
    <p class="text-white/70 leading-relaxed">
        Portée par sa devise « Là où l'énergie devient passion », ATLEX - Sport œuvre pour
        la formation sportive, la cohésion sociale et l'épanouissement de la jeunesse béninoise.
    </p>
</section>

<!-- Timeline -->
<section class="bg-atlex-dark py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h2 class="font-bebas text-4xl tracking-wider mb-10 text-center">Notre parcours</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <?php foreach ($timeline as $item): ?>
                <div class="text-center reveal">
                    <div class="font-bebas text-5xl text-atlex-red"><?= e($item['year']) ?></div>
                    <p class="text-white/60 text-sm font-montserrat mt-2"><?= e($item['label']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Valeurs -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h2 class="font-bebas text-4xl tracking-wider mb-10 text-center">Nos valeurs</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($values as $value): ?>
            <div class="bg-atlex-dark rounded-xl p-6 border border-white/5 reveal">
                <h3 class="font-bebas text-2xl tracking-wider text-atlex-red mb-2"><?= e($value['title']) ?></h3>
                <p class="text-white/60 text-sm"><?= e($value['text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Équipe -->
<section class="bg-atlex-dark py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <img src="<?= asset('images/team-photo.png') ?>" alt="L'équipe ATLEX - Sport" class="rounded-xl w-full object-cover">
        <div>
            <h2 class="font-bebas text-4xl tracking-wider mb-4">Notre équipe</h2>
            <p class="text-white/70 leading-relaxed mb-4">
                Une équipe dirigeante engagée encadre les activités de l'association.
                Le Secrétariat Général, conduit par Ulrich, assure la coordination
                quotidienne des opérations.
            </p>
            <div class="bg-atlex-bg rounded-lg p-4 border border-white/5 inline-block">
                <p class="font-montserrat font-bold">Ulrich</p>
                <p class="text-atlex-beige text-sm">Secrétaire Général</p>
            </div>
        </div>
    </div>
</section>

<!-- Reconnaissance officielle -->
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16 text-center">
    <h2 class="font-bebas text-3xl tracking-wider mb-4">Reconnaissance officielle</h2>
    <p class="text-white/60">
        ATLANTIS EXPERTISE SPORT est une association légalement constituée à Cotonou, République du Bénin.
    </p>
</section>
