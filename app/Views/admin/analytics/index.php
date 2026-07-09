<?php
/**
 * @var array<string,mixed> $overview7
 * @var array<string,mixed> $overview30
 * @var array<int,array<string,mixed>> $dailySeries
 * @var array<int,array<string,mixed>> $topPages
 * @var array<int,array<string,mixed>> $topCountries
 * @var array<int,array<string,mixed>> $topCities
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
                        <?php $maxCountryViews = max(array_column($topCountries, 'pageviews')); ?>
                        <?php foreach ($topCountries as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2 font-montserrat text-white">
                                        <span aria-hidden="true"><?= country_flag($row['country_code'] ?? null) ?></span>
                                        <span><?= e((string) ($row['country_name'] ?? 'Inconnu')) ?></span>
                                    </div>
                                    <div class="mt-1 h-1 bg-white/5 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-atlex-red rounded-full"
                                            style="width: <?= (int) round(((int) $row['pageviews'] / max(1, $maxCountryViews)) * 100) ?>%"
                                        ></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-atlex-red font-semibold align-top">
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

    <?php
        // Points de la carte : projection équirectangulaire simple
        // (lon/lat -> x/y), rayon proportionnel à la racine du nombre de
        // vues pour une échelle de surface perceptuellement correcte.
        $mapWidth = 1000;
        $mapHeight = 500;
        $mapPoints = [];
        $maxCityViews = !empty($topCities) ? max(array_column($topCities, 'pageviews')) : 0;

        foreach ($topCities as $row) {
            if ($row['latitude'] === null || $row['longitude'] === null) {
                continue;
            }

            $lat = (float) $row['latitude'];
            $lon = (float) $row['longitude'];
            $views = (int) $row['pageviews'];

            $mapPoints[] = [
                'x' => round((($lon + 180) / 360) * $mapWidth, 1),
                'y' => round(((90 - $lat) / 180) * $mapHeight, 1),
                'r' => round(4 + sqrt($views / max(1, $maxCityViews)) * 16, 1),
                'label' => trim((string) ($row['city_name'] ?? '') . ', ' . (string) ($row['country_name'] ?? '')),
                'views' => $views,
            ];
        }
    ?>

    <!-- Carte géographique + Top villes -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Carte -->
        <div class="xl:col-span-2 bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between gap-3">
                <h2 class="font-bebas text-xl tracking-wider text-white">Carte des visites</h2>
                <span class="text-xs text-white/40 font-montserrat whitespace-nowrap">taille = nombre de vues</span>
            </div>
            <div class="p-4">
                <?php if (empty($mapPoints)): ?>
                    <p class="px-2 py-10 text-center text-white/40 font-montserrat text-sm">
                        Aucune donnée de localisation pour le moment.
                    </p>
                <?php else: ?>
                    <svg
                        viewBox="0 0 <?= $mapWidth ?> <?= $mapHeight ?>"
                        class="w-full h-auto"
                        role="img"
                        aria-label="Carte des visites par ville"
                    >
                        <rect x="0" y="0" width="<?= $mapWidth ?>" height="<?= $mapHeight ?>" fill="#0a0e1a" />

                        <?php for ($gx = 0; $gx <= $mapWidth; $gx += $mapWidth / 12): ?>
                            <line x1="<?= round($gx, 1) ?>" y1="0" x2="<?= round($gx, 1) ?>" y2="<?= $mapHeight ?>"
                                  stroke="rgba(255,255,255,0.06)" stroke-width="1" />
                        <?php endfor; ?>
                        <?php for ($gy = 0; $gy <= $mapHeight; $gy += $mapHeight / 6): ?>
                            <line x1="0" y1="<?= round($gy, 1) ?>" x2="<?= $mapWidth ?>" y2="<?= round($gy, 1) ?>"
                                  stroke="rgba(255,255,255,0.06)" stroke-width="1" />
                        <?php endfor; ?>
                        <line x1="0" y1="<?= $mapHeight / 2 ?>" x2="<?= $mapWidth ?>" y2="<?= $mapHeight / 2 ?>"
                              stroke="rgba(255,255,255,0.14)" stroke-width="1" />

                        <?php foreach ($mapPoints as $p): ?>
                            <circle
                                cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="<?= $p['r'] ?>"
                                fill="#E53935" fill-opacity="0.5" stroke="#E53935" stroke-width="1.5"
                            >
                                <title><?= e($p['label']) ?> — <?= $p['views'] ?> vue(s)</title>
                            </circle>
                        <?php endforeach; ?>
                    </svg>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top villes -->
        <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="font-bebas text-xl tracking-wider text-white">Top villes</h2>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <?php if (empty($topCities)): ?>
                        <tr><td class="px-6 py-4 text-white/40">Aucune donnée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topCities as $row): ?>
                            <tr class="border-b border-white/5">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2 font-montserrat text-white">
                                        <span aria-hidden="true"><?= country_flag($row['country_code'] ?? null) ?></span>
                                        <span><?= e((string) ($row['city_name'] ?? '')) ?></span>
                                    </div>
                                    <div class="text-xs text-white/40">
                                        <?= e((string) ($row['country_name'] ?? '')) ?>
                                    </div>
                                    <div class="mt-1 h-1 bg-white/5 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-atlex-red rounded-full"
                                            style="width: <?= (int) round(((int) $row['pageviews'] / max(1, $maxCityViews)) * 100) ?>%"
                                        ></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-atlex-red font-semibold align-top">
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