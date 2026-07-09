<?php
/** @var array<int,array<string,mixed>> $events */
?>

<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
    <p class="text-white/50 font-montserrat text-sm">
        <?= count($events) ?> événement(s)
    </p>

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <form
            method="POST"
            action="<?= url('/admin/evenements/import-ics') ?>"
            enctype="multipart/form-data"
            class="flex flex-col sm:flex-row sm:items-center gap-2"
        >
            <?= csrf_field() ?>

            <input
                type="file"
                name="ics_file"
                accept=".ics,text/calendar"
                class="block w-full sm:w-auto text-sm text-white/70 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-white/10 file:text-white file:font-montserrat file:text-sm hover:file:bg-white/15"
            >

            <button type="submit" class="btn-atlex text-sm whitespace-nowrap">
                Importer ICS
            </button>
        </form>

        <a href="<?= url('/admin/evenements/nouveau') ?>" class="btn-atlex text-sm whitespace-nowrap">
            + Nouvel événement
        </a>
    </div>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Titre</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Discipline</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Source</th>
                <th class="px-5 py-3">Publié</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-6 text-white/40">
                        Aucun événement.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <?php
                        $discipline = !empty($ev['discipline']) ? discipline_label((string) $ev['discipline']) : 'Non défini';
                        $type = !empty($ev['type']) ? (string) $ev['type'] : 'ics';
                        $source = !empty($ev['source']) ? (string) $ev['source'] : 'manual';
                    ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold text-white">
                            <?= e((string) $ev['title']) ?>
                        </td>

                        <td class="px-5 py-3 text-white/60">
                            <?= !empty($ev['start_datetime']) ? e(format_date_fr((string) $ev['start_datetime'], true)) : '—' ?>
                        </td>

                        <td class="px-5 py-3">
                            <span class="text-xs px-2 py-1 rounded bg-atlex-blue/40 text-white/90">
                                <?= e($discipline) ?>
                            </span>
                        </td>

                        <td class="px-5 py-3 text-white/60">
                            <?= e($type) ?>
                        </td>

                        <td class="px-5 py-3">
                            <?php if ($source === 'ics'): ?>
                                <span class="text-xs px-2 py-1 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-400/20">
                                    ICS
                                </span>
                            <?php else: ?>
                                <span class="text-xs px-2 py-1 rounded bg-white/5 text-white/60 border border-white/10">
                                    Manuel
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-3">
                            <?= !empty($ev['is_published'])
                                ? '<span class="text-green-400">●</span>'
                                : '<span class="text-white/30">○</span>' ?>
                        </td>

                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/evenements/' . $ev['id'] . '/edit') ?>" class="text-atlex-beige hover:underline">
                                Éditer
                            </a>

                            <form
                                method="POST"
                                action="<?= url('/admin/evenements/' . $ev['id']) ?>"
                                class="inline"
                                data-confirm="Supprimer cet événement ?"
                            >
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button type="submit" class="text-atlex-red hover:underline ml-3">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>