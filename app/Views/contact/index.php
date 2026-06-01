<?php
$disciplines = ['football' => 'Football', 'basketball' => 'Basketball', 'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux'];

// Erreurs de validation (consommées une seule fois).
$formErrors = errors_all();
clear_errors();

$labelClass = 'block text-xs font-montserrat uppercase tracking-wide text-white/60 mb-1';

/** Affiche le message d'erreur d'un champ, s'il existe. */
$err = static function (string $field) use ($formErrors): string {
    if (empty($formErrors[$field][0])) {
        return '';
    }
    return '<p id="err-' . e($field) . '" class="text-atlex-red text-xs mt-1" role="alert">'
        . e($formErrors[$field][0]) . '</p>';
};
/** Attributs ARIA d'un champ en erreur. */
$invalid = static function (string $field) use ($formErrors): string {
    return empty($formErrors[$field][0])
        ? ''
        : ' aria-invalid="true" aria-describedby="err-' . e($field) . '"';
};
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Contact</h1>
    <p class="text-white/60 font-montserrat mb-12">Rejoignez-nous ou écrivez-nous</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Bloc infos -->
        <aside class="bg-atlex-dark rounded-xl p-6 border border-white/5 h-fit">
            <h2 class="font-bebas text-2xl tracking-wider mb-4">Nos coordonnées</h2>
            <ul class="space-y-3 text-sm text-white/70">
                <li><span class="text-atlex-red font-semibold">Adresse</span><br>Cotonou, Bénin</li>
                <li><span class="text-atlex-red font-semibold">Email</span><br>contact@atlexsport.com</li>
                <li><span class="text-atlex-red font-semibold">Téléphone</span><br>+229 21 30 36 00<br>+229 21 30 36 14</li>
            </ul>
        </aside>

        <!-- Formulaires à onglets -->
        <div class="lg:col-span-2">
            <div class="flex gap-2 mb-6" role="tablist" aria-label="Choisir un formulaire">
                <button type="button" id="tabbtn-inscription" class="contact-tab btn-atlex text-sm" data-tab="inscription" role="tab" aria-selected="true" aria-controls="tab-inscription">S'inscrire</button>
                <button type="button" id="tabbtn-contact" class="contact-tab btn-atlex-outline text-sm" data-tab="contact" role="tab" aria-selected="false" aria-controls="tab-contact">Nous contacter</button>
            </div>

            <!-- Inscription -->
            <form id="tab-inscription" method="POST" action="<?= url('/inscription') ?>" role="tabpanel" aria-labelledby="tabbtn-inscription" class="contact-panel bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <?= csrf_field() ?>
                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1">
                    <label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <h2 class="font-bebas text-2xl tracking-wider">Demande d'inscription</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="i-first" class="<?= $labelClass ?>">Prénom *</label>
                        <input id="i-first" name="first_name" required value="<?= e(old('first_name')) ?>" class="form-input w-full"<?= $invalid('first_name') ?>>
                        <?= $err('first_name') ?>
                    </div>
                    <div>
                        <label for="i-last" class="<?= $labelClass ?>">Nom *</label>
                        <input id="i-last" name="last_name" required value="<?= e(old('last_name')) ?>" class="form-input w-full"<?= $invalid('last_name') ?>>
                        <?= $err('last_name') ?>
                    </div>
                    <div>
                        <label for="i-email" class="<?= $labelClass ?>">Email *</label>
                        <input id="i-email" type="email" name="email" required value="<?= e(old('email')) ?>" class="form-input w-full"<?= $invalid('email') ?>>
                        <?= $err('email') ?>
                    </div>
                    <div>
                        <label for="i-phone" class="<?= $labelClass ?>">Téléphone *</label>
                        <input id="i-phone" name="phone" required value="<?= e(old('phone')) ?>" class="form-input w-full"<?= $invalid('phone') ?>>
                        <?= $err('phone') ?>
                    </div>
                    <div>
                        <label for="i-age" class="<?= $labelClass ?>">Âge</label>
                        <input id="i-age" type="number" name="age" min="3" max="99" value="<?= e(old('age')) ?>" class="form-input w-full">
                    </div>
                    <div>
                        <label for="i-gender" class="<?= $labelClass ?>">Genre</label>
                        <select id="i-gender" name="gender" class="form-input w-full">
                            <option value="">— Sélectionner —</option>
                            <option value="M" <?= old('gender') === 'M' ? 'selected' : '' ?>>Masculin</option>
                            <option value="F" <?= old('gender') === 'F' ? 'selected' : '' ?>>Féminin</option>
                            <option value="Autre" <?= old('gender') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="i-discipline" class="<?= $labelClass ?>">Discipline souhaitée *</label>
                        <select id="i-discipline" name="discipline" required class="form-input w-full"<?= $invalid('discipline') ?>>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($disciplines as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('discipline') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('discipline') ?>
                    </div>
                </div>
                <div>
                    <label for="i-message" class="<?= $labelClass ?>">Message (facultatif)</label>
                    <textarea id="i-message" name="message" rows="3" class="form-input w-full"><?= e(old('message')) ?></textarea>
                </div>
                <label class="flex items-start gap-2 text-sm text-white/70">
                    <input type="checkbox" name="consent" value="1" class="mt-1 accent-atlex-red"<?= old('consent') ? ' checked' : '' ?>>
                    <span>J'accepte que mes données soient traitées conformément à la
                        <a href="<?= url('/confidentialite') ?>" class="text-atlex-red underline">politique de confidentialité</a>. *</span>
                </label>
                <?= $err('consent') ?>
                <p class="text-white/40 text-xs">Pour une personne mineure, l'inscription est réalisée ou validée par un parent / représentant légal.</p>
                <button type="submit" class="btn-atlex">Envoyer ma demande</button>
            </form>

            <!-- Contact -->
            <form id="tab-contact" method="POST" action="<?= url('/contact') ?>" role="tabpanel" aria-labelledby="tabbtn-contact" class="contact-panel hidden bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <?= csrf_field() ?>
                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1">
                    <label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <h2 class="font-bebas text-2xl tracking-wider">Nous écrire</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="c-first" class="<?= $labelClass ?>">Prénom *</label>
                        <input id="c-first" name="first_name" required value="<?= e(old('first_name')) ?>" class="form-input w-full"<?= $invalid('first_name') ?>>
                        <?= $err('first_name') ?>
                    </div>
                    <div>
                        <label for="c-last" class="<?= $labelClass ?>">Nom *</label>
                        <input id="c-last" name="last_name" required value="<?= e(old('last_name')) ?>" class="form-input w-full"<?= $invalid('last_name') ?>>
                        <?= $err('last_name') ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="c-email" class="<?= $labelClass ?>">Email *</label>
                        <input id="c-email" type="email" name="email" required value="<?= e(old('email')) ?>" class="form-input w-full"<?= $invalid('email') ?>>
                        <?= $err('email') ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="c-phone" class="<?= $labelClass ?>">Téléphone</label>
                        <input id="c-phone" name="phone" value="<?= e(old('phone')) ?>" class="form-input w-full">
                    </div>
                </div>
                <div>
                    <label for="c-message" class="<?= $labelClass ?>">Votre message *</label>
                    <textarea id="c-message" name="message" rows="5" required class="form-input w-full"<?= $invalid('message') ?>><?= e(old('message')) ?></textarea>
                    <?= $err('message') ?>
                </div>
                <label class="flex items-start gap-2 text-sm text-white/70">
                    <input type="checkbox" name="consent" value="1" class="mt-1 accent-atlex-red"<?= old('consent') ? ' checked' : '' ?>>
                    <span>J'accepte que mes données soient traitées conformément à la
                        <a href="<?= url('/confidentialite') ?>" class="text-atlex-red underline">politique de confidentialité</a>. *</span>
                </label>
                <?= $err('consent') ?>
                <button type="submit" class="btn-atlex">Envoyer le message</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.contact-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = this.getAttribute('data-tab');
            document.querySelectorAll('.contact-panel').forEach(function (p) { p.classList.add('hidden'); });
            document.getElementById('tab-' + target).classList.remove('hidden');
            document.querySelectorAll('.contact-tab').forEach(function (t) {
                t.classList.remove('btn-atlex'); t.classList.add('btn-atlex-outline');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.remove('btn-atlex-outline'); this.classList.add('btn-atlex');
            this.setAttribute('aria-selected', 'true');
        });
    });
    if (location.hash === '#contact') { document.querySelector('[data-tab="contact"]').click(); }
</script>
