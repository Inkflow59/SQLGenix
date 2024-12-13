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
     * Database connection instance.
     * @var Database
     */
    private $db;
    
    /**
     * Conditions for the select statement.
     * @var array
     */
    private $conditions = [];
    
    /**
     * Joins for the select statement.
     * @var array
     */
    private $joins = [];
    
    /**
     * Constructor
     * @param Database $db Database connection instance
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
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
     * Add an INNER JOIN to the select statement.
     * 
     * @param string $table Table to join.
     * @param string $condition Join condition.
     * @return SQLSelect
     */
    public function innerJoin($table, $condition) {
        $this->joins[] = "INNER JOIN $table ON $condition";
        return $this;
    }

    /**
     * Add a LEFT JOIN to the select statement.
     * 
     * @param string $table Table to join.
     * @param string $condition Join condition.
     * @return SQLSelect
     */
    public function leftJoin($table, $condition) {
        $this->joins[] = "LEFT JOIN $table ON $condition";
        return $this;
    }

    /**
     * Add a RIGHT JOIN to the select statement.
     * 
     * @param string $table Table to join.
     * @param string $condition Join condition.
     * @return SQLSelect
     */
    public function rightJoin($table, $condition) {
        $this->joins[] = "RIGHT JOIN $table ON $condition";
        return $this;
    }
    
    /**
     * Build the SQL select statement.
     * 
     * @return string
     */
    public function build() {
        $query = "SELECT " . implode(", ", $this->columns) . " FROM " . $this->table;
        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        return $query;
    }
    
    /**
     * Get the constructed SQL select statement.
     * 
     * @return string
     */
    public function getQuery() {
        $query = "SELECT " . implode(", ", $this->columns) . " FROM " . $this->table;
        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        return $query;
    }
    
    /**
     * Execute the SQL select statement.
     * 
     * @return mixed
     */
    public function execute() {
        $query = $this->getQuery();
        $result = $this->db->executeQuery($query);
        $this->db->getLogger()->log("Execution of query: $query"); // Log the query execution
        return $result;
    }
}