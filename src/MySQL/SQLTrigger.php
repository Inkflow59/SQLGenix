<?php
/**
 * Class SQLTrigger
 *
 * This class is responsible for defining SQL triggers for a database.
 * It provides methods to create, drop, and modify triggers.
 */

class SQLTrigger {
    private $name;
    private $table;
    private $timing; // BEFORE or AFTER
    private $event;  // INSERT, UPDATE, DELETE
    private $statement;

    public function __construct($name, $table, $timing, $event, $statement) {
        $this->name = $name;
        $this->table = $table;
        $this->timing = $timing;
        $this->event = $event;
        $this->statement = $statement;
    }

    public function createTrigger() {
        return "CREATE TRIGGER {$this->name} {$this->timing} {$this->event} ON {$this->table} FOR EACH ROW {$this->statement};";
    }

    public function dropTrigger() {
        return "DROP TRIGGER IF EXISTS {$this->name};";
    }

    public function modifyTrigger($newStatement) {
        $this->statement = $newStatement;
        return $this->createTrigger(); // Recreate the trigger with the new statement
    }
}