<?php
/**
 * @var array<int,array<string,mixed>> $accounts
 * @var array<int,array<string,mixed>> $competitions
 */
$platforms = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'];
$byPlatform = [];
foreach ($accounts as $a) {
    $byPlatform[$a['platform']] = $a;
}
?>

<div class="mb-6">
    <h1 class="font-bebas text-4xl tracking-wider text-white">Comptes réseaux sociaux</h1>
    <p class="text-white/50 text-sm font-montserrat mt-1">
        <a href="<?= url('/admin/social') ?>" class="text-atlex-beige hover:underline">← Retour aux brouillons</a>
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
    <?php foreach ($platforms as $key => $label): ?>
        <?php $account = $byPlatform[$key] ?? null; ?>
        <div class="bg-atlex-dark rounded-xl border border-white/5 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-montserrat font-semibold text-white"><?= e($label) ?></h2>
                <?php if ($account && (int) $account['is_active'] === 1): ?>
                    <span class="text-xs px-2 py-1 rounded bg-green-600/20 text-green-300">Connecté</span>
                <?php else: ?>
                    <span class="text-xs px-2 py-1 rounded bg-white/10 text-white/50">Non connecté</span>
                <?php endif; ?>
            </div>
            <form method="POST" action="<?= url('/admin/social/comptes/enregistrer') ?>" class="space-y-2">
                <?= csrf_field() ?>
                <input type="hidden" name="platform" value="<?= e($key) ?>">
                <input
                    type="text" name="label" value="<?= e($account['label'] ?? $label) ?>"
                    placeholder="Nom (ex : Page ATLEX)" class="form-input w-full text-sm"
                >
                <input
                    type="text" name="account_ref" value="<?= e($account['account_ref'] ?? '') ?>"
                    placeholder="<?= $key === 'linkedin' ? "URN de l'organisation" : 'ID de la page/du compte' ?>"
                    class="form-input w-full text-sm"
                >
                <textarea
                    name="access_token" rows="2" placeholder="Jeton d'accès"
                    class="form-input w-full text-sm font-mono"
                ><?= e($account['access_token'] ?? '') ?></textarea>
                <button type="submit" class="btn-atlex-outline text-xs w-full">Enregistrer</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
    <div class="px-6 py-4 border-b border-white/5">
        <h2 class="font-bebas text-xl tracking-wider text-white">Compétitions suivies</h2>
        <p class="text-white/40 text-xs font-montserrat mt-1">
            Résumés de matchs (Coupe du Monde, CAN, Ligue des Champions...). Nécessite
            <code class="text-white/60">API_FOOTBALL_KEY</code> dans <code class="text-white/60">.env</code>.
            Vérifiez l'identifiant de chaque compétition auprès d'API-Football avant de l'activer.
        </p>
    </div>
    <table class="w-full text-sm">
        <tbody>
            <?php foreach ($competitions as $c): ?>
                <tr class="border-b border-white/5">
                    <td class="px-6 py-3 font-montserrat text-white"><?= e($c['name']) ?></td>
                    <td class="px-6 py-3 text-white/40 text-xs">
                        ID API-Football : <?= e($c['external_competition_id']) ?>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <form method="POST" action="<?= url('/admin/social/comptes/competitions/' . $c['id'] . '/toggle') ?>">
                            <?= csrf_field() ?>
                            <button
                                type="submit"
                                class="text-xs px-2 py-1 rounded <?= (int) $c['is_active'] === 1 ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>"
                            >
                                <?= (int) $c['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
