<?php
/**
 * @var array<int,array<string,mixed>> $posts
 * @var array<string,int> $counts
 * @var string|null $filterStatus
 * @var string|null $filterPlatform
 */
$statusTabs = [
    'brouillon' => 'Brouillons',
    'approuve'  => 'Approuvés',
    'publie'    => 'Publiés',
    'echec'     => 'Échecs',
    'tous'      => 'Tous',
];
$platformLabels = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'];
$sourceLabels = [
    'news'         => 'Actualité',
    'event'        => 'Événement',
    'athlete'      => 'Athlète',
    'match_resume' => 'Résumé de match',
    'manuel'       => 'Manuel',
];
$activeStatus = $filterStatus ?? 'brouillon';
?>

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-bebas text-4xl tracking-wider text-white">Réseaux sociaux</h1>
        <p class="text-white/50 text-sm font-montserrat mt-1">
            Brouillons générés par l'IA — rien n'est publié sans validation humaine.
            <a href="<?= url('/admin/social/comptes') ?>" class="text-atlex-beige hover:underline">Comptes &amp; compétitions →</a>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="<?= url('/admin/social/generer') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn-atlex text-sm">✨ Générer des brouillons</button>
        </form>
        <form method="POST" action="<?= url('/admin/social/generer-matchs') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn-atlex-outline text-sm">⚽ Vérifier les résultats</button>
        </form>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    <?php foreach ($statusTabs as $key => $label): ?>
        <a href="<?= url('/admin/social?status=' . $key) ?>"
           class="px-4 py-1.5 rounded-full text-sm font-montserrat transition-colors <?= $activeStatus === $key ? 'bg-atlex-red text-white' : 'bg-white/5 text-white/60 hover:bg-white/10' ?>">
            <?= e($label) ?><?php if (isset($counts[$key])): ?> (<?= e((string) $counts[$key]) ?>)<?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($posts)): ?>
    <div class="bg-atlex-dark rounded-xl border border-white/5 p-8 text-center text-white/40 font-montserrat">
        Aucun brouillon dans cette vue.
        <?php if ($activeStatus === 'brouillon'): ?>
            <br>Cliquez sur « Générer des brouillons » pour en créer à partir des dernières actualités/événements.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($posts as $post): ?>
            <div class="bg-atlex-dark rounded-xl border border-white/5 p-5">
                <div class="flex flex-wrap items-center gap-2 mb-3 text-xs font-montserrat">
                    <span class="px-2 py-0.5 rounded bg-white/10 text-white/60">
                        <?= e($platformLabels[$post['platform']] ?? $post['platform']) ?>
                    </span>
                    <span class="px-2 py-0.5 rounded bg-white/5 text-white/40">
                        <?= e($sourceLabels[$post['source_type']] ?? $post['source_type']) ?>
                    </span>
                    <?php if ($post['status'] === 'publie' && !empty($post['published_at'])): ?>
                        <span class="text-white/40">Publié le <?= e(format_date_fr($post['published_at'], true)) ?></span>
                    <?php endif; ?>
                    <?php if ($post['status'] === 'echec' && !empty($post['error_message'])): ?>
                        <span class="text-red-300"><?= e($post['error_message']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($post['status'] === 'brouillon'): ?>
                    <form method="POST" action="<?= url('/admin/social/' . $post['id']) ?>" class="space-y-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="PUT">
                        <textarea name="content_text" rows="4" class="form-input w-full text-sm"><?= e($post['content_text']) ?></textarea>
                        <input
                            type="text" name="media_path" value="<?= e($post['media_path'] ?? '') ?>"
                            placeholder="URL de l'image (obligatoire pour publier sur Instagram)"
                            class="form-input w-full text-sm"
                        >
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="btn-atlex-outline text-xs">Enregistrer les modifications</button>
                        </div>
                    </form>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <form method="POST" action="<?= url('/admin/social/' . $post['id'] . '/approuver') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-atlex text-xs">✓ Approuver</button>
                        </form>
                        <form method="POST" action="<?= url('/admin/social/' . $post['id'] . '/ignorer') ?>" data-confirm="Ignorer ce brouillon ?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-atlex-outline text-xs">Ignorer</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="text-white/80 text-sm whitespace-pre-line"><?= e($post['content_text']) ?></p>

                    <?php if ($post['status'] === 'approuve'): ?>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <form
                                method="POST" action="<?= url('/admin/social/' . $post['id'] . '/publier') ?>"
                                data-confirm="Publier réellement ce post sur <?= e($platformLabels[$post['platform']] ?? $post['platform']) ?> ?"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-atlex text-xs">🚀 Publier maintenant</button>
                            </form>
                        </div>
                    <?php elseif ($post['status'] === 'echec'): ?>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <form method="POST" action="<?= url('/admin/social/' . $post['id'] . '/publier') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-atlex-outline text-xs">↻ Réessayer</button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
