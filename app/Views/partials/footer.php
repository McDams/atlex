<footer class="bg-atlex-dark border-t border-white/5 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-1 md:grid-cols-4 gap-10">

        <!-- Colonne 1 : logo + slogan -->
        <div>
            <div style="display:flex; flex-direction:column; align-items:flex-start; gap:8px; margin-bottom:16px;">
                <img src="<?= asset('images/LOGO.jpeg') ?>" alt="ATLEX Sport" style="height:80px; width:auto; object-fit:contain; background:white; border-radius:8px; padding:6px;">
                <span style="font-family:'Bebas Neue',sans-serif; font-size:1.5rem; letter-spacing:0.05em; color:white; line-height:1.2;">ATLEX<br><span style="color:#E53935;">Sport</span></span>
            </div>
            <p class="text-atlex-beige font-montserrat italic text-sm mb-2">Là où l'énergie devient passion.</p>
            <p class="text-white/50 text-sm">Association sportive ATLANTIS EXPERTISE SPORT — Cotonou, Bénin. Fondée le 26 août 2023.</p>
        </div>

        <!-- Colonne 2 : navigation -->
        <div>
            <h3 class="font-bebas text-lg tracking-wider text-white mb-4">Navigation</h3>
            <ul class="space-y-2 text-sm text-white/60">
                <li><a href="<?= url('/clubs') ?>" class="hover:text-atlex-red">Clubs &amp; Disciplines</a></li>
                <li><a href="<?= url('/actualites') ?>" class="hover:text-atlex-red">Actualités</a></li>
                <li><a href="<?= url('/galerie') ?>" class="hover:text-atlex-red">Galerie</a></li>
                <li><a href="<?= url('/calendrier') ?>" class="hover:text-atlex-red">Calendrier</a></li>
            </ul>
        </div>

        <!-- Colonne 3 : association -->
        <div>
            <h3 class="font-bebas text-lg tracking-wider text-white mb-4">L'association</h3>
            <ul class="space-y-2 text-sm text-white/60">
                <li><a href="<?= url('/a-propos') ?>" class="hover:text-atlex-red">À propos</a></li>
                <li><a href="<?= url('/sponsors') ?>" class="hover:text-atlex-red">Sponsors &amp; Partenaires</a></li>
                <li><a href="<?= url('/contact') ?>" class="hover:text-atlex-red">Nous contacter</a></li>
                <li><a href="<?= url('/confidentialite') ?>" class="hover:text-atlex-red">Confidentialité</a></li>
                <li><a href="<?= url('/admin/login') ?>" class="hover:text-atlex-red">Espace SG</a></li>
            </ul>
        </div>

        <!-- Colonne 4 : contact -->
        <div>
            <h3 class="font-bebas text-lg tracking-wider text-white mb-4">Contact</h3>
            <ul class="space-y-2 text-sm text-white/60">
                <li>Cotonou, Bénin</li>
                <li><a href="mailto:contact@atlex-sport.com" class="hover:text-atlex-red">contact@atlex-sport.com</a></li>
                <li>+229 21 30 36 00</li>
                <li>+229 21 30 36 14</li>
            </ul>
            <div class="flex gap-3 mt-4">
                <a href="#" aria-label="Facebook" class="w-9 h-9 grid place-items-center rounded-full bg-white/5 hover:bg-atlex-red transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-9h3l.5-4H13V6.5c0-1.1.3-1.9 1.9-1.9H17V1.1C16.6 1 15.3 1 13.9 1 11 1 9 2.7 9 6.1V9H6v4h3v9h4z"/></svg>
                </a>
                <a href="#" aria-label="Instagram" class="w-9 h-9 grid place-items-center rounded-full bg-white/5 hover:bg-atlex-red transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.1.4.3 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.1-1 .3-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4a3.7 3.7 0 0 1-1.4-.9 3.7 3.7 0 0 1-.9-1.4c-.1-.4-.3-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.1 1-.3 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 4.9a4.9 4.9 0 1 0 0 9.8 4.9 4.9 0 0 0 0-9.8zm0 8.1a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zm6.2-8.3a1.1 1.1 0 1 1-2.3 0 1.1 1.1 0 0 1 2.3 0z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 text-center text-xs text-white/40">
            © <?= date('Y') ?> ATLANTIS EXPERTISE SPORT (ATLEX - Sport). Tous droits réservés.
        </div>
    </div>
</footer>
