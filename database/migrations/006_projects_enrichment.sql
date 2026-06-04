-- =====================================================================
-- ATLEX - Sport — Migration 006 : enrichissement Projets & Financements
-- =====================================================================
-- Inspiré de Tajiriba.africa (campagnes/redevabilité) et de l'UNESCO
-- (thématiques, partenaires, impact, types de financement institutionnels).
--   • Projets : thématique, bénéficiaires, impact attendu, partenaires
--   • Financements : types élargis (crowdfunding, bourse, prix)
-- La progression de collecte (objectif vs collecté) est calculée à partir
-- de budget_target et des financements « obtenu » (pas de colonne dédiée).

SET NAMES utf8mb4;

ALTER TABLE projects
  ADD COLUMN theme VARCHAR(120) AFTER discipline,
  ADD COLUMN beneficiaries TEXT AFTER lead,
  ADD COLUMN beneficiary_count INT AFTER beneficiaries,
  ADD COLUMN expected_impact TEXT AFTER beneficiary_count;

-- Partenaires d'un projet (liste, style programmes UNESCO)
CREATE TABLE IF NOT EXISTS project_partners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  name VARCHAR(180) NOT NULL,
  role VARCHAR(120),
  sort_order INT DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Types de financement élargis : + crowdfunding (Tajiriba), bourse & prix (UNESCO)
ALTER TABLE funding_opportunities
  MODIFY type ENUM('subvention','appel_projet','sponsoring','crowdfunding','don','bourse','prix','autre')
  DEFAULT 'subvention';
