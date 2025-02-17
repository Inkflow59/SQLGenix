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
     * @param string $charset The charset to use (default: utf8mb4)
     * @param array $options Additional PDO options
     */
    public function __construct($host, $db, $user, $pass, $charset = 'utf8mb4', $options = []) {
        $this->logger = new Logger();
        try {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '$charset'"
            ];
            $options = array_merge($defaultOptions, $options);
            
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->logger->log("Successfully connected to the database $db");
        } catch (PDOException $e) {
            $this->logger->log("Connection error: " . $e->getMessage());
            throw $e;
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
     * Check if the connection is active.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool {
        return $this->pdo !== null;
    }

    /**
     * Disconnect from the database.
     */
    public function disconnect() {
        $this->pdo = null;
    }

    /**
     * Execute a SQL query with better error handling and MySQL specific optimizations.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for the SQL query.
     * @return mixed The result of the executed query or null on failure.
     * @throws PDOException
     */
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute($params);
            
            if (!$success) {
                $error = $stmt->errorInfo();
                $this->logger->log("Query execution failed: " . $error[2]);
                throw new PDOException("Query execution failed: " . $error[2]);
            }
            
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->log("Error executing the query: " . $e->getMessage());
            throw $e;
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
     * Begin a transaction with optional consistency level.
     *
     * @param bool $consistent Whether to use REPEATABLE READ isolation level
     * @return bool
     */
    public function beginTransaction($consistent = false) {
        if ($consistent) {
            $this->executeQuery("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $this->executeQuery("START TRANSACTION WITH CONSISTENT SNAPSHOT");
        } else {
            $this->pdo->beginTransaction();
        }
        return true;
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

    /**
     * Set transaction isolation level
     * 
     * @param string $level One of: READ UNCOMMITTED, READ COMMITTED, REPEATABLE READ, SERIALIZABLE
     * @return bool
     */
    public function setTransactionIsolation($level) {
        $validLevels = [
            'READ UNCOMMITTED',
            'READ COMMITTED',
            'REPEATABLE READ',
            'SERIALIZABLE'
        ];
        
        if (in_array($level, $validLevels)) {
            return $this->executeQuery("SET TRANSACTION ISOLATION LEVEL $level");
        }
        return false;
    }

    /**
     * Lock tables for write operations
     * 
     * @param array $tables Array of table names with their lock types (e.g. ['users' => 'WRITE'])
     * @return bool
     */
    public function lockTables(array $tables) {
        $lockStatements = [];
        foreach ($tables as $table => $type) {
            $lockStatements[] = "$table $type";
        }
        return $this->executeQuery("LOCK TABLES " . implode(', ', $lockStatements));
    }

    /**
     * Unlock all tables
     * 
     * @return bool
     */
    public function unlockTables() {
        return $this->executeQuery("UNLOCK TABLES");
    }

    /**
     * Execute query with deadlock retry
     * 
     * @param string $query SQL query to execute
     * @param array $params Query parameters
     * @param int $maxRetries Maximum number of retries
     * @return mixed
     */
    public function executeWithDeadlockRetry($query, $params = [], $maxRetries = 3) {
        $retries = 0;
        while ($retries < $maxRetries) {
            try {
                return $this->executeQuery($query, $params);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] === 1213) { // MySQL deadlock error code
                    $retries++;
                    if ($retries === $maxRetries) {
                        throw $e;
                    }
                    usleep(rand(10000, 50000)); // Random delay between 10-50ms
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * Set MySQL session variables
     * 
     * @param array $variables Array of variable-value pairs
     * @return bool
     */
    public function setSessionVariables(array $variables) {
        try {
            foreach ($variables as $name => $value) {
                $this->executeQuery("SET SESSION $name = ?", [$value]);
            }
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error setting session variables: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable or disable autocommit mode
     * 
     * @param bool $enabled True to enable, false to disable
     * @return bool
     */
    public function setAutoCommit(bool $enabled) {
        return $this->executeQuery("SET autocommit = ?", [$enabled ? 1 : 0]);
    }
}