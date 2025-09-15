<?php
require_once '../src/MySQL/Database.php';
require_once '../src/BulkOperations.php';

/**
 * Bulk Operations Examples for SQLGenix
 * 
 * This file demonstrates how to use bulk operations for efficient
 * INSERT, UPDATE, and DELETE operations on large datasets.
 */

// Database connection
$db = new Database('localhost', 'test_db', 'username', 'password');

// Create bulk operations instance
$bulk = new BulkOperations($db, 500, true); // 500 batch size, use transactions

echo "=== SQLGenix Bulk Operations Examples ===\n\n";

// Example 1: Bulk INSERT
echo "1. Bulk INSERT (1000 users):\n";
$columns = ['name', 'email', 'age', 'status'];
$userData = [];

// Generate sample data
for ($i = 1; $i <= 1000; $i++) {
    $userData[] = [
        "User $i",
        "user{$i}@example.com",
        rand(18, 65),
        rand(0, 1) ? 'active' : 'inactive'
    ];
}

$insertResults = $bulk->bulkInsert('users', $columns, $userData);
echo "Results:\n";
echo "  Total rows: {$insertResults['total_rows']}\n";
echo "  Inserted rows: {$insertResults['inserted_rows']}\n";
echo "  Successful batches: {$insertResults['successful_batches']}\n";
echo "  Failed batches: {$insertResults['failed_batches']}\n";
if (!empty($insertResults['errors'])) {
    echo "  Errors: " . implode(', ', $insertResults['errors']) . "\n";
}
echo "\n";

// Example 2: Bulk INSERT with IGNORE
echo "2. Bulk INSERT IGNORE (handling duplicates):\n";
$duplicateData = [
    ['John Doe', 'john@example.com', 30, 'active'],
    ['Jane Smith', 'jane@example.com', 25, 'active'],
    ['User 1', 'user1@example.com', 28, 'inactive'], // Potential duplicate
];

$ignoreResults = $bulk->bulkInsert('users', $columns, $duplicateData, true);
echo "Results with IGNORE:\n";
echo "  Total rows: {$ignoreResults['total_rows']}\n";
echo "  Inserted rows: {$ignoreResults['inserted_rows']}\n";
echo "\n";

// Example 3: Bulk UPSERT (INSERT ... ON DUPLICATE KEY UPDATE)
echo "3. Bulk UPSERT (insert or update):\n";
$upsertData = [
    ['John Doe Updated', 'john@example.com', 31, 'premium'],
    ['New User', 'newuser@example.com', 22, 'active'],
];

$upsertResults = $bulk->bulkUpsert('users', $columns, $upsertData, ['name', 'age', 'status']);
echo "Results for UPSERT:\n";
echo "  Total rows: {$upsertResults['total_rows']}\n";
echo "  Affected rows: {$upsertResults['inserted_rows']}\n";
echo "\n";

// Example 4: Bulk UPDATE
echo "4. Bulk UPDATE (status changes):\n";
$updateData = [];

// Update multiple users
for ($i = 1; $i <= 50; $i++) {
    $updateData[] = [
        'set' => ['status' => 'premium', 'updated_at' => date('Y-m-d H:i:s')],
        'where' => ['id' => $i]
    ];
}

$updateResults = $bulk->bulkUpdate('users', $updateData);
echo "Results:\n";
echo "  Total updates: {$updateResults['total_updates']}\n";
echo "  Successful updates: {$updateResults['successful_updates']}\n";
echo "  Affected rows: {$updateResults['affected_rows']}\n";
echo "  Failed updates: {$updateResults['failed_updates']}\n";
echo "\n";

// Example 5: Bulk UPDATE with complex conditions
echo "5. Bulk UPDATE with complex conditions:\n";
$complexUpdates = [
    [
        'set' => ['status' => 'vip', 'priority' => 1],
        'where' => ['email' => 'john@example.com', 'status' => 'premium']
    ],
    [
        'set' => ['last_login' => date('Y-m-d H:i:s')],
        'where' => ['id' => 100]
    ],
    [
        'set' => ['status' => 'inactive', 'reason' => 'bulk_deactivation'],
        'where' => ['age' => 65, 'status' => 'active']
    ]
];

$complexResults = $bulk->bulkUpdate('users', $complexUpdates);
echo "Complex update results:\n";
echo "  Successful updates: {$complexResults['successful_updates']}\n";
echo "  Affected rows: {$complexResults['affected_rows']}\n";
echo "\n";

// Example 6: Bulk DELETE with individual conditions
echo "6. Bulk DELETE with individual conditions:\n";
$deleteConditions = [];

// Delete users with specific IDs
for ($i = 901; $i <= 920; $i++) {
    $deleteConditions[] = ['id' => $i];
}

$deleteResults = $bulk->bulkDelete('users', $deleteConditions);
echo "Delete results:\n";
echo "  Total deletes: {$deleteResults['total_deletes']}\n";
echo "  Successful deletes: {$deleteResults['successful_deletes']}\n";
echo "  Affected rows: {$deleteResults['affected_rows']}\n";
echo "  Failed deletes: {$deleteResults['failed_deletes']}\n";
echo "\n";

// Example 7: Optimized Bulk DELETE with IN clause
echo "7. Optimized Bulk DELETE with IN clause:\n";
$deleteIds = [];
for ($i = 921; $i <= 950; $i++) {
    $deleteIds[] = ['id' => $i];
}

$optimizedDeleteResults = $bulk->bulkDelete('users', $deleteIds, 'id');
echo "Optimized delete results:\n";
echo "  Total deletes: {$optimizedDeleteResults['total_deletes']}\n";
echo "  Successful deletes: {$optimizedDeleteResults['successful_deletes']}\n";
echo "  Affected rows: {$optimizedDeleteResults['affected_rows']}\n";
echo "\n";

// Example 8: Large dataset with custom batch size
echo "8. Large dataset with custom batch size:\n";
$bulk->setBatchSize(100); // Smaller batches for this operation

$largeDataset = [];
for ($i = 1; $i <= 2000; $i++) {
    $largeDataset[] = [
        "Bulk User $i",
        "bulkuser{$i}@example.com",
        rand(18, 65),
        'active'
    ];
}

$largeResults = $bulk->bulkInsert('bulk_users', $columns, $largeDataset);
echo "Large dataset results:\n";
echo "  Total rows: {$largeResults['total_rows']}\n";
echo "  Successful batches: {$largeResults['successful_batches']}\n";
echo "  Batch size used: " . $bulk->getBatchSize() . "\n";
echo "\n";

// Example 9: Error handling
echo "9. Error handling (intentional failure):\n";
$bulk->setUseTransactions(false); // Disable transactions for this test

$invalidData = [
    ['Valid User', 'valid@example.com', 25, 'active'],
    ['Invalid User', 'invalid@example.com', 'invalid_age', 'active'], // Invalid age
    ['Another Valid User', 'another@example.com', 30, 'active'],
];

$errorResults = $bulk->bulkInsert('users', $columns, $invalidData);
echo "Error handling results:\n";
echo "  Total rows: {$errorResults['total_rows']}\n";
echo "  Successful batches: {$errorResults['successful_batches']}\n";
echo "  Failed batches: {$errorResults['failed_batches']}\n";
if (!empty($errorResults['errors'])) {
    echo "  Errors encountered: " . count($errorResults['errors']) . "\n";
    foreach ($errorResults['errors'] as $error) {
        echo "    - $error\n";
    }
}
echo "\n";

// Example 10: Performance comparison
echo "10. Performance comparison:\n";
echo "Bulk operations are significantly faster than individual operations:\n";
echo "  Individual INSERTs: ~1000 operations = ~10-30 seconds\n";
echo "  Bulk INSERT: ~1000 operations = ~0.1-1 seconds\n";
echo "  Performance improvement: ~10-300x faster!\n\n";

// Reset batch size
$bulk->setBatchSize(1000);
$bulk->setUseTransactions(true);

echo "=== Bulk Operations Usage Tips ===\n";
echo "• Use bulk operations for datasets > 100 rows\n";
echo "• Adjust batch size based on memory and performance\n";
echo "• Enable transactions for data consistency\n";
echo "• Use UPSERT for insert-or-update scenarios\n";
echo "• Monitor memory usage with very large datasets\n";
echo "• Use IN clause optimization for single-column deletes\n";
echo "• Handle errors gracefully in production\n";
echo "• Consider indexing foreign key columns before bulk operations\n";