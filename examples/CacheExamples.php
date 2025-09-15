<?php
require_once '../src/MySQL/Database.php';
require_once '../src/MySQL/SQLSelect.php';
require_once '../src/QueryCache.php';

/**
 * Query Cache Examples for SQLGenix
 * 
 * This file demonstrates how to use the query caching system
 * to improve performance by avoiding repeated query construction and execution.
 */

// Database connection
$db = new Database('localhost', 'test_db', 'username', 'password');

// Create cache instance with custom settings
$cache = new QueryCache(50, 1800); // 50 items max, 30 minutes TTL

echo "=== SQLGenix Query Cache Examples ===\n\n";

// Example 1: Basic cache usage
echo "1. Basic cache usage:\n";
$select = new SQLSelect($db, $cache);
$query1 = $select->select(['*'])
                ->from('users')
                ->where('status = "active"')
                ->orderBy(['name'], 'ASC')
                ->limit(10)
                ->getQuery();

// This will be cached
echo "First call - Query: $query1\n";

// Create another identical query - this should hit the cache
$select2 = new SQLSelect($db, $cache);
$query2 = $select2->select(['*'])
                 ->from('users')
                 ->where('status = "active"')
                 ->orderBy(['name'], 'ASC')
                 ->limit(10)
                 ->getQuery();

echo "Second call (should be cached) - Query: $query2\n";
echo "Queries identical: " . ($query1 === $query2 ? 'Yes' : 'No') . "\n\n";

// Example 2: Cache statistics
echo "2. Cache statistics after queries:\n";
$stats = $cache->getStats();
foreach ($stats as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

// Example 3: Cache with different parameters
echo "3. Different queries (won't hit cache):\n";
$select3 = new SQLSelect($db, $cache);
$query3 = $select3->select(['id', 'name'])  // Different columns
                 ->from('users')
                 ->where('status = "active"')
                 ->orderBy(['name'], 'ASC')
                 ->limit(10)
                 ->getQuery();

echo "Different columns query: $query3\n";

$select4 = new SQLSelect($db, $cache);
$query4 = $select4->select(['*'])
                 ->from('users')
                 ->where('status = "inactive"')  // Different condition
                 ->orderBy(['name'], 'ASC')
                 ->limit(10)
                 ->getQuery();

echo "Different condition query: $query4\n\n";

// Example 4: Cache invalidation for specific table
echo "4. Cache invalidation:\n";
echo "Before invalidation - Cache size: " . $cache->getStats()['query_cache_size'] . "\n";
$cache->invalidateTable('users');
echo "After invalidating 'users' table - Cache size: " . $cache->getStats()['query_cache_size'] . "\n\n";

// Example 5: Pagination with cache
echo "5. Pagination queries with cache:\n";
for ($page = 1; $page <= 3; $page++) {
    $select = new SQLSelect($db, $cache);
    $query = $select->select(['id', 'name', 'email'])
                   ->from('products')
                   ->where('active = 1')
                   ->orderBy(['created_at'], 'DESC')
                   ->paginate($page, 20)
                   ->getQuery();
    echo "Page $page query: $query\n";
}
echo "\n";

// Example 6: Cache with complex joins
echo "6. Complex join query with cache:\n";
$select = new SQLSelect($db, $cache);
$query = $select->select(['u.name', 'u.email', 'p.title', 'r.name as role'])
               ->from('users u')
               ->leftJoin('profiles p', 'u.id = p.user_id')
               ->innerJoin('user_roles ur', 'u.id = ur.user_id')
               ->innerJoin('roles r', 'ur.role_id = r.id')
               ->where('u.status = "active"')
               ->orderBy(['u.name'], 'ASC')
               ->limit(50)
               ->getQuery();

echo "Complex join query: $query\n\n";

// Example 7: Cache configuration
echo "7. Cache configuration:\n";
echo "Current TTL: " . $cache->getTTL() . " seconds\n";
echo "Cache enabled: " . ($cache->isEnabled() ? 'Yes' : 'No') . "\n";

// Change cache settings
$cache->setTTL(3600); // 1 hour
$cache->setMaxCacheSize(100);
echo "Updated TTL: " . $cache->getTTL() . " seconds\n\n";

// Example 8: Manual cache operations
echo "8. Manual cache operations:\n";
echo "Final cache stats:\n";
$finalStats = $cache->getStats();
foreach ($finalStats as $key => $value) {
    echo "  $key: $value\n";
}

// Clear cache
$cache->clear();
echo "\nCache cleared. New stats:\n";
$clearedStats = $cache->getStats();
foreach ($clearedStats as $key => $value) {
    echo "  $key: $value\n";
}

echo "\n=== Cache Usage Tips ===\n";
echo "• Cache improves performance for repeated identical queries\n";
echo "• Use cache invalidation after INSERT/UPDATE/DELETE operations\n";
echo "• Monitor hit rate to optimize cache size and TTL\n";
echo "• Consider using different cache instances for different query types\n";
echo "• Cache works best with read-heavy applications\n";