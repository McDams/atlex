<?php
/** @var array<string,mixed> $member */
$disciplines = ['football' => 'Football', 'basketball' => 'Basketball', 'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux'];
$genders = ['M' => 'Masculin', 'F' => 'Féminin', 'Autre' => 'Autre'];
$statuses = ['actif' => 'Actif', 'inactif' => 'Inactif', 'suspendu' => 'Suspendu'];
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/membres') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la liste</a>
    <form method="POST" action="<?= url('/admin/membres/' . $member['id']) ?>" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?><?= method_field('PUT') ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Prénom *</label><input name="first_name" required value="<?= e($member['first_name']) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Nom *</label><input name="last_name" required value="<?= e($member['last_name']) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Email</label><input type="email" name="email" value="<?= e($member['email']) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Téléphone</label><input name="phone" value="<?= e($member['phone']) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Âge</label><input type="number" name="age" min="3" max="99" value="<?= e($member['age']) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Genre</label>
                <select name="gender" class="form-input w-full">
                    <option value="">—</option>
                    <?php foreach ($genders as $k => $v): ?><option value="<?= e($k) ?>" <?= $member['gender'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Discipline *</label>
                <select name="discipline" required class="form-input w-full">
                    <?php foreach ($disciplines as $k => $v): ?><option value="<?= e($k) ?>" <?= $member['discipline'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Statut</label>
                <select name="status" class="form-input w-full">
                    <?php foreach ($statuses as $k => $v): ?><option value="<?= e($k) ?>" <?= $member['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="sm:col-span-2"><label class="form-label">Date d'adhésion</label><input type="date" name="joined_at" value="<?= e($member['joined_at']) ?>" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-input w-full"><?= e($member['notes']) ?></textarea></div>
        <button type="submit" class="btn-atlex">Mettre à jour</button>
    </form>
</div>
