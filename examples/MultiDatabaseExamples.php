<?php
require_once '../src/DatabaseAdapterFactory.php';
require_once '../src/UniversalSQLSelect.php';
require_once '../src/QueryCache.php';

/**
 * Multi-Database Examples for SQLGenix
 * 
 * This file demonstrates how to use SQLGenix with multiple database systems
 * (MySQL and PostgreSQL) using the universal adapter pattern.
 */

echo "=== SQLGenix Multi-Database Examples ===\n\n";

// Database configurations
$mysqlConfig = [
    'host' => 'localhost',
    'database' => 'test_mysql',
    'username' => 'mysql_user',
    'password' => 'mysql_pass',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

$postgresConfig = [
    'host' => 'localhost',
    'database' => 'test_postgres',
    'username' => 'postgres_user',
    'password' => 'postgres_pass',
    'port' => 5432
];

// Create cache instance
$cache = new QueryCache(100, 3600);

try {
    // Example 1: Create adapters using factory
    echo "1. Creating database adapters:\n";
    $mysqlAdapter = DatabaseAdapterFactory::createMySQL($mysqlConfig);
    $postgresAdapter = DatabaseAdapterFactory::createPostgreSQL($postgresConfig);
    
    echo "  ✓ MySQL adapter created (Type: " . $mysqlAdapter->getDatabaseType() . ")\n";
    echo "  ✓ PostgreSQL adapter created (Type: " . $postgresAdapter->getDatabaseType() . ")\n\n";
    
    // Example 2: Basic SELECT queries on both databases
    echo "2. Basic SELECT queries:\n";
    
    // MySQL query
    $mysqlSelect = new UniversalSQLSelect($mysqlAdapter, $cache);
    $mysqlQuery = $mysqlSelect->select(['id', 'name', 'email'])
                            ->from('users')
                            ->where('status = ?')
                            ->orderBy(['created_at'], 'DESC')
                            ->limit(10)
                            ->getQuery();
    echo "  MySQL: $mysqlQuery\n";
    
    // PostgreSQL query (same logic, different syntax)
    $postgresSelect = new UniversalSQLSelect($postgresAdapter, $cache);
    $postgresQuery = $postgresSelect->select(['id', 'name', 'email'])
                                  ->from('users')
                                  ->where('status = ?')
                                  ->orderBy(['created_at'], 'DESC')
                                  ->limit(10)
                                  ->getQuery();
    echo "  PostgreSQL: $postgresQuery\n\n";
    
    // Example 3: Pagination with different database syntaxes
    echo "3. Pagination examples:\n";
    
    // MySQL pagination (LIMIT offset, count)
    $mysqlPagination = new UniversalSQLSelect($mysqlAdapter);
    $mysqlPaginationQuery = $mysqlPagination->select(['*'])
                                          ->from('products')
                                          ->where('active = 1')
                                          ->orderBy(['name'], 'ASC')
                                          ->paginate(2, 20) // Page 2, 20 per page
                                          ->getQuery();
    echo "  MySQL Pagination: $mysqlPaginationQuery\n";
    
    // PostgreSQL pagination (LIMIT count OFFSET offset)
    $postgresPagination = new UniversalSQLSelect($postgresAdapter);
    $postgresPaginationQuery = $postgresPagination->select(['*'])
                                                 ->from('products')
                                                 ->where('active = 1')
                                                 ->orderBy(['name'], 'ASC')
                                                 ->paginate(2, 20) // Page 2, 20 per page
                                                 ->getQuery();
    echo "  PostgreSQL Pagination: $postgresPaginationQuery\n\n";
    
    // Example 4: Database-specific features
    echo "4. Database-specific features:\n";
    
    // MySQL date formatting
    $mysqlDateQuery = new UniversalSQLSelect($mysqlAdapter);
    $mysqlDateQuery->select(['id', 'name'])
                   ->selectDateFormat('created_at', '%Y-%m', 'month_year')
                   ->from('orders')
                   ->where('status = "completed"');
    echo "  MySQL Date Format: " . $mysqlDateQuery->getQuery() . "\n";
    
    // PostgreSQL date formatting
    $postgresDateQuery = new UniversalSQLSelect($postgresAdapter);
    $postgresDateQuery->select(['id', 'name'])
                      ->selectDateFormat('created_at', 'YYYY-MM', 'month_year')
                      ->from('orders')
                      ->where('status = \'completed\'');
    echo "  PostgreSQL Date Format: " . $postgresDateQuery->getQuery() . "\n\n";
    
    // Example 5: Concatenation functions
    echo "5. Concatenation examples:\n";
    
    // MySQL CONCAT
    $mysqlConcat = new UniversalSQLSelect($mysqlAdapter);
    $mysqlConcat->select(['id'])
                ->selectConcat(['first_name', '\' \'', 'last_name'], 'full_name')
                ->from('users');
    echo "  MySQL Concat: " . $mysqlConcat->getQuery() . "\n";
    
    // PostgreSQL concatenation (||)
    $postgresConcat = new UniversalSQLSelect($postgresAdapter);
    $postgresConcat->select(['id'])
                   ->selectConcat(['first_name', '\' \'', 'last_name'], 'full_name')
                   ->from('users');
    echo "  PostgreSQL Concat: " . $postgresConcat->getQuery() . "\n\n";
    
    // Example 6: Complex queries with joins
    echo "6. Complex JOIN queries:\n";
    
    $complexQuery = function($adapter, $dbType) {
        $select = new UniversalSQLSelect($adapter);
        return $select->select(['u.id', 'u.name', 'u.email', 'p.title as profile_title', 'r.name as role_name'])
                     ->from('users u')
                     ->leftJoin('profiles p', 'u.id = p.user_id')
                     ->innerJoin('user_roles ur', 'u.id = ur.user_id')
                     ->innerJoin('roles r', 'ur.role_id = r.id')
                     ->where('u.active = 1')
                     ->where('r.status = \'active\'')
                     ->orderBy(['u.name', 'r.priority'], 'ASC')
                     ->limit(50)
                     ->getQuery();
    };
    
    echo "  MySQL Complex: " . $complexQuery($mysqlAdapter, 'MySQL') . "\n";
    echo "  PostgreSQL Complex: " . $complexQuery($postgresAdapter, 'PostgreSQL') . "\n\n";
    
    // Example 7: UNION operations
    echo "7. UNION operations:\n";
    
    $createUnionQuery = function($adapter, $dbType) {
        $activeUsers = new UniversalSQLSelect($adapter);
        $activeUsers->select(['id', 'name', 'email', '\'active\' as status'])
                   ->from('active_users');
        
        $inactiveUsers = new UniversalSQLSelect($adapter);
        $inactiveUsers->select(['id', 'name', 'email', '\'inactive\' as status'])
                     ->from('inactive_users')
                     ->where('last_login > CURRENT_DATE - INTERVAL \'30 days\'');
        
        $allUsers = new UniversalSQLSelect($adapter);
        return $allUsers->select(['id', 'name', 'email', '\'current\' as status'])
                       ->from('current_users')
                       ->union($activeUsers)
                       ->unionAll($inactiveUsers)
                       ->orderBy(['name'], 'ASC')
                       ->getQuery();
    };
    
    echo "  MySQL UNION: " . $createUnionQuery($mysqlAdapter, 'MySQL') . "\n";
    echo "  PostgreSQL UNION: " . $createUnionQuery($postgresAdapter, 'PostgreSQL') . "\n\n";
    
    // Example 8: Feature detection
    echo "8. Database feature detection:\n";
    
    $features = ['window_functions', 'cte', 'json', 'upsert', 'full_text_search'];
    
    echo "  MySQL features:\n";
    foreach ($features as $feature) {
        $supported = $mysqlAdapter->supportsFeature($feature) ? '✓' : '✗';
        echo "    $supported $feature\n";
    }
    
    echo "  PostgreSQL features:\n";
    foreach ($features as $feature) {
        $supported = $postgresAdapter->supportsFeature($feature) ? '✓' : '✗';
        echo "    $supported $feature\n";
    }
    echo "\n";
    
    // Example 9: Database-specific identifier quoting
    echo "9. Identifier quoting:\n";
    echo "  MySQL quotes table 'user-data': " . $mysqlAdapter->quoteIdentifier('user-data') . "\n";
    echo "  PostgreSQL quotes table 'user-data': " . $postgresAdapter->quoteIdentifier('user-data') . "\n\n";
    
    // Example 10: UPSERT syntax
    echo "10. UPSERT syntax comparison:\n";
    $columns = ['name', 'email', 'status'];
    $updateColumns = ['name', 'status'];
    
    echo "  MySQL UPSERT: " . $mysqlAdapter->getUpsertSyntax('users', $columns, $updateColumns) . "\n";
    echo "  PostgreSQL UPSERT: " . $postgresAdapter->getUpsertSyntax('users', $columns, $updateColumns) . "\n\n";
    
    // Example 11: Creating adapters from DSN
    echo "11. Creating adapters from DSN:\n";
    try {
        $mysqlFromDSN = DatabaseAdapterFactory::createFromDSN(
            'mysql:host=localhost;dbname=test;charset=utf8mb4',
            'username',
            'password'
        );
        echo "  ✓ MySQL adapter created from DSN\n";
        
        $postgresFromDSN = DatabaseAdapterFactory::createFromDSN(
            'pgsql:host=localhost;dbname=test;port=5432',
            'username',
            'password'
        );
        echo "  ✓ PostgreSQL adapter created from DSN\n";
    } catch (Exception $e) {
        echo "  ✗ DSN creation failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Example 12: Cache performance with different databases
    echo "12. Cache performance:\n";
    $cacheStats = $cache->getStats();
    echo "  Cache hits: {$cacheStats['hits']}\n";
    echo "  Cache misses: {$cacheStats['misses']}\n";
    echo "  Hit rate: {$cacheStats['hit_rate_percentage']}%\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Multi-Database Usage Tips ===\n";
echo "• Use the adapter factory for consistent database connection management\n";
echo "• Universal query builder handles database-specific syntax differences\n";
echo "• Feature detection allows conditional logic based on database capabilities\n";
echo "• Identifier quoting ensures compatibility with reserved words\n";
echo "• Cache works across different database types with unique keys\n";
echo "• UPSERT syntax varies significantly between databases\n";
echo "• Always handle database-specific exceptions appropriately\n";
echo "• Consider using connection pooling for high-traffic applications\n";