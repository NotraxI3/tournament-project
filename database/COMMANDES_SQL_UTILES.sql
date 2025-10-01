-- =============================================
-- COMMANDES SQL UTILES - TOURNAMENT DB
-- =============================================

-- Utilisez ces commandes dans phpMyAdmin (onglet SQL)
-- ou dans un client MySQL


-- =============================================
-- CONSULTATION DES DONNÉES
-- =============================================

-- Voir tous les tournois
SELECT * FROM tournaments;

-- Voir tous les participants
SELECT * FROM participants;

-- Voir les participants d'un tournoi spécifique
SELECT * FROM participants WHERE tournament_id = 'fortnite';

-- Voir les joueurs en lice
SELECT * FROM participants WHERE status = 'En lice';

-- Voir les joueurs éliminés
SELECT * FROM participants WHERE status = 'Éliminé';

-- Compter les participants par tournoi
SELECT 
    t.name as Tournoi,
    COUNT(p.id) as NbParticipants
FROM tournaments t
LEFT JOIN participants p ON t.id = p.tournament_id
GROUP BY t.id, t.name;

-- Voir les statistiques détaillées par tournoi
SELECT 
    t.name as Tournoi,
    COUNT(p.id) as Total,
    SUM(CASE WHEN p.status = 'En lice' THEN 1 ELSE 0 END) as EnLice,
    SUM(CASE WHEN p.status = 'Éliminé' THEN 1 ELSE 0 END) as Elimines
FROM tournaments t
LEFT JOIN participants p ON t.id = p.tournament_id
GROUP BY t.id, t.name;

-- Voir toutes les poules
SELECT * FROM pools;

-- Voir les poules avec leurs participants
SELECT 
    po.name as Poule,
    t.name as Tournoi,
    pa.name as Participant
FROM pools po
JOIN tournaments t ON po.tournament_id = t.id
LEFT JOIN pool_participants pp ON po.id = pp.pool_id
LEFT JOIN participants pa ON pp.participant_id = pa.id
ORDER BY po.pool_number, pa.name;


-- =============================================
-- AJOUT DE DONNÉES
-- =============================================

-- Ajouter un tournoi
INSERT INTO tournaments (id, name, icon, description) 
VALUES ('lol', 'League of Legends', '⚡', 'MOBA stratégique');

-- Ajouter un participant
INSERT INTO participants (tournament_id, name, status, inscription_date) 
VALUES ('fortnite', 'NouveauJoueur', 'En lice', CURDATE());

-- Ajouter plusieurs participants d'un coup
INSERT INTO participants (tournament_id, name, status, inscription_date) 
VALUES 
    ('fortnite', 'Joueur1', 'En lice', CURDATE()),
    ('fortnite', 'Joueur2', 'En lice', CURDATE()),
    ('fortnite', 'Joueur3', 'En lice', CURDATE());


-- =============================================
-- MODIFICATION DE DONNÉES
-- =============================================

-- Changer le statut d'un participant
UPDATE participants 
SET status = 'Éliminé' 
WHERE id = 1;

-- Changer plusieurs participants à "En lice"
UPDATE participants 
SET status = 'En lice' 
WHERE tournament_id = 'fortnite';

-- Mettre à jour le nom d'un tournoi
UPDATE tournaments 
SET name = 'Fortnite Championship' 
WHERE id = 'fortnite';

-- Mettre à jour l'icône d'un tournoi
UPDATE tournaments 
SET icon = '🎯' 
WHERE id = 'fortnite';


-- =============================================
-- SUPPRESSION DE DONNÉES
-- =============================================

-- Supprimer un participant spécifique
DELETE FROM participants WHERE id = 1;

-- Supprimer tous les participants d'un tournoi
DELETE FROM participants WHERE tournament_id = 'fortnite';

-- Supprimer tous les participants éliminés
DELETE FROM participants WHERE status = 'Éliminé';

-- Supprimer toutes les poules d'un tournoi
DELETE FROM pools WHERE tournament_id = 'fortnite';

-- Supprimer un tournoi (et tous ses participants grâce au CASCADE)
DELETE FROM tournaments WHERE id = 'fortnite';


-- =============================================
-- RÉINITIALISATION
-- =============================================

-- Vider toutes les poules
TRUNCATE TABLE pool_participants;
TRUNCATE TABLE pools;

-- Vider tous les participants (garde les tournois)
TRUNCATE TABLE participants;

-- Réinitialiser complètement la base (ATTENTION : tout est supprimé !)
DROP DATABASE tournament_db;
-- Puis ré-exécuter le script tournament_db.sql


-- =============================================
-- RECHERCHES AVANCÉES
-- =============================================

-- Trouver les tournois avec le plus de participants
SELECT 
    t.name,
    COUNT(p.id) as participants
FROM tournaments t
LEFT JOIN participants p ON t.id = p.tournament_id
GROUP BY t.id, t.name
ORDER BY participants DESC;

-- Trouver les participants inscrits récemment (7 derniers jours)
SELECT * FROM participants 
WHERE inscription_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY inscription_date DESC;

-- Voir les tournois sans participants
SELECT t.* FROM tournaments t
LEFT JOIN participants p ON t.id = p.tournament_id
WHERE p.id IS NULL;

-- Compter le nombre total de poules
SELECT 
    tournament_id,
    COUNT(*) as nb_poules
FROM pools
GROUP BY tournament_id;


-- =============================================
-- STATISTIQUES GLOBALES
-- =============================================

-- Statistiques générales
SELECT 
    (SELECT COUNT(*) FROM tournaments) as TotalTournois,
    (SELECT COUNT(*) FROM participants) as TotalParticipants,
    (SELECT COUNT(*) FROM participants WHERE status = 'En lice') as EnLice,
    (SELECT COUNT(*) FROM participants WHERE status = 'Éliminé') as Elimines,
    (SELECT COUNT(*) FROM pools) as TotalPoules;

-- Moyenne de participants par tournoi
SELECT 
    AVG(participant_count) as MoyenneParticipants
FROM (
    SELECT COUNT(p.id) as participant_count
    FROM tournaments t
    LEFT JOIN participants p ON t.id = p.tournament_id
    GROUP BY t.id
) as counts;


-- =============================================
-- MAINTENANCE
-- =============================================

-- Vérifier l'intégrité des données
SELECT 
    'Participants orphelins' as Type,
    COUNT(*) as Nombre
FROM participants p
LEFT JOIN tournaments t ON p.tournament_id = t.id
WHERE t.id IS NULL

UNION ALL

SELECT 
    'Poules orphelines' as Type,
    COUNT(*) as Nombre
FROM pools po
LEFT JOIN tournaments t ON po.tournament_id = t.id
WHERE t.id IS NULL;

-- Optimiser les tables
OPTIMIZE TABLE tournaments;
OPTIMIZE TABLE participants;
OPTIMIZE TABLE pools;
OPTIMIZE TABLE pool_participants;

-- Réparer les tables si nécessaire
REPAIR TABLE participants;


-- =============================================
-- SAUVEGARDE / RESTAURATION
-- =============================================

-- Exporter uniquement la structure (à exécuter dans le terminal)
-- mysqldump -u root -p --no-data tournament_db > structure.sql

-- Exporter structure + données
-- mysqldump -u root -p tournament_db > backup.sql

-- Restaurer une sauvegarde
-- mysql -u root -p tournament_db < backup.sql


-- =============================================
-- REQUÊTES POUR L'APPLICATION
-- =============================================

-- Ces requêtes sont déjà utilisées par l'application PHP
-- mais vous pouvez les exécuter manuellement pour tester

-- Récupérer les participants avec infos tournoi
SELECT 
    p.*,
    t.name as tournament_name,
    t.icon as tournament_icon
FROM participants p
JOIN tournaments t ON p.tournament_id = t.id
ORDER BY p.inscription_date DESC;

-- Générer les statistiques pour l'API
SELECT 
    t.id,
    t.name,
    COUNT(p.id) as total,
    SUM(CASE WHEN p.status = 'En lice' THEN 1 ELSE 0 END) as en_lice,
    SUM(CASE WHEN p.status = 'Éliminé' THEN 1 ELSE 0 END) as elimines
FROM tournaments t
LEFT JOIN participants p ON t.id = p.tournament_id
WHERE t.status = 'active'
GROUP BY t.id, t.name;


-- =============================================
-- NOTES IMPORTANTES
-- =============================================

/*
1. Toujours faire une sauvegarde avant de supprimer des données
2. Les suppressions en CASCADE sont activées (supprimer un tournoi supprime ses participants)
3. Utiliser CURDATE() pour la date du jour
4. Les accents sont supportés grâce à utf8mb4
5. phpMyAdmin : http://localhost/phpmyadmin
*/


-- =============================================
-- FIN DU FICHIER
-- =============================================
