<?php
/**
 * @var array<int,array<string,mixed>> $releases
 * @var array<string,array<int,array<string,mixed>>> $kit  groupé par catégorie
 * @var array<int,array<string,mixed>> $coverage
 * @var array<string,string|null> $contact
 */
?>
<section class="relative h-[36vh] min-h-[260px] flex items-center justify-center overflow-hidden">
    <?= responsive_image('images/team-photo.png', '', 'absolute inset-0 w-full h-full object-cover', ['eager' => true]) ?>
    <div class="absolute inset-0 bg-gradient-to-b from-atlex-bg/75 to-atlex-bg"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="font-bebas text-5xl sm:text-7xl tracking-wider">Centre média</h1>
        <p class="font-montserrat uppercase tracking-widest text-atlex-beige mt-2">Communiqués, ressources et contact presse</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 space-y-16">

    <!-- Communiqués de presse -->
    <div>
        <h2 class="font-bebas text-3xl tracking-wider mb-6">Communiqués de presse</h2>
        <?php if (empty($releases)): ?>
            <p class="text-white/50 font-montserrat">Aucun communiqué pour le moment.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($releases as $r): ?>
                    <article class="bg-atlex-dark rounded-xl border border-white/5 p-5 hover:border-atlex-red/40 transition-colors">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-white/40 text-xs font-montserrat mb-1">
                                    <?= e(format_date_fr($r['published_at'] ?? $r['created_at'])) ?>
                                    <?php if (!empty($r['reference'])): ?> · <?= e($r['reference']) ?><?php endif; ?>
                                </div>
                                <h3 class="font-montserrat font-semibold">
                                    <a href="<?= url('/centre-media/communiques/' . $r['slug']) ?>" class="hover:text-atlex-beige"><?= e($r['title']) ?></a>
                                </h3>
                                <?php if (!empty($r['excerpt'])): ?><p class="text-white/60 text-sm mt-1 line-clamp-2"><?= e($r['excerpt']) ?></p><?php endif; ?>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <a href="<?= url('/centre-media/communiques/' . $r['slug']) ?>" class="text-atlex-beige text-sm hover:underline whitespace-nowrap">Lire →</a>
                                <?php if (!empty($r['file'])): ?>
                                    <a href="<?= url($r['file']) ?>" download class="btn-atlex-outline text-xs whitespace-nowrap">PDF</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Kit presse -->
    <?php if (!empty($kit)): ?>
        <div>
            <h2 class="font-bebas text-3xl tracking-wider mb-6">Kit presse</h2>
            <?php foreach ($kit as $category => $items): ?>
                <h3 class="font-montserrat uppercase text-xs tracking-widest text-atlex-beige mt-6 mb-3"><?= e(press_kit_category_label($category)) ?></h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($items as $item): ?>
                        <a href="<?= url($item['file']) ?>" download class="flex items-center gap-3 bg-atlex-dark border border-white/5 rounded-xl px-5 py-4 hover:border-atlex-red/40 transition-colors">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-atlex-red flex-shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            <span class="min-w-0">
                                <span class="block font-montserrat text-sm font-semibold truncate"><?= e($item['title']) ?></span>
                                <?php if (!empty($item['description'])): ?><span class="block text-white/40 text-xs truncate"><?= e($item['description']) ?></span><?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Revue de presse -->
    <?php if (!empty($coverage)): ?>
        <div>
            <h2 class="font-bebas text-3xl tracking-wider mb-6">Ils parlent de nous</h2>
            <ul class="space-y-2">
                <?php foreach ($coverage as $c): ?>
                    <?php if ($link = safe_url($c['url'])): ?>
                        <li class="flex items-center gap-3 bg-atlex-dark border border-white/5 rounded-lg px-5 py-3">
                            <span class="flex-1 min-w-0">
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" class="font-montserrat text-sm hover:text-atlex-beige"><?= e($c['title']) ?> ↗</a>
                                <?php if (!empty($c['media_name'])): ?><span class="block text-white/40 text-xs"><?= e($c['media_name']) ?></span><?php endif; ?>
                            </span>
                            <?php if (!empty($c['published_date'])): ?><span class="text-white/40 text-xs whitespace-nowrap"><?= e(format_date_fr($c['published_date'])) ?></span><?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Contact presse -->
    <?php if (!empty($contact['press_contact_email']) || !empty($contact['press_contact_name'])): ?>
        <div class="bg-atlex-dark rounded-xl border border-white/5 p-8 text-center">
            <h2 class="font-bebas text-3xl tracking-wider mb-3">Contact presse</h2>
            <?php if (!empty($contact['press_contact_name'])): ?><p class="font-montserrat"><?= e($contact['press_contact_name']) ?></p><?php endif; ?>
            <?php if (!empty($contact['press_contact_email'])): ?><p><a href="mailto:<?= e($contact['press_contact_email']) ?>" class="text-atlex-beige hover:underline"><?= e($contact['press_contact_email']) ?></a></p><?php endif; ?>
            <?php if (!empty($contact['press_contact_phone'])): ?><p class="text-white/60 text-sm mt-1"><?= e($contact['press_contact_phone']) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
