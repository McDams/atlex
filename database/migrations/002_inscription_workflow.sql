-- =====================================================================
-- ATLEX - Sport — Migration : workflow de validation des inscriptions
--
-- Ajoute un statut et une date de traitement aux soumissions, afin que
-- l'admin puisse valider/refuser une demande d'inscription depuis le panel.
-- =====================================================================

ALTER TABLE contact_submissions
  ADD COLUMN status ENUM('nouveau','valide','refuse') NOT NULL DEFAULT 'nouveau' AFTER message,
  ADD COLUMN processed_at TIMESTAMP NULL DEFAULT NULL AFTER status;
