<?php
/**
 * @var array<int,array<string,mixed>> $latestNews
 * @var array<int,array<string,mixed>> $upcoming
 * @var array<int,array<string,mixed>> $photos
 * @var array<int,array<string,mixed>> $sponsors
 * @var int $memberCount
 * @var int $eventCount
 * @var array<int,array<string,mixed>> $tickerNews
 */
$clubs = [
    ['slug' => 'football', 'name' => 'Football', 'img' => 'images/hero-bg.png'],
    ['slug' => 'basketball', 'name' => 'Basketball', 'img' => 'images/basket-hero.png'],
    ['slug' => 'handball', 'name' => 'Handball', 'img' => 'images/handball-hero.png'],
    ['slug' => 'arts-martiaux', 'name' => 'Arts Martiaux', 'img' => 'images/martial-arts-hero.png'],
];
?>

<!-- HERO -->
<section class="relative min-h-[88vh] flex items-center justify-center overflow-hidden">
    <img src="<?= asset('images/hero-bg.png') ?>" alt="ATLÉX-SPORT" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/80 via-atlex-bg/55 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4 max-w-3xl">
        <p class="font-montserrat uppercase tracking-[0.3em] text-atlex-beige mb-4 text-sm">Cotonou · Bénin</p>
        <h1 class="font-bebas text-6xl sm:text-8xl leading-none tracking-wider">
            ATL<span class="text-atlex-beige">É</span>X<span class="text-atlex-red">·</span>SPORT
        </h1>
        <p class="mt-4 font-montserrat text-lg sm:text-xl text-white/90 italic">Là où l'énergie devient passion.</p>
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <a href="<?= url('/contact#inscription') ?>" class="btn-atlex">Rejoindre le club</a>
            <a href="<?= url('/clubs') ?>" class="btn-atlex-outline">Nos disciplines</a>
        </div>
    </div>
</section>

<!-- STATS BAR -->
<section class="bg-atlex-dark border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-2 md:grid-cols-4 gap-6 py-10 text-center">
        <div class="reveal">
            <div class="font-bebas text-5xl text-atlex-red"><?= e($memberCount) ?>+</div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Membres actifs</div>
        </div>
        <div class="reveal">
            <div class="font-bebas text-5xl text-atlex-red">4</div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Disciplines</div>
        </div>
        <div class="reveal">
            <div class="font-bebas text-5xl text-atlex-red"><?= e($eventCount) ?></div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Événements à venir</div>
        </div>
        <div class="reveal">
            <div class="font-bebas text-5xl text-atlex-red">2023</div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-1">Année de fondation</div>
        </div>
    </div>
</section>

<!-- TICKER -->
<?php require VIEWS_PATH . '/partials/ticker.php'; ?>

<!-- CLUBS GRID -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
    <div class="text-center mb-12 reveal">
        <h2 class="font-bebas text-4xl sm:text-5xl tracking-wider">Nos disciplines</h2>
        <p class="text-white/60 mt-2 font-montserrat">Quatre passions, une seule famille</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($clubs as $club): ?>
            <a href="<?= url('/clubs/' . $club['slug']) ?>" class="group relative h-72 rounded-xl overflow-hidden reveal">
                <img src="<?= asset($club['img']) ?>" alt="<?= e($club['name']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-atlex-bg via-atlex-bg/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-5">
                    <h3 class="font-bebas text-3xl tracking-wider"><?= e($club['name']) ?></h3>
                    <span class="text-atlex-red font-montserrat text-sm uppercase tracking-wide">Découvrir →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- NEWS -->
<?php if (!empty($latestNews)): ?>
<section class="bg-atlex-dark py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-12 reveal">
            <h2 class="font-bebas text-4xl sm:text-5xl tracking-wider">Actualités</h2>
            <a href="<?= url('/actualites') ?>" class="text-atlex-red font-montserrat text-sm uppercase tracking-wide hover:underline">Toutes les actus →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($latestNews as $article): ?>
                <article class="bg-atlex-bg rounded-xl overflow-hidden border border-white/5 reveal">
                    <a href="<?= url('/actualites/' . $article['slug']) ?>">
                        <div class="h-48 bg-atlex-blue/40 overflow-hidden">
                            <?php if (!empty($article['cover_image'])): ?>
                                <img src="<?= asset($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <span class="inline-block text-xs font-montserrat uppercase tracking-wide text-atlex-red mb-2"><?= e(ucfirst((string) $article['category'])) ?></span>
                            <h3 class="font-montserrat font-bold text-lg leading-snug mb-2"><?= e($article['title']) ?></h3>
                            <p class="text-white/60 text-sm line-clamp-3"><?= e($article['excerpt']) ?></p>
                            <p class="text-white/40 text-xs mt-3"><?= e(format_date_fr($article['published_at'] ?? $article['created_at'])) ?></p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CALENDAR PREVIEW -->
<?php if (!empty($upcoming)): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
    <div class="text-center mb-12 reveal">
        <h2 class="font-bebas text-4xl sm:text-5xl tracking-wider">Prochains événements</h2>
    </div>
    <div class="space-y-4 max-w-3xl mx-auto">
        <?php foreach ($upcoming as $event): ?>
            <div class="flex items-center gap-5 bg-atlex-dark rounded-xl p-5 border border-white/5 reveal">
                <div class="text-center flex-shrink-0 w-16">
                    <div class="font-bebas text-3xl text-atlex-red leading-none"><?= e(date('d', strtotime((string) $event['start_datetime']))) ?></div>
                    <div class="font-montserrat uppercase text-xs text-white/60"><?= e(strtoupper(date('M', strtotime((string) $event['start_datetime'])))) ?></div>
                </div>
                <div class="flex-1">
                    <h3 class="font-montserrat font-bold"><?= e($event['title']) ?></h3>
                    <p class="text-white/50 text-sm"><?= e($event['location']) ?> · <?= e(discipline_label($event['discipline'])) ?></p>
                </div>
                <span class="text-xs font-montserrat uppercase tracking-wide px-3 py-1 rounded bg-atlex-red/20 text-atlex-red"><?= e($event['type']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
        <a href="<?= url('/calendrier') ?>" class="btn-atlex-outline">Voir le calendrier</a>
    </div>
</section>
<?php endif; ?>

<!-- GALLERY PREVIEW -->
<?php if (!empty($photos)): ?>
<section class="bg-atlex-dark py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-12 reveal">
            <h2 class="font-bebas text-4xl sm:text-5xl tracking-wider">Galerie</h2>
            <a href="<?= url('/galerie') ?>" class="text-atlex-red font-montserrat text-sm uppercase tracking-wide hover:underline">Toute la galerie →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($photos as $photo): ?>
                <div class="aspect-square rounded-lg overflow-hidden reveal">
                    <img src="<?= asset(str_starts_with((string) $photo['filename'], 'uploads/') || str_starts_with((string) $photo['filename'], 'images/') ? $photo['filename'] : 'images/' . $photo['filename']) ?>"
                         alt="<?= e($photo['alt_text'] ?? $photo['title']) ?>"
                         class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- SPONSORS BAR -->
<?php if (!empty($sponsors)): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <p class="text-center font-montserrat uppercase tracking-widest text-white/40 text-sm mb-8">Ils nous soutiennent</p>
    <div class="flex flex-wrap items-center justify-center gap-8">
        <?php foreach ($sponsors as $sponsor): ?>
            <div class="font-bebas text-2xl text-white/50 hover:text-white transition-colors"><?= e($sponsor['name']) ?></div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="bg-atlex-red">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-16 text-center">
        <h2 class="font-bebas text-4xl sm:text-6xl tracking-wider text-white">Prêt à rejoindre l'aventure ?</h2>
        <p class="text-white/90 font-montserrat mt-3">Intègre la famille ATLÉX-SPORT dès aujourd'hui.</p>
        <a href="<?= url('/contact#inscription') ?>" class="inline-block mt-8 bg-white text-atlex-red font-montserrat font-bold uppercase tracking-wide px-8 py-3 rounded hover:bg-atlex-beige transition-colors">S'inscrire maintenant</a>
    </div>
</section>
