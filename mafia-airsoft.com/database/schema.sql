-- Création de la base de données
CREATE DATABASE IF NOT EXISTS zelu6269_airsoft_association CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE zelu6269_airsoft_association;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    phone VARCHAR(20),
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_pseudo (pseudo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des événements/parties
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    scenario TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    max_players_blue INT DEFAULT 0,
    max_players_red INT DEFAULT 0,
    max_players_neutral INT DEFAULT 0,
    max_players_orga INT DEFAULT 0,
    equipment_required TEXT,
    rules TEXT,
    is_active TINYINT(1) DEFAULT 1,
    registration_open TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des inscriptions
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    team ENUM('BLUE', 'RED', 'NEUTRAL', 'ORGA') NOT NULL,
    notes TEXT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id),
    INDEX idx_event_team (event_id, team),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des articles de blog
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    is_published TINYINT(1) DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'un compte admin par défaut (mot de passe: admin123)
INSERT INTO users (pseudo, email, password_hash, is_admin) 
VALUES ('admin', 'admin@airsoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insertion d'exemples de parties
INSERT INTO events (title, description, scenario, event_date, location, max_players_blue, max_players_red, max_players_neutral, max_players_orga)
VALUES 
('Opération Aiguille Noire', 
 'Mission de reconnaissance et d\'infiltration en milieu urbain',
 'Les forces BLEUES doivent infiltrer la base ROUGE et récupérer des documents classifiés. Les ROUGES doivent défendre leur QG et protéger les objectifs.',
 '2025-12-15 09:00:00',
 'Terrain de jeu - Forêt de Fontainebleau',
 15, 15, 2, 3),
 
('Défense du Fort', 
 'Scénario de défense tactique avec objectifs multiples',
 'L\'équipe ROUGE défend une position fortifiée avec des ressources limitées. L\'équipe BLEUE doit capturer plusieurs points stratégiques avant la fin du temps imparti.',
 '2025-12-22 10:00:00',
 'Fort abandonné - Seine-et-Marne',
 20, 12, 0, 4);
