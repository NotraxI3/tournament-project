<?php
class Participant {
    private $conn;
    private $table_name = "participants";

    public $id;
    public $tournament_id;
    public $name;
    public $status;
    public $inscription_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les participants
    public function getAll() {
        $query = "SELECT p.*, t.name as tournament_name, t.icon as tournament_icon 
                  FROM " . $this->table_name . " p 
                  JOIN tournaments t ON p.tournament_id = t.id 
                  ORDER BY p.inscription_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les participants par tournoi
    public function getByTournament($tournament_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tournament_id = ? 
                  ORDER BY inscription_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tournament_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un participant
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET tournament_id = :tournament_id, 
                      name = :name, 
                      status = :status, 
                      inscription_date = :inscription_date";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->tournament_id = htmlspecialchars(strip_tags($this->tournament_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->inscription_date = htmlspecialchars(strip_tags($this->inscription_date));

        // Bind des valeurs
        $stmt->bindParam(":tournament_id", $this->tournament_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":inscription_date", $this->inscription_date);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour le statut d'un participant
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id AND tournament_id = :tournament_id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->tournament_id = htmlspecialchars(strip_tags($this->tournament_id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":tournament_id", $this->tournament_id);

        return $stmt->execute();
    }

    // Supprimer un participant
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND tournament_id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->tournament_id = htmlspecialchars(strip_tags($this->tournament_id));
        
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->tournament_id);
        
        return $stmt->execute();
    }
}
?>
