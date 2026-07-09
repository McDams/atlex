<?php
/**
 * @var array<string,mixed> $overview7
 * @var array<string,mixed> $overview30
 * @var array<int,array<string,mixed>> $dailySeries
 * @var array<int,array<string,mixed>> $topPages
 * @var array<int,array<string,mixed>> $topCountries
 * @var array<int,array<string,mixed>> $topSources
 * @var array<int,array<string,mixed>> $devices
 * @var array<int,array<string,mixed>> $browsers
 * @var float $bounceRate
 */
?>

<section class="space-y-8">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="font-bebas text-4xl tracking-wider text-white">Analytics</h1>
            <p class="text-white/60 font-montserrat text-sm">
                Vue d’ensemble du trafic du site.
            </p>
        </div>
        <a href="<?= url('/admin') ?>" class="text-sm font-montserrat text-atlex-red">
            ← Retour au dashboard
        </a>
    </header>

    <!-- KPIs 7 derniers jours -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red leading-none">
                <?= e((string) ($overview7['pageviews'] ?? 0)) ?>
            </div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2">
                Pages vues (7 j)
            </div>
        </div>

        <div class="bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red leading-none">
                <?= e((string) ($overview7['visitors'] ?? 0)) ?>
            </div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2">
                Visiteurs uniques (7 j)
            </div>
        </div>

        <div class="bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red leading-none">
                <?= e((string) ($overview7['sessions'] ?? 0)) ?>
            </div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2">
                Sessions (7 j)
            </div>
        </div>

        <div class="bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="font-bebas text-5xl text-atlex-red leading-none">
                <?= e(number_format((float) $bounceRate, 1, ',', ' ')) ?>%
            </div>
            <div class="font-montserrat uppercase text-xs tracking-widest text-white/60 mt-2">
                Taux de rebond
            </div>
        </div>
    </div>

    <!-- Série des 30 derniers jours -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="font-bebas text-xl tracking-wider text-white">
                Trafic sur 30 jours
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-white/50">
                        <th class="px-6 py-3 text-left font-montserrat">Date</th>
                        <th class="px-6 py-3 text-left font-montserrat">Pages vues</th>
                        <th class="px-6 py-3 text-left font-montserrat">Visiteurs</th>
                        <th class="px-6 py-3 text-left font-montserrat">Sessions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dailySeries)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-white/40">
                                Aucune donnée disponible.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dailySeries as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3"><?= e((string) ($row['date'] ?? $row['visit_date'] ?? '')) ?></td>
                                <td class="px-6 py-3"><?= e((string) ($row['pageviews'] ?? 0)) ?></td>
                                <td class="px-6 py-3"><?= e((string) ($row['visitors'] ?? 0)) ?></td>
                                <td class="px-6 py-3"><?= e((string) ($row['sessions'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top pages / pays / sources -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Top pages -->
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Top pages</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($topPages)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topPages as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3">
                                    <div class="font-montserrat text-white">
                                        <?= e((string) ($row['page_title'] ?? $row['page_name'] ?? $row['page_path'] ?? '')) ?>
                                    </div>
                                    <div class="text-xs text-white/40">
                                        <?= e((string) ($row['page_path'] ?? '')) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right text-atlex-red font-semibold">
                                    <?= e((string) ($row['pageviews'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top pays -->
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Top pays</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($topCountries)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topCountries as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3 font-montserrat text-white">
                                    <?= e((string) ($row['country_name'] ?? 'Inconnu')) ?>
                                </td>
                                <td class="px-6 py-3 text-right text-atlex-red font-semibold">
                                    <?= e((string) ($row['pageviews'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sources -->
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Sources</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($topSources)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topSources as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3 font-montserrat text-white">
                                    <?= e((string) ($row['source_name'] ?? $row['referrer_source'] ?? 'direct')) ?>
                                </td>
                                <td class="px-6 py-3 text-right text-atlex-red font-semibold">
                                    <?= e((string) ($row['pageviews'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Devices / navigateurs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Appareils</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($devices)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($devices as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3 font-montserrat text-white">
                                    <?= e((string) ($row['device_type'] ?? 'Inconnu')) ?>
                                </td>
                                <td class="px-6 py-3 text-right text-atlex-red font-semibold">
                                    <?= e((string) ($row['pageviews'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Navigateurs</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($browsers)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($browsers as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3 font-montserrat text-white">
                                    <?= e((string) ($row['browser_name'] ?? 'Autre')) ?>
                                </td>
                                <td class="px-6 py-3 text-right text-atlex-red font-semibold">
                                    <?= e((string) ($row['pageviews'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>