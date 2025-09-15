<?php
require_once '../Interfaces/DatabaseAdapterInterface.php';
require_once '../Logger.php';

/**
 * MySQL Database Adapter for SQLGenix.
 * 
 * This class provides MySQL-specific implementations for database operations,
 * refactored from the original Database class to implement the adapter interface.
 */
class MySQLAdapter implements DatabaseAdapterInterface {
    
    /**
     * @var PDO The PDO instance for MySQL connection.
     */
    private $pdo;
    
    /**
     * @var Logger The logger instance.
     */
    private $logger;
    
    /**
     * @var array MySQL-specific features support map.
     */
    private $supportedFeatures = [
        'window_functions' => true, // MySQL 8.0+
        'cte' => true, // MySQL 8.0+
        'json' => true, // MySQL 5.7+
        'full_text_search' => true,
        'upsert' => true,
        'auto_increment' => true,
        'stored_procedures' => true,
        'triggers' => true,
        'views' => true,
        'partitioning' => true
    ];
    
    /**
     * Constructor
     * 
     * @param string $host Database host
     * @param string $db Database name
     * @param string $user Username
     * @param string $pass Password
     * @param int $port Port (default: 3306)
     * @param string $charset Charset (default: utf8mb4)
     * @param array $options Additional PDO options
     */
    public function __construct($host, $db, $user, $pass, $port = 3306, $charset = 'utf8mb4', $options = []) {
        $this->logger = new Logger();
        
        try {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '$charset'"
            ];
            $options = array_merge($defaultOptions, $options);
            
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            
            $this->logger->log("Successfully connected to MySQL database $db");
        } catch (PDOException $e) {
            $this->logger->log("MySQL connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get the database connection instance.
     * 
     * @return PDO The PDO connection instance
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute a SQL query with parameters.
     * 
     * @param string $query The SQL query to execute
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute($params);
            
            if (!$success) {
                $error = $stmt->errorInfo();
                $this->logger->log("MySQL query execution failed: " . $error[2]);
                throw new PDOException("Query execution failed: " . $error[2]);
            }
            
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->log("Error executing MySQL query: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Begin a database transaction.
     * 
     * @param bool $consistent Whether to use consistent snapshot
     * @return bool Success status
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
     * 
     * @return bool Success status
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback the current transaction.
     * 
     * @return bool Success status
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get the last inserted ID.
     * 
     * @param string|null $sequence Not used in MySQL (for interface compatibility)
     * @return string Last inserted ID
     */
    public function getLastInsertId($sequence = null) {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Quote a string for safe SQL usage.
     * 
     * @param string $string String to quote
     * @return string Quoted string
     */
    public function quote($string) {
        return $this->pdo->quote($string);
    }
    
    /**
     * Quote an identifier (table, column name) using MySQL's backticks.
     * 
     * @param string $identifier Identifier to quote
     * @return string Quoted identifier
     */
    public function quoteIdentifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Get the MySQL LIMIT syntax.
     * 
     * @param int|null $limit Row limit
     * @param int|null $offset Row offset
     * @return string LIMIT clause
     */
    public function getLimitClause($limit = null, $offset = null) {
        $clause = '';
        
        if ($limit !== null) {
            if ($offset !== null) {
                $clause = " LIMIT $offset, $limit";
            } else {
                $clause = " LIMIT $limit";
            }
        }
        
        return $clause;
    }
    
    /**
     * Get MySQL date format function.
     * 
     * @param string $format Date format (MySQL format)
     * @param string $column Column name
     * @return string Date format function
     */
    public function getDateFormatFunction($format, $column) {
        return "DATE_FORMAT($column, '$format')";
    }
    
    /**
     * Get MySQL concatenation function.
     * 
     * @param array $columns Columns to concatenate
     * @return string Concatenation function
     */
    public function getConcatFunction($columns) {
        return 'CONCAT(' . implode(', ', $columns) . ')';
    }
    
    /**
     * Get MySQL UPSERT syntax (INSERT ... ON DUPLICATE KEY UPDATE).
     * 
     * @param string $table Table name
     * @param array $columns Column names
     * @param array $updateColumns Columns to update on conflict
     * @return string UPSERT query template
     */
    public function getUpsertSyntax($table, $columns, $updateColumns) {
        $quotedTable = $this->quoteIdentifier($table);
        $quotedColumns = array_map([$this, 'quoteIdentifier'], $columns);
        $columnsStr = implode(', ', $quotedColumns);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $updateParts = [];
        foreach ($updateColumns as $column) {
            $quotedCol = $this->quoteIdentifier($column);
            $updateParts[] = "$quotedCol = VALUES($quotedCol)";
        }
        $updateClause = implode(', ', $updateParts);
        
        return "INSERT INTO $quotedTable ($columnsStr) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updateClause";
    }
    
    /**
     * Get the database type identifier.
     * 
     * @return string Database type
     */
    public function getDatabaseType() {
        return 'mysql';
    }
    
    /**
     * Check if MySQL supports a specific feature.
     * 
     * @param string $feature Feature name
     * @return bool Whether the feature is supported
     */
    public function supportsFeature($feature) {
        return isset($this->supportedFeatures[$feature]) && $this->supportedFeatures[$feature];
    }
    
    /**
     * Get MySQL error information.
     * 
     * @return array Error information
     */
    public function getLastError() {
        return $this->pdo->errorInfo();
    }
    
    /**
     * Set MySQL session variables.
     * 
     * @param array $variables Variable-value pairs
     * @return bool Success status
     */
    public function setSessionVariables($variables) {
        try {
            foreach ($variables as $name => $value) {
                $this->executeQuery("SET SESSION $name = ?", [$value]);
            }
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error setting MySQL session variables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get MySQL version information.
     * 
     * @return string MySQL version
     */
    public function getVersion() {
        try {
            $stmt = $this->pdo->query("SELECT VERSION()");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->log("Could not get MySQL version: " . $e->getMessage());
            return 'Unknown';
        }
    }
    
    /**
     * Lock tables for write operations.
     * 
     * @param array $tables Array of table names with their lock types
     * @return bool Success status
     */
    public function lockTables(array $tables) {
        $lockStatements = [];
        foreach ($tables as $table => $type) {
            $quotedTable = $this->quoteIdentifier($table);
            $lockStatements[] = "$quotedTable $type";
        }
        
        try {
            $this->executeQuery("LOCK TABLES " . implode(', ', $lockStatements));
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error locking tables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Unlock all tables.
     * 
     * @return bool Success status
     */
    public function unlockTables() {
        try {
            $this->executeQuery("UNLOCK TABLES");
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error unlocking tables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set transaction isolation level.
     * 
     * @param string $level Isolation level
     * @return bool Success status
     */
    public function setTransactionIsolation($level) {
        $validLevels = [
            'READ UNCOMMITTED',
            'READ COMMITTED',
            'REPEATABLE READ',
            'SERIALIZABLE'
        ];
        
        if (in_array($level, $validLevels)) {
            try {
                $this->executeQuery("SET TRANSACTION ISOLATION LEVEL $level");
                return true;
            } catch (PDOException $e) {
                $this->logger->log("Error setting isolation level: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Execute query with deadlock retry.
     * 
     * @param string $query SQL query to execute
     * @param array $params Query parameters
     * @param int $maxRetries Maximum number of retries
     * @return mixed Query result
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
     * Get the logger instance.
     * 
     * @return Logger The logger instance
     */
    public function getLogger() {
        return $this->logger;
    }
}