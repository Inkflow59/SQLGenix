<?php

/**
 * Interface for database adapters in SQLGenix.
 * 
 * This interface defines the contract that all database adapters must implement
 * to ensure consistent behavior across different database systems.
 */
interface DatabaseAdapterInterface {
    
    /**
     * Get the database connection instance.
     * 
     * @return PDO The PDO connection instance
     */
    public function getConnection();
    
    /**
     * Execute a SQL query with parameters.
     * 
     * @param string $query The SQL query to execute
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function executeQuery($query, $params = []);
    
    /**
     * Begin a database transaction.
     * 
     * @param bool $consistent Whether to use consistent snapshot
     * @return bool Success status
     */
    public function beginTransaction($consistent = false);
    
    /**
     * Commit the current transaction.
     * 
     * @return bool Success status
     */
    public function commit();
    
    /**
     * Rollback the current transaction.
     * 
     * @return bool Success status
     */
    public function rollback();
    
    /**
     * Get the last inserted ID.
     * 
     * @param string|null $sequence Sequence name for PostgreSQL
     * @return string Last inserted ID
     */
    public function getLastInsertId($sequence = null);
    
    /**
     * Quote a string for safe SQL usage.
     * 
     * @param string $string String to quote
     * @return string Quoted string
     */
    public function quote($string);
    
    /**
     * Quote an identifier (table, column name).
     * 
     * @param string $identifier Identifier to quote
     * @return string Quoted identifier
     */
    public function quoteIdentifier($identifier);
    
    /**
     * Get the database-specific LIMIT syntax.
     * 
     * @param int|null $limit Row limit
     * @param int|null $offset Row offset
     * @return string LIMIT clause
     */
    public function getLimitClause($limit = null, $offset = null);
    
    /**
     * Get the database-specific date format function.
     * 
     * @param string $format Date format
     * @param string $column Column name
     * @return string Date format function
     */
    public function getDateFormatFunction($format, $column);
    
    /**
     * Get the database-specific concatenation function.
     * 
     * @param array $columns Columns to concatenate
     * @return string Concatenation function
     */
    public function getConcatFunction($columns);
    
    /**
     * Get the database-specific UPSERT syntax.
     * 
     * @param string $table Table name
     * @param array $columns Column names
     * @param array $updateColumns Columns to update on conflict
     * @return string UPSERT query template
     */
    public function getUpsertSyntax($table, $columns, $updateColumns);
    
    /**
     * Get the database type identifier.
     * 
     * @return string Database type (mysql, postgresql, sqlite, etc.)
     */
    public function getDatabaseType();
    
    /**
     * Check if the database supports a specific feature.
     * 
     * @param string $feature Feature name
     * @return bool Whether the feature is supported
     */
    public function supportsFeature($feature);
    
    /**
     * Get database-specific error information.
     * 
     * @return array Error information
     */
    public function getLastError();
    
    /**
     * Set database-specific session variables.
     * 
     * @param array $variables Variable-value pairs
     * @return bool Success status
     */
    public function setSessionVariables($variables);
}