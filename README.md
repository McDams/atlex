# 🏆 ATLÉX-SPORT

> Plateforme web officielle de l'association sportive **ATLÉX-SPORT** — Cotonou, Bénin.
> *Là où l'énergie devient passion.*

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/Licence-MIT-green)

---

## 📋 Présentation

ATLÉX-SPORT est une association sportive béninoise active dans **quatre disciplines** :
football, basketball, handball et arts martiaux.

Cette plateforme propose :

- 🌐 **Site vitrine public** : présentation des clubs, actualités, galerie photo, calendrier des événements, partenaires et formulaire d'inscription / contact.
- 🔐 **Espace d'administration** (Secrétariat Général) : tableau de bord, gestion des membres, des événements, des actualités, des documents internes et un tableau de tâches Kanban.

---

## 🛠️ Stack technique

| Couche        | Technologie                                   |
| ------------- | --------------------------------------------- |
| Back-end      | PHP 8.2 (architecture MVC maison, sans framework) |
| Base de données | MySQL 8.0 (utf8mb4), accès via PDO préparé   |
| Front-end     | Tailwind CSS 3, JavaScript vanilla            |
| Autoload      | Composer (PSR-4 `App\` → `app/`)              |
| Configuration | vlucas/phpdotenv                              |
| Polices       | Bebas Neue, Montserrat, Poppins               |

---

## ✅ Prérequis

- PHP **8.2** ou supérieur (extensions `pdo_mysql`, `mbstring`, `fileinfo`)
- MySQL **8.0** ou supérieur
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & npm (pour compiler les CSS Tailwind)

---

## 🚀 Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/McDams/atlex-sport.git
cd atlex-sport

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances front et compiler les CSS
npm install
npm run build:css

# 4. Configurer l'environnement
cp .env.example .env
#   → renseigner les identifiants de base de données dans .env

# 5. Créer la base de données puis importer le schéma et les données
mysql -u root -p atlex_sport < database/schema.sql
mysql -u root -p atlex_sport < database/seeds.sql

# 6. Lancer le serveur de développement
php -S localhost:8000 -t public
```

Le site est alors accessible sur **http://localhost:8000**.

---

## 🔑 Accès administrateur

| Champ        | Valeur                  |
| ------------ | ----------------------- |
| URL          | `/admin/login`          |
| Email        | `admin@atlexsport.com`  |
| Mot de passe | `Atlex2024!`            |

> ⚠️ Pensez à modifier ces identifiants en production.

---

## 📁 Arborescence

```
atlex-sport/
├── app/
│   ├── Controllers/        # Contrôleurs publics + Admin/
│   ├── Core/               # Router, Database, Session, Auth, CSRF, etc.
│   ├── Helpers/            # functions.php, constants.php
│   ├── Models/             # BaseModel + modèles métier
│   └── Views/              # Gabarits PHP (layouts, partials, pages)
├── config/                 # app.php, database.php, routes.php
├── database/
│   ├── migrations/         # Migrations SQL
│   ├── schema.sql          # Schéma complet
│   └── seeds.sql           # Données initiales
├── public/                 # Racine web (front controller)
│   ├── assets/             # css/, js/, images/
│   ├── uploads/            # Fichiers téléversés
│   ├── .htaccess
│   └── index.php
├── storage/                # logs/, cache/, uploads/ (documents internes)
├── .env.example
├── composer.json
├── package.json
└── tailwind.config.js
```

---

## 🌐 Principales routes

### Public

| Méthode | URL                       | Description                  |
| ------- | ------------------------- | ---------------------------- |
| GET     | `/`                       | Accueil                      |
| GET     | `/clubs`                  | Liste des disciplines        |
| GET     | `/clubs/{slug}`           | Détail d'une discipline      |
| GET     | `/actualites`             | Liste des actualités         |
| GET     | `/actualites/{slug}`      | Détail d'un article          |
| GET     | `/galerie`                | Galerie photo                |
| GET     | `/calendrier`             | Calendrier des événements    |
| GET     | `/api/events/{year}/{month}` | Événements du mois (JSON) |
| GET     | `/a-propos`               | À propos                     |
| GET     | `/partenaires`            | Sponsors & partenaires       |
| GET / POST | `/contact`             | Formulaire de contact        |
| POST    | `/inscription`            | Formulaire d'inscription     |

### Administration (authentification requise)

| Méthode | URL                       | Description                  |
| ------- | ------------------------- | ---------------------------- |
| GET / POST | `/admin/login`         | Connexion                    |
| POST    | `/admin/logout`           | Déconnexion                  |
| GET     | `/admin`                  | Tableau de bord              |
| GET     | `/admin/membres`          | Gestion des membres          |
| GET     | `/admin/evenements`       | Gestion des événements       |
| GET     | `/admin/actualites`       | Gestion des actualités       |
| GET     | `/admin/documents`        | Documents internes           |
| GET     | `/admin/taches`           | Tableau Kanban des tâches    |

---

## 🗺️ Feuille de route

- **Phase 1 — ✅ MVP** : site vitrine + espace SG (membres, événements, actualités, documents, tâches).
- **Phase 2 — Réseaux sociaux** : intégration des flux Facebook / Instagram et partage automatisé.
- **Phase 3 — Intelligence artificielle** : recommandations de contenu et assistant de rédaction d'articles.
- **Phase 4 — Emails professionnels & sauvegardes** : notifications email transactionnelles et sauvegardes automatiques de la base.

---

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Forkez le projet
2. Créez une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Committez vos changements (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Poussez la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

---

## 📄 Licence

Distribué sous licence **MIT**. Voir le fichier `LICENSE` pour plus d'informations.

---

<p align="center">
  <strong>ATLÉX-SPORT</strong> — Là où l'énergie devient passion. 🇧🇯
</p>
