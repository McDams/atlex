<?php
/** @var array<string,array<int,array<string,mixed>>> $sponsors */
$tierLabels = ['officiel' => 'Partenaires officiels', 'associe' => 'Partenaires associés', 'media' => 'Partenaires média'];

$offers = [
    ['title' => 'Officiel', 'price' => 'Sur devis', 'features' => ['Logo sur tous les supports', 'Présence aux événements majeurs', 'Communication dédiée']],
    ['title' => 'Associé', 'price' => 'Sur devis', 'features' => ['Logo sur le site web', 'Visibilité événements', 'Mention réseaux sociaux']],
    ['title' => 'Média', 'price' => 'Échange', 'features' => ['Couverture des événements', 'Échange de visibilité', 'Contenus partagés']],
];
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Sponsors &amp; Partenaires</h1>
    <p class="text-white/60 font-montserrat mb-12">Ils accompagnent ATLEX - Sport dans son développement</p>

    <?php foreach ($tierLabels as $tier => $label): ?>
        <?php if (!empty($sponsors[$tier])): ?>
            <div class="mb-12">
                <h2 class="font-bebas text-3xl tracking-wider mb-6"><?= e($label) ?></h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($sponsors[$tier] as $sponsor): ?>
                        <?php $card = '<div class="bg-atlex-dark rounded-xl p-6 border border-white/5 flex flex-col items-center justify-center h-36 text-center hover:border-atlex-red/40 transition-colors">'
                            . '<span class="font-bebas text-2xl tracking-wide">' . e($sponsor['name']) . '</span>'
                            . (!empty($sponsor['description']) ? '<span class="text-white/40 text-xs mt-2">' . e($sponsor['description']) . '</span>' : '')
                            . '</div>'; ?>
                        <?php if (!empty($sponsor['website_url'])): ?>
                            <a href="<?= e($sponsor['website_url']) ?>" target="_blank" rel="noopener"><?= $card ?></a>
                        <?php else: ?>
                            <?= $card ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Offres de sponsoring -->
    <div class="mt-16">
        <h2 class="font-bebas text-3xl tracking-wider mb-8 text-center">Devenir partenaire</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($offers as $offer): ?>
                <div class="bg-atlex-dark rounded-xl p-8 border border-white/5 reveal">
                    <h3 class="font-bebas text-3xl tracking-wider text-atlex-red mb-2"><?= e($offer['title']) ?></h3>
                    <p class="font-montserrat text-white/60 mb-5"><?= e($offer['price']) ?></p>
                    <ul class="space-y-2 mb-6">
                        <?php foreach ($offer['features'] as $feature): ?>
                            <li class="flex items-start gap-2 text-sm text-white/70">
                                <span class="text-atlex-red mt-0.5">✓</span> <?= e($feature) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= url('/contact#contact') ?>" class="btn-atlex w-full text-center text-sm">Nous contacter</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
