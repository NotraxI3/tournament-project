<?php
class Pool {
    private $conn;
    private $table_name = "pools";
    private $participants_table = "pool_participants";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer des poules aléatoirement
    public function createRandomPools($tournament_id, $pool_size = 4) {
        try {
            // Commencer une transaction
            $this->conn->beginTransaction();

            // Supprimer les anciennes poules
            $this->clearPools($tournament_id);

            // Récupérer les participants en lice
            $query = "SELECT * FROM participants 
                      WHERE tournament_id = ? AND status = 'En lice' 
                      ORDER BY RAND()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $tournament_id);
            $stmt->execute();
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($participants) < $pool_size) {
                throw new Exception("Pas assez de participants en lice");
            }

            // Créer les poules
            $pool_number = 1;
            $pools_created = [];

            for ($i = 0; $i < count($participants); $i += $pool_size) {
                $pool_name = "Poule " . chr(64 + $pool_number);
                
                // Insérer la poule
                $query = "INSERT INTO pools (tournament_id, name, pool_number) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $tournament_id);
                $stmt->bindParam(2, $pool_name);
                $stmt->bindParam(3, $pool_number);
                $stmt->execute();
                
                $pool_id = $this->conn->lastInsertId();
                $pool_participants = array_slice($participants, $i, $pool_size);

                // Ajouter les participants à la poule
                foreach ($pool_participants as $participant) {
                    $query = "INSERT INTO pool_participants (pool_id, participant_id) VALUES (?, ?)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(1, $pool_id);
                    $stmt->bindParam(2, $participant['id']);
                    $stmt->execute();
                }

                $pools_created[] = [
                    'id' => $pool_id,
                    'name' => $pool_name,
                    'participants' => $pool_participants
                ];

                $pool_number++;
            }

            $this->conn->commit();
            return $pools_created;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Récupérer les poules d'un tournoi
    public function getByTournament($tournament_id) {
        $query = "SELECT p.*, 
                         GROUP_CONCAT(
                             JSON_OBJECT(
                                 'id', pt.id,
                                 'name', pt.name,
                                 'status', pt.status
                             )
                         ) as participants
                  FROM pools p
                  LEFT JOIN pool_participants pp ON p.id = pp.pool_id
                  LEFT JOIN participants pt ON pp.participant_id = pt.id
                  WHERE p.tournament_id = ?
                  GROUP BY p.id
                  ORDER BY p.pool_number";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tournament_id);
        $stmt->execute();
        
        $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parser les participants JSON
        foreach ($pools as &$pool) {
            if ($pool['participants']) {
                $pool['participants'] = array_map('json_decode', explode(',', $pool['participants']));
            } else {
                $pool['participants'] = [];
            }
        }
        
        return $pools;
    }

    // Supprimer toutes les poules d'un tournoi
    public function clearPools($tournament_id) {
        $query = "DELETE FROM pools WHERE tournament_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tournament_id);
        return $stmt->execute();
    }
}
?>
