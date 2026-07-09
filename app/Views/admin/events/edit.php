<?php
/**
 * @var array<string,mixed> $event
 * @var array<int,array<string,mixed>> $categories
 */
$types = [
    'match' => 'Match',
    'tournoi' => 'Tournoi',
    'stage' => 'Stage',
    'entrainement' => 'Entraînement',
    'remise' => 'Remise',
    'autre' => 'Autre'
];

$disciplines = [
    'tous' => 'Toutes',
    'basketball' => 'Basketball',
    'handball' => 'Handball',
    'arts_martiaux' => 'Arts Martiaux'
];

$toInput = static fn (?string $dt): string => $dt ? str_replace(' ', 'T', substr($dt, 0, 16)) : '';
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/evenements') ?>" class="text-white/50 text-sm hover:text-white font-montserrat">← Retour</a>
    <h2 class="font-bebas text-3xl tracking-wider mt-4 mb-6">Modifier l'événement</h2>

    <form method="POST" action="<?= url('/admin/evenements/' . $event['id']) ?>"
          class="bg-atlex-dark rounded-xl border border-white/5 p-6 space-y-4">
        <?= csrf_field() ?>
        <?= method_field('PUT') ?>

        <?php if (!empty($event['source']) && $event['source'] === 'ics'): ?>
            <div class="rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm font-montserrat text-emerald-200">
                <div class="font-semibold">Événement importé depuis un fichier ICS</div>

                <?php if (!empty($event['external_uid'])): ?>
                    <div class="text-emerald-300/80 text-xs mt-1 break-all">
                        UID externe : <?= e((string) $event['external_uid']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div>
            <label class="form-label">Titre *</label>
            <input name="title" required value="<?= e((string) $event['title']) ?>" class="form-input w-full">
        </div>

        <!-- Catégorie avec aperçu couleur -->
        <div>
            <label class="form-label">Catégorie</label>
            <div class="flex items-center gap-3">
                <?php
                $currentColor = '#4B5563';
                foreach ($categories as $cat) {
                    if ((string) $cat['id'] === (string) ($event['category_id'] ?? '')) {
                        $currentColor = (string) $cat['color'];
                        break;
                    }
                }
                ?>
                <div id="cat-preview" class="w-8 h-8 rounded-full flex-shrink-0 transition-colors"
                     style="background-color: <?= e($currentColor) ?>;"></div>

                <select name="category_id" id="cat-select" class="form-input flex-1" onchange="updateCatPreview(this)">
                    <option value="">— Sans catégorie —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option
                            value="<?= e((string) $cat['id']) ?>"
                            data-color="<?= e((string) $cat['color']) ?>"
                            <?= (string) ($event['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>
                        >
                            <?= e((string) $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="<?= url('/admin/evenements/categories') ?>"
               class="text-white/30 text-xs font-montserrat hover:text-white/60 mt-1 inline-block">
                Gérer les catégories →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-input w-full">
                    <?php foreach ($types as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= (($event['type'] ?? '') === $k) ? 'selected' : '' ?>>
                            <?= e($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Discipline</label>
                <select name="discipline" class="form-input w-full">
                    <?php foreach ($disciplines as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= (($event['discipline'] ?? '') === $k) ? 'selected' : '' ?>>
                            <?= e($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Début *</label>
                <input
                    type="datetime-local"
                    name="start_datetime"
                    required
                    value="<?= e($toInput($event['start_datetime'] ?? null)) ?>"
                    class="form-input w-full"
                >
            </div>

            <div>
                <label class="form-label">Fin</label>
                <input
                    type="datetime-local"
                    name="end_datetime"
                    value="<?= e($toInput($event['end_datetime'] ?? null)) ?>"
                    class="form-input w-full"
                >
            </div>

            <div class="sm:col-span-2">
                <label class="form-label">Lieu</label>
                <input
                    name="location"
                    value="<?= e((string) ($event['location'] ?? '')) ?>"
                    class="form-input w-full"
                    placeholder="Ex : Stade de l'Amitié, Cotonou"
                >
            </div>
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-input w-full"><?= e((string) ($event['description'] ?? '')) ?></textarea>
        </div>

        <label class="flex items-center gap-2 text-sm font-montserrat">
            <input type="checkbox" name="is_published" value="1" <?= !empty($event['is_published']) ? 'checked' : '' ?>>
            Publié
        </label>

        <button type="submit" class="btn-atlex w-full">Mettre à jour</button>
    </form>
</div>

<script nonce="<?= \App\Core\Security::nonce() ?>">
function updateCatPreview(select) {
    const opt = select.options[select.selectedIndex];
    const color = opt.dataset.color || '#4B5563';
    document.getElementById('cat-preview').style.backgroundColor = color;
}

document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('cat-select');
    if (select) {
        updateCatPreview(select);
    }
});
</script>