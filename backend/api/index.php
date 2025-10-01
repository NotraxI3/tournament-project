<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Inclure les fichiers nécessaires
require_once '../config/database.php';
require_once '../classes/Tournament.php';
require_once '../classes/Participant.php';
require_once '../classes/Pool.php';

// Initialiser la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Erreur de connexion à la base de données."));
    exit();
}

// Récupérer la méthode HTTP et l'endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

try {
    switch ($endpoint) {
        case 'tournaments':
            $tournament = new Tournament($db);
            
            if ($method === 'GET') {
                if (isset($request[1])) {
                    // Récupérer un tournoi spécifique
                    $result = $tournament->getById($request[1]);
                    if ($result) {
                        echo json_encode($result);
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Tournoi non trouvé."));
                    }
                } else {
                    // Récupérer tous les tournois
                    $result = $tournament->getAll();
                    echo json_encode($result);
                }
            }
            break;

        case 'participants':
            $participant = new Participant($db);
            
            if ($method === 'GET') {
                if (isset($_GET['tournament_id'])) {
                    // Récupérer les participants d'un tournoi
                    $result = $participant->getByTournament($_GET['tournament_id']);
                } else {
                    // Récupérer tous les participants
                    $result = $participant->getAll();
                }
                echo json_encode($result);
                
            } elseif ($method === 'POST') {
                // Ajouter un nouveau participant
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['tournament_id']) && isset($data['name'])) {
                    $participant->tournament_id = $data['tournament_id'];
                    $participant->name = $data['name'];
                    $participant->status = $data['status'] ?? 'En lice';
                    $participant->inscription_date = $data['inscription_date'] ?? date('Y-m-d');
                    
                    $participant_id = $participant->create();
                    if ($participant_id) {
                        http_response_code(201);
                        echo json_encode(array(
                            "message" => "Participant ajouté avec succès.",
                            "id" => $participant_id
                        ));
                    } else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Impossible d'ajouter le participant."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Données manquantes."));
                }
                
            } elseif ($method === 'PUT') {
                // Mettre à jour le statut d'un participant
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['id']) && isset($data['tournament_id']) && isset($data['status'])) {
                    $participant->id = $data['id'];
                    $participant->tournament_id = $data['tournament_id'];
                    $participant->status = $data['status'];
                    
                    if ($participant->updateStatus()) {
                        echo json_encode(array("message" => "Statut mis à jour avec succès."));
                    } else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Impossible de mettre à jour le statut."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Données manquantes."));
                }
                
            } elseif ($method === 'DELETE') {
                // Supprimer un participant
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['id']) && isset($data['tournament_id'])) {
                    $participant->id = $data['id'];
                    $participant->tournament_id = $data['tournament_id'];
                    
                    if ($participant->delete()) {
                        echo json_encode(array("message" => "Participant supprimé avec succès."));
                    } else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Impossible de supprimer le participant."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Données manquantes."));
                }
            }
            break;

        case 'pools':
            $pool = new Pool($db);
            
            if ($method === 'GET') {
                if (isset($_GET['tournament_id'])) {
                    $result = $pool->getByTournament($_GET['tournament_id']);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "tournament_id requis."));
                }
                
            } elseif ($method === 'POST') {
                // Créer des poules aléatoirement
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['tournament_id'])) {
                    $pool_size = $data['pool_size'] ?? 4;
                    
                    try {
                        $result = $pool->createRandomPools($data['tournament_id'], $pool_size);
                        echo json_encode(array(
                            "message" => "Poules créées avec succès.",
                            "pools" => $result
                        ));
                    } catch (Exception $e) {
                        http_response_code(400);
                        echo json_encode(array("message" => $e->getMessage()));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "tournament_id requis."));
                }
                
            } elseif ($method === 'DELETE') {
                // Supprimer toutes les poules d'un tournoi
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['tournament_id'])) {
                    if ($pool->clearPools($data['tournament_id'])) {
                        echo json_encode(array("message" => "Poules supprimées avec succès."));
                    } else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Impossible de supprimer les poules."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "tournament_id requis."));
                }
            }
            break;

        case 'stats':
            if ($method === 'GET') {
                // Statistiques générales
                $participant = new Participant($db);
                $tournament = new Tournament($db);
                
                $tournaments = $tournament->getAll();
                $all_participants = $participant->getAll();
                
                $stats = [
                    'total_participants' => count($all_participants),
                    'active_players' => count(array_filter($all_participants, fn($p) => $p['status'] === 'En lice')),
                    'eliminated_players' => count(array_filter($all_participants, fn($p) => $p['status'] === 'Éliminé')),
                    'total_tournaments' => count($tournaments),
                    'tournament_stats' => []
                ];
                
                // Stats par tournoi
                foreach ($tournaments as $t) {
                    $tournament_participants = $participant->getByTournament($t['id']);
                    $stats['tournament_stats'][] = [
                        'id' => $t['id'],
                        'name' => $t['name'],
                        'total' => count($tournament_participants),
                        'en_lice' => count(array_filter($tournament_participants, fn($p) => $p['status'] === 'En lice')),
                        'elimines' => count(array_filter($tournament_participants, fn($p) => $p['status'] === 'Éliminé'))
                    ];
                }
                
                echo json_encode($stats);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(array("message" => "Endpoint non trouvé."));
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Erreur serveur: " . $e->getMessage()));
}
?>
