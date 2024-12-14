<?php

/**
 * Class Subquery
 *
 * This class represents a SQL subquery, allowing for the construction of subqueries with conditions.
 * It provides methods to add conditions and build the final SQL query string.
 */
class Subquery {
    /**
     * The SQL query string for the subquery.
     * @var string
     */
    private $query;

    /**
     * Conditions for the subquery.
     * @var array
     */
    private $conditions = [];

    /**
     * Order by clause.
     * @var array
     */
    private $orderBy = [];

    /**
     * Subquery constructor.
     *
     * @param string $query The SQL query string for the subquery.
     */
    public function __construct($query) {
        $this->query = $query;
    }

    /**
     * Get the SQL query string.
     *
     * @return string The SQL query string.
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Set the main query string directly.
     *
     * @param string $query The SQL query string for the subquery.
     * @return Subquery
     */
    public function setQuery($query) {
        if (empty($query)) {
            throw new InvalidArgumentException('Query cannot be empty.');
        }
        $this->query = $query;
        return $this;
    }

    /**
     * Validate the conditions before adding them.
     *
     * @param string $condition The condition to validate.
     * @return bool
     */
    private function validateCondition($condition) {
        // Simple validation: Check if the condition is not empty.
        return !empty($condition);
    }

    /**
     * Add a condition to the subquery with validation.
     *
     * @param string $condition The condition to add.
     * @return Subquery
     * @throws InvalidArgumentException
     */
    public function addCondition($condition) {
        if (!$this->validateCondition($condition)) {
            throw new InvalidArgumentException('Invalid condition provided.');
        }
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * Get all conditions associated with the subquery.
     *
     * @return array
     */
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * Add an ORDER BY clause to the subquery.
     *
     * @param string $column Column name to order by.
     * @param string $direction Direction of ordering (ASC or DESC).
     * @return Subquery
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    /**
     * Add a nested subquery.
     *
     * @param Subquery $subquery The nested subquery.
     * @return Subquery
     */
    public function addNestedSubquery(Subquery $subquery) {
        $this->query = "(" . $subquery->build() . ")";
        return $this;
    }

    /**
     * Build the final SQL query string.
     *
     * @return string
     */
    public function build() {
        $query = $this->query;
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(' AND ', $this->conditions);
        }
        if (!empty($this->orderBy)) {
            $query .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        return $query;
    }

    /**
     * String representation of the subquery.
     *
     * @return string
     */
    public function __toString() {
        return $this->build();
    }
}

?>
