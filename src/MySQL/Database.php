<?php
require_once 'Logger.php';

/**
 * Class Database
 *
 * This class handles the database connection using PDO. It provides methods for executing queries,
 * managing transactions, and logging errors.
 */
class Database {
    /**
     * @var PDO The PDO instance for database connection.
     */
    private $pdo;

    /**
     * @var Logger The logger instance for logging database operations.
     */
    private $logger;
    
    /**
     * Database constructor.
     *
     * @param string $host The database host.
     * @param string $db The database name.
     * @param string $user The database username.
     * @param string $pass The database password.
     */
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
    
    /**
     * Get the PDO connection instance.
     *
     * @return PDO The PDO instance.
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Disconnect from the database.
     */
    public function disconnect() {
        $this->pdo = null;
    }
    
    /**
     * Check if the connection is active.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool {
        return $this->pdo !== null;
    }
    
    /**
     * Execute a SQL query.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for the SQL query.
     * @return mixed The result of the executed query or null on failure.
     */
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
    
    /**
     * Get the last error information.
     *
     * @return array The error information from the last operation.
     */
    public function getLastError() {
        return $this->pdo->errorInfo();
    }
    
    /**
     * Begin a transaction.
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit the current transaction.
     */
    public function commit() {
        $this->pdo->commit();
    }
    
    /**
     * Roll back the current transaction.
     */
    public function rollback() {
        $this->pdo->rollBack();
    }
    
    /**
     * Get the logger instance.
     *
     * @return Logger The logger instance.
     */
    public function getLogger() {
        return $this->logger;
    }
}