<?php
$disciplines = ['football' => 'Football', 'basketball' => 'Basketball', 'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux'];
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/membres') ?>" class="text-white/50 text-sm hover:text-white">← Retour à la liste</a>
    <form method="POST" action="<?= url('/admin/membres') ?>" class="bg-atlex-dark rounded-xl border border-white/5 p-6 mt-4 space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="form-label">Prénom *</label><input name="first_name" required value="<?= e(old('first_name')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Nom *</label><input name="last_name" required value="<?= e(old('last_name')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Email</label><input type="email" name="email" value="<?= e(old('email')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Téléphone</label><input name="phone" value="<?= e(old('phone')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Âge</label><input type="number" name="age" min="3" max="99" value="<?= e(old('age')) ?>" class="form-input w-full"></div>
            <div><label class="form-label">Genre</label>
                <select name="gender" class="form-input w-full">
                    <option value="">—</option><option value="M">Masculin</option><option value="F">Féminin</option><option value="Autre">Autre</option>
                </select>
            </div>
            <div><label class="form-label">Discipline *</label>
                <select name="discipline" required class="form-input w-full">
                    <option value="">—</option>
                    <?php foreach ($disciplines as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Statut</label>
                <select name="status" class="form-input w-full">
                    <option value="actif">Actif</option><option value="inactif">Inactif</option><option value="suspendu">Suspendu</option>
                </select>
            </div>
            <div class="sm:col-span-2"><label class="form-label">Date d'adhésion</label><input type="date" name="joined_at" value="<?= e(old('joined_at')) ?>" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-input w-full"><?= e(old('notes')) ?></textarea></div>
        <button type="submit" class="btn-atlex">Enregistrer</button>
    </form>
</div>
