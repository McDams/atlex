-- =====================================================================
-- ATLEX - Sport — Migration 008 : Tableau de bord d'impact
-- =====================================================================
-- Indicateurs d'impact saisis manuellement par le SG, en complément des
-- indicateurs calculés automatiquement (bénéficiaires, athlètes, projets,
-- financements, partenaires, événements…).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS impact_indicators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(150) NOT NULL,        -- ex : « Communes touchées »
  value VARCHAR(60) NOT NULL,         -- ex : « 8 », « 250 », « 1 200 »
  unit VARCHAR(40),                   -- ex : « communes », « jeunes »
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
