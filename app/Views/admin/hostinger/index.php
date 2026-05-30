<?php
/**
 * Vue admin — Monitoring Hostinger
 *
 * @var string                         $token         Token API actuel
 * @var array<int,array<string,mixed>> $subscriptions Liste des abonnements
 * @var array<int,array<string,mixed>> $domains       Liste des domaines
 * @var array{subscriptions:array,domains:array} $alerts Alertes expirations
 * @var string|null                    $apiError      Message d'erreur API
 */

/**
 * Retourne les classes de badge selon le nombre de jours restants.
 */
function daysLeftBadge(?int $days): string
{
    if ($days === null) {
        return 'bg-gray-700 text-gray-300';
    }
    if ($days <= 7) {
        return 'bg-red-600/30 text-red-300 ring-1 ring-red-500/50';
    }
    if ($days <= 30) {
        return 'bg-orange-500/30 text-orange-300 ring-1 ring-orange-400/50';
    }
    return 'bg-green-600/20 text-green-300';
}

/**
 * Formate un texte de jours restants.
 */
function daysLeftText(?int $days): string
{
    if ($days === null) {
        return '—';
    }
    if ($days < 0) {
        return 'Expiré depuis ' . abs($days) . 'j';
    }
    if ($days === 0) {
        return 'Expire aujourd\'hui';
    }
    return $days . ' jour' . ($days > 1 ? 's' : '');
}

/**
 * Formate le statut en badge Tailwind.
 */
function statusBadge(string $status): string
{
    return match (strtolower($status)) {
        'active'    => '<span class="text-xs px-2 py-1 rounded-full bg-green-600/20 text-green-300">Actif</span>',
        'inactive',
        'suspended' => '<span class="text-xs px-2 py-1 rounded-full bg-red-600/20 text-red-300">Suspendu</span>',
        'expired'   => '<span class="text-xs px-2 py-1 rounded-full bg-gray-600/40 text-gray-300">Expiré</span>',
        default     => '<span class="text-xs px-2 py-1 rounded-full bg-white/5 text-white/50">' . htmlspecialchars(ucfirst($status), ENT_QUOTES) . '</span>',
    };
}

$hasAlerts = !empty($alerts['subscriptions']) || !empty($alerts['domains']);
?>

<!-- =========================================================
     ALERTES ACTIVES (bandeau rouge/orange si expiration ≤ 30j)
     ========================================================= -->
<?php if ($hasAlerts): ?>
    <div class="mb-6 space-y-3">
        <div class="flex items-center gap-2 mb-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E53935" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span class="font-bebas text-lg text-atlex-red tracking-wider">Alertes actives</span>
        </div>

        <?php foreach ($alerts['subscriptions'] as $sub): ?>
            <?php
            $days = $sub['days_left'] ?? null;
            $bg   = $days !== null && $days <= 7 ? 'bg-red-900/40 border-red-500/60' : 'bg-orange-900/30 border-orange-500/40';
            $text = $days !== null && $days <= 7 ? 'text-red-200' : 'text-orange-200';
            $icon = $days !== null && $days <= 7 ? '#E53935' : '#F57C00';
            ?>
            <div class="flex items-start gap-3 px-4 py-3 rounded-lg border <?= $bg ?>">
                <svg class="shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?= $icon ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                <div class="<?= $text ?> text-sm font-montserrat">
                    <strong>Abonnement :</strong> <?= e($sub['name']) ?>
                    — expire dans <strong><?= daysLeftText($days) ?></strong>
                    <?php if (!empty($sub['expires_at'])): ?>
                        <span class="opacity-60">(<?= e($sub['expires_at']) ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($alerts['domains'] as $domain): ?>
            <?php
            $days = $domain['days_left'] ?? null;
            $bg   = $days !== null && $days <= 7 ? 'bg-red-900/40 border-red-500/60' : 'bg-orange-900/30 border-orange-500/40';
            $text = $days !== null && $days <= 7 ? 'text-red-200' : 'text-orange-200';
            $icon = $days !== null && $days <= 7 ? '#E53935' : '#F57C00';
            ?>
            <div class="flex items-start gap-3 px-4 py-3 rounded-lg border <?= $bg ?>">
                <svg class="shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?= $icon ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <div class="<?= $text ?> text-sm font-montserrat">
                    <strong>Domaine :</strong> <?= e($domain['domain']) ?>
                    — expire dans <strong><?= daysLeftText($days) ?></strong>
                    <?php if (!empty($domain['expires_at'])): ?>
                        <span class="opacity-60">(<?= e($domain['expires_at']) ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- =========================================================
     ERREUR API
     ========================================================= -->
<?php if ($apiError !== null): ?>
    <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-lg bg-red-900/30 border border-red-500/40 text-red-300 text-sm font-montserrat">
        <svg class="shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Erreur API Hostinger : <?= e($apiError) ?></span>
    </div>
<?php endif; ?>

<div class="space-y-8">

    <!-- =====================================================
         SECTION 1 : CONFIGURATION TOKEN API
         ===================================================== -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center gap-3">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E53935" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
            <h2 class="font-bebas text-xl tracking-wider">Configuration du Token API</h2>
        </div>
        <div class="p-6">
            <p class="text-sm text-white/50 font-montserrat mb-6">
                Renseignez votre token API Hostinger pour activer le monitoring.
                Vous pouvez le générer depuis votre
                <a href="https://hpanel.hostinger.com/api" target="_blank" rel="noopener noreferrer" class="text-atlex-red hover:underline">espace hPanel → API</a>.
            </p>

            <!-- Formulaire de sauvegarde du token -->
            <form method="POST" action="<?= url('/admin/hostinger/save') ?>" class="flex flex-col sm:flex-row gap-3 mb-6" id="tokenForm">
                <?= csrf_field() ?>
                <input
                    type="text"
                    name="hostinger_api_token"
                    id="hostingerToken"
                    value="<?= e($token) ?>"
                    placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
                    class="flex-1 bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-white/30 font-mono focus:outline-none focus:border-atlex-red/60 focus:ring-1 focus:ring-atlex-red/30 transition-colors"
                    autocomplete="off"
                >
                <button
                    type="submit"
                    class="shrink-0 px-5 py-2.5 bg-atlex-red hover:bg-red-600 text-white rounded-lg text-sm font-montserrat font-semibold transition-colors"
                >
                    Sauvegarder
                </button>
            </form>

            <!-- Bouton Test Connexion AJAX -->
            <div class="flex items-center gap-4">
                <button
                    type="button"
                    id="btnTestApi"
                    onclick="testHostingerApi()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 border border-white/20 hover:border-white/40 text-white/80 hover:text-white rounded-lg text-sm font-montserrat transition-colors"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Tester la connexion
                </button>
                <div id="testResult" class="hidden items-center gap-2 text-sm font-montserrat"></div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 2 : ABONNEMENTS
         ===================================================== -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#003366" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bg-white/5 rounded p-1 w-7 h-7"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <h2 class="font-bebas text-xl tracking-wider">Abonnements Hostinger</h2>
                <?php if (!empty($subscriptions)): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-white/5 text-white/40 font-montserrat">
                        <?= count($subscriptions) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($subscriptions)): ?>
            <div class="px-6 py-10 text-center">
                <p class="text-white/30 text-sm font-montserrat">
                    <?= $token === '' ? 'Configurez votre token API pour afficher les abonnements.' : 'Aucun abonnement trouvé.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Début</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Expiration</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Jours restants</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Fréquence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr class="border-b border-white/5 hover:bg-white/2 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-montserrat font-semibold text-white"><?= e($sub['name']) ?></span>
                                    <?php if (!empty($sub['id'])): ?>
                                        <span class="block text-xs text-white/30 mt-0.5">ID: <?= e((string) $sub['id']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= statusBadge($sub['status'] ?? 'unknown') ?>
                                </td>
                                <td class="px-6 py-4 text-white/60 font-montserrat text-xs">
                                    <?= e($sub['starts_at'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4 text-white/80 font-montserrat text-xs">
                                    <?= e($sub['expires_at'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block text-xs px-2.5 py-1 rounded-full font-semibold <?= daysLeftBadge($sub['days_left']) ?>">
                                        <?= daysLeftText($sub['days_left']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white/80 font-montserrat">
                                    <?php if ($sub['price'] !== null): ?>
                                        <?= e((string) $sub['price']) ?> <span class="text-white/40 text-xs"><?= e($sub['currency'] ?? 'EUR') ?></span>
                                    <?php else: ?>
                                        <span class="text-white/30">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-white/60 font-montserrat text-xs">
                                    <?= e($sub['billing_period'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- =====================================================
         SECTION 3 : NOMS DE DOMAINE
         ===================================================== -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center gap-3">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#003366" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bg-white/5 rounded p-1 w-7 h-7"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <h2 class="font-bebas text-xl tracking-wider">Noms de domaine</h2>
            <?php if (!empty($domains)): ?>
                <span class="text-xs px-2 py-0.5 rounded-full bg-white/5 text-white/40 font-montserrat">
                    <?= count($domains) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($domains)): ?>
            <div class="px-6 py-10 text-center">
                <p class="text-white/30 text-sm font-montserrat">
                    <?= $token === '' ? 'Configurez votre token API pour afficher les domaines.' : 'Aucun domaine trouvé.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Domaine</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Expiration</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Jours restants</th>
                            <th class="px-6 py-3 text-left text-xs text-white/40 font-montserrat uppercase tracking-wider">Renouvellement auto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($domains as $domain): ?>
                            <tr class="border-b border-white/5 hover:bg-white/2 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-white font-semibold"><?= e($domain['domain']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?= statusBadge($domain['status'] ?? 'unknown') ?>
                                </td>
                                <td class="px-6 py-4 text-white/80 font-montserrat text-xs">
                                    <?= e($domain['expires_at'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block text-xs px-2.5 py-1 rounded-full font-semibold <?= daysLeftBadge($domain['days_left']) ?>">
                                        <?= daysLeftText($domain['days_left']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($domain['auto_renew']): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-green-300">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            Oui
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-red-400">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Non
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /space-y-8 -->

<!-- =========================================================
     JAVASCRIPT — Test connexion AJAX
     ========================================================= -->
<script>
/**
 * Teste la connexion API Hostinger via AJAX.
 */
async function testHostingerApi() {
    const btn        = document.getElementById('btnTestApi');
    const result     = document.getElementById('testResult');
    const tokenInput = document.getElementById('hostingerToken');

    // État de chargement
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    result.classList.remove('hidden');
    result.classList.add('flex');
    result.innerHTML = `
        <svg class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <span class="text-white/50">Test en cours…</span>
    `;

    try {
        const formData = new FormData();
        formData.append('hostinger_api_token', tokenInput.value);
        formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= csrf_token() ?>');

        const response = await fetch('<?= url('/admin/hostinger/test') ?>', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            result.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span style="color:#4ade80;">${data.message}</span>
            `;
        } else {
            result.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="color:#f87171;">${data.message}</span>
            `;
        }
    } catch (err) {
        result.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="color:#f87171;">Erreur réseau : ${err.message}</span>
        `;
    } finally {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}
</script>
