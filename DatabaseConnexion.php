<?php
class DatabaseConnexion {
    private $pdo;
    
    public function __construct($host, $db, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
