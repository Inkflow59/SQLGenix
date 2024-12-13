<?php
class SQLUpdate {
    private $table;
    private $set = [];
    private $where = [];
    
    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function set($columns) {
        $this->set = $columns;
        return $this;
    }

    public function where($condition) {
        $this->where = $condition;
        return $this;
    }

    public function build() {
        $setClause = implode(", ", array_map(function($col, $val) {
            return "$col = ?";
        }, array_keys($this->set), $this->set));

        return "UPDATE " . $this->table . " SET " . $setClause . " WHERE " . $this->where;
    }
}