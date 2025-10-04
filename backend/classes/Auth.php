<?php
// classes/Auth.php
class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Connexion utilisateur
    public function login($username, $password) {
        $query = "SELECT id, username, password, role FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Créer une session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Identifiants incorrects'];
    }

    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }

    // Vérifier si l'utilisateur est admin
    public function isAdmin() {
        session_start();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Déconnexion
    public function logout() {
        session_start();
        session_destroy();
        return ['success' => true];
    }

    // Créer un utilisateur (admin uniquement)
    public function createUser($username, $password, $role = 'operator') {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Accès non autorisé'];
        }

        $query = "INSERT INTO " . $this->table_name . " (username, password, role) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $hashed_password);
        $stmt->bindParam(3, $role);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la création'];
    }

    // Lister tous les utilisateurs (admin uniquement)
    public function getAllUsers() {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Accès non autorisé'];
        }

        $query = "SELECT id, username, role, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer un utilisateur (admin uniquement)
    public function deleteUser($user_id) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Accès non autorisé'];
        }

        // Ne pas pouvoir se supprimer soi-même
        if ($user_id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas vous supprimer'];
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }

    // Obtenir l'utilisateur courant
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $query = "SELECT id, username, role FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $_SESSION['user_id']);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
