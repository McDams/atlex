<?php
/**
 * @var array<int,array<string,mixed>> $sources
 * @var string                         $template
 */
?>
<a href="<?= url('/admin/veille') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la veille</a>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
    <!-- Ajouter une source -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <h2 class="font-bebas text-xl tracking-wider mb-4">Ajouter une source</h2>
        <form method="POST" action="<?= url('/admin/veille/sources') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div><label class="form-label">Nom *</label><input name="name" required placeholder="ex : UNESCO — appels à projets" class="form-input w-full"></div>
            <div><label class="form-label">Type</label>
                <select name="type" class="form-input w-full" id="src-type">
                    <option value="google_news">Recherche Google Actualités (recommandé)</option>
                    <option value="rss">Flux RSS direct</option>
                </select>
            </div>
            <div><label class="form-label">Requête de recherche</label>
                <input name="query" placeholder="ex : site:unesco.org appel à projets sport jeunesse" class="form-input w-full">
                <p class="text-white/40 text-xs mt-1">Pour le type Google Actualités. Utilisez <code>site:domaine.org</code> pour cibler un portail précis.</p>
            </div>
            <div><label class="form-label">URL du flux RSS</label>
                <input type="url" name="url" placeholder="https://exemple.org/feed.xml" class="form-input w-full">
                <p class="text-white/40 text-xs mt-1">Pour le type Flux RSS direct uniquement.</p>
            </div>
            <button type="submit" class="btn-atlex">Ajouter la source</button>
        </form>
    </div>

    <!-- Modèle de démarches -->
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-6">
        <h2 class="font-bebas text-xl tracking-wider mb-2">Modèle de démarches</h2>
        <p class="text-white/40 text-xs mb-4">Une étape par ligne. Ces étapes sont appliquées automatiquement à chaque opportunité ajoutée au suivi.</p>
        <form method="POST" action="<?= url('/admin/veille/template') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <textarea name="template" rows="9" class="form-input w-full"><?= e($template) ?></textarea>
            <button type="submit" class="btn-atlex">Enregistrer le modèle</button>
        </form>
    </div>
</div>

<!-- Sources existantes -->
<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto mt-6">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Source</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Cible</th>
                <th class="px-5 py-3">Dernière collecte</th>
                <th class="px-5 py-3">Active</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sources)): ?>
                <tr><td colspan="6" class="px-5 py-6 text-white/40">Aucune source configurée.</td></tr>
            <?php else: ?>
                <?php foreach ($sources as $s): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($s['name']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= $s['type'] === 'rss' ? 'RSS' : 'Google Actualités' ?></td>
                        <td class="px-5 py-3 text-white/50 max-w-xs truncate"><?= e($s['type'] === 'rss' ? $s['url'] : $s['query']) ?></td>
                        <td class="px-5 py-3 text-white/50 whitespace-nowrap"><?= !empty($s['last_fetch_at']) ? e(format_date_fr($s['last_fetch_at'], true)) : '—' ?></td>
                        <td class="px-5 py-3">
                            <form method="POST" action="<?= url('/admin/veille/sources/' . $s['id']) ?>" class="inline">
                                <?= csrf_field() ?><?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $s['is_active'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>">
                                    <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="<?= url('/admin/veille/sources/' . $s['id']) ?>" class="inline" data-confirm="Supprimer cette source ?">
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
