-- =====================================================================

-- ATLEX - Sport — Schéma de base de données complet
-- MySQL 8.0 / utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Utilisateurs admin
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,   -- bcrypt
  role ENUM('admin','editor') DEFAULT 'editor',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membres de l'association
CREATE TABLE IF NOT EXISTS members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  email VARCHAR(150),
  phone VARCHAR(30),
  age TINYINT,
  gender ENUM('M','F','Autre'),
  discipline ENUM('football','basketball','handball','arts_martiaux'),
  status ENUM('actif','inactif','suspendu') DEFAULT 'actif',
  joined_at DATE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Événements / Calendrier
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) UNIQUE,
  type ENUM('match','tournoi','stage','entrainement','remise','autre') DEFAULT 'autre',
  discipline ENUM('football','basketball','handball','arts_martiaux','tous') DEFAULT 'tous',
  description TEXT,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME,
  location VARCHAR(200),
  is_published BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles actualités
CREATE TABLE IF NOT EXISTS news_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  slug VARCHAR(270) UNIQUE NOT NULL,
  excerpt TEXT,
  content LONGTEXT,
  category ENUM('resultat','recrutement','evenement','partenariat','general','rapport') DEFAULT 'general',
  cover_image VARCHAR(300),
  is_published BOOLEAN DEFAULT FALSE,
  published_at DATETIME,
  author_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galerie photos
CREATE TABLE IF NOT EXISTS gallery_photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200),
  filename VARCHAR(300) NOT NULL,
  category ENUM('football','basketball','handball','arts_martiaux','evenements','general') DEFAULT 'general',
  alt_text VARCHAR(200),
  is_published BOOLEAN DEFAULT TRUE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sponsors
CREATE TABLE IF NOT EXISTS sponsors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  tier ENUM('officiel','associe','media') DEFAULT 'associe',
  logo VARCHAR(300),
  website_url VARCHAR(300),
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents (stockage interne SG)
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  filename VARCHAR(300) NOT NULL,
  file_type VARCHAR(50),
  file_size INT,
  category ENUM('administratif','sportif','financier','communication','autre') DEFAULT 'autre',
  uploaded_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tâches internes (espace SG)
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  description TEXT,
  status ENUM('a_faire','en_cours','termine') DEFAULT 'a_faire',
  priority ENUM('basse','normale','haute','urgente') DEFAULT 'normale',
  due_date DATE,
  assigned_to INT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts / inscriptions formulaire
CREATE TABLE IF NOT EXISTS contact_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('inscription','contact','partenariat') DEFAULT 'contact',
  first_name VARCHAR(80),
  last_name VARCHAR(80),
  email VARCHAR(150),
  phone VARCHAR(30),
  age TINYINT,
  gender ENUM('M','F','Autre'),
  discipline VARCHAR(50),
  message TEXT,
  status ENUM('nouveau','valide','refuse') NOT NULL DEFAULT 'nouveau',
  processed_at TIMESTAMP NULL DEFAULT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Athlètes (profils numériques publics — cf. migration 003)
CREATE TABLE IF NOT EXISTS athletes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) UNIQUE NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  discipline ENUM('football','basketball','handball','arts_martiaux') NOT NULL,
  category VARCHAR(80),
  ranking VARCHAR(150),
  photo VARCHAR(300),
  bio TEXT,
  is_published BOOLEAN DEFAULT TRUE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_athletes_discipline (discipline),
  INDEX idx_athletes_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS athlete_achievements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  year VARCHAR(9),
  title VARCHAR(250) NOT NULL,
  position VARCHAR(100),
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS athlete_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  result_date DATE,
  competition VARCHAR(250) NOT NULL,
  result VARCHAR(150),
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS athlete_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  athlete_id INT NOT NULL,
  title VARCHAR(200),
  url VARCHAR(500) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
