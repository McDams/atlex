-- =====================================================================
-- ATLÉX-SPORT — Données initiales (seeds)
-- Mot de passe admin : Atlex2024!  (bcrypt)
-- =====================================================================

SET NAMES utf8mb4;

-- Utilisateur admin
INSERT INTO users (name, email, password, role) VALUES
('Ulrich — Secrétaire Général', 'admin@atlexsport.com',
 '$2b$10$ndMnXb7.SGmsyk8FmfMzr.QjZc1Qy2uPcjACmiPWuwcv/Yk1h9pCW', 'admin');

-- Sponsors
INSERT INTO sponsors (name, tier, website_url, description, is_active, sort_order) VALUES
('BIC Bénin',  'officiel', 'https://www.bicbenin.com', 'Partenaire bancaire officiel', 1, 1),
('SOBEMAP',    'officiel', NULL, 'Société béninoise des manutentions portuaires', 1, 2),
('MTN Bénin',  'associe',  'https://www.mtn.bj', 'Partenaire télécom', 1, 3),
('Cotonou FC', 'media',    NULL, 'Partenaire média sportif', 1, 4);

-- Événements (juin / juillet 2026)
INSERT INTO events (title, slug, type, discipline, description, start_datetime, end_datetime, location, is_published) VALUES
('Tournoi inter-quartiers de football', 'tournoi-inter-quartiers-football', 'tournoi', 'football',
 'Grand tournoi de football opposant les quartiers de Cotonou.', '2026-06-07 09:00:00', '2026-06-07 18:00:00', 'Stade municipal de Cotonou', 1),
('Match amical de basketball', 'match-amical-basketball', 'match', 'basketball',
 'Rencontre amicale entre les équipes seniors.', '2026-06-14 16:00:00', '2026-06-14 18:00:00', 'Gymnase ATLÉX', 1),
('Stage intensif d''arts martiaux', 'stage-intensif-arts-martiaux', 'stage', 'arts_martiaux',
 'Stage de perfectionnement ouvert à tous les niveaux.', '2026-06-21 08:00:00', '2026-06-22 17:00:00', 'Dojo ATLÉX', 1),
('Tournoi de handball jeunes', 'tournoi-handball-jeunes', 'tournoi', 'handball',
 'Compétition réservée aux catégories jeunes.', '2026-07-05 09:00:00', '2026-07-05 17:00:00', 'Complexe sportif de Cotonou', 1),
('Remise des trophées de la saison', 'remise-trophees-saison', 'remise', 'tous',
 'Cérémonie de clôture et remise des récompenses.', '2026-07-19 18:00:00', '2026-07-19 21:00:00', 'Salle des fêtes ATLÉX', 1);

-- Articles d'actualités (publiés)
INSERT INTO news_articles (title, slug, excerpt, content, category, is_published, published_at, author_id) VALUES
('ATLÉX-SPORT lance sa nouvelle saison 2026', 'atlex-sport-lance-saison-2026',
 'La saison sportive 2026 démarre avec de nombreuses nouveautés pour nos quatre disciplines.',
 'La saison 2026 s''annonce exceptionnelle pour ATLÉX-SPORT.\n\nNos quatre disciplines — football, basketball, handball et arts martiaux — reprennent du service avec des entraînements renforcés et un calendrier riche en compétitions.\n\nRejoignez-nous pour vivre cette aventure sportive intense, là où l''énergie devient passion.',
 'general', 1, '2026-05-10 10:00:00', 1),
('Nos basketteurs remportent le tournoi régional', 'basketteurs-remportent-tournoi-regional',
 'L''équipe senior de basketball s''impose en finale du tournoi régional de Cotonou.',
 'Quelle performance !\n\nNos basketteurs ont brillé lors du tournoi régional, décrochant une victoire éclatante en finale.\n\nUn grand bravo à toute l''équipe et au staff technique pour ce résultat qui récompense des mois de travail acharné.',
 'resultat', 1, '2026-05-18 14:30:00', 1),
('Campagne de recrutement : rejoignez la famille ATLÉX', 'campagne-recrutement-rejoignez-atlex',
 'ATLÉX-SPORT ouvre ses inscriptions pour la saison 2026. Toutes les disciplines recrutent !',
 'Vous rêvez de pratiquer un sport dans une ambiance conviviale et exigeante ?\n\nATLÉX-SPORT recrute dans ses quatre disciplines. Que vous soyez débutant ou confirmé, une place vous attend.\n\nRendez-vous sur notre page contact pour vous inscrire dès aujourd''hui.',
 'recrutement', 1, '2026-05-25 09:00:00', 1);

-- Membres d'exemple
INSERT INTO members (first_name, last_name, email, phone, age, gender, discipline, status, joined_at) VALUES
('Koffi', 'Adjovi', 'koffi.adjovi@example.bj', '+22997000001', 22, 'M', 'football', 'actif', '2024-09-01'),
('Aïcha', 'Sossou', 'aicha.sossou@example.bj', '+22997000002', 19, 'F', 'basketball', 'actif', '2024-10-15'),
('Mawuli', 'Hounkpatin', NULL, '+22997000003', 25, 'M', 'handball', 'actif', '2025-01-20'),
('Fatima', 'Bello', 'fatima.bello@example.bj', '+22997000004', 17, 'F', 'arts_martiaux', 'actif', '2025-03-05');

-- Galerie (placeholder à partir des assets existants)
INSERT INTO gallery_photos (title, filename, category, alt_text, is_published, sort_order) VALUES
('Match de football', 'hero-bg.png', 'football', 'Action de football ATLÉX', 1, 1),
('Entraînement basketball', 'basket-hero.png', 'basketball', 'Séance de basketball', 1, 2),
('Match de handball', 'handball-hero.png', 'handball', 'Rencontre de handball', 1, 3),
('Démonstration arts martiaux', 'martial-arts-hero.png', 'arts_martiaux', 'Démonstration d''arts martiaux', 1, 4),
('Tournoi annuel', 'tournoi-hero.png', 'evenements', 'Tournoi ATLÉX-SPORT', 1, 5),
('Équipe ATLÉX', 'team-photo.png', 'general', 'Photo d''équipe ATLÉX-SPORT', 1, 6);

-- Tâches d'exemple (espace SG)
INSERT INTO tasks (title, description, status, priority, due_date, created_by) VALUES
('Préparer le tournoi de juin', 'Logistique, terrains, arbitres', 'en_cours', 'haute', '2026-06-01', 1),
('Mettre à jour la liste des membres', 'Vérifier les cotisations', 'a_faire', 'normale', '2026-06-10', 1),
('Publier le bilan de la saison', 'Rédiger et publier l''article bilan', 'a_faire', 'basse', NULL, 1);
