<?php
/**
 * Page de détail d'un événement public.
 *
 * @var array<string,mixed>            $event
 * @var array<int,array<string,mixed>> $related
 */

$catColor = !empty($event['category_color']) ? (string) $event['category_color'] : '#003366';

// Statut de l'événement à partir des dates.
$now   = time();
$start = !empty($event['start_datetime']) ? strtotime((string) $event['start_datetime']) : false;
$end   = !empty($event['end_datetime']) ? strtotime((string) $event['end_datetime']) : false;

if ($start !== false && $start > $now) {
    $statusLabel = 'À venir';
    $statusColor = '#22c55e';
} elseif ($start !== false && ($end !== false ? $end >= $now : $start >= $now)) {
    $statusLabel = 'En cours';
    $statusColor = '#E53935';
} else {
    $statusLabel = 'Terminé';
    $statusColor = '#6b7280';
}

$hasTime = $start !== false;
$timeRange = '';
if ($hasTime) {
    $timeRange = date('H\hi', $start);
    if ($end !== false && date('H:i', $end) !== date('H:i', $start)) {
        $timeRange .= ' – ' . date('H\hi', $end);
    }
}
?>

<!-- ===== HEADER ===== -->
<section style="position:relative; padding:5rem 1rem 3.5rem; background:linear-gradient(160deg, <?= e($catColor) ?> 0%, #001a3d 55%, #0a0e1a 100%);">
    <div style="max-width:80rem; margin:0 auto;">
        <a href="<?= url('/evenements') ?>" style="display:inline-block; margin-bottom:1.5rem; color:rgba(255,255,255,.7); text-decoration:none; font-family:var(--font-montserrat,sans-serif); font-size:.875rem;"
           onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.7)'">← Retour au calendrier</a>

        <?php if (!empty($event['category_name'])): ?>
        <div style="margin-bottom:1rem;">
            <span style="display:inline-block; font-family:var(--font-montserrat,sans-serif); font-weight:700; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; padding:.35rem .85rem; border-radius:999px; background:<?= e($catColor) ?>; color:#fff; box-shadow:0 0 0 1px rgba(255,255,255,.15);">
                <?= e($event['category_name']) ?>
            </span>
        </div>
        <?php endif; ?>

        <h1 class="font-bebas" style="font-size:clamp(2.5rem,6vw,4.5rem); line-height:1.05; letter-spacing:.04em; margin:0 0 1.25rem; color:#fff;">
            <?= e($event['title']) ?>
        </h1>

        <div style="display:flex; flex-wrap:wrap; gap:1.25rem 2rem; font-family:var(--font-montserrat,sans-serif); color:rgba(255,255,255,.85); font-size:.95rem;">
            <?php if ($hasTime): ?>
            <span style="display:flex; align-items:center; gap:.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= e(ucfirst(format_date_fr((string) $event['start_datetime']))) ?><?= $timeRange !== '' ? ' · ' . e($timeRange) : '' ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($event['location'])): ?>
            <span style="display:flex; align-items:center; gap:.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?= e($event['location']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== CONTENU ===== -->
<section style="max-width:80rem; margin:0 auto; padding:3rem 1rem 4rem;">
    <div style="display:flex; flex-wrap:wrap; gap:2.5rem; align-items:flex-start;">

        <!-- Colonne principale -->
        <div style="flex:1 1 360px; min-width:0;">
            <h2 class="font-bebas" style="font-size:1.85rem; letter-spacing:.04em; margin:0 0 1.25rem; color:#fff;">À propos de l'événement</h2>

            <?php if (!empty($event['description'])): ?>
            <div style="font-family:var(--font-montserrat,sans-serif); color:rgba(255,255,255,.8); line-height:1.7; font-size:1rem;">
                <?php foreach (preg_split('/\n\s*\n/', (string) $event['description']) as $paragraph): ?>
                    <?php if (trim($paragraph) !== ''): ?>
                        <p style="margin:0 0 1rem;"><?= nl2br(e($paragraph)) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="font-family:var(--font-montserrat,sans-serif); color:rgba(255,255,255,.5); font-style:italic;">Aucune description disponible pour cet événement.</p>
            <?php endif; ?>
        </div>

        <!-- Colonne latérale : infos pratiques -->
        <aside style="flex:0 1 340px; width:100%; max-width:360px;">
            <div style="background:#001a3d; border:1px solid rgba(255,255,255,.06); border-radius:1rem; padding:1.5rem;">
                <h3 class="font-bebas" style="font-size:1.4rem; letter-spacing:.04em; margin:0 0 1.25rem; color:#fff;">Informations pratiques</h3>

                <dl style="margin:0; font-family:var(--font-montserrat,sans-serif); font-size:.9rem;">
                    <?php if ($hasTime): ?>
                    <div style="display:flex; justify-content:space-between; gap:1rem; padding:.65rem 0; border-bottom:1px solid rgba(255,255,255,.06);">
                        <dt style="color:rgba(255,255,255,.5);">Date</dt>
                        <dd style="margin:0; color:#fff; text-align:right;"><?= e(ucfirst(format_date_fr((string) $event['start_datetime']))) ?></dd>
                    </div>
                    <?php if ($timeRange !== ''): ?>
                    <div style="display:flex; justify-content:space-between; gap:1rem; padding:.65rem 0; border-bottom:1px solid rgba(255,255,255,.06);">
                        <dt style="color:rgba(255,255,255,.5);">Heure</dt>
                        <dd style="margin:0; color:#fff; text-align:right;"><?= e($timeRange) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($event['location'])): ?>
                    <div style="display:flex; justify-content:space-between; gap:1rem; padding:.65rem 0; border-bottom:1px solid rgba(255,255,255,.06);">
                        <dt style="color:rgba(255,255,255,.5);">Lieu</dt>
                        <dd style="margin:0; color:#fff; text-align:right;"><?= e($event['location']) ?></dd>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($event['discipline'])): ?>
                    <div style="display:flex; justify-content:space-between; gap:1rem; padding:.65rem 0; border-bottom:1px solid rgba(255,255,255,.06);">
                        <dt style="color:rgba(255,255,255,.5);">Discipline</dt>
                        <dd style="margin:0; color:#fff; text-align:right;"><?= e(discipline_label((string) $event['discipline'])) ?></dd>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($event['category_name'])): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:.65rem 0; border-bottom:1px solid rgba(255,255,255,.06);">
                        <dt style="color:rgba(255,255,255,.5);">Catégorie</dt>
                        <dd style="margin:0; text-align:right;">
                            <span style="display:inline-block; font-size:.75rem; font-weight:700; padding:.2rem .6rem; border-radius:999px; background:<?= e($catColor) ?>33; color:<?= e($catColor) ?>;"><?= e($event['category_name']) ?></span>
                        </dd>
                    </div>
                    <?php endif; ?>

                    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:.65rem 0;">
                        <dt style="color:rgba(255,255,255,.5);">Statut</dt>
                        <dd style="margin:0; text-align:right;">
                            <span style="display:inline-block; font-size:.75rem; font-weight:700; padding:.2rem .6rem; border-radius:999px; background:<?= e($statusColor) ?>22; color:<?= e($statusColor) ?>;"><?= e($statusLabel) ?></span>
                        </dd>
                    </div>
                </dl>

                <div style="margin-top:1.5rem; display:flex; flex-direction:column; gap:.75rem;">
                    <a href="<?= url('/contact') ?>" style="display:block; text-align:center; padding:.8rem 1rem; border-radius:.65rem; background:#E53935; color:#fff; font-family:var(--font-montserrat,sans-serif); font-weight:700; font-size:.9rem; text-decoration:none; transition:filter .2s;"
                       onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">S'inscrire à cet événement</a>
                    <a href="<?= url('/evenements') ?>" style="display:block; text-align:center; padding:.8rem 1rem; border-radius:.65rem; background:rgba(255,255,255,.06); color:rgba(255,255,255,.85); font-family:var(--font-montserrat,sans-serif); font-weight:600; font-size:.9rem; text-decoration:none; transition:background .2s;"
                       onmouseover="this.style.background='rgba(255,255,255,.12)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">Retour au calendrier</a>
                </div>
            </div>
        </aside>
    </div>
</section>

<!-- ===== ÉVÉNEMENTS SIMILAIRES ===== -->
<?php if (!empty($related)): ?>
<section style="background:#001a3d; padding:3.5rem 1rem;">
    <div style="max-width:80rem; margin:0 auto;">
        <h2 class="font-bebas" style="font-size:1.85rem; letter-spacing:.04em; margin:0 0 2rem; color:#fff;">Événements similaires</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1.5rem;">
            <?php foreach ($related as $r): ?>
            <?php $rColor = !empty($r['category_color']) ? (string) $r['category_color'] : '#4B5563'; ?>
            <a href="<?= url('/evenements/' . (int) $r['id']) ?>"
               style="display:block; background:#0a0e1a; border:1px solid rgba(255,255,255,.06); border-radius:1rem; padding:1.25rem; text-decoration:none; transition:transform .2s, border-color .2s;"
               onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='<?= e($rColor) ?>'"
               onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,.06)'">
                <?php if (!empty($r['category_name'])): ?>
                <span style="display:inline-block; font-family:var(--font-montserrat,sans-serif); font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:.2rem .6rem; border-radius:999px; background:<?= e($rColor) ?>33; color:<?= e($rColor) ?>; margin-bottom:.75rem;"><?= e($r['category_name']) ?></span>
                <?php endif; ?>
                <h3 class="font-montserrat" style="font-weight:700; font-size:1.05rem; color:#fff; margin:0 0 .5rem; line-height:1.3;"><?= e($r['title']) ?></h3>
                <p style="font-family:var(--font-montserrat,sans-serif); font-size:.8rem; color:rgba(255,255,255,.5); margin:0;"><?= e(ucfirst(format_date_fr((string) $r['start_datetime']))) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
