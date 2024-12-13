<?php
require_once 'Logger.php';

class DatabaseConnexion {
    private $pdo;
    private $logger;
    
    public function __construct($host, $db, $user, $pass) {
        $this->logger = new Logger(); // Instance de Logger
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->logger->log("Connexion réussie à la base de données $db");
        } catch (PDOException $e) {
            $this->logger->log("Erreur de connexion: " . $e->getMessage());
            echo "Erreur de connexion: " . $e->getMessage();
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function disconnect() {
        $this->pdo = null;
    }
    
    public function isConnected(): bool {
        return $this->pdo !== null;
    }
    
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->log("Erreur lors de l'exécution de la requête: " . $e->getMessage());
            echo "Erreur lors de l'exécution de la requête: " . $e->getMessage();
            return null;
        }
    }
    
    public function getLastError() {
        return $this->pdo->errorInfo();
    }
    
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    public function commit() {
        $this->pdo->commit();
    }
    
    public function rollback() {
        $this->pdo->rollBack();
    }
    
    public function getLogger() {
        return $this->logger;
    }
}
