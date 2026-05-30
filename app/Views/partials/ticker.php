<?php
/** @var array<int,array<string,mixed>> $tickerNews */
$items = $tickerNews ?? [];
?>
<?php if (!empty($items)): ?>
<div class="bg-atlex-red overflow-hidden py-2 select-none">
    <div class="ticker-inner whitespace-nowrap flex items-center">
        <?php for ($i = 0; $i < 2; $i++): ?>
            <?php foreach ($items as $item): ?>
                <a href="<?= url('/actualites/' . $item['slug']) ?>"
                   class="inline-flex items-center font-montserrat text-sm font-semibold uppercase tracking-wide text-white mx-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-white mr-3"></span>
                    <?= e($item['title']) ?>
                </a>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>
