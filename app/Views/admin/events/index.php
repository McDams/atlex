<?php
/** @var array<int,array<string,mixed>> $events */
?>
<div class="flex items-center justify-between mb-6">
    <p class="text-white/50 font-montserrat text-sm"><?= count($events) ?> événement(s)</p>
    <a href="<?= url('/admin/evenements/nouveau') ?>" class="btn-atlex text-sm">+ Nouvel événement</a>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Titre</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Discipline</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Publié</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($events)): ?>
                <tr><td colspan="6" class="px-5 py-6 text-white/40">Aucun événement.</td></tr>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($ev['title']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e(format_date_fr($ev['start_datetime'], true)) ?></td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-1 rounded bg-atlex-blue/40"><?= e(discipline_label($ev['discipline'])) ?></span></td>
                        <td class="px-5 py-3 text-white/60"><?= e($ev['type']) ?></td>
                        <td class="px-5 py-3"><?= $ev['is_published'] ? '<span class="text-green-400">●</span>' : '<span class="text-white/30">○</span>' ?></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/evenements/' . $ev['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">Éditer</a>
                            <form method="POST" action="<?= url('/admin/evenements/' . $ev['id']) ?>" class="inline" data-confirm="Supprimer cet événement ?">
                                <?= csrf_field() ?><?= method_field('DELETE') ?>
                                <button type="submit" class="text-atlex-red hover:underline ml-3">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
