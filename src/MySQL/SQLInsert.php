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
     * Subqueries for the insert statement.
     * @var array
     */
    private $subqueries = [];
    
    /**
     * WHERE clause conditions for the insert statement.
     * @var array
     */
    private $where = [];
    
    /**
     * Subquery for the insert statement.
     * @var Subquery
     */
    private $subquery;

    /**
     * Constructor to initialize SQLInsert with database connection
     * @param Database $db Database connection instance
     */
    public function __construct($db) {
        $this->db = $db;
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
     * Add a subquery to the insert statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLInsert
     */
    public function subquery($subquery, $alias = null) {
        if ($alias) {
            $this->subqueries[] = "($subquery) AS $alias";
        } else {
            $this->subqueries[] = "($subquery)";
        }
        return $this;
    }
    
    /**
     * Add an EXISTS condition to the WHERE clause.
     * 
     * @param string $subquery The subquery to check for existence.
     * @return SQLInsert
     */
    public function exists($subquery) {
        $this->where[] = "EXISTS ($subquery)";
        return $this;
    }
    
    /**
     * Add an EXISTS condition to the WHERE clause.
     * 
     * @param string $subquery The subquery to check for existence.
     * @param string $alias Optional alias for the subquery.
     * @return SQLInsert
     */
    public function existsWithAlias($subquery, $alias) {
        $this->where[] = "EXISTS ($subquery) AS $alias";
        return $this;
    }
    
    /**
     * Add a CASE statement to the insert statement.
     * 
     * @param string $column Column name to check.
     * @param array $cases Array of when-then pairs.
     * @param string $default Default value.
     * @return SQLInsert
     */
    public function case($column, $cases, $default = null) {
        $caseStatement = "CASE ";
        foreach ($cases as $when => $then) {
            $caseStatement .= "WHEN {$when} THEN {$then} ";
        }
        if ($default !== null) {
            $caseStatement .= "ELSE {$default} ";
        }
        $caseStatement .= "END";
        $this->values[$column] = $caseStatement;
        return $this;
    }

    /**
     * Add an IF statement to the WHERE clause.
     * 
     * @param string $condition Condition to evaluate.
     * @param string $thenResult Result if condition is true.
     * @param string $elseResult Result if condition is false.
     * @return SQLInsert
     */
    public function if($condition, $thenResult, $elseResult = null) {
        $ifStatement = "IF ({$condition}) THEN {$thenResult} ";
        if ($elseResult !== null) {
            $ifStatement .= "ELSE {$elseResult} ";
        }
        $this->where[] = $ifStatement;
        return $this;
    }
    
    /**
     * Add a subquery to the WHERE clause.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLInsert
     */
    public function whereSubquery($subquery, $alias = null) {
        if ($alias) {
            $this->where[] = "($subquery) AS $alias";
        } else {
            $this->where[] = "($subquery)";
        }
        return $this;
    }
    
    /**
     * Set the subquery for the insert statement.
     * 
     * @param Subquery $subquery Subquery instance.
     * @return SQLInsert
     */
    public function setSubquery(Subquery $subquery) {
        $this->subquery = $subquery;
        return $this;
    }
    
    /**
     * Get the subquery for the insert statement.
     * 
     * @return Subquery
     */
    public function getSubquery() {
        return $this->subquery;
    }
    
    /**
     * Build the SQL insert statement.
     * 
     * @return string
     */
    public function build() {
        $columns = implode(", ", $this->columns);
        $placeholders = implode(", ", array_fill(0, count($this->values), "?"));
        $subqueries = implode(", ", $this->subqueries);
        $where = implode(" AND ", $this->where);
        if (!empty($subqueries)) {
            $columns .= ", $subqueries";
        }
        $query = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        if ($this->subquery) {
            $query .= " SELECT " . $this->subquery;
        }
        return $query;
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
     * @param Database $db Database connection object.
     * @return mixed
     */
    public function execute(Database $db) {
        $query = $this->getQuery();
        $result = $db->executeQuery($query);
        $db->getLogger()->log("Execution of query: $query"); // Log the query execution
        return $result;
    }
}