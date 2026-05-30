<div class="min-h-screen grid place-items-center px-4">
    <div class="w-full max-w-md bg-atlex-dark rounded-2xl border border-white/5 p-8 shadow-2xl">
        <div class="flex items-center justify-center gap-2 mb-8">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path d="M20 3 L37 34 H3 Z" fill="#E53935"/><path d="M20 14 L28 30 H12 Z" fill="#001a3d"/>
            </svg>
            <span class="font-bebas text-2xl tracking-wider">ATL<span class="text-atlex-beige">É</span>X · Espace SG</span>
        </div>

        <h1 class="font-bebas text-3xl tracking-wider text-center mb-1">Connexion</h1>
        <p class="text-white/50 text-sm text-center mb-6 font-montserrat">Accès réservé au Secrétariat Général</p>

        <form method="POST" action="<?= url('/admin/login') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm text-white/60 mb-1 font-montserrat">Email</label>
                <input type="email" name="email" required autofocus class="form-input w-full" placeholder="admin@atlexsport.com">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-1 font-montserrat">Mot de passe</label>
                <input type="password" name="password" required class="form-input w-full" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-atlex w-full text-center">Se connecter</button>
        </form>

        <a href="<?= url('/') ?>" class="block text-center text-white/40 text-xs mt-6 hover:text-white">← Retour au site</a>
    </div>
</div>
