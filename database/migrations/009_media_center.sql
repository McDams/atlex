-- =====================================================================
-- ATLEX - Sport — Migration 009 : Centre média
-- =====================================================================
-- Espace presse public (+ gestion SG) : communiqués de presse, kit presse
-- téléchargeable et revue de presse. Le contact presse est stocké dans
-- la table `settings` (clés press_contact_*).

SET NAMES utf8mb4;

-- Communiqués de presse (entité dédiée, avec PDF joint optionnel)
CREATE TABLE IF NOT EXISTS press_releases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  slug VARCHAR(270) UNIQUE NOT NULL,
  reference VARCHAR(60),               -- référence interne, ex : CP-2026-001
  excerpt TEXT,
  content LONGTEXT,
  file VARCHAR(300),                   -- PDF téléchargeable (chemin sous public/)
  is_published BOOLEAN DEFAULT FALSE,
  published_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_press_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kit presse : ressources téléchargeables (logos, charte, photos, dossier)
CREATE TABLE IF NOT EXISTS press_kit_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description VARCHAR(300),
  category ENUM('logo','charte','photo','dossier','autre') DEFAULT 'autre',
  file VARCHAR(300) NOT NULL,          -- fichier téléchargeable (chemin sous public/)
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Revue de presse : articles externes parlant de l'association
CREATE TABLE IF NOT EXISTS press_coverage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  media_name VARCHAR(150),             -- nom du média / source
  url VARCHAR(500) NOT NULL,
  published_date DATE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
