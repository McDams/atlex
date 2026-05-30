<?php
/**
 * @var array<int,array<string,mixed>> $photos
 * @var string|null $category
 */
$filters = [
    'all' => 'Tout', 'football' => 'Football', 'basketball' => 'Basketball',
    'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux',
    'evenements' => 'Événements', 'general' => 'Général',
];

$resolve = static function (string $filename): string {
    if (str_starts_with($filename, 'uploads/') || str_starts_with($filename, 'images/')) {
        return asset($filename);
    }
    return asset('images/' . $filename);
};
$active = $category ?? 'all';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Galerie</h1>
    <p class="text-white/60 font-montserrat mb-10">Les moments forts d'ATLÉX-SPORT en images</p>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 mb-10">
        <?php foreach ($filters as $key => $label): ?>
            <button type="button"
                    onclick="filterGallery('<?= e($key) ?>', this)"
                    class="g-filter px-4 py-1.5 rounded-full text-sm font-montserrat transition-colors <?= $active === $key ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>"
                    data-filter="<?= e($key) ?>">
                <?= e($label) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php if (empty($photos)): ?>
        <p class="text-white/50 font-montserrat">Aucune photo publiée pour le moment.</p>
    <?php else: ?>
        <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
            <?php foreach ($photos as $photo): ?>
                <figure class="g-cell break-inside-avoid rounded-lg overflow-hidden cursor-pointer"
                        data-cat="<?= e($photo['category']) ?>"
                        onclick="openLightbox('<?= e($resolve((string) $photo['filename'])) ?>', '<?= e($photo['alt_text'] ?? $photo['title']) ?>')">
                    <img src="<?= e($resolve((string) $photo['filename'])) ?>"
                         alt="<?= e($photo['alt_text'] ?? $photo['title']) ?>"
                         class="w-full hover:scale-105 transition-transform duration-500" loading="lazy">
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" onclick="closeLightbox(event)">
    <button class="absolute top-5 right-6 text-white text-4xl" aria-label="Fermer" onclick="closeLightbox(event)">&times;</button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-lg">
</div>

<script src="<?= asset('js/gallery.js') ?>" defer></script>
