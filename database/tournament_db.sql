-- Script de création de la base de données pour les tournois esports

CREATE DATABASE IF NOT EXISTS tournament_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tournament_db;

-- Table des tournois
CREATE TABLE tournaments (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(10) NOT NULL,
    description TEXT,
    max_participants INT DEFAULT NULL,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des participants
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    status ENUM('En lice', 'Éliminé') DEFAULT 'En lice',
    inscription_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- Table des poules
CREATE TABLE pools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    pool_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- Table de liaison participants-poules
CREATE TABLE pool_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pool_id INT NOT NULL,
    participant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant_pool (pool_id, participant_id)
);

-- Insertion des tournois de base
INSERT INTO tournaments (id, name, icon, description) VALUES
('fortnite', 'Fortnite Battle Royale', '🏗️', 'Tournoi de Battle Royale'),
('mariokart', 'Mario Kart 8 Deluxe', '🏎️', 'Course de kart arcade'),
('smash', 'Super Smash Bros Ultimate', '⚔️', 'Jeu de combat'),
('fc26', 'FC 26', '⚽', 'Simulation de football');

-- Insertion de participants de test
INSERT INTO participants (tournament_id, name, status, inscription_date) VALUES
('fortnite', 'ProGamer123', 'En lice', '2024-03-01'),
('fortnite', 'NinjaFort', 'En lice', '2024-03-02'),
('fortnite', 'BuildMaster', 'Éliminé', '2024-03-01'),
('fortnite', 'VictoryRoyale', 'En lice', '2024-03-03'),

('mariokart', 'SpeedRacer', 'En lice', '2024-03-02'),
('mariokart', 'KartKing', 'En lice', '2024-03-01'),
('mariokart', 'DriftMaster', 'En lice', '2024-03-03'),
('mariokart', 'ShellShock', 'Éliminé', '2024-03-02'),

('smash', 'ComboKing', 'En lice', '2024-03-01'),
('smash', 'SmashLord', 'En lice', '2024-03-02'),
('smash', 'FinalSmash', 'En lice', '2024-03-03'),
('smash', 'KOFighter', 'En lice', '2024-03-01'),

('fc26', 'GoalMachine', 'En lice', '2024-03-02'),
('fc26', 'SoccerPro', 'En lice', '2024-03-01'),
('fc26', 'FootballKing', 'Éliminé', '2024-03-03');

-- Index pour optimiser les performances
CREATE INDEX idx_participants_tournament ON participants(tournament_id);
CREATE INDEX idx_participants_status ON participants(status);
CREATE INDEX idx_pools_tournament ON pools(tournament_id);
CREATE INDEX idx_pool_participants_pool ON pool_participants(pool_id);
CREATE INDEX idx_pool_participants_participant ON pool_participants(participant_id);
