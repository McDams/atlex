-- Migration 013: Colonnes ville/coordonnées pour la carte géographique
-- des visites (GeoIpService, base locale MaxMind GeoLite2-City).
ALTER TABLE `visits`
  ADD COLUMN `city_name` varchar(100) DEFAULT NULL,
  ADD COLUMN `latitude` decimal(9,6) DEFAULT NULL,
  ADD COLUMN `longitude` decimal(9,6) DEFAULT NULL,
  ADD KEY `idx_visits_city_name` (`city_name`);
