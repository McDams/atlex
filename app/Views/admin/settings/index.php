<?php
/**
 * @var array<string,mixed>  $user
 * @var array<string,string> $settings
 */

$card = 'background:#001a3d;border:1px solid rgba(255,255,255,0.06);border-radius:14px;padding:28px;margin-bottom:24px;';
$sectionTitle = "font-family:'Bebas Neue',sans-serif;color:#E53935;font-size:1.9rem;letter-spacing:0.04em;margin:0 0 6px;";
$sectionDesc = "font-family:'Montserrat',sans-serif;color:rgba(255,255,255,0.55);font-size:0.85rem;margin:0 0 22px;";
$label = "display:block;font-family:'Montserrat',sans-serif;font-size:0.8rem;color:rgba(255,255,255,0.75);margin:0 0 6px;font-weight:600;";
$input = "width:100%;background:#0a0e1a;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:11px 13px;color:#fff;font-family:'Poppins',sans-serif;font-size:0.9rem;box-sizing:border-box;";
$field = 'margin-bottom:16px;';
$btn = "background:#E53935;color:#fff;border:none;border-radius:8px;padding:12px 26px;font-family:'Montserrat',sans-serif;font-weight:600;font-size:0.9rem;cursor:pointer;transition:background 0.15s;";
$btnHover = "this.style.background='#b71c1c'";
$btnOut = "this.style.background='#E53935'";
?>
<div style="max-width:760px;">

    <?php if ($msg = flash('success')): ?>
        <div data-flash style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.4);color:#86efac;padding:12px 16px;border-radius:8px;margin-bottom:24px;font-family:'Poppins',sans-serif;font-size:0.9rem;">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div data-flash style="background:rgba(229,57,53,0.15);border:1px solid rgba(229,57,53,0.4);color:#fca5a5;padding:12px 16px;border-radius:8px;margin-bottom:24px;font-family:'Poppins',sans-serif;font-size:0.9rem;">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Section 1 — Mon Profil -->
    <section style="<?= $card ?>">
        <h2 style="<?= $sectionTitle ?>">Mon Profil</h2>
        <p style="<?= $sectionDesc ?>">Cette adresse email est utilisée pour vous connecter à l'admin.</p>
        <form method="POST" action="<?= url('/admin/settings/profile') ?>">
            <?= csrf_field() ?>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Nom complet</label>
                <input type="text" name="name" required value="<?= e($user['name'] ?? '') ?>" style="<?= $input ?>">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Adresse email de connexion</label>
                <input type="email" name="email" required value="<?= e($user['email'] ?? '') ?>" style="<?= $input ?>">
            </div>
            <button type="submit" style="<?= $btn ?>" onmouseover="<?= $btnHover ?>" onmouseout="<?= $btnOut ?>">Enregistrer le profil</button>
        </form>
    </section>

    <!-- Section 2 — Sécurité / Mot de passe -->
    <section style="<?= $card ?>">
        <h2 style="<?= $sectionTitle ?>">Sécurité</h2>
        <p style="<?= $sectionDesc ?>">Choisissez un mot de passe d'au moins 8 caractères.</p>
        <form method="POST" action="<?= url('/admin/settings/password') ?>">
            <?= csrf_field() ?>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Mot de passe actuel</label>
                <input type="password" name="current_password" required autocomplete="current-password" style="<?= $input ?>">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Nouveau mot de passe</label>
                <input type="password" name="new_password" id="new_password" required minlength="8" autocomplete="new-password" style="<?= $input ?>" oninput="atlexPwdStrength(this.value)">
                <div style="height:6px;border-radius:4px;background:rgba(255,255,255,0.1);margin-top:8px;overflow:hidden;">
                    <div id="pwd-bar" style="height:100%;width:0;background:#E53935;transition:width 0.2s,background 0.2s;"></div>
                </div>
                <span id="pwd-label" style="font-family:'Poppins',sans-serif;font-size:0.75rem;color:rgba(255,255,255,0.5);"></span>
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password" style="<?= $input ?>">
            </div>
            <button type="submit" style="<?= $btn ?>" onmouseover="<?= $btnHover ?>" onmouseout="<?= $btnOut ?>">Changer le mot de passe</button>
        </form>
    </section>

    <!-- Section 3 — Paramètres du site -->
    <section style="<?= $card ?>">
        <h2 style="<?= $sectionTitle ?>">Paramètres du site</h2>
        <p style="<?= $sectionDesc ?>">Informations publiques affichées sur le site et coordonnées de contact.</p>
        <form method="POST" action="<?= url('/admin/settings/site') ?>">
            <?= csrf_field() ?>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Nom du site</label>
                <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>" style="<?= $input ?>">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Description</label>
                <textarea name="site_description" rows="3" style="<?= $input ?>resize:vertical;"><?= e($settings['site_description'] ?? '') ?></textarea>
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Email de contact public</label>
                <input type="email" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>" style="<?= $input ?>">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Téléphone</label>
                <input type="text" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>" style="<?= $input ?>">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">Adresse</label>
                <textarea name="contact_address" rows="2" style="<?= $input ?>resize:vertical;"><?= e($settings['contact_address'] ?? '') ?></textarea>
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">URL Facebook</label>
                <input type="url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>" style="<?= $input ?>" placeholder="https://facebook.com/...">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">URL Instagram</label>
                <input type="url" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>" style="<?= $input ?>" placeholder="https://instagram.com/...">
            </div>
            <div style="<?= $field ?>">
                <label style="<?= $label ?>">URL YouTube</label>
                <input type="url" name="youtube_url" value="<?= e($settings['youtube_url'] ?? '') ?>" style="<?= $input ?>" placeholder="https://youtube.com/...">
            </div>
            <div style="<?= $field ?>display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="maintenance_mode" value="1" id="maintenance_mode" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#E53935;cursor:pointer;">
                <label for="maintenance_mode" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;color:rgba(255,255,255,0.75);cursor:pointer;margin:0;">Mode maintenance (site fermé au public)</label>
            </div>
            <button type="submit" style="<?= $btn ?>" onmouseover="<?= $btnHover ?>" onmouseout="<?= $btnOut ?>">Enregistrer les paramètres</button>
        </form>
    </section>
</div>

<script nonce="<?= \App\Core\Security::nonce() ?>">
function atlexPwdStrength(value) {
    var bar = document.getElementById('pwd-bar');
    var label = document.getElementById('pwd-label');
    if (!bar || !label) return;
    var score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    var pct = [0, 25, 50, 75, 100][score];
    var colors = ['#E53935', '#E53935', '#f59e0b', '#eab308', '#22c55e'];
    var texts = ['', 'Faible', 'Moyen', 'Bon', 'Fort'];
    bar.style.width = pct + '%';
    bar.style.background = colors[score];
    label.textContent = value ? texts[score] : '';
}
</script>
