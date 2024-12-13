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
     * Build the SQL delete statement.
     * 
     * @return string
     */
    public function build() {
        return "DELETE FROM " . $this->table . " WHERE " . $this->where;
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