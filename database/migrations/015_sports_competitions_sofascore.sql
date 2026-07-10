-- Migration 015: Bascule des identifiants de compétition vers Sofascore
-- =====================================================================
-- La veille de résultats de matchs utilise désormais l'API Sofascore (via
-- RapidAPI) plutôt qu'API-Football. external_competition_id doit contenir
-- l'ID "uniqueTournament" Sofascore de la compétition, pas l'ID API-Football
-- précédent (les deux espaces d'identifiants sont incompatibles).
--
-- Les valeurs ci-dessous sont des espaces réservés explicites — il n'existe
-- pas d'identifiant Sofascore fiable à insérer sans vérification. Éditez-les
-- avec l'ID réel (visible via un appel de test sur RapidAPI) avant d'activer
-- une compétition dans /admin/social/comptes.
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_CM'  WHERE name = 'Coupe du Monde';
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_CAN' WHERE name LIKE 'Coupe d''Afrique%';
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_LDC' WHERE name = 'Ligue des Champions';
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_EURO' WHERE name = 'Euro';
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_LIGA' WHERE name = 'Liga';
UPDATE sports_competitions SET external_competition_id = 'A_COMPLETER_PL'  WHERE name = 'Premier League';
