-- =====================================================================
-- ATLEX - Sport — Migration 005 : Projets & Recherche de financements
-- =====================================================================
-- Gestion interne (Espace SG) des projets de l'association et suivi des
-- opportunités de financement (subventions, appels à projets, sponsoring),
-- chaque financement pouvant être rattaché à un projet.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  discipline ENUM('football','basketball','handball','arts_martiaux','tous') DEFAULT 'tous',
  status ENUM('planifie','en_cours','en_pause','termine','annule') DEFAULT 'planifie',
  lead VARCHAR(150),                    -- responsable du projet
  budget_target DECIMAL(12,2),          -- budget visé (FCFA)
  start_date DATE,
  end_date DATE,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_projects_status (status),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funding_opportunities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT,                       -- rattachement optionnel à un projet
  name VARCHAR(200) NOT NULL,           -- intitulé de l'opportunité
  funder VARCHAR(200),                  -- bailleur / organisme financeur
  type ENUM('subvention','appel_projet','sponsoring','don','autre') DEFAULT 'subvention',
  amount DECIMAL(12,2),                 -- montant visé / accordé (FCFA)
  deadline DATE,                        -- date limite de candidature
  status ENUM('identifie','en_preparation','depose','obtenu','refuse') DEFAULT 'identifie',
  application_url VARCHAR(500),         -- lien vers l'appel / dossier
  notes TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_funding_status (status),
  INDEX idx_funding_project (project_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
