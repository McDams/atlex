-- =====================================================================
-- ATLEX - Sport — Migration 014 : Assistant IA réseaux sociaux
-- =====================================================================
-- Génère des brouillons de posts (actualités/événements/athlètes du site,
-- résumés de grandes compétitions) qu'un membre du bureau relit, modifie
-- et publie manuellement — aucune publication n'est jamais automatique.

SET NAMES utf8mb4;

-- Comptes réseaux sociaux connectés (un par plateforme)
CREATE TABLE IF NOT EXISTS social_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform ENUM('facebook','instagram','linkedin') NOT NULL,
  label VARCHAR(150) NOT NULL,
  access_token TEXT NOT NULL,
  account_ref VARCHAR(150) NOT NULL,   -- Page ID / IG Business ID / Organization URN
  token_expires_at DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_social_accounts_platform (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File de brouillons de posts (générés par l'IA ou créés manuellement)
CREATE TABLE IF NOT EXISTS social_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform ENUM('facebook','instagram','linkedin') NOT NULL,
  status ENUM('brouillon','approuve','publie','echec','ignore') NOT NULL DEFAULT 'brouillon',
  content_text TEXT NOT NULL,
  -- source_id référence news_articles.id / events.id selon source_type — pas de FK
  -- (table polymorphe), résolu applicativement.
  source_type ENUM('news','event','athlete','match_resume','manuel') NOT NULL DEFAULT 'manuel',
  source_id INT NULL,
  media_path VARCHAR(500) NULL,
  scheduled_at DATETIME NULL,
  published_at DATETIME NULL,
  external_post_id VARCHAR(150) NULL,
  error_message VARCHAR(500) NULL,
  created_by ENUM('ia','humain') NOT NULL DEFAULT 'ia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_social_posts_status (status),
  INDEX idx_social_posts_platform (platform),
  INDEX idx_social_posts_source (source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compétitions sportives suivies pour les résumés de matchs
CREATE TABLE IF NOT EXISTS sports_competitions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  external_competition_id VARCHAR(50) NOT NULL,  -- identifiant API-Football
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_checked_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_sports_competitions_external_id (external_competition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compétitions de départ — désactivées par défaut. Les identifiants
-- external_competition_id ci-dessous sont ceux généralement documentés par
-- API-Football au moment de l'écriture de cette migration, MAIS à vérifier
-- et corriger si besoin depuis /admin/social/competitions (liste des
-- ligues renvoyée par l'API) avant d'activer une compétition — un mauvais
-- identifiant ferait simplement échouer silencieusement la récupération
-- (aucun match trouvé), sans casser le reste de l'app.
INSERT INTO sports_competitions (name, external_competition_id, is_active)
SELECT * FROM (
  SELECT 'Coupe du Monde' AS name, '1' AS external_competition_id, 0 AS is_active
  UNION ALL SELECT 'Coupe d''Afrique des Nations (CAN)', '6', 0
  UNION ALL SELECT 'Ligue des Champions', '2', 0
  UNION ALL SELECT 'Euro', '4', 0
  UNION ALL SELECT 'Liga', '140', 0
  UNION ALL SELECT 'Premier League', '39', 0
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM sports_competitions);
