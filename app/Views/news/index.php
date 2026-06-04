<?php
/**
 * @var array<int,array<string,mixed>> $articles
 * @var array<string,mixed>|null $featured
 * @var int $page
 * @var int $totalPages
 * @var string|null $category
 */
$categories = [
    '' => 'Toutes', 'resultat' => 'Résultats', 'recrutement' => 'Recrutement',
    'evenement' => 'Événements', 'partenariat' => 'Partenariats',
    'rapport' => "Rapports d'activité", 'general' => 'Général',
];
$rest = array_slice($articles, 1);
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Actualités</h1>
    <p class="text-white/60 font-montserrat mb-10">Toute la vie du club ATLEX - Sport</p>

    <!-- Filtres catégorie -->
    <div class="flex flex-wrap gap-2 mb-10">
        <?php foreach ($categories as $key => $label): ?>
            <a href="<?= url('/actualites' . ($key ? '?categorie=' . $key : '')) ?>"
               class="px-4 py-1.5 rounded-full text-sm font-montserrat transition-colors <?= ($category ?? '') === $key || ($category === null && $key === '') ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($articles)): ?>
        <p class="text-white/50 font-montserrat">Aucun article pour le moment.</p>
    <?php else: ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Article à la une -->
        <?php if ($featured): ?>
            <article class="lg:col-span-2 bg-atlex-dark rounded-xl overflow-hidden border border-white/5">
                <a href="<?= url('/actualites/' . $featured['slug']) ?>">
                    <div class="h-72 bg-atlex-blue/40 overflow-hidden">
                        <?php if (!empty($featured['cover_image'])): ?>
                            <img src="<?= asset($featured['cover_image']) ?>" alt="<?= e($featured['title']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <span class="text-xs font-montserrat uppercase tracking-wide text-atlex-red"><?= e(news_category_label($featured['category'])) ?></span>
                        <h2 class="font-bebas text-3xl tracking-wide mt-2 mb-3"><?= e($featured['title']) ?></h2>
                        <p class="text-white/60"><?= e($featured['excerpt']) ?></p>
                        <p class="text-white/40 text-xs mt-4"><?= e(format_date_fr($featured['published_at'] ?? $featured['created_at'])) ?></p>
                    </div>
                </a>
            </article>
        <?php endif; ?>

        <!-- Sidebar : derniers titres -->
        <aside class="space-y-4">
            <h3 class="font-bebas text-2xl tracking-wider">À lire aussi</h3>
            <?php foreach (array_slice($rest, 0, 4) as $a): ?>
                <a href="<?= url('/actualites/' . $a['slug']) ?>" class="block bg-atlex-dark rounded-lg p-4 border border-white/5 hover:border-atlex-red/40 transition-colors">
                    <span class="text-xs text-atlex-red font-montserrat uppercase"><?= e(news_category_label($a['category'])) ?></span>
                    <h4 class="font-montserrat font-semibold text-sm mt-1"><?= e($a['title']) ?></h4>
                </a>
            <?php endforeach; ?>
        </aside>
    </div>

    <!-- Grille -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($rest as $article): ?>
            <article class="bg-atlex-dark rounded-xl overflow-hidden border border-white/5 reveal">
                <a href="<?= url('/actualites/' . $article['slug']) ?>">
                    <div class="h-44 bg-atlex-blue/40 overflow-hidden">
                        <?php if (!empty($article['cover_image'])): ?>
                            <img src="<?= asset($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <span class="text-xs font-montserrat uppercase tracking-wide text-atlex-red"><?= e(news_category_label($article['category'])) ?></span>
                        <h3 class="font-montserrat font-bold mt-1 mb-2 leading-snug"><?= e($article['title']) ?></h3>
                        <p class="text-white/60 text-sm line-clamp-2"><?= e($article['excerpt']) ?></p>
                        <p class="text-white/40 text-xs mt-3"><?= e(format_date_fr($article['published_at'] ?? $article['created_at'])) ?></p>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-12">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="<?= url('/actualites?page=' . $p . ($category ? '&categorie=' . $category : '')) ?>"
                   class="w-10 h-10 grid place-items-center rounded font-montserrat <?= $p === $page ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <?php endif; ?>
</section>
