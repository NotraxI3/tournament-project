<?php
class Tournament {
    private $conn;
    private $table_name = "tournaments";

    public $id;
    public $name;
    public $icon;
    public $description;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les tournois
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un tournoi par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
