-- =============================================
-- NOUVELLE BASE DE DONNÉES COMPLÈTE - TOURNOIS
-- =============================================

-- Supprimer les anciennes tables si elles existent
DROP TABLE IF EXISTS pool_participants;
DROP TABLE IF EXISTS pools;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS users;

-- =============================================
-- TABLE DES UTILISATEURS (Connexion)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer un compte admin par défaut
-- Mot de passe: admin123 (hashé)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =============================================
-- TABLE DES TOURNOIS
-- =============================================
CREATE TABLE tournaments (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(10) NOT NULL,
    description TEXT,
    max_participants INT NOT NULL DEFAULT 32,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES PARTICIPANTS
-- =============================================
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id VARCHAR(50) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    status ENUM('En lice', 'Éliminé', 'Liste d\'attente') DEFAULT 'En lice',
    inscription_date DATE NOT NULL,
    position_attente INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    INDEX idx_tournament (tournament_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES POULES
-- =============================================
CREATE TABLE pools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    pool_number INT NOT NULL,
    max_players INT NOT NULL DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    INDEX idx_tournament (tournament_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DE LIAISON PARTICIPANTS-POULES
-- =============================================
CREATE TABLE pool_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pool_id INT NOT NULL,
    participant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant_pool (pool_id, participant_id),
    INDEX idx_pool (pool_id),
    INDEX idx_participant (participant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERTION DES TOURNOIS DE BASE
-- =============================================
INSERT INTO tournaments (id, name, icon, description, max_participants) VALUES
('fortnite', 'Fortnite Battle Royale', '🏗️', 'Tournoi de Battle Royale', 32),
('mariokart', 'Mario Kart 8 Deluxe', '🏎️', 'Course de kart arcade', 24),
('smash', 'Super Smash Bros Ultimate', '⚔️', 'Jeu de combat', 16),
('fc26', 'FC 26', '⚽', 'Simulation de football', 20);

-- =============================================
-- INSERTION DE PARTICIPANTS DE TEST
-- =============================================
INSERT INTO participants (tournament_id, prenom, nom, age, telephone, status, inscription_date) VALUES
('fortnite', 'Jean', 'Dupont', 18, '0601020304', 'En lice', '2024-03-01'),
('fortnite', 'Marie', 'Martin', 20, '0605060708', 'En lice', '2024-03-02'),
('fortnite', 'Pierre', 'Durand', 19, '0609101112', 'En lice', '2024-03-03'),
('fortnite', 'Sophie', 'Lefebvre', 21, '0613141516', 'En lice', '2024-03-04'),

('mariokart', 'Lucas', 'Bernard', 17, '0617181920', 'En lice', '2024-03-02'),
('mariokart', 'Emma', 'Petit', 19, '0621222324', 'En lice', '2024-03-03'),

('smash', 'Thomas', 'Robert', 22, '0625262728', 'En lice', '2024-03-01'),
('smash', 'Julie', 'Richard', 18, '0629303132', 'En lice', '2024-03-02'),

('fc26', 'Alexandre', 'Dubois', 20, '0633343536', 'En lice', '2024-03-01'),
('fc26', 'Camille', 'Moreau', 19, '0637383940', 'En lice', '2024-03-02');

-- =============================================
-- PROCÉDURE : Mettre à jour les positions en liste d'attente
-- =============================================
DELIMITER $$

CREATE PROCEDURE update_waiting_list_positions(IN p_tournament_id VARCHAR(50))
BEGIN
    DECLARE position INT DEFAULT 1;
    DECLARE done INT DEFAULT FALSE;
    DECLARE participant INT;
    
    DECLARE cur CURSOR FOR 
        SELECT id FROM participants 
        WHERE tournament_id = p_tournament_id 
        AND status = 'Liste d\'attente' 
        ORDER BY inscription_date ASC;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO participant;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        UPDATE participants 
        SET position_attente = position 
        WHERE id = participant;
        
        SET position = position + 1;
    END LOOP;
    
    CLOSE cur;
END$$

DELIMITER ;

-- =============================================
-- TRIGGER : Gérer la liste d'attente automatiquement
-- =============================================
DELIMITER $$

CREATE TRIGGER before_participant_insert
BEFORE INSERT ON participants
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    DECLARE max_count INT;
    
    -- Récupérer le nombre actuel de participants en lice
    SELECT COUNT(*) INTO current_count
    FROM participants
    WHERE tournament_id = NEW.tournament_id
    AND status = 'En lice';
    
    -- Récupérer le maximum autorisé
    SELECT max_participants INTO max_count
    FROM tournaments
    WHERE id = NEW.tournament_id;
    
    -- Si le tournoi est complet, mettre en liste d'attente
    IF current_count >= max_count THEN
        SET NEW.status = 'Liste d\'attente';
    END IF;
END$$

DELIMITER ;

-- =============================================
-- INDEX POUR OPTIMISATION
-- =============================================
CREATE INDEX idx_participants_tournament_status ON participants(tournament_id, status);
CREATE INDEX idx_participants_attente ON participants(tournament_id, position_attente);

-- =============================================
-- FIN DU SCRIPT
-- =============================================
