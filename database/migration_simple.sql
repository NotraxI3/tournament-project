-- =============================================
-- MIGRATION SIMPLIFIÉE - VERSION SÛRE
-- =============================================
-- Cette version est garantie de fonctionner sur toutes les versions de MySQL/MariaDB

USE tournament_db;

-- Étape 1: Ajouter les nouvelles colonnes si elles n'existent pas
ALTER TABLE participants 
ADD COLUMN IF NOT EXISTS prenom VARCHAR(100) AFTER tournament_id;

ALTER TABLE participants 
ADD COLUMN IF NOT EXISTS nom VARCHAR(100) AFTER prenom;

ALTER TABLE participants 
ADD COLUMN IF NOT EXISTS age INT AFTER nom;

ALTER TABLE participants 
ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) AFTER age;

ALTER TABLE participants 
ADD COLUMN IF NOT EXISTS position_attente INT NULL AFTER inscription_date;

-- Étape 2: Remplir les nouvelles colonnes avec des données par défaut si elles sont vides
UPDATE participants 
SET prenom = 'Joueur', nom = CONCAT('N°', id), age = 18, telephone = '0600000000'
WHERE prenom IS NULL OR prenom = '';

-- Étape 3: Modifier le type ENUM du statut pour accepter 'Liste d'attente'
ALTER TABLE participants 
MODIFY COLUMN status ENUM('En lice', 'Éliminé', 'Liste d\'attente') DEFAULT 'En lice';

-- Étape 4: Supprimer l'ancienne colonne 'name' si elle existe
-- On vérifie d'abord si elle existe pour éviter une erreur
SET @col_exists = (SELECT COUNT(*) 
                   FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = 'tournament_db' 
                   AND TABLE_NAME = 'participants' 
                   AND COLUMN_NAME = 'name');

SET @query = IF(@col_exists > 0,
    'ALTER TABLE participants DROP COLUMN name',
    'SELECT "Colonne name déjà supprimée ou inexistante" as message');

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Étape 5: Ajouter max_participants aux tournois si n'existe pas
ALTER TABLE tournaments 
ADD COLUMN IF NOT EXISTS max_participants INT NOT NULL DEFAULT 32 AFTER description;

-- Étape 6: Mettre à jour les valeurs de max_participants
UPDATE tournaments SET max_participants = 32 WHERE id = 'fortnite' AND max_participants IS NULL;
UPDATE tournaments SET max_participants = 24 WHERE id = 'mariokart' AND max_participants IS NULL;
UPDATE tournaments SET max_participants = 16 WHERE id = 'smash' AND max_participants IS NULL;
UPDATE tournaments SET max_participants = 20 WHERE id = 'fc26' AND max_participants IS NULL;

-- Afficher un message de succès
SELECT '✅ Migration terminée avec succès!' as message;
SELECT 'Vous pouvez maintenant recharger votre application' as conseil;
