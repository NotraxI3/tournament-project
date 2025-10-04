/*
 * FICHIER DE CONFIGURATION
 * 
 * Ce fichier contient les paramètres de configuration du projet.
 * Modifier selon vos besoins.
 */

// ========================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ========================================

// Hôte de la base de données
// Par défaut : 'localhost'
// Si MySQL est sur un autre serveur : '192.168.1.100' par exemple
$DB_HOST = 'localhost';

// Nom de la base de données
// Par défaut : 'tournament_db'
$DB_NAME = 'tournament_db';

// Nom d'utilisateur MySQL
// Par défaut : 'root' (XAMPP)
$DB_USER = 'root';

// Mot de passe MySQL
// Par défaut : '' (vide pour XAMPP)
// Si vous avez défini un mot de passe : 'votre_mot_de_passe'
$DB_PASSWORD = '';

// Charset de la base de données
// Ne pas modifier sauf si nécessaire
$DB_CHARSET = 'utf8mb4';


// ========================================
// CONFIGURATION DE L'API
// ========================================

// URL de base de l'API
// À modifier selon votre installation
// Exemples :
//   - 'http://localhost/tournament-project/backend/api'
//   - 'http://localhost:8080/tournament-project/backend/api'
//   - 'http://192.168.1.100/tournament-project/backend/api'
$API_BASE_URL = 'http://localhost/tournament-project/backend/api';

// Port Apache (si différent de 80)
// Par défaut : null (port 80)
// Si Apache utilise le port 8080 : 8080
$APACHE_PORT = null;


// ========================================
// CONFIGURATION DE L'APPLICATION
// ========================================

// Taille par défaut des poules
// Nombre de joueurs par poule
$DEFAULT_POOL_SIZE = 4;

// Fuseau horaire
// Par défaut : 'Europe/Paris'
// Autres exemples : 'America/New_York', 'Asia/Tokyo'
$TIMEZONE = 'Europe/Paris';

// Langue de l'application
// Par défaut : 'fr' (français)
// Autres : 'en' (anglais), 'es' (espagnol)
$LANGUAGE = 'fr';


// ========================================
// CONFIGURATION DE SÉCURITÉ
// ========================================

// Activer le mode debug
// true : affiche les erreurs détaillées
// false : cache les erreurs (RECOMMANDÉ EN PRODUCTION)
$DEBUG_MODE = true;

// Origines autorisées pour CORS
// '*' : permet toutes les origines (développement)
// 'http://localhost' : limite aux requêtes locales (production)
$CORS_ALLOWED_ORIGINS = '*';

// Méthodes HTTP autorisées
$CORS_ALLOWED_METHODS = 'GET, POST, PUT, DELETE, OPTIONS';


// ========================================
// CONFIGURATION DES TOURNOIS
// ========================================

// Nombre maximum de participants par tournoi
// null : pas de limite
// 32 : limite à 32 participants
$MAX_PARTICIPANTS = null;

// Statuts de participants autorisés
$ALLOWED_STATUSES = ['En lice', 'Éliminé'];

// Statuts de tournois autorisés
$TOURNAMENT_STATUSES = ['active', 'inactive', 'completed'];


// ========================================
// NOTES IMPORTANTES
// ========================================

/*
 * 1. Ce fichier est un exemple de configuration
 * 2. Les vraies configurations sont dans :
 *    - backend/config/database.php (pour la DB)
 *    - frontend/tournament-dashboard.html (pour l'API URL)
 * 3. Ne jamais commiter de mots de passe en production
 * 4. Utiliser des variables d'environnement pour la production
 */


// ========================================
// EXEMPLES DE CONFIGURATIONS
// ========================================

// Configuration pour XAMPP Windows
/*
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = '';
$API_BASE_URL = 'http://localhost/tournament-project/backend/api';
*/

// Configuration pour MAMP Mac
/*
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = 'root';
$API_BASE_URL = 'http://localhost:8888/tournament-project/backend/api';
*/

// Configuration pour serveur distant
/*
$DB_HOST = '192.168.1.100';
$DB_USER = 'tournament_user';
$DB_PASSWORD = 'secure_password_123';
$API_BASE_URL = 'http://192.168.1.100/api';
*/

// Configuration avec port personnalisé
/*
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = '';
$APACHE_PORT = 8080;
$API_BASE_URL = 'http://localhost:8080/tournament-project/backend/api';
*/
