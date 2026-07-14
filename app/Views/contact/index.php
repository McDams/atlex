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

                <div class="bg-atlex-bg rounded-lg border border-white/10 p-5 mt-2">
                    <div class="flex gap-2 mb-5">
                        <button type="button" class="don-method-tab btn-atlex text-sm" data-method="momo">Mobile Money (MTN)</button>
                        <button type="button" class="don-method-tab btn-atlex-outline text-sm" data-method="paypal">PayPal</button>
                    </div>

                    <div id="don-alert" class="hidden text-sm rounded-lg px-4 py-3 mb-4"></div>

                    <!-- Champs communs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="<?= $labelClass ?>">Nom complet</label>
                            <input type="text" id="don-name" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Email</label>
                            <input type="email" id="don-email" class="form-input w-full" required>
                        </div>
                    </div>
                    <label class="hidden">Ne pas remplir<input type="text" id="don-website" tabindex="-1" autocomplete="off"></label>
                    <input type="hidden" id="don-csrf" value="<?= e(\App\Core\CSRF::generateToken()) ?>">

                    <!-- MoMo -->
                    <div id="don-panel-momo" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="<?= $labelClass ?>">Numéro MTN MoMo (le vôtre)</label>
                                <input type="tel" id="don-momo-phone" placeholder="+229 XX XX XX XX" class="form-input w-full" required>
                            </div>
                            <div>
                                <label class="<?= $labelClass ?>">Montant (FCFA)</label>
                                <input type="number" id="don-momo-amount" min="100" step="1" placeholder="5000" class="form-input w-full" required>
                            </div>
                        </div>
                        <button type="button" id="don-momo-submit" class="btn-atlex text-sm">Faire le don via MoMo</button>
                        <p class="text-white/40 text-xs">
                            Vous recevrez une notification sur votre téléphone pour confirmer le paiement
                            avec votre code MoMo.
                        </p>
                        <div id="don-momo-status" class="hidden text-sm text-white/70"></div>
                    </div>

                    <!-- PayPal -->
                    <div id="don-panel-paypal" class="space-y-4 hidden">
                        <div class="max-w-xs">
                            <label class="<?= $labelClass ?>">Montant (EUR)</label>
                            <input type="number" id="don-paypal-amount" min="1" step="0.01" placeholder="10.00" class="form-input w-full" required>
                        </div>
                        <?php if (empty($paypalClientId)): ?>
                            <p class="text-atlex-red text-sm">Le paiement PayPal n'est pas encore configuré.</p>
                        <?php else: ?>
                            <div id="don-paypal-buttons" class="max-w-xs"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($paypalClientId)): ?>
    <script
        src="https://www.paypal.com/sdk/js?client-id=<?= urlencode($paypalClientId) ?>&currency=EUR"
        nonce="<?= \App\Core\Security::nonce() ?>"
    ></script>
<?php endif; ?>

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

    // ------------------------------------------------------------
    // Dons — bascule de méthode
    // ------------------------------------------------------------
    document.querySelectorAll('.don-method-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var method = this.getAttribute('data-method');
            document.getElementById('don-panel-momo').classList.toggle('hidden', method !== 'momo');
            document.getElementById('don-panel-paypal').classList.toggle('hidden', method !== 'paypal');
            document.querySelectorAll('.don-method-tab').forEach(function (b) {
                b.classList.remove('btn-atlex'); b.classList.add('btn-atlex-outline');
            });
            this.classList.remove('btn-atlex-outline'); this.classList.add('btn-atlex');
        });
    });

    function donAlert(message, type) {
        var el = document.getElementById('don-alert');
        el.textContent = message;
        el.className = 'text-sm rounded-lg px-4 py-3 mb-4 ' +
            (type === 'error' ? 'bg-atlex-red/20 text-atlex-red' : 'bg-green-600/20 text-green-300');
    }
    function donCsrf() { return document.getElementById('don-csrf').value; }
    function donHoneypot() { return document.getElementById('don-website').value; }

    // ------------------------------------------------------------
    // Dons — MTN MoMo
    // ------------------------------------------------------------
    var momoSubmit = document.getElementById('don-momo-submit');
    if (momoSubmit) {
        momoSubmit.addEventListener('click', function () {
            var name = document.getElementById('don-name').value.trim();
            var email = document.getElementById('don-email').value.trim();
            var phone = document.getElementById('don-momo-phone').value.trim();
            var amount = document.getElementById('don-momo-amount').value;

            if (!name || !email || !phone || !amount) {
                donAlert('Merci de remplir tous les champs.', 'error');
                return;
            }

            momoSubmit.disabled = true;
            momoSubmit.textContent = 'Envoi en cours...';

            var body = new URLSearchParams();
            body.set('_token', donCsrf());
            body.set('website', donHoneypot());
            body.set('donor_name', name);
            body.set('donor_email', email);
            body.set('donor_phone', phone);
            body.set('amount', amount);

            fetch('<?= url('/don/momo/initier') ?>', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) {
                        donAlert(data.error, 'error');
                        momoSubmit.disabled = false;
                        momoSubmit.textContent = 'Faire le don via MoMo';
                        return;
                    }
                    var status = document.getElementById('don-momo-status');
                    status.classList.remove('hidden');
                    status.textContent = 'Consultez votre téléphone pour confirmer le paiement...';
                    pollMomoStatus(data.reference, 0);
                })
                .catch(function () {
                    donAlert('Erreur réseau. Réessayez.', 'error');
                    momoSubmit.disabled = false;
                    momoSubmit.textContent = 'Faire le don via MoMo';
                });
        });
    }

    function pollMomoStatus(reference, attempt) {
        var status = document.getElementById('don-momo-status');

        if (attempt > 40) {
            status.textContent = 'Délai dépassé. Si vous avez confirmé sur votre téléphone, votre don sera bien enregistré sous peu.';
            return;
        }

        fetch('<?= url('/don/momo/statut') ?>/' + encodeURIComponent(reference))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'completed') {
                    donAlert('Merci pour votre don ! Il a bien été confirmé.', 'success');
                    status.classList.add('hidden');
                    momoSubmit.disabled = false;
                    momoSubmit.textContent = 'Faire le don via MoMo';
                } else if (data.status === 'failed') {
                    donAlert('Le paiement a échoué ou a été annulé.', 'error');
                    status.classList.add('hidden');
                    momoSubmit.disabled = false;
                    momoSubmit.textContent = 'Faire le don via MoMo';
                } else {
                    setTimeout(function () { pollMomoStatus(reference, attempt + 1); }, 3000);
                }
            })
            .catch(function () {
                setTimeout(function () { pollMomoStatus(reference, attempt + 1); }, 3000);
            });
    }

    // ------------------------------------------------------------
    // Dons — PayPal
    // ------------------------------------------------------------
    <?php if (!empty($paypalClientId)): ?>
    if (window.paypal && document.getElementById('don-paypal-buttons')) {
        paypal.Buttons({
            createOrder: function () {
                var name = document.getElementById('don-name').value.trim();
                var email = document.getElementById('don-email').value.trim();
                var amount = document.getElementById('don-paypal-amount').value;

                if (!name || !email || !amount) {
                    donAlert('Merci de renseigner votre nom, email et le montant avant de continuer.', 'error');
                    return Promise.reject(new Error('missing-fields'));
                }

                var body = new URLSearchParams();
                body.set('_token', donCsrf());
                body.set('website', donHoneypot());
                body.set('donor_name', name);
                body.set('donor_email', email);
                body.set('amount', amount);

                return fetch('<?= url('/don/paypal/creer-commande') ?>', { method: 'POST', body: body })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.error) {
                            donAlert(data.error, 'error');
                            throw new Error(data.error);
                        }
                        return data.orderID;
                    });
            },
            onApprove: function (data) {
                var body = new URLSearchParams();
                body.set('_token', donCsrf());
                body.set('orderID', data.orderID);

                return fetch('<?= url('/don/paypal/capturer') ?>', { method: 'POST', body: body })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.status === 'completed') {
                            donAlert('Merci pour votre don ! Il a bien été confirmé.', 'success');
                        } else {
                            donAlert("Le paiement n'a pas pu être confirmé.", 'error');
                        }
                    });
            },
            onError: function () {
                donAlert('Une erreur est survenue avec PayPal. Réessayez.', 'error');
            }
        }).render('#don-paypal-buttons');
    }
    <?php endif; ?>
</script>
