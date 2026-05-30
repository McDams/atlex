<?php
/** @var array<string,mixed> $event */
$types = ['match' => 'Match', 'tournoi' => 'Tournoi', 'stage' => 'Stage', 'entrainement' => 'Entraînement', 'remise' => 'Remise', 'autre' => 'Autre'];
$disciplines = ['tous' => 'Toutes', 'football' => 'Football', 'basketball' => 'Basketball', 'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux'];
$toInput = static fn (?string $dt): string => $dt ? str_replace(' ', 'T', substr($dt, 0, 16)) : '';
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/evenements') ?>" class="text-white/50 text-sm hover:text-white">← Retour</a>
    <form method="POST" action="<?= url('/admin/evenements/' . $event['id']) ?>" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?><?= method_field('PUT') ?>
        <div><label class="form-label">Titre *</label><input name="title" required value="<?= e($event['title']) ?>" class="form-input w-full"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Type</label><select name="type" class="form-input w-full"><?php foreach ($types as $k => $v): ?><option value="<?= e($k) ?>" <?= $event['type'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label">Discipline</label><select name="discipline" class="form-input w-full"><?php foreach ($disciplines as $k => $v): ?><option value="<?= e($k) ?>" <?= $event['discipline'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label">Début *</label><input type="datetime-local" name="start_datetime" required value="<?= e($toInput($event['start_datetime'])) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Fin</label><input type="datetime-local" name="end_datetime" value="<?= e($toInput($event['end_datetime'])) ?>" class="form-input w-full"></div>
            <div class="sm:col-span-2"><label class="form-label">Lieu</label><input name="location" value="<?= e($event['location']) ?>" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Description</label><textarea name="description" rows="4" class="form-input w-full"><?= e($event['description']) ?></textarea></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" <?= $event['is_published'] ? 'checked' : '' ?>> Publié</label>
        <button type="submit" class="btn-atlex">Mettre à jour</button>
    </form>
</div>
