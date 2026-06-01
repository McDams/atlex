# 🚀 Guide de Déploiement — ATLEX - Sport
**Domaine cible :** atlexsport.com  
**Hébergeur :** Hostinger  
**Stack :** PHP 8.2 · MySQL 8.0 · Tailwind CSS v3

---

## ÉTAPE 1 — Créer le compte Hostinger et acheter le domaine

1. Aller sur [hostinger.com](https://www.hostinger.com/fr)
2. Créer un compte avec l'email **ulrich@atlexsport.com** (ou une adresse existante en attendant)
3. Souscrire au plan **Business Web Hosting** (recommandé — PHP 8.2 + MySQL inclus)
4. Durant le processus d'achat, rechercher et acheter le domaine **`atlexsport.com`**
5. Noter le token API Hostinger depuis **Account Settings → API Tokens → Generate New Token** (scope : `billing:read`, `domains:read`)

---

## ÉTAPE 2 — Configurer le nom de domaine (DNS)

Dans le panneau Hostinger → **DNS Zone** pour `atlexsport.com` :

| Type | Nom | Valeur | TTL |
|------|-----|--------|-----|
| A | @ | IP de ton hébergement Hostinger | 14400 |
| A | www | IP de ton hébergement Hostinger | 14400 |
| CNAME | mail | atlexsport.com | 14400 |
| MX | @ | mail.atlexsport.com | 14400 (priorité 10) |
| TXT | @ | `v=spf1 include:_spf.hostinger.com ~all` | 14400 |

> L'IP de ton hébergement est visible dans Hostinger → **Hosting → Manage → Overview**

---

## ÉTAPE 3 — Activer le SSL (HTTPS)

Dans Hostinger → **Hosting → Manage → SSL** :
1. Cliquer sur **"Install SSL"** pour `atlexsport.com`
2. Sélectionner **Let's Encrypt** (gratuit)
3. Activer également pour `www.atlexsport.com`
4. Activer **"Force HTTPS"** pour rediriger tout le trafic HTTP

---

## ÉTAPE 4 — Configurer la base de données MySQL

Dans Hostinger → **Hosting → Manage → Databases** :

1. Créer une base de données : **`atlex_sport`**
2. Créer un utilisateur : **`atlex_user`** avec un mot de passe fort
3. Accorder tous les droits à l'utilisateur sur la base
4. Noter les infos de connexion (host, user, password, dbname)

Importer le schéma via **phpMyAdmin** (accessible depuis le panneau Hostinger) :
```sql
-- 1. Importer le schéma principal
SOURCE database/schema.sql;

-- 2. Importer les données initiales
SOURCE database/seeds.sql;

-- 3. Importer la migration Hostinger
SOURCE database/migrations/hostinger.sql;
```

---

## ÉTAPE 5 — Uploader les fichiers du site

### Option A — Via le File Manager Hostinger (interface web)
1. Hostinger → **Hosting → Manage → File Manager**
2. Naviguer dans `public_html/`
3. Uploader le ZIP du projet et l'extraire

### Option B — Via FTP (recommandé pour les gros projets)
Récupérer les infos FTP dans Hostinger → **Hosting → Manage → FTP Accounts** :
```
Host     : ftp.atlexsport.com
User     : ton_user_ftp
Password : ton_mot_de_passe
Port     : 21
```

Uploader le contenu du dossier du projet dans `public_html/`.

> ⚠️ S'assurer que le dossier `public/` est le dossier racine web. Dans Hostinger → **Manage → Website → Document Root**, pointer vers `public_html/public/`

---

## ÉTAPE 6 — Configurer le fichier .env

Copier `.env.example` en `.env` à la racine du projet et remplir :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://atlexsport.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=atlex_sport
DB_USER=atlex_user
DB_PASS=TON_MOT_DE_PASSE_BDD

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USER=contact@atlexsport.com
MAIL_PASS=TON_MOT_DE_PASSE_EMAIL
MAIL_ENCRYPTION=ssl

ADMIN_EMAIL=ulrich@atlexsport.com
HOSTINGER_API_TOKEN=TON_TOKEN_API_HOSTINGER
```

---

## ÉTAPE 7 — Créer le compte administrateur

Aucun identifiant par défaut n'est livré avec le code. Créez le compte admin
avec un mot de passe fort (≥ 12 caractères) en exécutant, depuis la racine du projet :

```bash
php bin/create-admin.php
```

Le script demande le nom, l'email de connexion et le mot de passe. En
non-interactif (déploiement automatisé) :

```bash
ADMIN_NAME="Ulrich — SG" ADMIN_LOGIN_EMAIL="admin@atlexsport.com" \
ADMIN_PASSWORD="<mot_de_passe_fort>" php bin/create-admin.php
```

Connectez-vous ensuite sur `https://atlexsport.com/admin` avec ces identifiants.

> 🔒 Après 5 tentatives échouées depuis une même IP, la connexion est bloquée
> 15 minutes (protection anti-bruteforce).

---

## ÉTAPE 8 — Configurer le Token API Hostinger

1. Dans le panel admin → **Hébergement**
2. Saisir le token généré à l'ÉTAPE 1
3. Cliquer **Sauvegarder** et vérifier que la connexion est établie

---

## ÉTAPE 9 — Configurer le Cron (rappels automatiques)

Dans Hostinger → **Hosting → Manage → Cron Jobs** :

```
Commande : php /home/ton_user/public_html/cron/hostinger_reminders.php >> /home/ton_user/public_html/storage/logs/cron.log 2>&1
Fréquence : Tous les jours à 08h00 (0 8 * * *)
```

---

## ÉTAPE 10 — Vérifications finales

- [ ] `https://atlexsport.com` charge la page d'accueil ✅
- [ ] `https://atlexsport.com/admin` affiche le formulaire de connexion ✅
- [ ] Connexion admin fonctionne ✅
- [ ] Formulaire de contact envoie un email ✅
- [ ] Calendrier des événements s'affiche ✅
- [ ] Panel hébergement Hostinger affiche les données d'abonnement ✅
- [ ] SSL actif (cadenas vert dans le navigateur) ✅

---

## 📧 Boîtes email à créer (voir section suivante)

Voir `GUIDE_EMAILS.md` pour la configuration complète des emails professionnels.

---

## 🆘 Contacts techniques

En cas de problème, partager les logs PHP situés dans :
`storage/logs/` ou via Hostinger → **Hosting → Manage → Error Logs**
