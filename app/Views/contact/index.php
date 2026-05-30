<?php
$disciplines = ['football' => 'Football', 'basketball' => 'Basketball', 'handball' => 'Handball', 'arts_martiaux' => 'Arts Martiaux'];
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
            <div class="flex gap-2 mb-6" role="tablist">
                <button type="button" class="contact-tab btn-atlex text-sm" data-tab="inscription">S'inscrire</button>
                <button type="button" class="contact-tab btn-atlex-outline text-sm" data-tab="contact">Nous contacter</button>
            </div>

            <!-- Inscription -->
            <form id="tab-inscription" method="POST" action="<?= url('/inscription') ?>" class="contact-panel bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <?= csrf_field() ?>
                <h2 class="font-bebas text-2xl tracking-wider">Demande d'inscription</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input name="first_name" placeholder="Prénom *" required value="<?= e(old('first_name')) ?>" class="form-input">
                    <input name="last_name" placeholder="Nom *" required value="<?= e(old('last_name')) ?>" class="form-input">
                    <input type="email" name="email" placeholder="Email *" required value="<?= e(old('email')) ?>" class="form-input">
                    <input name="phone" placeholder="Téléphone *" required value="<?= e(old('phone')) ?>" class="form-input">
                    <input type="number" name="age" placeholder="Âge" min="3" max="99" value="<?= e(old('age')) ?>" class="form-input">
                    <select name="gender" class="form-input">
                        <option value="">Genre</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                        <option value="Autre">Autre</option>
                    </select>
                    <select name="discipline" required class="form-input sm:col-span-2">
                        <option value="">Discipline souhaitée *</option>
                        <?php foreach ($disciplines as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <textarea name="message" rows="3" placeholder="Message (facultatif)" class="form-input w-full"></textarea>
                <button type="submit" class="btn-atlex">Envoyer ma demande</button>
            </form>

            <!-- Contact -->
            <form id="tab-contact" method="POST" action="<?= url('/contact') ?>" class="contact-panel hidden bg-atlex-dark rounded-xl p-6 border border-white/5 space-y-4">
                <?= csrf_field() ?>
                <h2 class="font-bebas text-2xl tracking-wider">Nous écrire</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input name="first_name" placeholder="Prénom *" required value="<?= e(old('first_name')) ?>" class="form-input">
                    <input name="last_name" placeholder="Nom *" required value="<?= e(old('last_name')) ?>" class="form-input">
                    <input type="email" name="email" placeholder="Email *" required value="<?= e(old('email')) ?>" class="form-input sm:col-span-2">
                    <input name="phone" placeholder="Téléphone" class="form-input sm:col-span-2">
                </div>
                <textarea name="message" rows="5" placeholder="Votre message *" required class="form-input w-full"></textarea>
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
            });
            this.classList.remove('btn-atlex-outline'); this.classList.add('btn-atlex');
        });
    });
    if (location.hash === '#contact') { document.querySelector('[data-tab="contact"]').click(); }
</script>
