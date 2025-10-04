<?php
// classes/Participant.php - VERSION 2
class Participant {
    private $conn;
    private $table_name = "participants";

    public $id;
    public $tournament_id;
    public $prenom;
    public $nom;
    public $age;
    public $telephone;
    public $status;
    public $inscription_date;
    public $position_attente;

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
                  ORDER BY 
                    CASE status
                        WHEN 'En lice' THEN 1
                        WHEN 'Éliminé' THEN 2
                        WHEN 'Liste d''attente' THEN 3
                    END,
                    position_attente ASC,
                    inscription_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tournament_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un participant
    public function create() {
        // Vérifier le nombre de places disponibles
        $query = "SELECT COUNT(*) as count, t.max_participants 
                  FROM participants p 
                  JOIN tournaments t ON p.tournament_id = t.id 
                  WHERE p.tournament_id = ? AND p.status = 'En lice'
                  GROUP BY t.max_participants";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tournament_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Déterminer le statut
        if ($result && $result['count'] >= $result['max_participants']) {
            $this->status = 'Liste d\'attente';
            
            // Calculer la position dans la liste d'attente
            $query_pos = "SELECT COALESCE(MAX(position_attente), 0) + 1 as next_position 
                          FROM participants 
                          WHERE tournament_id = ? AND status = 'Liste d''attente'";
            $stmt_pos = $this->conn->prepare($query_pos);
            $stmt_pos->bindParam(1, $this->tournament_id);
            $stmt_pos->execute();
            $pos_result = $stmt_pos->fetch(PDO::FETCH_ASSOC);
            $this->position_attente = $pos_result['next_position'];
        } else {
            $this->status = 'En lice';
            $this->position_attente = null;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET tournament_id = :tournament_id, 
                      prenom = :prenom,
                      nom = :nom,
                      age = :age,
                      telephone = :telephone,
                      status = :status, 
                      inscription_date = :inscription_date,
                      position_attente = :position_attente";

        $stmt = $this->conn->prepare($query);

        // Nettoyage
        $this->tournament_id = htmlspecialchars(strip_tags($this->tournament_id));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->inscription_date = htmlspecialchars(strip_tags($this->inscription_date));

        $stmt->bindParam(":tournament_id", $this->tournament_id);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":inscription_date", $this->inscription_date);
        $stmt->bindParam(":position_attente", $this->position_attente);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour le statut
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

    // Supprimer un participant (et promouvoir quelqu'un de la liste d'attente)
    public function delete() {
        // Commence une transaction
        $this->conn->beginTransaction();
        
        try {
            // Récupérer les infos du participant
            $query = "SELECT status, tournament_id FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            $participant = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Supprimer le participant
            $query_del = "DELETE FROM " . $this->table_name . " WHERE id = ? AND tournament_id = ?";
            $stmt_del = $this->conn->prepare($query_del);
            $stmt_del->bindParam(1, $this->id);
            $stmt_del->bindParam(2, $this->tournament_id);
            $stmt_del->execute();
            
            // Si c'était quelqu'un "En lice", promouvoir le premier de la liste d'attente
            if ($participant && $participant['status'] === 'En lice') {
                $query_promote = "UPDATE " . $this->table_name . " 
                                  SET status = 'En lice', position_attente = NULL 
                                  WHERE tournament_id = ? 
                                  AND status = 'Liste d''attente' 
                                  ORDER BY position_attente ASC 
                                  LIMIT 1";
                $stmt_promote = $this->conn->prepare($query_promote);
                $stmt_promote->bindParam(1, $participant['tournament_id']);
                $stmt_promote->execute();
                
                // Réorganiser les positions dans la liste d'attente
                $this->updateWaitingListPositions($participant['tournament_id']);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Réorganiser les positions dans la liste d'attente
    private function updateWaitingListPositions($tournament_id) {
        $query = "SET @position = 0; 
                  UPDATE " . $this->table_name . " 
                  SET position_attente = (@position := @position + 1) 
                  WHERE tournament_id = ? AND status = 'Liste d''attente' 
                  ORDER BY inscription_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tournament_id);
        return $stmt->execute();
    }
}
?>
