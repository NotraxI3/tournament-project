-- =============================================
-- SCRIPT DE VÉRIFICATION POST-MIGRATION
-- =============================================
-- Ce script vérifie que la migration s'est bien déroulée

USE tournament_db;

-- =============================================
-- 1. Vérification de la structure de la table participants
-- =============================================
SELECT 'VÉRIFICATION 1: Structure de la table participants' as verification;
SELECT 
    COLUMN_NAME as colonne,
    COLUMN_TYPE as type,
    IS_NULLABLE as nullable,
    COLUMN_DEFAULT as valeur_defaut
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants'
ORDER BY ORDINAL_POSITION;

-- =============================================
-- 2. Vérification que la colonne 'name' n'existe plus
-- =============================================
SELECT 'VÉRIFICATION 2: Colonne name supprimée' as verification;
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN '✅ OK - Colonne name supprimée'
        ELSE '❌ ERREUR - Colonne name existe encore'
    END as resultat
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants' 
AND COLUMN_NAME = 'name';

-- =============================================
-- 3. Vérification que les nouvelles colonnes existent
-- =============================================
SELECT 'VÉRIFICATION 3: Nouvelles colonnes présentes' as verification;
SELECT 
    CASE 
        WHEN COUNT(*) = 4 THEN '✅ OK - Toutes les colonnes sont présentes (prenom, nom, age, telephone)'
        ELSE CONCAT('❌ ERREUR - Seulement ', COUNT(*), ' colonne(s) trouvée(s) sur 4')
    END as resultat
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants' 
AND COLUMN_NAME IN ('prenom', 'nom', 'age', 'telephone');

-- =============================================
-- 4. Vérification des données existantes
-- =============================================
SELECT 'VÉRIFICATION 4: Données des participants' as verification;
SELECT 
    COUNT(*) as total_participants,
    SUM(CASE WHEN prenom IS NOT NULL AND prenom != '' THEN 1 ELSE 0 END) as avec_prenom,
    SUM(CASE WHEN nom IS NOT NULL AND nom != '' THEN 1 ELSE 0 END) as avec_nom,
    SUM(CASE WHEN age IS NOT NULL AND age > 0 THEN 1 ELSE 0 END) as avec_age,
    SUM(CASE WHEN telephone IS NOT NULL AND telephone != '' THEN 1 ELSE 0 END) as avec_telephone
FROM participants;

-- =============================================
-- 5. Vérification du statut (doit inclure 'Liste d'attente')
-- =============================================
SELECT 'VÉRIFICATION 5: Type ENUM du statut' as verification;
SELECT 
    COLUMN_TYPE as type_enum,
    CASE 
        WHEN COLUMN_TYPE LIKE '%Liste d\'\'attente%' THEN '✅ OK - Liste d\'attente incluse'
        ELSE '❌ ERREUR - Liste d\'attente manquante'
    END as resultat
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants' 
AND COLUMN_NAME = 'status';

-- =============================================
-- 6. Vérification de la colonne position_attente
-- =============================================
SELECT 'VÉRIFICATION 6: Colonne position_attente' as verification;
SELECT 
    CASE 
        WHEN COUNT(*) = 1 THEN '✅ OK - Colonne position_attente existe'
        ELSE '❌ ERREUR - Colonne position_attente manquante'
    END as resultat
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'participants' 
AND COLUMN_NAME = 'position_attente';

-- =============================================
-- 7. Vérification de max_participants dans tournaments
-- =============================================
SELECT 'VÉRIFICATION 7: Colonne max_participants' as verification;
SELECT 
    CASE 
        WHEN COUNT(*) = 1 THEN '✅ OK - Colonne max_participants existe'
        ELSE '❌ ERREUR - Colonne max_participants manquante'
    END as resultat
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'tournament_db' 
AND TABLE_NAME = 'tournaments' 
AND COLUMN_NAME = 'max_participants';

-- =============================================
-- 8. Affichage d'exemple de données
-- =============================================
SELECT 'VÉRIFICATION 8: Exemple de données' as verification;
SELECT 
    id,
    tournament_id,
    CONCAT(prenom, ' ', nom) as nom_complet,
    age,
    telephone,
    status,
    inscription_date
FROM participants 
LIMIT 5;

-- =============================================
-- 9. Test de jointure avec poules (si des poules existent)
-- =============================================
SELECT 'VÉRIFICATION 9: Test jointure poules-participants' as verification;
SELECT 
    po.name as poule,
    p.id,
    CONCAT(p.prenom, ' ', p.nom) as participant,
    p.age,
    p.telephone
FROM pools po
JOIN pool_participants pp ON po.id = pp.pool_id
JOIN participants p ON pp.participant_id = p.id
LIMIT 10;

-- =============================================
-- RÉSUMÉ FINAL
-- =============================================
SELECT '========================================' as '';
SELECT 'RÉSUMÉ DE LA VÉRIFICATION' as '';
SELECT '========================================' as '';

SELECT 
    CONCAT('✅ Table participants: ', 
           (SELECT COUNT(*) FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = 'tournament_db' AND TABLE_NAME = 'participants'),
           ' table(s) trouvée(s)') as check1;

SELECT 
    CONCAT('✅ Colonnes requises: ', 
           (SELECT COUNT(*) FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'tournament_db' 
            AND TABLE_NAME = 'participants' 
            AND COLUMN_NAME IN ('prenom', 'nom', 'age', 'telephone')),
           '/4 présente(s)') as check2;

SELECT 
    CONCAT('✅ Total participants: ', 
           COUNT(*), ' participant(s)') as check3
FROM participants;

SELECT 
    CONCAT('✅ Participants avec données complètes: ',
           SUM(CASE WHEN prenom IS NOT NULL AND nom IS NOT NULL 
                        AND age IS NOT NULL AND telephone IS NOT NULL 
                    THEN 1 ELSE 0 END),
           '/', COUNT(*)) as check4
FROM participants;

SELECT '========================================' as '';
SELECT '✅ VÉRIFICATION TERMINÉE' as '';
SELECT '========================================' as '';
SELECT 'Si tous les checks sont OK, la migration est réussie !' as message;
