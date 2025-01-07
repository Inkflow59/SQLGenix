<?php
/**
 * Class SQLTrigger
 *
 * This class is responsible for defining SQL triggers for a database.
 * It provides methods to create, drop, and modify triggers.
 */

class SQLTrigger {
    /**
     * @var string The name of the trigger.
     */
    private $name;

    /**
     * @var string The table on which the trigger is defined.
     */
    private $table;

    /**
     * @var string The timing of the trigger (BEFORE or AFTER).
     */
    private $timing;

    /**
     * @var string The event that activates the trigger (INSERT, UPDATE, DELETE).
     */
    private $event;

    /**
     * @var string The SQL statement to be executed when the trigger is activated.
     */
    private $statement;

    /**
     * SQLTrigger constructor.
     *
     * @param string $name The name of the trigger.
     * @param string $table The table on which the trigger is defined.
     * @param string $timing The timing of the trigger (BEFORE or AFTER).
     * @param string $event The event that activates the trigger (INSERT, UPDATE, DELETE).
     * @param string $statement The SQL statement to be executed when the trigger is activated.
     */
    public function __construct($name, $table, $timing, $event, $statement) {
        $this->name = $name;
        $this->table = $table;
        $this->timing = $timing;
        $this->event = $event;
        $this->statement = $statement;
    }

    /**
     * Creates the SQL statement for the trigger.
     *
     * @return string The SQL statement to create the trigger.
     */
    public function createTrigger() {
        return "CREATE TRIGGER {$this->name} {$this->timing} {$this->event} ON {$this->table} FOR EACH ROW {$this->statement};";
    }

    /**
     * Drops the trigger if it exists.
     *
     * @return string The SQL statement to drop the trigger.
     */
    public function dropTrigger() {
        return "DROP TRIGGER IF EXISTS {$this->name};";
    }

    /**
     * Modifies the trigger with a new statement.
     *
     * @param string $newStatement The new SQL statement for the trigger.
     * @return string The SQL statement to recreate the trigger with the new statement.
     */
    public function modifyTrigger($newStatement) {
        $this->statement = $newStatement;
        return $this->createTrigger(); // Recreate the trigger with the new statement
    }
}