<?php
/**
 * @var array<string,mixed> $athlete
 */
$achievements = $athlete['achievements'] ?? [];
$results      = $athlete['results'] ?? [];
$videos       = $athlete['videos'] ?? [];
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-12">
    <a href="<?= url('/athletes') ?>" class="text-white/50 text-sm font-montserrat hover:text-white">← Tous les athlètes</a>

    <!-- En-tête profil -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
        <div class="md:col-span-1">
            <div class="rounded-xl overflow-hidden aspect-[3/4] bg-atlex-dark border border-white/5">
                <?php if (!empty($athlete['photo'])): ?>
                    <img src="<?= url($athlete['photo']) ?>" alt="<?= e($athlete['first_name'] . ' ' . $athlete['last_name']) ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full grid place-items-center font-bebas text-7xl text-white/20">
                        <?= e(strtoupper(mb_substr((string) $athlete['first_name'], 0, 1) . mb_substr((string) $athlete['last_name'], 0, 1))) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="md:col-span-2 flex flex-col justify-center">
            <span class="inline-block w-fit text-xs uppercase tracking-widest text-atlex-beige font-montserrat mb-2"><?= e(discipline_label($athlete['discipline'])) ?></span>
            <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider leading-none"><?= e($athlete['first_name']) ?> <?= e($athlete['last_name']) ?></h1>

            <div class="flex flex-wrap gap-x-8 gap-y-2 mt-4 font-montserrat text-sm">
                <?php if (!empty($athlete['category'])): ?>
                    <div><span class="text-white/40 uppercase text-xs block">Catégorie</span><span class="text-white"><?= e($athlete['category']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($athlete['ranking'])): ?>
                    <div><span class="text-white/40 uppercase text-xs block">Classement</span><span class="text-white"><?= e($athlete['ranking']) ?></span></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($athlete['bio'])): ?>
                <p class="text-white/70 font-poppins leading-relaxed mt-5 whitespace-pre-line"><?= e($athlete['bio']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Palmarès -->
    <?php if (!empty($achievements)): ?>
        <div class="mt-14">
            <h2 class="font-bebas text-3xl tracking-wider mb-5">Palmarès</h2>
            <ul class="space-y-2">
                <?php foreach ($achievements as $ach): ?>
                    <li class="flex items-center gap-4 bg-atlex-dark border border-white/5 rounded-lg px-5 py-3">
                        <?php if (!empty($ach['year'])): ?>
                            <span class="font-bebas text-2xl text-atlex-red w-16 flex-shrink-0"><?= e($ach['year']) ?></span>
                        <?php endif; ?>
                        <span class="flex-1 font-montserrat"><?= e($ach['title']) ?></span>
                        <?php if (!empty($ach['position'])): ?>
                            <span class="text-xs px-2 py-1 rounded bg-atlex-beige/15 text-atlex-beige whitespace-nowrap"><?= e($ach['position']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Résultats -->
    <?php if (!empty($results)): ?>
        <div class="mt-14">
            <h2 class="font-bebas text-3xl tracking-wider mb-5">Résultats</h2>
            <div class="bg-atlex-dark border border-white/5 rounded-xl overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
                        <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Compétition</th><th class="px-5 py-3 text-right">Résultat</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-5 py-3 text-white/60 whitespace-nowrap"><?= e(format_date_fr($r['result_date'] ?? null)) ?: '—' ?></td>
                                <td class="px-5 py-3 font-montserrat"><?= e($r['competition']) ?></td>
                                <td class="px-5 py-3 text-right text-atlex-beige whitespace-nowrap"><?= e($r['result'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vidéos -->
    <?php if (!empty($videos)): ?>
        <div class="mt-14">
            <h2 class="font-bebas text-3xl tracking-wider mb-5">Vidéos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($videos as $v): ?>
                    <?php $embed = youtube_embed_url((string) $v['url']); ?>
                    <div>
                        <?php if ($embed !== null): ?>
                            <div class="aspect-video rounded-xl overflow-hidden border border-white/5">
                                <iframe src="<?= e($embed) ?>" title="<?= e($v['title'] ?: 'Vidéo') ?>" class="w-full h-full"
                                        loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        <?php elseif ($link = safe_url($v['url'])): ?>
                            <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer"
                               class="flex items-center gap-3 bg-atlex-dark border border-white/5 rounded-xl px-5 py-4 hover:border-atlex-red/40 transition-colors">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-atlex-red"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span class="font-montserrat text-sm"><?= e($v['title'] ?: 'Voir la vidéo') ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($embed !== null && !empty($v['title'])): ?>
                            <p class="text-white/60 text-sm font-montserrat mt-2"><?= e($v['title']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
