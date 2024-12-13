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
    private $set = [];
    
    /**
     * Conditions for the update statement.
     * @var array
     */
    private $where = [];
    
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
     * Set the columns and their new values for the update statement.
     * 
     * @param array $columns Columns and their new values.
     * @return SQLUpdate
     */
    public function set($columns) {
        $this->set = $columns;
        return $this;
    }
    
    /**
     * Set the conditions for the update statement.
     * 
     * @param string $condition Condition for the update statement.
     * @return SQLUpdate
     */
    public function where($condition) {
        $this->where = $condition;
        return $this;
    }
    
    /**
     * Build the SQL update statement.
     * 
     * @return string
     */
    public function build() {
        $setClause = implode(", ", array_map(function($col, $val) {
            return "$col = ?";
        }, array_keys($this->set), $this->set));

        return "UPDATE " . $this->table . " SET " . $setClause . " WHERE " . $this->where;
    }
    
    /**
     * Execute the SQL update statement.
     * 
     * @param DatabaseConnexion $db Database connection object.
     * @return mixed
     */
    public function execute(DatabaseConnexion $db) {
        $query = $this->build();
        $params = array_values($this->set);
        $result = $db->executeQuery($query, $params);
        if ($result === null) {
            throw new Exception("Erreur lors de l'exécution de la requête UPDATE");
        }
        $db->getLogger()->log("Exécution de la requête : $query");
        return $result->rowCount();
    }
    
    /**
     * Get the constructed SQL update statement.
     * 
     * @return string
     */
    public function getQuery() {
        $setClause = implode(', ', array_map(function($col, $val) use ($this) {
            return "$col = '$val'";
        }, array_keys($this->set), $this->set));
        
        $query = 'UPDATE ' . $this->table . ' SET ' . $setClause;
        if (!empty($this->where)) {
            $query .= ' WHERE ' . $this->where;
        }
        return $query;
    }
}