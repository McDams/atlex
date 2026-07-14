-- =====================================================================
-- ATLEX - Sport — Migration 016 : Dons en ligne (MTN MoMo + PayPal)
-- =====================================================================
-- Une seule table pour les deux rails de paiement. reference est notre
-- identifiant interne (UUID), external_reference est l'identifiant côté
-- fournisseur (X-Reference-Id MTN ou order ID PayPal).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference CHAR(36) NOT NULL,
  method ENUM('momo','paypal') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'XOF',
  donor_name VARCHAR(150) NOT NULL,
  donor_email VARCHAR(190) NOT NULL,
  donor_phone VARCHAR(30) NULL,
  status ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  external_reference VARCHAR(190) NULL,
  provider_payload TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_donations_reference (reference),
  INDEX idx_donations_status (status),
  INDEX idx_donations_method (method),
  INDEX idx_donations_external_reference (external_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
