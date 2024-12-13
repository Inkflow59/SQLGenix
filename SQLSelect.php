<?php
class SQLSelect {
    private $columns = [];
    private $table;
    private $conditions = [];
    
    public function select($columns) {
        $this->columns = $columns;
        return $this;
    }
    
    public function from($table) {
        $this->table = $table;
        return $this;
    }
    
    public function where($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function build() {
        $query = "SELECT " . implode(", ", $this->columns) . " FROM " . $this->table;
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }
        return $query;
    }
}
