<?php
/**
 * @var array<string,mixed> $article
 * @var array<int,array<string,mixed>> $related
 */
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <a href="<?= url('/actualites') ?>" class="text-white/60 hover:text-white text-sm font-montserrat">← Retour aux actualités</a>

    <div class="flex items-center gap-3 mt-6 mb-4">
        <span class="text-xs font-montserrat uppercase tracking-wide px-3 py-1 rounded bg-atlex-red text-white"><?= e(news_category_label($article['category'])) ?></span>
        <span class="text-white/40 text-sm"><?= e(format_date_fr($article['published_at'] ?? $article['created_at'])) ?></span>
    </div>

    <h1 class="font-bebas text-4xl sm:text-5xl tracking-wide leading-tight mb-6"><?= e($article['title']) ?></h1>

    <?php if (!empty($article['cover_image'])): ?>
        <div class="rounded-xl overflow-hidden mb-8">
            <img src="<?= url($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" class="w-full object-cover">
        </div>
    <?php endif; ?>

    <?php if (!empty($article['excerpt'])): ?>
        <p class="text-lg text-atlex-beige font-montserrat italic mb-6"><?= e($article['excerpt']) ?></p>
    <?php endif; ?>

    <div class="prose prose-invert max-w-none text-white/80 leading-relaxed space-y-4">
        <?php foreach (preg_split('/\n\s*\n/', (string) $article['content']) as $paragraph): ?>
            <?php if (trim($paragraph) !== ''): ?>
                <p><?= nl2br(e($paragraph)) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</article>

<?php if (!empty($related)): ?>
<section class="bg-atlex-dark py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <h2 class="font-bebas text-3xl tracking-wider mb-8">Autres actualités</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($related as $r): ?>
                <?php if ($r['id'] === $article['id']) { continue; } ?>
                <a href="<?= url('/actualites/' . $r['slug']) ?>" class="block bg-atlex-bg rounded-xl p-5 border border-white/5 hover:border-atlex-red/40 transition-colors">
                    <span class="text-xs text-atlex-red font-montserrat uppercase"><?= e(news_category_label($r['category'])) ?></span>
                    <h3 class="font-montserrat font-bold mt-1"><?= e($r['title']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
