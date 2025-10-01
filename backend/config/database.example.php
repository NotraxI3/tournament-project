<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'tournament_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $conn = null;

    public function getConnection() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
            return null;
        }
    }
}
?>
