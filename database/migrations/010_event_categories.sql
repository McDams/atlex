-- Migration 010 : Catégories d'événements
-- Ajoute une table event_categories et lie les événements à une catégorie

CREATE TABLE IF NOT EXISTS event_categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    slug       VARCHAR(80)  NOT NULL UNIQUE,
    name       VARCHAR(120) NOT NULL,
    description TEXT,
    icon       VARCHAR(80)  NOT NULL DEFAULT 'trophy',   -- nom d'icône Heroicons
    color      VARCHAR(20)  NOT NULL DEFAULT '#E53935',  -- couleur du cercle
    sort_order INT          NOT NULL DEFAULT 0,
    is_active  BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajout de la colonne category_id dans events
ALTER TABLE events
    ADD COLUMN category_id INT NULL AFTER discipline,
    ADD CONSTRAINT fk_events_category
        FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE SET NULL;

-- Données initiales : 6 catégories ATLÉX-SPORT
INSERT INTO event_categories (slug, name, description, icon, color, sort_order) VALUES
('basketball',      'Basket-ball',              'Matchs, tournois et entraînements basket',                        'basketball',   '#E53935', 1),
('handball',        'Handball',                 'Compétitions, stages et matchs de handball',                      'handball',     '#003366', 2),
('arts-martiaux',   'Arts Martiaux',            'Judo, karaté, self-défense et démonstrations',                    'arts-martiaux','#D7B899', 3),
('competitions',    'Compétitions',             'Tournois officiels, championnats et rencontres inter-clubs',       'trophy',       '#F59E0B', 4),
('formation',       'Formation & Stage',        'Stages techniques, formations d\'arbitres et ateliers sportifs',  'academique',   '#10B981', 5),
('social',          'Événement Social',         'Galas, cérémonies, assemblées et vie associative',                'social',       '#8B5CF6', 6);
