<?php
/** @var array<int,array<string,mixed>> $documents */
$categories = ['administratif' => 'Administratif', 'sportif' => 'Sportif', 'financier' => 'Financier', 'communication' => 'Communication', 'autre' => 'Autre'];

$humanSize = static function (?int $bytes): string {
    $bytes = (int) $bytes;
    if ($bytes <= 0) {
        return '—';
    }
    $units = ['o', 'Ko', 'Mo', 'Go'];
    $i = (int) floor(log($bytes, 1024));
    return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
};
?>
<!-- Formulaire d'upload -->
<form method="POST" action="<?= url('/admin/documents') ?>" enctype="multipart/form-data" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mb-6">
    <?= csrf_field() ?>
    <h2 class="font-bebas text-xl tracking-wider mb-4">Téléverser un document</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <input name="title" required placeholder="Titre du document *" class="form-input">
        <select name="category" class="form-input"><?php foreach ($categories as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?></select>
        <input type="file" name="document" required class="form-input">
    </div>
    <button type="submit" class="btn-atlex text-sm mt-4">Téléverser</button>
</form>

<!-- Liste -->
<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Document</th>
                <th class="px-5 py-3">Catégorie</th>
                <th class="px-5 py-3">Taille</th>
                <th class="px-5 py-3">Ajouté par</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($documents)): ?>
                <tr><td colspan="5" class="px-5 py-6 text-white/40">Aucun document.</td></tr>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($doc['title']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($doc['category']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($humanSize($doc['file_size'] ?? 0)) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e($doc['uploader_name'] ?? '—') ?></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <form method="POST" action="<?= url('/admin/documents/' . $doc['id']) ?>" class="inline" data-confirm="Supprimer ce document ?">
                                <?= csrf_field() ?><?= method_field('DELETE') ?>
                                <button type="submit" class="text-atlex-red hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
