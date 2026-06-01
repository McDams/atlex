-- =============================================================================
-- Migration : Monitoring Hostinger — Table settings
-- Projet     : ATLEX - Sport
-- Date       : 2025
-- Description: Crée la table `settings` (clé/valeur) pour stocker les
--              configurations persistentes (token API Hostinger, email admin, etc.)
-- =============================================================================

-- Table de paramètres clé/valeur
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT          AUTO_INCREMENT PRIMARY KEY,
  `key`        VARCHAR(100) NOT NULL,
  `value`      TEXT,
  `updated_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Paramètres applicatifs clé/valeur';

-- Initialisation des clés par défaut
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('hostinger_api_token', ''),
  ('admin_email', ''),
  ('hostinger_notifications_enabled', '1');

-- =============================================================================
-- Instructions d'exécution :
--
-- Via MySQL CLI :
--   mysql -u <user> -p <database> < database/migrations/hostinger.sql
--
-- Via PHP (une seule fois, au déploiement) :
--   Inclure ce fichier dans votre script d'initialisation de base de données.
--
-- Crontab pour les rappels email (à configurer sur le serveur) :
--   0 8 * * * php /var/www/atlex-sport/cron/hostinger_reminders.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
-- =============================================================================
