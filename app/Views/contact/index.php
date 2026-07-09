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
                <li><span class="text-atlex-red font-semibold">Email</span><br>contact@atlex-sport.com</li>
                <li><span class="text-atlex-red font-semibold">Téléphone</span><br>+229 01 92 57 33 33</li>
            </ul>
        </aside>

        <!-- Formulaires à onglets -->
        <div class="lg:col-span-2">
            <div class="flex gap-2 mb-6" role="tablist" aria-label="Choisir un formulaire">
                <button type="button" id="tabbtn-inscription" class="contact-tab btn-atlex text-sm" data-tab="inscription" role="tab" aria-selected="true" aria-controls="tab-inscription">S'inscrire</button>
                <button type="button" id="tabbtn-contact" class="contact-tab btn-atlex-outline text-sm" data-tab="contact" role="tab" aria-selected="false" aria-controls="tab-contact">Nous contacter</button>
                <button type="button" id="tabbtn-benevol" class="contact-tab btn-atlex-outline text-sm" data-tab="benevol" role="tab" aria-selected="false" aria-controls="tab-benevol">Devenir bénévole</button>
                <button type="button" id="tabbtn-don" class="contact-tab btn-atlex-outline text-sm" data-tab="don" role="tab" aria-selected="false" aria-controls="tab-don">Faire un don</button>
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
                        <!--Proposer le choix de l'indicatif de tous les pays en plus du numéro de téléphone-->
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

            <!-- Bénévolat -->
            <form id="tab-benevol" method="POST" action="<?= url('/contact/benevole') ?>" role="tabpanel" aria-labelledby="tabbtn-benevol" class="contact-panel hidden bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <?= csrf_field() ?>
                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1">
                    <label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <h2 class="font-bebas text-2xl tracking-wider">Devenir bénévole</h2>
                <p class="text-white/70">L'Espace Bénévolat d'Atlex-Sport permet aux associations, clubs, projets communautaires et initiatives sportives de faire connaître leurs besoins en ressources humaines, tout en offrant aux citoyens l'opportunité de s'engager selon leurs compétences, leurs centres d'intérêt et leurs disponibilités.
                    En quelques clics, chacun peut rejoindre une mission bénévole, participer à l'organisation d'événements sportifs, soutenir des actions sociales ou contribuer à des projets en faveur de la jeunesse et du développement local.
                    Parce que le sport est un puissant levier de transformation sociale, l'engagement bénévole permet à chaque citoyen de devenir un acteur du changement, de renforcer la cohésion communautaire et de contribuer à la construction d'un avenir plus inclusif, solidaire et dynamique pour tous.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="b-first" class="<?= $labelClass ?>">Prénom *</label>
                        <input id="b-first" name="first_name" required value="<?= e(old('first_name')) ?>" class="form-input w-full"<?= $invalid('first_name') ?>>
                        <?= $err('first_name') ?>
                    </div>
                    <div>
                        <label for="b-last" class="<?= $labelClass ?>">Nom *</label>
                        <input id="b-last" name="last_name" required value="<?= e(old('last_name')) ?>" class="form-input w-full"<?= $invalid('last_name') ?>>
                        <?= $err('last_name') ?>
                    </div>
                    <div>
                        <label for="b-phone" class="<?= $labelClass ?>">Téléphone *</label>
                        <input id="b-phone" name="phone" required value="<?= e(old('phone')) ?>" class="form-input w-full"<?= $invalid('phone') ?>>
                        <?= $err('phone') ?>
                    </div>
                    <div>
                        <label for="b-email" class="<?= $labelClass ?>">Email *</label>
                        <input id="b-email" type="email" name="email" required value="<?= e(old('email')) ?>" class="form-input w-full"<?= $invalid('email') ?>>
                        <?= $err('email') ?>
                    </div>
                    <div>
                        <label for="b-age" class="<?= $labelClass ?>">Âge *</label>
                        <input id="b-age" type="number" name="age" min="16" max="99" required value="<?= e(old('age')) ?>" class="form-input w-full"<?= $invalid('age') ?>>
                        <?= $err('age') ?>
                    </div>
                </div>
                <?php
                $volunteerMissions = \App\Controllers\ContactController::VOLUNTEER_MISSIONS;
                $selectedMissions = (array) old('missions', []);
                ?>
                <fieldset>
                    <legend class="<?= $labelClass ?>">Missions souhaitées * (plusieurs choix possibles)</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                        <?php foreach ($volunteerMissions as $value => $label): ?>
                            <label class="flex items-center gap-2 text-sm text-white/80 bg-atlex-bg border border-white/10 rounded-md px-3 py-2 cursor-pointer">
                                <input type="checkbox" name="missions[]" value="<?= e($value) ?>" class="accent-atlex-red"<?= in_array($value, $selectedMissions, true) ? ' checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= $err('missions') ?>
                </fieldset>
                <div>
                    <label for="b-message" class="<?= $labelClass ?>">Message (optionnel)</label>
                    <textarea id="b-message" name="message" rows="4" placeholder="Parlez-nous de vous, de vos disponibilités, de vos compétences..." class="form-input w-full"><?= e(old('message')) ?></textarea>
                </div>
                <button type="submit" class="btn-atlex">Envoyer ma candidature</button>
            </form>

            <!-- Don -->
            <div id="tab-don" role="tabpanel" aria-labelledby="tabbtn-don" class="contact-panel hidden bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <h2 class="font-bebas text-2xl tracking-wider">Faire un don</h2>
                <p class="text-white/70">Votre soutien financier est essentiel pour permettre à Atlex-Sport de poursuivre sa mission d'inclusion sociale par le sport. En faisant un don, vous contribuez directement à la mise en place de programmes sportifs accessibles à tous, au développement d'infrastructures adaptées et à l'organisation d'événements qui rassemblent les communautés autour des valeurs du sport.
                    Chaque contribution, quelle que soit sa taille, fait une différence significative dans la vie des bénéficiaires de nos actions. En soutenant Atlex-Sport, vous devenez un acteur clé du changement social, en aidant à créer des opportunités pour les personnes défavorisées, en renforçant la cohésion sociale et en promouvant un avenir plus inclusif et solidaire grâce au pouvoir du sport.
                </p>
            </div>
        </div>
    </div>
</section>

<script nonce="<?= \App\Core\Security::nonce() ?>">
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
    if (location.hash === '#benevol') { document.querySelector('[data-tab="benevol"]').click(); }
</script>
