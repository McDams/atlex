<?php
/** @var array<string,mixed> $release */
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <a href="<?= url('/centre-media') ?>" class="text-white/60 hover:text-white text-sm font-montserrat">← Centre média</a>

    <div class="flex flex-wrap items-center gap-3 mt-6 mb-3">
        <span class="text-xs font-montserrat uppercase tracking-wide px-3 py-1 rounded bg-atlex-red text-white">Communiqué</span>
        <span class="text-white/40 text-sm"><?= e(format_date_fr($release['published_at'] ?? $release['created_at'])) ?></span>
        <?php if (!empty($release['reference'])): ?><span class="text-white/40 text-sm">· <?= e($release['reference']) ?></span><?php endif; ?>
    </div>

    <h1 class="font-bebas text-4xl sm:text-5xl tracking-wide leading-tight mb-6"><?= e($release['title']) ?></h1>

    <?php if (!empty($release['excerpt'])): ?>
        <p class="text-lg text-atlex-beige font-montserrat italic mb-6"><?= e($release['excerpt']) ?></p>
    <?php endif; ?>

    <?php if (!empty($release['content'])): ?>
        <div class="prose prose-invert max-w-none text-white/80 leading-relaxed space-y-4">
            <?php foreach (preg_split('/\n\s*\n/', (string) $release['content']) as $paragraph): ?>
                <?php if (trim($paragraph) !== ''): ?><p><?= nl2br(e($paragraph)) ?></p><?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($release['file'])): ?>
        <a href="<?= url($release['file']) ?>" download class="btn-atlex inline-flex items-center gap-2 mt-8">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Télécharger le communiqué (PDF)
        </a>
    <?php endif; ?>
</article>
