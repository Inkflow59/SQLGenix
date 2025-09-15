<?php
require_once '../Interfaces/DatabaseAdapterInterface.php';
require_once '../Logger.php';

/**
 * PostgreSQL Database Adapter for SQLGenix.
 * 
 * This class provides PostgreSQL-specific implementations for database operations,
 * handling PostgreSQL's unique syntax and features.
 */
class PostgreSQLAdapter implements DatabaseAdapterInterface {
    
    /**
     * @var PDO The PDO instance for PostgreSQL connection.
     */
    private $pdo;
    
    /**
     * @var Logger The logger instance.
     */
    private $logger;
    
    /**
     * @var array PostgreSQL-specific features support map.
     */
    private $supportedFeatures = [
        'window_functions' => true,
        'cte' => true,
        'json' => true,
        'arrays' => true,
        'full_text_search' => true,
        'upsert' => true,
        'returning' => true,
        'sequences' => true,
        'materialized_views' => true,
        'partitioning' => true
    ];
    
    /**
     * Constructor
     * 
     * @param string $host Database host
     * @param string $db Database name
     * @param string $user Username
     * @param string $pass Password
     * @param int $port Port (default: 5432)
     * @param array $options Additional PDO options
     */
    public function __construct($host, $db, $user, $pass, $port = 5432, $options = []) {
        $this->logger = new Logger();
        
        try {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $options = array_merge($defaultOptions, $options);
            
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            
            // Set PostgreSQL-specific settings
            $this->pdo->exec("SET client_encoding TO 'UTF8'");
            $this->pdo->exec("SET timezone TO 'UTC'");
            
            $this->logger->log("Successfully connected to PostgreSQL database $db");
        } catch (PDOException $e) {
            $this->logger->log("PostgreSQL connection error: " . $e->getMessage());
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
                $this->logger->log("PostgreSQL query execution failed: " . $error[2]);
                throw new PDOException("Query execution failed: " . $error[2]);
            }
            
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->log("Error executing PostgreSQL query: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Begin a database transaction.
     * 
     * @param bool $consistent Whether to use consistent snapshot (READ COMMITTED in PostgreSQL)
     * @return bool Success status
     */
    public function beginTransaction($consistent = false) {
        if ($consistent) {
            $this->pdo->exec("BEGIN ISOLATION LEVEL REPEATABLE READ");
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
     * @param string|null $sequence Sequence name for PostgreSQL
     * @return string Last inserted ID
     */
    public function getLastInsertId($sequence = null) {
        if ($sequence) {
            return $this->pdo->lastInsertId($sequence);
        }
        
        // Try to get the last value from the default sequence
        try {
            $stmt = $this->pdo->query("SELECT lastval()");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->log("Could not get last insert ID: " . $e->getMessage());
            return null;
        }
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
     * Quote an identifier (table, column name) using PostgreSQL's double quotes.
     * 
     * @param string $identifier Identifier to quote
     * @return string Quoted identifier
     */
    public function quoteIdentifier($identifier) {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
    
    /**
     * Get the PostgreSQL LIMIT syntax.
     * 
     * @param int|null $limit Row limit
     * @param int|null $offset Row offset
     * @return string LIMIT clause
     */
    public function getLimitClause($limit = null, $offset = null) {
        $clause = '';
        
        if ($limit !== null) {
            $clause .= " LIMIT $limit";
        }
        
        if ($offset !== null) {
            $clause .= " OFFSET $offset";
        }
        
        return $clause;
    }
    
    /**
     * Get PostgreSQL date format function.
     * 
     * @param string $format Date format (PostgreSQL format)
     * @param string $column Column name
     * @return string Date format function
     */
    public function getDateFormatFunction($format, $column) {
        return "TO_CHAR($column, '$format')";
    }
    
    /**
     * Get PostgreSQL concatenation function.
     * 
     * @param array $columns Columns to concatenate
     * @return string Concatenation function
     */
    public function getConcatFunction($columns) {
        return implode(' || ', $columns);
    }
    
    /**
     * Get PostgreSQL UPSERT syntax (INSERT ... ON CONFLICT).
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
            $updateParts[] = "$quotedCol = EXCLUDED.$quotedCol";
        }
        $updateClause = implode(', ', $updateParts);
        
        return "INSERT INTO $quotedTable ($columnsStr) VALUES ($placeholders) ON CONFLICT DO UPDATE SET $updateClause";
    }
    
    /**
     * Get the database type identifier.
     * 
     * @return string Database type
     */
    public function getDatabaseType() {
        return 'postgresql';
    }
    
    /**
     * Check if PostgreSQL supports a specific feature.
     * 
     * @param string $feature Feature name
     * @return bool Whether the feature is supported
     */
    public function supportsFeature($feature) {
        return isset($this->supportedFeatures[$feature]) && $this->supportedFeatures[$feature];
    }
    
    /**
     * Get PostgreSQL error information.
     * 
     * @return array Error information
     */
    public function getLastError() {
        return $this->pdo->errorInfo();
    }
    
    /**
     * Set PostgreSQL session variables.
     * 
     * @param array $variables Variable-value pairs
     * @return bool Success status
     */
    public function setSessionVariables($variables) {
        try {
            foreach ($variables as $name => $value) {
                $this->executeQuery("SET $name = ?", [$value]);
            }
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error setting PostgreSQL session variables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get PostgreSQL version information.
     * 
     * @return string PostgreSQL version
     */
    public function getVersion() {
        try {
            $stmt = $this->pdo->query("SELECT version()");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->log("Could not get PostgreSQL version: " . $e->getMessage());
            return 'Unknown';
        }
    }
    
    /**
     * Check if a table exists in PostgreSQL.
     * 
     * @param string $table Table name
     * @param string $schema Schema name (default: public)
     * @return bool Whether the table exists
     */
    public function tableExists($table, $schema = 'public') {
        try {
            $stmt = $this->executeQuery(
                "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?)",
                [$schema, $table]
            );
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->log("Error checking table existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get table columns information.
     * 
     * @param string $table Table name
     * @param string $schema Schema name (default: public)
     * @return array Column information
     */
    public function getTableColumns($table, $schema = 'public') {
        try {
            $stmt = $this->executeQuery(
                "SELECT column_name, data_type, is_nullable, column_default 
                 FROM information_schema.columns 
                 WHERE table_schema = ? AND table_name = ? 
                 ORDER BY ordinal_position",
                [$schema, $table]
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->log("Error getting table columns: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute a query with RETURNING clause (PostgreSQL-specific).
     * 
     * @param string $query Query with RETURNING clause
     * @param array $params Query parameters
     * @return array Returned values
     */
    public function executeWithReturning($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create a PostgreSQL-specific index.
     * 
     * @param string $table Table name
     * @param array $columns Columns to index
     * @param string $type Index type (btree, gin, gist, etc.)
     * @param string $name Index name (optional)
     * @return bool Success status
     */
    public function createIndex($table, $columns, $type = 'btree', $name = null) {
        if (!$name) {
            $name = $table . '_' . implode('_', $columns) . '_idx';
        }
        
        $quotedTable = $this->quoteIdentifier($table);
        $quotedName = $this->quoteIdentifier($name);
        $quotedColumns = array_map([$this, 'quoteIdentifier'], $columns);
        $columnsStr = implode(', ', $quotedColumns);
        
        $query = "CREATE INDEX $quotedName ON $quotedTable USING $type ($columnsStr)";
        
        try {
            $this->executeQuery($query);
            $this->logger->log("Created index $name on table $table");
            return true;
        } catch (PDOException $e) {
            $this->logger->log("Error creating index: " . $e->getMessage());
            return false;
        }
    }
}