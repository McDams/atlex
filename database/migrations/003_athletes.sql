-- =====================================================================
-- ATLEX - Sport — Migration 003 : Profils numériques des athlètes
-- =====================================================================
-- Athlètes (cartes de présentation publiques, gérées par le SG).
-- Distincts des membres (gestion administrative interne).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS athletes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) UNIQUE NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  discipline ENUM('football','basketball','handball','arts_martiaux') NOT NULL,
  category VARCHAR(80),                 -- ex : Senior, U17, Cadet, Ceinture noire
  ranking VARCHAR(150),                 -- classement actuel, ex : « 3e championnat national 2025 »
  photo VARCHAR(300),                   -- chemin relatif sous public/ (ex: uploads/xxx.jpg)
  bio TEXT,
  is_published BOOLEAN DEFAULT TRUE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_athletes_discipline (discipline),
  INDEX idx_athletes_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Palmarès (titres / distinctions)
CREATE TABLE IF NOT EXISTS athlete_achievements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  year VARCHAR(9),                      -- ex : 2025 ou 2024-2025
  title VARCHAR(250) NOT NULL,
  position VARCHAR(100),                -- ex : 1er, Médaille d'or, Finaliste
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Résultats (compétitions / rencontres)
CREATE TABLE IF NOT EXISTS athlete_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  result_date DATE,
  competition VARCHAR(250) NOT NULL,
  result VARCHAR(150),                  -- ex : Victoire 3-1, 12.4s, 2e place
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vidéos (liens YouTube / réseaux sociaux)
CREATE TABLE IF NOT EXISTS athlete_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  title VARCHAR(200),
  url VARCHAR(500) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
