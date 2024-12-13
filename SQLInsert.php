<?php
class SQLInsert {
    private $table;
    private $columns = [];
    private $values = [];
    
    public function into($table) {
        $this->table = $table;
        return $this;
    }
    
    public function set($columns, $values) {
        $this->columns = $columns;
        $this->values = $values;
        return $this;
    }

    public function build() {
        $columns = implode(", ", $this->columns);
        $placeholders = implode(", ", array_fill(0, count($this->values), "?"));
        return "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
    }
}