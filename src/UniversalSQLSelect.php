<?php
require_once 'QueryCache.php';
require_once 'Interfaces/DatabaseAdapterInterface.php';

/**
 * Universal SQLSelect class that works with any database adapter.
 * 
 * This class builds SQL SELECT statements in a database-agnostic way,
 * using the adapter pattern to handle database-specific syntax differences.
 */
class UniversalSQLSelect {
    
    /**
     * @var DatabaseAdapterInterface Database adapter instance
     */
    private $adapter;
    
    /**
     * @var QueryCache Query cache instance
     */
    private $cache;
    
    /**
     * @var array Columns to be selected
     */
    private $columns = [];
    
    /**
     * @var string Table name for the select statement
     */
    private $table;
    
    /**
     * @var array Conditions for the select statement
     */
    private $conditions = [];
    
    /**
     * @var array Joins for the select statement
     */
    private $joins = [];
    
    /**
     * @var array Columns for the GROUP BY clause
     */
    private $groupByColumns = [];
    
    /**
     * @var string Condition for the HAVING clause
     */
    private $havingCondition = '';
    
    /**
     * @var array Columns for the ORDER BY clause
     */
    private $orderByColumns = [];
    
    /**
     * @var string Sort direction for the ORDER BY clause
     */
    private $orderByDirection = 'ASC';
    
    /**
     * @var int|null Limit for the select statement
     */
    private $limit = null;
    
    /**
     * @var int|null Offset for the select statement
     */
    private $offset = null;
    
    /**
     * @var array Union queries for the select statement
     */
    private $unions = [];
    
    /**
     * @var array Subqueries for the select statement
     */
    private $subqueries = [];
    
    /**
     * Constructor
     * 
     * @param DatabaseAdapterInterface $adapter Database adapter instance
     * @param QueryCache|null $cache Optional query cache instance
     */
    public function __construct(DatabaseAdapterInterface $adapter, QueryCache $cache = null) {
        $this->adapter = $adapter;
        $this->cache = $cache;
    }
    
    /**
     * Set the columns to be selected.
     * 
     * @param array $columns Columns to select
     * @return UniversalSQLSelect
     */
    public function select($columns) {
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }
    
    /**
     * Set the table name for the select statement.
     * 
     * @param string $table Table name
     * @return UniversalSQLSelect
     */
    public function from($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Add a condition to the select statement.
     * 
     * @param string $condition Condition to add
     * @return UniversalSQLSelect
     */
    public function where($condition) {
        $this->conditions[] = $condition;
        return $this;
    }
    
    /**
     * Add multiple WHERE conditions with AND.
     * 
     * @param array $conditions Array of conditions
     * @return UniversalSQLSelect
     */
    public function whereAnd($conditions) {
        foreach ($conditions as $condition) {
            $this->where($condition);
        }
        return $this;
    }
    
    /**
     * Add a WHERE IN condition.
     * 
     * @param string $column Column name
     * @param array $values Values for IN clause
     * @return UniversalSQLSelect
     */
    public function whereIn($column, $values) {
        $quotedColumn = $this->adapter->quoteIdentifier($column);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $this->conditions[] = "$quotedColumn IN ($placeholders)";
        return $this;
    }
    
    /**
     * Add a WHERE BETWEEN condition.
     * 
     * @param string $column Column name
     * @param mixed $start Start value
     * @param mixed $end End value
     * @return UniversalSQLSelect
     */
    public function whereBetween($column, $start, $end) {
        $quotedColumn = $this->adapter->quoteIdentifier($column);
        $this->conditions[] = "$quotedColumn BETWEEN ? AND ?";
        return $this;
    }
    
    /**
     * Add an INNER JOIN to the select statement.
     * 
     * @param string $table Table to join
     * @param string $condition Join condition
     * @return UniversalSQLSelect
     */
    public function innerJoin($table, $condition) {
        $quotedTable = $this->adapter->quoteIdentifier($table);
        $this->joins[] = "INNER JOIN $quotedTable ON $condition";
        return $this;
    }
    
    /**
     * Add a LEFT JOIN to the select statement.
     * 
     * @param string $table Table to join
     * @param string $condition Join condition
     * @return UniversalSQLSelect
     */
    public function leftJoin($table, $condition) {
        $quotedTable = $this->adapter->quoteIdentifier($table);
        $this->joins[] = "LEFT JOIN $quotedTable ON $condition";
        return $this;
    }
    
    /**
     * Add a RIGHT JOIN to the select statement.
     * 
     * @param string $table Table to join
     * @param string $condition Join condition
     * @return UniversalSQLSelect
     */
    public function rightJoin($table, $condition) {
        $quotedTable = $this->adapter->quoteIdentifier($table);
        $this->joins[] = "RIGHT JOIN $quotedTable ON $condition";
        return $this;
    }
    
    /**
     * Add a GROUP BY clause to the select statement.
     * 
     * @param array $columns Columns to group by
     * @return UniversalSQLSelect
     */
    public function groupBy($columns) {
        $this->groupByColumns = is_array($columns) ? $columns : [$columns];
        return $this;
    }
    
    /**
     * Add a HAVING clause to the select statement.
     * 
     * @param string $condition Condition for the HAVING clause
     * @return UniversalSQLSelect
     */
    public function having($condition) {
        $this->havingCondition = $condition;
        return $this;
    }
    
    /**
     * Add an ORDER BY clause to the select statement.
     * 
     * @param array|string $columns Columns to order by
     * @param string $direction Sort direction (ASC or DESC)
     * @return UniversalSQLSelect
     */
    public function orderBy($columns, $direction = 'ASC') {
        $this->orderByColumns = is_array($columns) ? $columns : [$columns];
        $this->orderByDirection = strtoupper($direction);
        return $this;
    }
    
    /**
     * Add a LIMIT clause to the select statement.
     * 
     * @param int $limit Maximum number of records to return
     * @return UniversalSQLSelect
     */
    public function limit($limit) {
        $this->limit = (int)$limit;
        return $this;
    }
    
    /**
     * Add an OFFSET clause to the select statement.
     * 
     * @param int $offset Number of records to skip
     * @return UniversalSQLSelect
     */
    public function offset($offset) {
        $this->offset = (int)$offset;
        return $this;
    }
    
    /**
     * Add pagination to the select statement.
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Number of records per page
     * @return UniversalSQLSelect
     */
    public function paginate($page, $perPage) {
        $this->limit = (int)$perPage;
        $this->offset = ((int)$page - 1) * (int)$perPage;
        return $this;
    }
    
    /**
     * Add a UNION clause to combine with another query.
     * 
     * @param UniversalSQLSelect|string $query Another query instance or raw SQL
     * @param bool $all Whether to use UNION ALL
     * @return UniversalSQLSelect
     */
    public function union($query, $all = false) {
        $unionType = $all ? 'UNION ALL' : 'UNION';
        
        if ($query instanceof UniversalSQLSelect) {
            $this->unions[] = $unionType . ' (' . $query->build() . ')';
        } else {
            $this->unions[] = $unionType . ' (' . $query . ')';
        }
        
        return $this;
    }
    
    /**
     * Add a UNION ALL clause to combine with another query.
     * 
     * @param UniversalSQLSelect|string $query Another query instance or raw SQL
     * @return UniversalSQLSelect
     */
    public function unionAll($query) {
        return $this->union($query, true);
    }
    
    /**
     * Add database-specific date formatting.
     * 
     * @param string $column Date column name
     * @param string $format Date format
     * @param string $alias Column alias
     * @return UniversalSQLSelect
     */
    public function selectDateFormat($column, $format, $alias) {
        $dateFunc = $this->adapter->getDateFormatFunction($format, $column);
        $this->columns[] = "$dateFunc AS " . $this->adapter->quoteIdentifier($alias);
        return $this;
    }
    
    /**
     * Add database-specific concatenation.
     * 
     * @param array $columns Columns to concatenate
     * @param string $alias Column alias
     * @return UniversalSQLSelect
     */
    public function selectConcat($columns, $alias) {
        $concatFunc = $this->adapter->getConcatFunction($columns);
        $this->columns[] = "$concatFunc AS " . $this->adapter->quoteIdentifier($alias);
        return $this;
    }
    
    /**
     * Generate a unique key for the current query builder state.
     * 
     * @return string Unique key representing the current query state
     */
    private function generateCacheKey() {
        $state = [
            'adapter_type' => $this->adapter->getDatabaseType(),
            'columns' => $this->columns,
            'table' => $this->table,
            'conditions' => $this->conditions,
            'joins' => $this->joins,
            'groupByColumns' => $this->groupByColumns,
            'havingCondition' => $this->havingCondition,
            'orderByColumns' => $this->orderByColumns,
            'orderByDirection' => $this->orderByDirection,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'unions' => $this->unions,
            'subqueries' => $this->subqueries
        ];
        return md5(serialize($state));
    }
    
    /**
     * Build the SQL select statement using the adapter.
     * 
     * @return string Complete SQL query
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
        
        // Build the main SELECT clause
        $columnsStr = empty($this->columns) ? '*' : implode(', ', array_merge($this->columns, $this->subqueries));
        $quotedTable = $this->adapter->quoteIdentifier($this->table);
        $query = "SELECT $columnsStr FROM $quotedTable";
        
        // Add JOINs
        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }
        
        // Add WHERE conditions
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        
        // Add GROUP BY
        if (!empty($this->groupByColumns)) {
            $quotedGroupCols = array_map([$this->adapter, 'quoteIdentifier'], $this->groupByColumns);
            $query .= " GROUP BY " . implode(", ", $quotedGroupCols);
        }
        
        // Add HAVING
        if (!empty($this->havingCondition)) {
            $query .= " HAVING " . $this->havingCondition;
        }
        
        // Add UNIONs
        if (!empty($this->unions)) {
            $query .= ' ' . implode(' ', $this->unions);
        }
        
        // Add ORDER BY
        if (!empty($this->orderByColumns)) {
            $quotedOrderCols = array_map([$this->adapter, 'quoteIdentifier'], $this->orderByColumns);
            $query .= " ORDER BY " . implode(", ", $quotedOrderCols) . " " . $this->orderByDirection;
        }
        
        // Add LIMIT and OFFSET using adapter-specific syntax
        $query .= $this->adapter->getLimitClause($this->limit, $this->offset);
        
        // Cache the built query if cache is available
        if ($this->cache && $this->cache->isEnabled()) {
            $this->cache->cacheQuery($cacheKey, $query);
        }
        
        return $query;
    }
    
    /**
     * Get the constructed SQL select statement.
     * 
     * @return string SQL query
     */
    public function getQuery() {
        return $this->build();
    }
    
    /**
     * Execute the SQL select statement.
     * 
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function execute($params = []) {
        $query = $this->getQuery();
        
        // Check result cache first if available
        if ($this->cache && $this->cache->isEnabled()) {
            $cachedResult = $this->cache->getCachedResult($query, $params);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        
        $result = $this->adapter->executeQuery($query, $params);
        
        // Cache the result if cache is available
        if ($this->cache && $this->cache->isEnabled()) {
            $this->cache->cacheResult($query, $params, $result);
        }
        
        return $result;
    }
    
    /**
     * Get the database adapter instance.
     * 
     * @return DatabaseAdapterInterface Database adapter
     */
    public function getAdapter() {
        return $this->adapter;
    }
    
    /**
     * Set the query cache instance.
     * 
     * @param QueryCache $cache Query cache instance
     * @return UniversalSQLSelect
     */
    public function setCache(QueryCache $cache) {
        $this->cache = $cache;
        return $this;
    }
    
    /**
     * Get the query cache instance.
     * 
     * @return QueryCache|null Query cache instance
     */
    public function getCache() {
        return $this->cache;
    }
    
    /**
     * Reset the query builder to initial state.
     * 
     * @return UniversalSQLSelect
     */
    public function reset() {
        $this->columns = [];
        $this->table = null;
        $this->conditions = [];
        $this->joins = [];
        $this->groupByColumns = [];
        $this->havingCondition = '';
        $this->orderByColumns = [];
        $this->orderByDirection = 'ASC';
        $this->limit = null;
        $this->offset = null;
        $this->unions = [];
        $this->subqueries = [];
        
        return $this;
    }
}