# 🏆 ATLEX - Sport

> Plateforme web officielle de l'association sportive **ATLEX - Sport** — Cotonou, Bénin.
> *Là où l'énergie devient passion.*

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/Licence-MIT-green)

---

## 📋 Présentation

ATLEX - Sport est une association sportive béninoise active dans **quatre disciplines** :
football, basketball, handball et arts martiaux.

La plateforme comprend deux espaces :

- 🌐 **Site vitrine public** : clubs/disciplines, **profils d'athlètes**, actualités (dont **rapports d'activité**), galerie, calendrier, partenaires, **centre média (presse)**, à-propos, contact & inscription.
- 🔐 **Espace d'administration (Secrétariat Général)** : tableau de bord, **tableau de bord d'impact**, gestion des membres, des athlètes, des événements, des actualités, des **projets**, de la **recherche de financements** (avec **veille automatique** et **démarches à suivre**), des **partenaires**, du **centre média**, des documents internes, des tâches (Kanban), des inscriptions et du monitoring de l'hébergement.

---

## ✨ Fonctionnalités

### Site public
- **Accueil** : hero, dernières actualités, ticker alimenté par Google Actualités (Sport).
- **Clubs** : présentation des 4 disciplines + nombre de membres actifs.
- **Athlètes** : galerie filtrable par discipline + **profil numérique** (photo, discipline, catégorie, classement, **palmarès**, **résultats**, **vidéos** YouTube intégrées).
- **Actualités** : liste paginée + filtres par catégorie (résultat, recrutement, événement, partenariat, **rapports d'activité**, général), article détaillé.
- **Galerie** photo, **Calendrier** d'événements (API JSON mensuelle).
- **Partenaires / sponsors** : groupés par niveau (officiel, associé, média), logos.
- **Centre média (presse)** : communiqués de presse (+ PDF), kit presse téléchargeable, revue de presse, contact presse.
- **Contact & inscription** : formulaires protégés (honeypot + rate-limiting), emails transactionnels.

### Espace SG (administration)
- **Tableau de bord** opérationnel + **Tableau de bord d'impact** (bénéficiaires, athlètes, membres, projets, financements obtenus, partenaires, événements, rapports — + indicateurs manuels).
- **Membres** : CRUD, recherche, statut, discipline.
- **Athlètes** : CRUD complet (photo, palmarès / résultats / vidéos en lignes répétables, publication).
- **Projets** : CRUD (statut, discipline, responsable, budget, bénéficiaires, impact, **partenaires**, **progression de collecte**).
- **Recherche de financements** : tracker d'opportunités (bailleur, type, montant, échéance, statut), rattachées à un projet, **tableau de bord** (obtenu / pipeline) + **démarches à suivre** (checklist).
- **Veille de financements** : récupération automatique d'opportunités depuis des **sources curées** (RSS ou requêtes Google Actualités) → promotion dans le tracker.
- **Partenaires**, **Centre média**, **Actualités**, **Événements**, **Documents**, **Tâches (Kanban)**, **Inscriptions** (validation/refus), **Hébergement** (monitoring Hostinger).

---

## 🛠️ Stack technique

| Couche          | Technologie                                       |
| --------------- | ------------------------------------------------- |
| Back-end        | PHP 8.2 (architecture MVC maison, sans framework) |
| Base de données | MySQL 8.0 (utf8mb4), accès via PDO préparé        |
| Front-end       | Tailwind CSS 3 (précompilé), JavaScript vanilla   |
| Autoload        | Composer (PSR-4 `App\` → `app/`)                  |
| Emails          | PHPMailer (SMTP)                                  |
| Configuration   | vlucas/phpdotenv                                  |
| Tâches planifiées | Scripts CLI dans `cron/` (crontab)              |
| Polices         | Bebas Neue, Montserrat, Poppins                   |

---

## ✅ Prérequis

- PHP **8.2**+ (extensions `pdo_mysql`, `mbstring`, `fileinfo`, `curl`)
- MySQL **8.0**+
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & npm (compilation Tailwind)

---

## 🚀 Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/McDams/atlex-sport.git
cd atlex-sport

# 2. Dépendances PHP
composer install

# 3. Dépendances front + compilation du CSS et du JS
npm install
npm run build

# 4. Environnement
cp .env.example .env
#   → renseigner la base de données et le SMTP dans .env

# 5. Base de données : schéma + données initiales
mysql -u root -p atlex_sport < database/schema.sql
mysql -u root -p atlex_sport < database/seeds.sql

# 6. Compte administrateur (mot de passe d'au moins 12 caractères)
php bin/create-admin.php

# 7. Serveur de développement
php -S localhost:8000 -t public
```

Le site est accessible sur **http://localhost:8000**.

> 🎨 **CSS et JS sont précompilés** (`app.min.css`, `app.min.js`, `admin.min.js`,
> `calendar.min.js`, `gallery.min.js`). Après toute modification d'une vue
> (nouvelles classes Tailwind) ou d'un fichier JS source, relancer
> `npm run build` (ou `npm run watch:css` pour le CSS en développement).
>
> ✅ **Tests** : `composer test` exécute la suite PHPUnit (`app/Core` —
> authentification, CSRF, limiteur de débit, validation, nettoyage HTML).

> 🗃️ **Migrations** : le schéma complet est dans `database/schema.sql`. Les
> évolutions sont versionnées dans `database/migrations/` (`001` → `009`) et
> peuvent être appliquées individuellement sur une base existante.

---

## 🔑 Accès administrateur

| Champ        | Valeur                                  |
| ------------ | --------------------------------------- |
| URL          | `/admin/login`                          |
| Identifiants | définis via `php bin/create-admin.php`  |

> 🔒 **Aucun identifiant par défaut.** Le compte admin se crée uniquement via
> `bin/create-admin.php` (mot de passe ≥ 12 caractères). Après 5 échecs de
> connexion depuis une même IP, l'accès est bloqué 15 minutes.

---

## ⏱️ Tâches planifiées (cron)

```cron
# Rappels d'expiration Hostinger — chaque jour à 8h
0 8 * * *  php /chemin/atlex-sport/cron/hostinger_reminders.php >> /chemin/atlex-sport/storage/logs/cron.log 2>&1

# Veille de financements — deux fois par jour (7h et 19h)
0 7,19 * * * php /chemin/atlex-sport/cron/funding_watch.php >> /chemin/atlex-sport/storage/logs/cron.log 2>&1

# Mise à jour de la base de géolocalisation IP (carte Analytics) — 1x/mois
0 4 5 * * php /chemin/atlex-sport/cron/geoip_update.php >> /chemin/atlex-sport/storage/logs/cron.log 2>&1

# Assistant IA réseaux sociaux — brouillons à partir des actus/événements
0 6 * * * php /chemin/atlex-sport/cron/social_content_generate.php >> /chemin/atlex-sport/storage/logs/cron.log 2>&1

# Résumés de matchs (grandes compétitions) — toutes les 2h
0 0,2,4,6,8,10,12,14,16,18,20,22 * * * php /chemin/atlex-sport/cron/sports_results_check.php >> /chemin/atlex-sport/storage/logs/cron.log 2>&1
```

> La veille fonctionne aussi à la demande via le bouton **« Rafraîchir »** dans `/admin/veille`.
>
> 🌍 **Carte géographique (Analytics)** : nécessite `MAXMIND_LICENSE_KEY` dans
> `.env` (compte gratuit sur [maxmind.com/en/geolite2/signup](https://www.maxmind.com/en/geolite2/signup)).
> Sans clé, la carte affiche simplement « Aucune donnée » — rien d'autre n'est affecté.
>
> 🤖 **Assistant IA réseaux sociaux (`/admin/social`) et articles Actualités** :
> nécessite `GEMINI_API_KEY` (clé gratuite sur
> [aistudio.google.com/apikey](https://aistudio.google.com/apikey)) et, pour les
> résumés de matchs, `SOFASCORE_API_KEY` (RapidAPI). **Supervisé** : l'IA ne fait
> que proposer des brouillons — chaque publication passe par une approbation
> manuelle explicite dans l'admin, jamais automatique.

---

## 📁 Arborescence

```
atlex-sport/
├── app/
│   ├── Controllers/        # Contrôleurs publics + Admin/
│   ├── Core/               # Router, Database, Session, Auth, CSRF, FileUpload, Validator, RateLimiter, Mailer
│   ├── Helpers/            # functions.php, constants.php
│   ├── Models/             # BaseModel + modèles métier
│   ├── Services/           # GoogleNewsService, FundingWatchService, Hostinger*
│   └── Views/              # Gabarits PHP (layouts, partials, pages)
├── config/                 # app.php, database.php, routes.php
├── cron/                   # Scripts planifiés (hostinger_reminders, funding_watch)
├── database/
│   ├── migrations/         # 001 → 009 (migrations SQL)
│   ├── schema.sql          # Schéma complet
│   └── seeds.sql           # Données initiales
├── public/                 # Racine web (front controller)
│   ├── assets/             # css/, js/, images/
│   ├── uploads/            # Fichiers téléversés (photos, logos, PDF…)
│   ├── .htaccess           # Réécriture + en-têtes de sécurité (CSP, HSTS…)
│   └── index.php
├── storage/                # logs/, cache/ (ratelimit, flux), uploads internes
├── .env.example
├── composer.json
├── package.json
└── tailwind.config.js
```

---

## 🗄️ Base de données (tables principales)

| Table | Rôle |
| ----- | ---- |
| `users` | Comptes administrateurs (SG) |
| `members` | Membres de l'association |
| `athletes` (+ `athlete_achievements`, `athlete_results`, `athlete_videos`) | Profils numériques d'athlètes |
| `events` | Événements / calendrier |
| `news_articles` | Actualités (catégorie `rapport` pour les rapports d'activité) |
| `gallery_photos` | Galerie photo |
| `sponsors` | Partenaires / sponsors |
| `projects` (+ `project_partners`) | Projets de l'association |
| `funding_opportunities` (+ `funding_checklist`) | Tracker de financements + démarches |
| `funding_sources`, `funding_leads` | Veille de financements (sources + opportunités détectées) |
| `impact_indicators` | Indicateurs d'impact manuels |
| `press_releases`, `press_kit_items`, `press_coverage` | Centre média |
| `documents` | Documents internes (SG) |
| `tasks` | Tâches (Kanban) |
| `contact_submissions` | Messages de contact + demandes d'inscription |
| `settings` | Paramètres clé/valeur (token Hostinger, contact presse, modèle de démarches…) |

---

## 🌐 Routes

### Public

| Méthode | URL | Description |
| ------- | --- | ----------- |
| GET | `/` | Accueil |
| GET | `/clubs` · `/clubs/{slug}` | Disciplines |
| GET | `/athletes` · `/athletes/{slug}` | Athlètes (liste + profil) |
| GET | `/actualites` · `/actualites/{slug}` | Actualités |
| GET | `/galerie` | Galerie photo |
| GET | `/calendrier` · `/api/events/{year}/{month}` | Calendrier (+ API JSON) |
| GET | `/a-propos` | À propos |
| GET | `/sponsors` | Partenaires & sponsors |
| GET | `/centre-media` · `/centre-media/communiques/{slug}` | Centre média (presse) |
| GET / POST | `/contact` | Formulaire de contact |
| POST | `/inscription` | Formulaire d'inscription |
| GET | `/confidentialite` · `/sitemap.xml` | Confidentialité, sitemap |

### Administration (authentification requise)

| Méthode | URL | Description |
| ------- | --- | ----------- |
| GET/POST | `/admin/login` · POST `/admin/logout` | Connexion / déconnexion |
| GET | `/admin` | Tableau de bord |
| GET | `/admin/impact` | Tableau de bord d'impact |
| CRUD | `/admin/membres` | Membres |
| CRUD | `/admin/athletes` | Profils athlètes |
| CRUD | `/admin/evenements` | Événements |
| CRUD | `/admin/actualites` | Actualités |
| CRUD | `/admin/projets` | Projets |
| CRUD | `/admin/financements` (+ checklist) | Recherche de financements |
| GET/POST | `/admin/veille` (+ `/sources`) | Veille de financements |
| CRUD | `/admin/partenaires` | Partenaires |
| GET/CRUD | `/admin/media` (communiqués, kit, revue, contact) | Centre média |
| GET | `/admin/inscriptions` | Validation des inscriptions |
| CRUD | `/admin/documents` | Documents internes |
| CRUD | `/admin/taches` | Tâches (Kanban) |
| GET/POST | `/admin/hostinger` | Monitoring hébergement |

> *CRUD* = `index`, `nouveau`/`create`, `store` (POST), `edit`, `update` (PUT), `destroy` (DELETE). Les verbes PUT/DELETE passent par un champ `_method`.

---

## 🔐 Sécurité

- **CSRF** par jeton de session (`hash_equals`) sur toutes les écritures.
- **PDO préparé** partout, `EMULATE_PREPARES = false` (anti-injection SQL).
- **Mass-assignment** verrouillé (liste `$fillable` par modèle).
- **Sessions** durcies (`HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS, régénération à la connexion).
- **Rate-limiting** sur la connexion admin et les formulaires publics ; **honeypot** anti-spam.
- **Téléversements** : validation MIME réelle, exécution désactivée dans `public/uploads/` (SVG/HTML/PHP refusés).
- **En-têtes** : CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy ; HTTPS forcé.
- **Liens externes** filtrés (`http(s)` uniquement) ; secrets hors dépôt (`.env`).

---

## 🗺️ Feuille de route

- **Phase 1 — ✅ MVP** : site vitrine + espace SG (membres, événements, actualités, documents, tâches).
- **Phase 2 — ✅ Extension métier** : profils athlètes, projets, recherche de financements (+ veille auto & démarches), tableau de bord d'impact, espace partenaires, centre média.
- **Phase 3 — Réseaux sociaux & IA** : flux Facebook/Instagram, recommandations de contenu, assistant de rédaction.
- **Pistes** : tests automatisés (PHPUnit), pagination des listes admin, sauvegardes automatiques de la base.

---

## 🤝 Contribution

1. Forkez le projet
2. Créez une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Committez (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Poussez (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

---

## 📄 Licence

Distribué sous licence **MIT**. Voir le fichier `LICENSE`.

---

<p align="center">
  <strong>ATLEX - Sport</strong> — Là où l'énergie devient passion. 🇧🇯
</p>
