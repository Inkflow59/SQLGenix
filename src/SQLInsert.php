<?php
/**
 * SQLInsert class to build SQL insert statements.
 */
class SQLInsert {
    /**
     * Table name for the insert statement.
     * @var string
     */
    private $table;
    
    /**
     * Columns for the insert statement.
     * @var array
     */
    private $columns = [];
    
    /**
     * Values for the insert statement.
     * @var array
     */
    private $values = [];
    
    /**
     * Constructor to initialize properties if needed.
     * 
     * @throws Exception If an error occurs during initialization.
     */
    public function __construct() {
        try {
            // Initialize properties if needed
        } catch (Exception $e) {
            throw new Exception('Error during initialization: ' . $e->getMessage());
        }
    }
    
    /**
     * Set the table name for the insert statement.
     * 
     * @param string $table Table name.
     * @return SQLInsert
     * @throws InvalidArgumentException If table name is empty.
     * @throws Exception If an error occurs during table name setting.
     */
    public function into($table) {
        try {
            if (empty($table)) {
                throw new InvalidArgumentException('Table name cannot be empty.');
            }
            $this->table = $table;
            return $this;
        } catch (Exception $e) {
            throw new Exception('Error setting table name: ' . $e->getMessage());
        }
    }
    
    /**
     * Set the columns and values for the insert statement.
     * 
     * @param array $columns Columns for the insert statement.
     * @param array $values Values for the insert statement.
     * @return SQLInsert
     * @throws InvalidArgumentException If columns and values count do not match.
     * @throws Exception If an error occurs during columns and values setting.
     */
    public function set($columns, $values) {
        try {
            if (count($columns) !== count($values)) {
                throw new InvalidArgumentException('Columns and values count must match.');
            }
            $this->columns = $columns;
            $this->values = $values;
            return $this;
        } catch (Exception $e) {
            throw new Exception('Error setting columns and values: ' . $e->getMessage());
        }
    }
    
    /**
     * Build the SQL insert statement.
     * 
     * @return string
     */
    public function build() {
        $columns = implode(", ", $this->columns);
        $placeholders = implode(", ", array_fill(0, count($this->values), "?"));
        return "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
    }
    
    /**
     * Get the constructed SQL insert statement.
     * 
     * @return string
     */
    public function getQuery() {
        return $this->build();
    }
    
    /**
     * Execute the SQL insert statement.
     * 
     * @param DatabaseConnexion $db Database connection object.
     * @return mixed
     */
    public function execute(DatabaseConnexion $db) {
        $query = $this->getQuery();
        $result = $db->executeQuery($query);
        $db->getLogger()->log("Execution of query: $query"); // Log the query execution
        return $result;
    }
}