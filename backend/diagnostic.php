<?php
// Script de diagnostic pour vérifier l'état de la base de données
header("Content-Type: text/html; charset=UTF-8");

require_once '../config/database.php';

echo "<html><head><meta charset='UTF-8'><title>Diagnostic BDD</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #4CAF50; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
</style></head><body>";

echo "<h1>🔍 Diagnostic de la base de données</h1>";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='box'><p class='error'>❌ ERREUR: Impossible de se connecter à la base de données</p></div>";
    exit();
}

echo "<div class='box'><p class='success'>✅ Connexion à la base de données réussie</p></div>";

// Test 1: Structure de la table participants
echo "<div class='box'><h2>📋 Test 1: Structure de la table participants</h2>";
try {
    $query = "DESCRIBE participants";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table><tr><th>Colonne</th><th>Type</th><th>Null</th><th>Défaut</th></tr>";
    $required_columns = ['prenom', 'nom', 'age', 'telephone'];
    $found_columns = [];
    
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
        if (in_array($col['Field'], $required_columns)) {
            $found_columns[] = $col['Field'];
        }
    }
    echo "</table>";
    
    $missing = array_diff($required_columns, $found_columns);
    if (empty($missing)) {
        echo "<p class='success'>✅ Toutes les colonnes requises sont présentes</p>";
    } else {
        echo "<p class='error'>❌ Colonnes manquantes: " . implode(', ', $missing) . "</p>";
        echo "<p class='warning'>⚠️ Vous devez exécuter le script de migration!</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 2: Données de test
echo "<div class='box'><h2>👥 Test 2: Participants existants</h2>";
try {
    $query = "SELECT COUNT(*) as total FROM participants";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de participants: <strong>{$result['total']}</strong></p>";
    
    if ($result['total'] > 0) {
        // Essayer de récupérer les données avec les nouveaux champs
        try {
            $query = "SELECT id, tournament_id, prenom, nom, age, telephone, status, inscription_date FROM participants LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table><tr><th>ID</th><th>Tournoi</th><th>Prénom</th><th>Nom</th><th>Âge</th><th>Téléphone</th><th>Statut</th><th>Date</th></tr>";
            foreach ($participants as $p) {
                echo "<tr>";
                echo "<td>{$p['id']}</td>";
                echo "<td>{$p['tournament_id']}</td>";
                echo "<td>" . ($p['prenom'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($p['nom'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($p['age'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($p['telephone'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>{$p['status']}</td>";
                echo "<td>{$p['inscription_date']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p class='success'>✅ Les nouveaux champs fonctionnent correctement</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erreur lors de la lecture des données: " . $e->getMessage() . "</p>";
            echo "<p class='warning'>⚠️ Les colonnes prenom, nom, age, telephone n'existent probablement pas encore</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Structure de la table tournaments
echo "<div class='box'><h2>🏆 Test 3: Structure de la table tournaments</h2>";
try {
    $query = "DESCRIBE tournaments";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table><tr><th>Colonne</th><th>Type</th></tr>";
    $has_max_participants = false;
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
        if ($col['Field'] === 'max_participants') {
            $has_max_participants = true;
        }
    }
    echo "</table>";
    
    if ($has_max_participants) {
        echo "<p class='success'>✅ Colonne max_participants présente</p>";
    } else {
        echo "<p class='warning'>⚠️ Colonne max_participants absente (optionnelle pour l'instant)</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Test de l'API
echo "<div class='box'><h2>🔌 Test 4: Test de l'API</h2>";
echo "<p>Testez les endpoints suivants :</p>";
echo "<ul>";
echo "<li><a href='../api/index.php/tournaments' target='_blank'>GET /tournaments</a></li>";
echo "<li><a href='../api/index.php/participants?tournament_id=fortnite' target='_blank'>GET /participants (Fortnite)</a></li>";
echo "<li><a href='../api/index.php/stats' target='_blank'>GET /stats</a></li>";
echo "</ul>";
echo "</div>";

// Recommandations
echo "<div class='box'><h2>💡 Recommandations</h2>";

// Vérifier si migration nécessaire
$query = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
          WHERE TABLE_SCHEMA = 'tournament_db' 
          AND TABLE_NAME = 'participants' 
          AND COLUMN_NAME IN ('prenom', 'nom', 'age', 'telephone')";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] < 4) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
    echo "<h3>⚠️ MIGRATION REQUISE</h3>";
    echo "<p><strong>Les nouvelles colonnes ne sont pas présentes dans la base de données.</strong></p>";
    echo "<p>Pour corriger cela :</p>";
    echo "<ol>";
    echo "<li>Ouvrez phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Sélectionnez la base <code>tournament_db</code></li>";
    echo "<li>Allez dans l'onglet <strong>SQL</strong></li>";
    echo "<li>Copiez et exécutez le contenu du fichier: <code>database/migration_add_participant_fields.sql</code></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
    echo "<h3>✅ BASE DE DONNÉES À JOUR</h3>";
    echo "<p>Toutes les colonnes requises sont présentes. L'application devrait fonctionner correctement.</p>";
    echo "<p>Si vous rencontrez encore des erreurs, videz le cache de votre navigateur (Ctrl+Shift+R)</p>";
    echo "</div>";
}

echo "</div>";

echo "</body></html>";
?>
