<h1><?= htmlspecialchars($event['title']) ?></h1>

<p><strong>Date :</strong>
    <?= date('d/m/Y H:i', strtotime($event['start_datetime'])) ?>
</p>

<?php if (!empty($event['end_datetime'])): ?>
<p><strong>Fin :</strong>
    <?= date('d/m/Y H:i', strtotime($event['end_datetime'])) ?>
</p>
<?php endif; ?>

<p><strong>Lieu :</strong> <?= htmlspecialchars($event['location']) ?></p>

<p><strong>Catégorie :</strong>
    <span style="color: <?= $event['category_color'] ?>">
        <?= htmlspecialchars($event['category_name']) ?>
    </span>
</p>

<p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
