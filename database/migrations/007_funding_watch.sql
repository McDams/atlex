-- =====================================================================
-- ATLEX - Sport — Migration 007 : Veille de financements + démarches
-- =====================================================================
-- Récupération automatique d'opportunités (appels à projets, subventions…)
-- depuis des sources curées (flux RSS ou requêtes Google Actualités ciblées),
-- présentées dans une liste « Veille » puis promues dans le tracker existant.
-- Chaque opportunité suivie porte une checklist de démarches (réutilisable).

SET NAMES utf8mb4;

-- Sources de veille configurées par le SG
CREATE TABLE IF NOT EXISTS funding_sources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  type ENUM('rss','google_news') NOT NULL DEFAULT 'google_news',
  url VARCHAR(500),               -- pour type rss : URL du flux
  query VARCHAR(300),             -- pour type google_news : requête (ex : site:unesco.org appel à projets sport)
  is_active TINYINT(1) DEFAULT 1,
  last_fetch_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opportunités détectées automatiquement (file de veille)
CREATE TABLE IF NOT EXISTS funding_leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  source_id INT,
  title VARCHAR(400) NOT NULL,
  url VARCHAR(700) NOT NULL,
  url_hash CHAR(40) NOT NULL UNIQUE,   -- sha1(url) : dédoublonnage
  summary TEXT,
  source_name VARCHAR(200),
  published_at DATETIME NULL,
  status ENUM('nouveau','promu','ignore') NOT NULL DEFAULT 'nouveau',
  fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_leads_status (status),
  FOREIGN KEY (source_id) REFERENCES funding_sources(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Démarches à suivre (checklist) par opportunité de financement suivie
CREATE TABLE IF NOT EXISTS funding_checklist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opportunity_id INT NOT NULL,
  label VARCHAR(300) NOT NULL,
  is_done TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (opportunity_id) REFERENCES funding_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sources d'exemple (à éditer/remplacer par le SG) — insérées seulement si vide
INSERT INTO funding_sources (name, type, query)
SELECT * FROM (
  SELECT 'Subventions sport Bénin' AS name, 'google_news' AS type, 'subvention OR financement "association sportive" Bénin' AS query
  UNION ALL SELECT 'Appels à projets sport Afrique', 'google_news', 'appel à projets sport Afrique de l''Ouest jeunesse'
  UNION ALL SELECT 'UNESCO — appels à projets', 'google_news', 'site:unesco.org appel à projets sport jeunesse'
  UNION ALL SELECT 'Solidarité Olympique', 'google_news', 'Solidarité Olympique financement programme'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM funding_sources);
