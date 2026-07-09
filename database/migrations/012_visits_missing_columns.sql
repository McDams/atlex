-- Migration 012: Ajoute les colonnes de `visits` attendues par le code
-- (VisitTracker::track(), Visit::topPages()) mais absentes du schéma réel
-- de production — ce dernier a été créé hors dépôt et jamais synchronisé.
ALTER TABLE `visits`
  ADD COLUMN `language_code` varchar(10) DEFAULT NULL,
  ADD COLUMN `page_title` varchar(255) DEFAULT NULL,
  ADD COLUMN `utm_source` varchar(100) DEFAULT NULL,
  ADD COLUMN `utm_medium` varchar(100) DEFAULT NULL,
  ADD COLUMN `utm_campaign` varchar(150) DEFAULT NULL,
  ADD COLUMN `utm_term` varchar(150) DEFAULT NULL,
  ADD COLUMN `utm_content` varchar(150) DEFAULT NULL,
  ADD COLUMN `is_unique_daily` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `is_entrance` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  ADD KEY `idx_visits_session_key` (`session_key`);
