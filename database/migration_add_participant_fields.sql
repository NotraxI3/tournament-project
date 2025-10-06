-- =============================================
-- MIGRATION : Ajout des champs prenom, nom, age, telephone
-- =============================================
-- Ce script met à jour la table participants existante
-- pour ajouter les nouveaux champs sans perdre les données

USE tournament_db;

-- Vérifier si la colonne 'name' existe encore
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants' 
AND COLUMN_NAME = 'name';

-- Si la colonne 'name' existe, on fait la migration
SET @query = IF(@col_exists > 0,
    'ALTER TABLE participants 
     ADD COLUMN prenom VARCHAR(100) AFTER tournament_id,
     ADD COLUMN nom VARCHAR(100) AFTER prenom,
     ADD COLUMN age INT DEFAULT 18 AFTER nom,
     ADD COLUMN telephone VARCHAR(20) DEFAULT "0600000000" AFTER age,
     ADD COLUMN position_attente INT NULL AFTER inscription_date',
    'SELECT "Migration déjà effectuée ou colonnes déjà présentes" as message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrer les données de 'name' vers 'prenom' et 'nom' si nécessaire
SET @migrate_data = IF(@col_exists > 0,
    'UPDATE participants 
     SET prenom = SUBSTRING_INDEX(name, " ", 1),
         nom = CASE 
             WHEN LOCATE(" ", name) > 0 THEN SUBSTRING(name, LOCATE(" ", name) + 1)
             ELSE name
         END
     WHERE name IS NOT NULL',
    'SELECT "Pas de migration de données nécessaire" as message'
);

PREPARE stmt FROM @migrate_data;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Supprimer l'ancienne colonne 'name' si elle existe
SET @drop_col = IF(@col_exists > 0,
    'ALTER TABLE participants DROP COLUMN name',
    'SELECT "Colonne name déjà supprimée" as message'
);

PREPARE stmt FROM @drop_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modifier le statut pour accepter 'Liste d'attente'
ALTER TABLE participants 
MODIFY COLUMN status ENUM('En lice', 'Éliminé', 'Liste d\'attente') DEFAULT 'En lice';

-- Ajouter max_participants aux tournois s'il n'existe pas
SET @add_max = 0;
SELECT COUNT(*) INTO @add_max 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'tournaments' 
AND COLUMN_NAME = 'max_participants';

SET @add_max_query = IF(@add_max = 0,
    'ALTER TABLE tournaments 
     ADD COLUMN max_participants INT NOT NULL DEFAULT 32 AFTER description',
    'SELECT "Colonne max_participants déjà présente" as message'
);

PREPARE stmt FROM @add_max_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mettre à jour les valeurs max_participants pour les tournois existants
UPDATE tournaments SET max_participants = 32 WHERE id = 'fortnite';
UPDATE tournaments SET max_participants = 24 WHERE id = 'mariokart';
UPDATE tournaments SET max_participants = 16 WHERE id = 'smash';
UPDATE tournaments SET max_participants = 20 WHERE id = 'fc26';

SELECT 'Migration terminée avec succès!' as message;
