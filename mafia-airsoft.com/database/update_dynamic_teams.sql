-- Mise à jour pour système d'équipes dynamiques
USE zelu6269_airsoft_association;

-- Créer la table pour les équipes personnalisées par événement
CREATE TABLE IF NOT EXISTS event_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    team_key VARCHAR(50) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_color VARCHAR(7) NOT NULL,
    max_players INT DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_per_event (event_id, team_key),
    INDEX idx_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modifier la table registrations pour utiliser une clé d'équipe flexible
ALTER TABLE registrations 
    MODIFY COLUMN team VARCHAR(50) NOT NULL;

-- Supprimer les anciennes colonnes max_players_* de la table events
ALTER TABLE events 
    DROP COLUMN IF EXISTS max_players_blue,
    DROP COLUMN IF EXISTS max_players_red,
    DROP COLUMN IF EXISTS max_players_neutral,
    DROP COLUMN IF EXISTS max_players_orga;

-- Migrer les données existantes vers le nouveau système
-- Pour chaque événement existant, créer les équipes par défaut
INSERT INTO event_teams (event_id, team_key, team_name, team_color, max_players, display_order)
SELECT id, 'BLUE', 'Équipe Bleue', '#3b82f6', 15, 1 FROM events
WHERE NOT EXISTS (SELECT 1 FROM event_teams WHERE event_id = events.id AND team_key = 'BLUE');

INSERT INTO event_teams (event_id, team_key, team_name, team_color, max_players, display_order)
SELECT id, 'RED', 'Équipe Rouge', '#dc2626', 15, 2 FROM events
WHERE NOT EXISTS (SELECT 1 FROM event_teams WHERE event_id = events.id AND team_key = 'RED');

INSERT INTO event_teams (event_id, team_key, team_name, team_color, max_players, display_order)
SELECT id, 'ORGA', 'Organisation', '#a3a3a3', 3, 3 FROM events
WHERE NOT EXISTS (SELECT 1 FROM event_teams WHERE event_id = events.id AND team_key = 'ORGA');
