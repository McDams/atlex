<?php
/**
 * @var array<int,array<string,mixed>> $athletes
 * @var array<string,int>              $counts
 * @var string|null                    $discipline
 */
$total = array_sum($counts);
?>
<section class="relative h-[40vh] min-h-[300px] flex items-center justify-center overflow-hidden">
    <?= responsive_image('images/team-photo.png', '', 'absolute inset-0 w-full h-full object-cover', ['eager' => true]) ?>
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/75 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="font-bebas text-5xl sm:text-7xl tracking-wider">Nos athlètes</h1>
        <p class="font-montserrat uppercase tracking-widest text-atlex-beige mt-2">Celles et ceux qui portent nos couleurs</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <!-- Filtres par discipline -->
    <div class="flex flex-wrap justify-center gap-3 mb-12 font-montserrat text-sm uppercase tracking-wide">
        <a href="<?= url('/athletes') ?>"
           class="px-4 py-2 rounded-full border transition-colors <?= $discipline === null ? 'bg-atlex-red border-atlex-red text-white' : 'border-white/15 text-white/70 hover:text-white' ?>">
            Tous (<?= e($total) ?>)
        </a>
        <?php foreach (ATLEX_DISCIPLINES as $key => $label): ?>
            <a href="<?= url('/athletes?discipline=' . $key) ?>"
               class="px-4 py-2 rounded-full border transition-colors <?= $discipline === $key ? 'bg-atlex-red border-atlex-red text-white' : 'border-white/15 text-white/70 hover:text-white' ?>">
                <?= e($label) ?> (<?= e($counts[$key] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($athletes)): ?>
        <p class="text-center text-white/50 font-montserrat py-12">Aucun athlète à afficher pour le moment.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($athletes as $a): ?>
                <a href="<?= url('/athletes/' . $a['slug']) ?>" class="group relative rounded-xl overflow-hidden aspect-[3/4] bg-atlex-dark border border-white/5 reveal">
                    <?php if (!empty($a['photo'])): ?>
                        <img src="<?= url($a['photo']) ?>" alt="<?= e($a['first_name'] . ' ' . $a['last_name']) ?>" loading="lazy" decoding="async"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="absolute inset-0 grid place-items-center font-bebas text-6xl text-white/20">
                            <?= e(strtoupper(mb_substr((string) $a['first_name'], 0, 1) . mb_substr((string) $a['last_name'], 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-atlex-bg via-atlex-bg/30 to-transparent"></div>
                    <div class="absolute bottom-0 p-4 w-full">
                        <span class="inline-block text-[10px] uppercase tracking-widest text-atlex-beige font-montserrat mb-1"><?= e(discipline_label($a['discipline'])) ?></span>
                        <h3 class="font-bebas text-2xl tracking-wider leading-none"><?= e($a['first_name']) ?> <?= e($a['last_name']) ?></h3>
                        <?php if (!empty($a['ranking'])): ?>
                            <p class="text-white/60 text-xs font-montserrat mt-1 line-clamp-1"><?= e($a['ranking']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
