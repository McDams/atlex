<?php
/**
 * @var array<string,mixed>  $request
 * @var array<string,string> $missions
 */
use App\Models\VolunteerRequest;

$name = trim(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? ''));
$chosen = json_decode((string) ($request['missions'] ?? '[]'), true) ?: [];
$current = (string) ($request['status'] ?? 'nouveau');

$statusBadge = static function (string $status): string {
    $map = [
        'nouveau'  => ['Nouveau', 'bg-blue-600/20 text-blue-300'],
        'en_cours' => ['En cours', 'bg-orange-600/20 text-orange-300'],
        'accepte'  => ['Accepté', 'bg-green-600/20 text-green-300'],
        'refuse'   => ['Refusé', 'bg-atlex-red/20 text-red-300'],
    ];
    [$label, $classes] = $map[$status] ?? ['Inconnu', 'bg-white/10 text-white/60'];
    return '<span class="text-xs px-2.5 py-1 rounded-full font-montserrat font-semibold ' . $classes . '">' . e($label) . '</span>';
};
?>

<div class="mb-6">
    <a href="<?= url('/admin/benevoles') ?>" class="text-sm text-white/50 hover:text-white font-montserrat">&larr; Retour à la liste</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Fiche -->
    <div class="lg:col-span-2 bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="font-bebas text-2xl tracking-wider"><?= e($name) ?></h2>
            <?= $statusBadge($current) ?>
        </div>
        <dl class="px-6 py-5 space-y-4 text-sm">
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Téléphone</dt>
                <dd class="text-white/80 mt-1"><?= e($request['phone'] ?? '') ?></dd>
            </div>
            
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Âge</dt>
                <dd class="text-white/80 mt-1"><?= e($request['age'] ?? '') ?: '—' ?></dd>
            </div>
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Email</dt>
                <dd class="text-white/80 mt-1"><?= e($request['email'] ?? '') ?: '—' ?></dd>
            </div>
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Missions choisies</dt>
                <dd class="flex flex-wrap gap-2 mt-2">
                    <?php if (empty($chosen)): ?>
                        <span class="text-white/40">—</span>
                    <?php else: ?>
                        <?php foreach ($chosen as $m): ?>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-white/10 text-white/70"><?= e($missions[$m] ?? $m) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Message</dt>
                <dd class="text-white/80 mt-1 whitespace-pre-line"><?= e($request['message'] ?? '') ?: '—' ?></dd>
            </div>
            <div>
                <dt class="text-white/40 text-xs uppercase tracking-wider font-montserrat">Reçue le</dt>
                <dd class="text-white/80 mt-1"><?= e(format_date_fr($request['created_at'] ?? null, true)) ?></dd>
            </div>
        </dl>
    </div>

    <!-- Actions -->
    <div class="space-y-6">
        <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
            <h3 class="font-bebas text-lg tracking-wider mb-4">Changer le statut</h3>
            <form method="POST" action="<?= url('/admin/benevoles/' . $request['id'] . '/status') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <select name="status" class="form-input w-full">
                    <?php foreach (VolunteerRequest::STATUSES as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $current === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-atlex w-full">Mettre à jour</button>
            </form>
        </div>

        <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
            <h3 class="font-bebas text-lg tracking-wider mb-4">Supprimer</h3>
            <form method="POST" action="<?= url('/admin/benevoles/' . $request['id'] . '/delete') ?>"
                  onsubmit="return confirm('Supprimer définitivement cette candidature ?');">
                <?= csrf_field() ?>
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg text-sm font-montserrat font-semibold bg-atlex-red/20 hover:bg-atlex-red text-red-300 hover:text-white transition-colors">Supprimer la candidature</button>
            </form>
        </div>
    </div>
</div>
