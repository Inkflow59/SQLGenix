<?php
/**
 * SQLDelete class to build SQL delete statements.
 */
class SQLDelete {
    /**
     * Table name for the delete statement.
     * @var string
     */
    private $table;
    
    /**
     * Conditions for the delete statement.
     * @var array
     */
    private $where = [];
    
    /**
     * Subqueries for the delete statement.
     * @var array
     */
    private $subqueries = [];
    
    /**
     * Subquery for the delete statement.
     * @var Subquery
     */
    private $subquery;

    /**
     * Constructor to initialize SQLDelete with database connection
     * @param Database $db Database connection instance
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Set the table name for the delete statement.
     * 
     * @param string $table Table name.
     * @return SQLDelete
     */
    public function from($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Set the conditions for the delete statement.
     * 
     * @param string $condition Condition for the delete statement.
     * @return SQLDelete
     */
    public function where($condition) {
        $this->where = $condition;
        return $this;
    }
    
    /**
     * Add a subquery to the delete statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLDelete
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
     * @return SQLDelete
     */
    public function exists($subquery) {
        $this->where[] = "EXISTS ($subquery)";
        return $this;
    }
    
    /**
     * Add a subquery to the FROM clause of the delete statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLDelete
     */
    public function fromSubquery($subquery, $alias = null) {
        if ($alias) {
            $this->subqueries[] = "($subquery) AS $alias";
        } else {
            $this->subqueries[] = "($subquery)";
        }
        return $this;
    }
    
    /**
     * Add a subquery to the WHERE clause of the delete statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLDelete
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
     * Add a CASE statement to the WHERE clause.
     * 
     * @param string $column Column name to check.
     * @param array $cases Array of when-then pairs.
     * @param string $default Default value.
     * @return SQLDelete
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
        $this->where[] = "$column = $caseStatement";
        return $this;
    }

    /**
     * Add an IF statement to the WHERE clause.
     * 
     * @param string $condition Condition to evaluate.
     * @param string $thenResult Result if condition is true.
     * @param string $elseResult Result if condition is false.
     * @return SQLDelete
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
     * Set the subquery for the delete statement.
     * 
     * @param Subquery $subquery Subquery for the delete statement.
     * @return SQLDelete
     */
    public function setSubquery(Subquery $subquery) {
        $this->subquery = $subquery;
        return $this;
    }

    /**
     * Get the subquery for the delete statement.
     * 
     * @return Subquery
     */
    public function getSubquery() {
        return $this->subquery;
    }

    /**
     * Build the SQL delete statement.
     * 
     * @return string
     */
    public function build() {
        $query = "DELETE FROM " . $this->table;
        if (!empty($this->subqueries)) {
            $query .= " USING " . implode(", ", $this->subqueries);
        }
        if (!empty($this->where)) {
            $query .= " WHERE " . implode(" AND ", $this->where);
        }
        if ($this->subquery) {
            $query .= " WHERE EXISTS " . $this->subquery;
        }
        return $query;
    }
    
    /**
     * Execute the SQL delete statement.
     * 
     * @param Database $db Database connection object.
     * @return mixed
     */
    public function execute(Database $db) {
        $query = $this->getQuery();
        $result = $db->executeQuery($query);
        $db->getLogger()->log("Execution of query: $query");
        return $result;
    }

    /**
     * Get the constructed SQL delete statement.
     * 
     * @return string
     */
    public function getQuery() {
        return $this->build();
    }
}