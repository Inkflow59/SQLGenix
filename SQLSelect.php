<?php
/**
 * SQLSelect class to build SQL select statements.
 */
class SQLSelect {
    /**
     * Columns to be selected.
     * @var array
     */
    private $columns = [];
    
    /**
     * Table name for the select statement.
     * @var string
     */
    private $table;
    
    /**
     * Conditions for the select statement.
     * @var array
     */
    private $conditions = [];
    
    /**
     * Set the columns to be selected.
     * 
     * @param array $columns Columns to select.
     * @return SQLSelect
     */
    public function select($columns) {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * Set the table name for the select statement.
     * 
     * @param string $table Table name.
     * @return SQLSelect
     */
    public function from($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Add a condition to the select statement.
     * 
     * @param string $condition Condition to add.
     * @return SQLSelect
     */
    public function where($condition) {
        $this->conditions[] = $condition;
        return $this;
    }
    
    /**
     * Build the SQL select statement.
     * 
     * @return string
     */
    public function build() {
        $query = "SELECT " . implode(", ", $this->columns) . " FROM " . $this->table;
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        return $query;
    }
    
    /**
     * Execute the SQL select statement.
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
    
    /**
     * Get the constructed SQL select statement.
     * 
     * @return string
     */
    public function getQuery() {
        $query = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;
        if (!empty($this->conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $this->conditions);
        }
        return $query;
    }
}
