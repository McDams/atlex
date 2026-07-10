<?php
/** @var array<int,array<string,mixed>> $articles */
$newsCategories = ['general', 'resultat', 'recrutement', 'evenement', 'partenariat', 'rapport'];
?>
<div class="flex items-center justify-between mb-6">
    <p class="text-white/50 font-montserrat text-sm"><?= count($articles) ?> article(s)</p>
    <a href="<?= url('/admin/actualites/nouveau') ?>" class="btn-atlex text-sm">+ Nouvel article</a>
</div>

<details class="bg-atlex-dark rounded-xl border border-white/5 p-5 mb-6">
    <summary class="cursor-pointer font-montserrat font-semibold text-white">✨ Générer un brouillon avec l'IA</summary>
    <form method="POST" action="<?= url('/admin/actualites/generer-ia') ?>" class="space-y-3 mt-4">
        <?= csrf_field() ?>
        <div>
            <label class="form-label">Sujet / faits à mettre en article</label>
            <textarea
                name="brief" rows="3" required class="form-input w-full text-sm"
                placeholder="Ex : L'équipe de basketball a remporté le tournoi régional 78-65 contre les Panthères de Porto-Novo, dimanche 6 juillet."
            ></textarea>
        </div>
        <div class="max-w-xs">
            <label class="form-label">Catégorie</label>
            <select name="category" class="form-input w-full text-sm">
                <?php foreach ($newsCategories as $cat): ?>
                    <option value="<?= e($cat) ?>"><?= e(news_category_label($cat)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-atlex text-sm">Générer le brouillon</button>
        <p class="text-white/40 text-xs">
            L'IA rédige dans le même style que les articles existants du site. Rien n'est publié :
            vous serez redirigé vers l'édition pour relire, ajuster et publier vous-même.
        </p>
    </form>
</details>

<div class="bg-atlex-dark rounded-xl border border-white/5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-white/50 font-montserrat uppercase text-xs border-b border-white/5">
            <tr>
                <th class="px-5 py-3">Titre</th>
                <th class="px-5 py-3">Catégorie</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="5" class="px-5 py-6 text-white/40">Aucun article.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($articles as $a): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-montserrat font-semibold"><?= e($a['title']) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e(news_category_label($a['category'])) ?></td>
                        <td class="px-5 py-3 text-white/60"><?= e(format_date_fr($a['published_at'] ?? $a['created_at'])) ?></td>
                        <td class="px-5 py-3">
                            <form method="POST" action="<?= url('/admin/actualites/' . $a['id']) ?>" class="inline js-toggle-publish" data-id="<?= (int) $a['id'] ?>">
                                <?= csrf_field() ?>
                                <?= method_field('PUT') ?>
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $a['is_published'] ? 'bg-green-600/20 text-green-300' : 'bg-white/10 text-white/60' ?>">
                                    <?= $a['is_published'] ? 'Publié' : 'Brouillon' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/actualites/' . $a['slug']) ?>" target="_blank" class="text-atlex-beige hover:underline">
                                Voir
                            </a>

                            <a href="<?= url('/admin/actualites/' . $a['id'] . '/edit') ?>" class="text-blue-300 hover:underline ml-3">
                                Modifier
                            </a>

                            <form method="POST" action="<?= url('/admin/actualites/' . $a['id']) ?>" class="inline" data-confirm="Supprimer cet article ?">
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