<?php
/**
 * SQLUpdate class to build SQL update statements.
 */
class SQLUpdate {
    /**
     * Table name for the update statement.
     * @var string
     */
    private $table;
    
    /**
     * Columns and their new values for the update statement.
     * @var array
     */
    private $updates = [];
    
    /**
     * Conditions for the update statement.
     * @var array
     */
    private $conditions = [];
    
    /**
     * Subqueries for the update statement.
     * @var array
     */
    private $subqueries = [];
    
    /**
     * Subquery for the update statement.
     * @var Subquery
     */
    private $subquery;

    /**
     * Database connection instance
     * @var Database
     */
    private $db;

    /**
     * Constructor to initialize SQLUpdate with database connection
     * @param Database $db Database connection instance
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Set the table name for the update statement.
     * 
     * @param string $table Table name.
     * @return SQLUpdate
     */
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Add a CASE statement to the SET clause of the update statement.
     * 
     * @param string $column Column name to update.
     * @param array $cases Array of when-then pairs.
     * @param string $default Default value.
     * @return SQLUpdate
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
        $this->set($column, $caseStatement);
        return $this;
    }

    /**
     * Add an IF statement to the WHERE clause.
     * 
     * @param string $condition Condition to evaluate.
     * @param string $thenResult Result if condition is true.
     * @param string $elseResult Result if condition is false.
     * @return SQLUpdate
     */
    public function if($condition, $thenResult, $elseResult = null) {
        $ifStatement = "IF ({$condition}) THEN {$thenResult} ";
        if ($elseResult !== null) {
            $ifStatement .= "ELSE {$elseResult} ";
        }
        $this->conditions[] = $ifStatement;
        return $this;
    }

    /**
     * Set the column values for the update statement.
     * 
     * @param string $column Column name.
     * @param mixed $value Value to set.
     * @return SQLUpdate
     */
    public function set($column, $value) {
        $this->updates[$column] = $value;
        return $this;
    }
    
    /**
     * Add a WHERE clause to the update statement.
     * 
     * @param string $condition Condition for the WHERE clause.
     * @return SQLUpdate
     */
    public function where($condition) {
        $this->conditions[] = $condition;
        return $this;
    }
    
    /**
     * Add a subquery to the update statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLUpdate
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
     * @return SQLUpdate
     */
    public function exists($subquery) {
        $this->conditions[] = "EXISTS ($subquery)";
        return $this;
    }
    
    /**
     * Add a subquery to the WHERE clause.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLUpdate
     */
    public function whereSubquery($subquery, $alias = null) {
        if ($alias) {
            $this->conditions[] = "($subquery) AS $alias";
        } else {
            $this->conditions[] = "($subquery)";
        }
        return $this;
    }
    
    /**
     * Set the subquery for the update statement.
     * 
     * @param Subquery $subquery Subquery instance.
     * @return SQLUpdate
     */
    public function setSubquery(Subquery $subquery) {
        $this->subquery = $subquery;
        return $this;
    }

    /**
     * Get the subquery for the update statement.
     * 
     * @return Subquery
     */
    public function getSubquery() {
        return $this->subquery;
    }

    /**
     * Build the SQL update statement.
     * 
     * @return string
     */
    public function build() {
        $setClause = implode(", ", array_map(function($col, $val) {
            return "$col = $val";
        }, array_keys($this->updates), $this->updates));
        $whereClause = implode(" AND ", $this->conditions);
        $subqueryClause = implode(", ", $this->subqueries);
        $query = "UPDATE " . $this->table . " SET " . $setClause;
        if (!empty($this->subqueries)) {
            $query .= " FROM " . $subqueryClause;
        }
        if (!empty($this->conditions)) {
            $query .= " WHERE " . $whereClause;
        }
        if ($this->subquery) {
            $query .= " WHERE EXISTS (" . $this->subquery->getQuery() . ")";
        }
        return $query;
    }
    
    /**
     * Execute the SQL update statement.
     * 
     * @return mixed
     */
    public function execute() {
        $query = $this->build();
        $result = $this->db->executeQuery($query);
        if ($result === null) {
            throw new Exception("Erreur lors de l'exécution de la requête UPDATE");
        }
        $this->db->getLogger()->log("Exécution de la requête : $query");
        return $result->rowCount();
    }
    
    /**
     * Get the constructed SQL update statement.
     * 
     * @return string
     */
    public function getQuery() {
        $setClause = implode(', ', array_map(function($col, $val) use ($this) {
            return "$col = $val";
        }, array_keys($this->updates), $this->updates));
        
        $query = 'UPDATE ' . $this->table . ' SET ' . $setClause;

        $subqueryClause = implode(", ", $this->subqueries);

        if (!empty($this->subqueries)) {
            $query .= " FROM " . $subqueryClause;
        }

        if (!empty($this->conditions)) {
            $query .= ' WHERE ' . implode(" AND ", $this->conditions);
        }

        if ($this->subquery) {
            $query .= " WHERE EXISTS (" . $this->subquery->getQuery() . ")";
        }

        return $query;
    }
}