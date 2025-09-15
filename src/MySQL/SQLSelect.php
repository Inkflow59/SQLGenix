<?php
require_once '../QueryCache.php';
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
     * Columns for the GROUP BY clause.
     * @var array
     */
    private $groupByColumns = [];
    
    /**
     * Condition for the HAVING clause.
     * @var string
     */
    private $havingCondition = '';
    
    /**
     * Columns for the ORDER BY clause.
     * @var array
     */
    private $orderByColumns = [];
    
    /**
     * Sort direction for the ORDER BY clause.
     * @var string
     */
    private $orderByDirection = '';
    
    /**
     * Subqueries for the select statement.
     * @var array
     */
    private $subqueries = [];

    /**
     * Subquery instance.
     * @var Subquery
     */
    private $subquery;

    /**
     * Limit for the select statement.
     * @var int
     */
    private $limit = null;
    
    /**
     * Offset for the select statement.
     * @var int
     */
    private $offset = null;

    /**
     * Query cache instance.
     * @var QueryCache
     */
    private $cache = null;

    /**
     * Union queries for the select statement.
     * @var array
     */
    private $unions = [];

    /**
     * Constructor
     * @param Database $db Database connection instance
     * @param QueryCache $cache Optional query cache instance
     */
    public function __construct(Database $db, QueryCache $cache = null) {
        $this->db = $db;
        $this->cache = $cache;
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
     * Add a GROUP BY clause to the select statement.
     * 
     * @param array $columns Columns to group by.
     * @return SQLSelect
     */
    public function groupBy($columns) {
        $this->groupByColumns = $columns;
        return $this;
    }

    /**
     * Add a HAVING clause to the select statement.
     * 
     * @param string $condition Condition for the HAVING clause.
     * @return SQLSelect
     */
    public function having($condition) {
        $this->havingCondition = $condition;
        return $this;
    }
    
    /**
     * Add an ORDER BY clause to the select statement.
     * 
     * @param array $columns Columns to order by.
     * @param string $direction Sort direction (ASC or DESC).
     * @return SQLSelect
     */
    public function orderBy($columns, $direction = 'ASC') {
        $this->orderByColumns = $columns;
        $this->orderByDirection = strtoupper($direction);
        return $this;
    }
    
    /**
     * Add a LIMIT clause to the select statement.
     * 
     * @param int $limit Maximum number of records to return.
     * @return SQLSelect
     */
    public function limit($limit) {
        $this->limit = (int)$limit;
        return $this;
    }
    
    /**
     * Add an OFFSET clause to the select statement.
     * 
     * @param int $offset Number of records to skip.
     * @return SQLSelect
     */
    public function offset($offset) {
        $this->offset = (int)$offset;
        return $this;
    }
    
    /**
     * Add pagination to the select statement.
     * 
     * @param int $page Page number (1-based).
     * @param int $perPage Number of records per page.
     * @return SQLSelect
     */
    public function paginate($page, $perPage) {
        $this->limit = (int)$perPage;
        $this->offset = ((int)$page - 1) * (int)$perPage;
        return $this;
    }
    
    /**
     * Add a UNION clause to combine with another query.
     * 
     * @param SQLSelect|string $query Another SQLSelect instance or raw SQL query
     * @param bool $all Whether to use UNION ALL (default: false for UNION)
     * @return SQLSelect
     */
    public function union($query, $all = false) {
        $unionType = $all ? 'UNION ALL' : 'UNION';
        
        if ($query instanceof SQLSelect) {
            $this->unions[] = $unionType . ' (' . $query->build() . ')';
        } else {
            $this->unions[] = $unionType . ' (' . $query . ')';
        }
        
        return $this;
    }
    
    /**
     * Add a UNION ALL clause to combine with another query.
     * 
     * @param SQLSelect|string $query Another SQLSelect instance or raw SQL query
     * @return SQLSelect
     */
    public function unionAll($query) {
        return $this->union($query, true);
    }
    
    /**
     * Add a subquery to the select statement.
     * 
     * @param string $subquery The subquery to include.
     * @param string $alias Optional alias for the subquery.
     * @return SQLSelect
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
     * @return SQLSelect
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
     * @return SQLSelect
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
     * Set the subquery instance.
     * 
     * @param Subquery $subquery Subquery instance.
     * @return SQLSelect
     */
    public function setSubquery(Subquery $subquery) {
        $this->subquery = $subquery;
        return $this;
    }

    /**
     * Get the subquery instance.
     * 
     * @return Subquery
     */
    public function getSubquery() {
        return $this->subquery;
    }

    /**
     * Generate a unique key for the current query builder state.
     * 
     * @return string Unique key representing the current query state
     */
    private function generateCacheKey() {
        $state = [
            'columns' => $this->columns,
            'table' => $this->table,
            'conditions' => $this->conditions,
            'joins' => $this->joins,
            'groupByColumns' => $this->groupByColumns,
            'havingCondition' => $this->havingCondition,
            'orderByColumns' => $this->orderByColumns,
            'orderByDirection' => $this->orderByDirection,
            'subqueries' => $this->subqueries,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'unions' => $this->unions
        ];
        return md5(serialize($state));
    }

    /**
     * Build the SQL select statement.
     * 
     * @return string
     */
    public function build() {
        // Check cache first if available
        if ($this->cache && $this->cache->isEnabled()) {
            $cacheKey = $this->generateCacheKey();
            $cachedQuery = $this->cache->getCachedQuery($cacheKey);
            if ($cachedQuery !== null) {
                return $cachedQuery;
            }
        }
        
        $query = "SELECT " . implode(", ", array_merge($this->columns, $this->subqueries)) . " FROM " . $this->table;
        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        if ($this->subquery) {
            $query .= " WHERE EXISTS (" . $this->subquery->build() . ")";
        }
        if (!empty($this->groupByColumns)) {
            $query .= " GROUP BY " . implode(", ", $this->groupByColumns);
        }
        if (!empty($this->havingCondition)) {
            $query .= " HAVING " . $this->havingCondition;
        }
        
        // Add UNION clauses
        if (!empty($this->unions)) {
            $query .= ' ' . implode(' ', $this->unions);
        }
        
        if (!empty($this->orderByColumns)) {
            $query .= " ORDER BY " . implode(", ", $this->orderByColumns) . " " . $this->orderByDirection;
        }
        if ($this->limit !== null) {
            $query .= " LIMIT " . $this->limit;
        }
        if ($this->offset !== null) {
            $query .= " OFFSET " . $this->offset;
        }
        
        // Cache the built query if cache is available
        if ($this->cache && $this->cache->isEnabled()) {
            $this->cache->cacheQuery($cacheKey, $query);
        }
        
        return $query;
    }
    
    /**
     * Get the constructed SQL select statement.
     * 
     * @return string
     */
    public function getQuery() {
        return $this->build();
    }
    
    /**
     * Execute the SQL select statement.
     * 
     * @return mixed
     */
    public function execute() {
        $query = $this->getQuery();
        
        // Check result cache first if available
        if ($this->cache && $this->cache->isEnabled()) {
            $cachedResult = $this->cache->getCachedResult($query, []);
            if ($cachedResult !== null) {
                $this->db->getLogger()->log("Cache hit for query: $query");
                return $cachedResult;
            }
        }
        
        $result = $this->db->executeQuery($query);
        $this->db->getLogger()->log("Execution of query: $query"); // Log the query execution
        
        // Cache the result if cache is available
        if ($this->cache && $this->cache->isEnabled()) {
            $this->cache->cacheResult($query, [], $result);
        }
        
        return $result;
    }

    /**
     * Set the query cache instance.
     * 
     * @param QueryCache $cache Query cache instance
     * @return SQLSelect
     */
    public function setCache(QueryCache $cache) {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Get the query cache instance.
     * 
     * @return QueryCache|null
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Add a CASE statement to the select statement.
     * 
     * @param string $column Column name.
     * @param array $cases Array of when-then pairs.
     * @param string $default Default value.
     * @return SQLSelect
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
        $this->select([$column => $caseStatement]);
        return $this;
    }

    /**
     * Add an IF statement to the WHERE clause.
     * 
     * @param string $condition Condition to evaluate.
     * @param string $thenResult Result if condition is true.
     * @param string $elseResult Result if condition is false.
     * @return SQLSelect
     */
    public function if($condition, $thenResult, $elseResult = null) {
        $ifStatement = "IF ({$condition}) THEN {$thenResult} ";
        if ($elseResult !== null) {
            $ifStatement .= "ELSE {$elseResult} ";
        }
        $this->conditions[] = $ifStatement;
        return $this;
    }
}