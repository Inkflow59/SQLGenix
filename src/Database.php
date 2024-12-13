<?php
require_once 'Logger.php';

class Database {
    private $pdo;
    private $logger;
    
    public function __construct($host, $db, $user, $pass) {
        $this->logger = new Logger(); // Logger instance
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->logger->log("Successfully connected to the database $db");
        } catch (PDOException $e) {
            $this->logger->log("Connection error: " . $e->getMessage());
            echo "Connection error: " . $e->getMessage();
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
            $this->logger->log("Error executing the query: " . $e->getMessage());
            echo "Error executing the query: " . $e->getMessage();
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
