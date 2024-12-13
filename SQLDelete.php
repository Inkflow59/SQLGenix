<?php
class SQLDelete {
    private $table;
    private $where = [];
    
    public function from($table) {
        $this->table = $table;
        return $this;
    }

    public function where($condition) {
        $this->where = $condition;
        return $this;
    }

    public function build() {
        return "DELETE FROM " . $this->table . " WHERE " . $this->where;
    }
}