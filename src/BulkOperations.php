<?php

/**
 * BulkOperations class for handling batch INSERT, UPDATE, and DELETE operations.
 * 
 * This class provides efficient methods for performing bulk database operations
 * which are much faster than individual operations when dealing with large datasets.
 */
class BulkOperations {
    /**
     * Database connection instance.
     * @var Database
     */
    private $db;
    
    /**
     * Maximum number of rows to process in a single batch.
     * @var int
     */
    private $batchSize = 1000;
    
    /**
     * Whether to use transactions for bulk operations.
     * @var bool
     */
    private $useTransactions = true;
    
    /**
     * Constructor
     * 
     * @param Database $db Database connection instance
     * @param int $batchSize Maximum batch size (default: 1000)
     * @param bool $useTransactions Whether to use transactions (default: true)
     */
    public function __construct(Database $db, $batchSize = 1000, $useTransactions = true) {
        $this->db = $db;
        $this->batchSize = $batchSize;
        $this->useTransactions = $useTransactions;
    }
    
    /**
     * Perform bulk INSERT operation.
     * 
     * @param string $table Table name
     * @param array $columns Column names
     * @param array $data Array of data rows to insert
     * @param bool $ignore Whether to use INSERT IGNORE
     * @param string $onDuplicate ON DUPLICATE KEY UPDATE clause
     * @return array Results with success count and any errors
     */
    public function bulkInsert($table, $columns, $data, $ignore = false, $onDuplicate = null) {
        $results = [
            'total_rows' => count($data),
            'successful_batches' => 0,
            'failed_batches' => 0,
            'errors' => [],
            'inserted_rows' => 0
        ];
        
        if (empty($data)) {
            return $results;
        }
        
        // Validate that all rows have the same number of columns
        $columnCount = count($columns);
        foreach ($data as $index => $row) {
            if (count($row) !== $columnCount) {
                $results['errors'][] = "Row $index has " . count($row) . " values but expected $columnCount";
                return $results;
            }
        }
        
        $batches = array_chunk($data, $this->batchSize);
        $insertType = $ignore ? 'INSERT IGNORE' : 'INSERT';
        $columnsStr = implode(', ', array_map(function($col) {
            return "`$col`";
        }, $columns));
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                if ($this->useTransactions) {
                    $this->db->beginTransaction();
                }
                
                // Build the VALUES clause
                $placeholders = [];
                $params = [];
                
                foreach ($batch as $row) {
                    $rowPlaceholders = str_repeat('?,', $columnCount - 1) . '?';
                    $placeholders[] = "($rowPlaceholders)";
                    $params = array_merge($params, array_values($row));
                }
                
                $valuesStr = implode(', ', $placeholders);
                $query = "$insertType INTO `$table` ($columnsStr) VALUES $valuesStr";
                
                if ($onDuplicate) {
                    $query .= " ON DUPLICATE KEY UPDATE $onDuplicate";
                }
                
                $stmt = $this->db->executeQuery($query, $params);
                $results['inserted_rows'] += $stmt->rowCount();
                $results['successful_batches']++;
                
                if ($this->useTransactions) {
                    $this->db->commit();
                }
                
                $this->db->getLogger()->log("Bulk insert batch $batchIndex completed: " . count($batch) . " rows");
                
            } catch (Exception $e) {
                if ($this->useTransactions) {
                    $this->db->rollback();
                }
                
                $results['failed_batches']++;
                $results['errors'][] = "Batch $batchIndex failed: " . $e->getMessage();
                $this->db->getLogger()->log("Bulk insert batch $batchIndex failed: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Perform bulk UPDATE operation.
     * 
     * @param string $table Table name
     * @param array $updates Array of update data [['set' => ['col' => 'val'], 'where' => ['id' => 1]]]
     * @return array Results with success count and any errors
     */
    public function bulkUpdate($table, $updates) {
        $results = [
            'total_updates' => count($updates),
            'successful_updates' => 0,
            'failed_updates' => 0,
            'errors' => [],
            'affected_rows' => 0
        ];
        
        if (empty($updates)) {
            return $results;
        }
        
        $batches = array_chunk($updates, $this->batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                if ($this->useTransactions) {
                    $this->db->beginTransaction();
                }
                
                foreach ($batch as $updateIndex => $update) {
                    if (!isset($update['set']) || !isset($update['where'])) {
                        throw new InvalidArgumentException("Update data must contain 'set' and 'where' keys");
                    }
                    
                    // Build SET clause
                    $setParts = [];
                    $params = [];
                    foreach ($update['set'] as $column => $value) {
                        $setParts[] = "`$column` = ?";
                        $params[] = $value;
                    }
                    $setClause = implode(', ', $setParts);
                    
                    // Build WHERE clause
                    $whereParts = [];
                    foreach ($update['where'] as $column => $value) {
                        $whereParts[] = "`$column` = ?";
                        $params[] = $value;
                    }
                    $whereClause = implode(' AND ', $whereParts);
                    
                    $query = "UPDATE `$table` SET $setClause WHERE $whereClause";
                    $stmt = $this->db->executeQuery($query, $params);
                    $results['affected_rows'] += $stmt->rowCount();
                    $results['successful_updates']++;
                }
                
                if ($this->useTransactions) {
                    $this->db->commit();
                }
                
                $this->db->getLogger()->log("Bulk update batch $batchIndex completed: " . count($batch) . " updates");
                
            } catch (Exception $e) {
                if ($this->useTransactions) {
                    $this->db->rollback();
                }
                
                $results['failed_updates'] += count($batch) - ($results['successful_updates'] % $this->batchSize);
                $results['errors'][] = "Batch $batchIndex failed: " . $e->getMessage();
                $this->db->getLogger()->log("Bulk update batch $batchIndex failed: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Perform bulk DELETE operation.
     * 
     * @param string $table Table name
     * @param array $conditions Array of WHERE conditions [['id' => 1], ['id' => 2]]
     * @param string $whereColumn Column to use for IN clause optimization
     * @return array Results with success count and any errors
     */
    public function bulkDelete($table, $conditions, $whereColumn = null) {
        $results = [
            'total_deletes' => count($conditions),
            'successful_deletes' => 0,
            'failed_deletes' => 0,
            'errors' => [],
            'affected_rows' => 0
        ];
        
        if (empty($conditions)) {
            return $results;
        }
        
        // If a single column is specified, use IN clause for better performance
        if ($whereColumn && $this->canUseInClause($conditions, $whereColumn)) {
            return $this->bulkDeleteWithIn($table, $conditions, $whereColumn);
        }
        
        $batches = array_chunk($conditions, $this->batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                if ($this->useTransactions) {
                    $this->db->beginTransaction();
                }
                
                foreach ($batch as $condition) {
                    // Build WHERE clause
                    $whereParts = [];
                    $params = [];
                    foreach ($condition as $column => $value) {
                        $whereParts[] = "`$column` = ?";
                        $params[] = $value;
                    }
                    $whereClause = implode(' AND ', $whereParts);
                    
                    $query = "DELETE FROM `$table` WHERE $whereClause";
                    $stmt = $this->db->executeQuery($query, $params);
                    $results['affected_rows'] += $stmt->rowCount();
                    $results['successful_deletes']++;
                }
                
                if ($this->useTransactions) {
                    $this->db->commit();
                }
                
                $this->db->getLogger()->log("Bulk delete batch $batchIndex completed: " . count($batch) . " deletes");
                
            } catch (Exception $e) {
                if ($this->useTransactions) {
                    $this->db->rollback();
                }
                
                $results['failed_deletes'] += count($batch) - ($results['successful_deletes'] % $this->batchSize);
                $results['errors'][] = "Batch $batchIndex failed: " . $e->getMessage();
                $this->db->getLogger()->log("Bulk delete batch $batchIndex failed: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Optimized bulk delete using IN clause.
     * 
     * @param string $table Table name
     * @param array $conditions Array of conditions
     * @param string $whereColumn Column to use for IN clause
     * @return array Results
     */
    private function bulkDeleteWithIn($table, $conditions, $whereColumn) {
        $results = [
            'total_deletes' => count($conditions),
            'successful_deletes' => 0,
            'failed_deletes' => 0,
            'errors' => [],
            'affected_rows' => 0
        ];
        
        // Extract values for the IN clause
        $values = array_column($conditions, $whereColumn);
        $batches = array_chunk($values, $this->batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                if ($this->useTransactions) {
                    $this->db->beginTransaction();
                }
                
                $placeholders = str_repeat('?,', count($batch) - 1) . '?';
                $query = "DELETE FROM `$table` WHERE `$whereColumn` IN ($placeholders)";
                $stmt = $this->db->executeQuery($query, $batch);
                $results['affected_rows'] += $stmt->rowCount();
                $results['successful_deletes'] += count($batch);
                
                if ($this->useTransactions) {
                    $this->db->commit();
                }
                
                $this->db->getLogger()->log("Bulk delete IN batch $batchIndex completed: " . count($batch) . " deletes");
                
            } catch (Exception $e) {
                if ($this->useTransactions) {
                    $this->db->rollback();
                }
                
                $results['failed_deletes'] += count($batch);
                $results['errors'][] = "Batch $batchIndex failed: " . $e->getMessage();
                $this->db->getLogger()->log("Bulk delete IN batch $batchIndex failed: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Check if conditions can use IN clause optimization.
     * 
     * @param array $conditions Array of conditions
     * @param string $whereColumn Column to check
     * @return bool
     */
    private function canUseInClause($conditions, $whereColumn) {
        foreach ($conditions as $condition) {
            if (!is_array($condition) || count($condition) !== 1 || !isset($condition[$whereColumn])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Perform UPSERT operation (INSERT ... ON DUPLICATE KEY UPDATE).
     * 
     * @param string $table Table name
     * @param array $columns Column names
     * @param array $data Array of data rows
     * @param array $updateColumns Columns to update on duplicate (if null, updates all except key)
     * @return array Results
     */
    public function bulkUpsert($table, $columns, $data, $updateColumns = null) {
        if ($updateColumns === null) {
            $updateColumns = $columns;
        }
        
        $updateClause = implode(', ', array_map(function($col) {
            return "`$col` = VALUES(`$col`)";
        }, $updateColumns));
        
        return $this->bulkInsert($table, $columns, $data, false, $updateClause);
    }
    
    /**
     * Set batch size for bulk operations.
     * 
     * @param int $batchSize Batch size
     */
    public function setBatchSize($batchSize) {
        $this->batchSize = max(1, (int)$batchSize);
    }
    
    /**
     * Get current batch size.
     * 
     * @return int Current batch size
     */
    public function getBatchSize() {
        return $this->batchSize;
    }
    
    /**
     * Enable or disable transactions for bulk operations.
     * 
     * @param bool $useTransactions Whether to use transactions
     */
    public function setUseTransactions($useTransactions) {
        $this->useTransactions = (bool)$useTransactions;
    }
    
    /**
     * Check if transactions are enabled.
     * 
     * @return bool Whether transactions are enabled
     */
    public function getUseTransactions() {
        return $this->useTransactions;
    }
}