<?php
require_once '../src/MySQL/Database.php';
require_once '../src/MySQL/SQLSelect.php';

/**
 * UNION Examples for SQLGenix
 * 
 * This file demonstrates how to use UNION and UNION ALL
 * to combine multiple SELECT queries.
 */

// Database connection
$db = new Database('localhost', 'test_db', 'username', 'password');

echo "=== SQLGenix UNION Examples ===\n\n";

// Example 1: Basic UNION
echo "1. Basic UNION (removes duplicates):\n";
$query1 = new SQLSelect($db);
$query1->select(['name', 'email'])
       ->from('active_users')
       ->where('status = "premium"');

$query2 = new SQLSelect($db);
$query2->select(['name', 'email'])
       ->from('inactive_users')
       ->where('last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)');

$unionQuery = new SQLSelect($db);
$unionQuery->select(['name', 'email'])
           ->from('current_users')
           ->where('active = 1')
           ->union($query1)
           ->union($query2);

echo "SQL: " . $unionQuery->getQuery() . "\n\n";

// Example 2: UNION ALL (keeps duplicates)
echo "2. UNION ALL (keeps duplicates):\n";
$salesQ1 = new SQLSelect($db);
$salesQ1->select(['product_id', 'quantity', 'sale_date'])
        ->from('sales_2023')
        ->where('quarter = 1');

$salesQ2 = new SQLSelect($db);
$salesQ2->select(['product_id', 'quantity', 'sale_date'])
        ->from('sales_2023')
        ->where('quarter = 2');

$allSales = new SQLSelect($db);
$allSales->select(['product_id', 'quantity', 'sale_date'])
         ->from('sales_2023')
         ->where('quarter = 3')
         ->unionAll($salesQ1)
         ->unionAll($salesQ2);

echo "SQL: " . $allSales->getQuery() . "\n\n";

// Example 3: UNION with different tables
echo "3. UNION with different table structures:\n";
$customers = new SQLSelect($db);
$customers->select(['name', 'email', '"customer" as type'])
          ->from('customers')
          ->where('active = 1');

$suppliers = new SQLSelect($db);
$suppliers->select(['company_name as name', 'contact_email as email', '"supplier" as type'])
          ->from('suppliers')
          ->where('status = "active"');

$allContacts = new SQLSelect($db);
$allContacts->select(['name', 'email', '"employee" as type'])
            ->from('employees')
            ->where('department != "inactive"')
            ->union($customers)
            ->union($suppliers)
            ->orderBy(['name'], 'ASC');

echo "SQL: " . $allContacts->getQuery() . "\n\n";

// Example 4: Complex UNION with joins
echo "4. Complex UNION with joins:\n";
$currentOrders = new SQLSelect($db);
$currentOrders->select(['o.id', 'o.total', 'c.name as customer', 'o.created_at'])
              ->from('orders o')
              ->innerJoin('customers c', 'o.customer_id = c.id')
              ->where('o.status = "pending"');

$completedOrders = new SQLSelect($db);
$completedOrders->select(['o.id', 'o.total', 'c.name as customer', 'o.created_at'])
                ->from('archived_orders o')
                ->innerJoin('customers c', 'o.customer_id = c.id')
                ->where('o.status = "completed"')
                ->where('o.completed_at > DATE_SUB(NOW(), INTERVAL 7 DAY)');

$recentOrders = new SQLSelect($db);
$recentOrders->select(['o.id', 'o.total', 'c.name as customer', 'o.created_at'])
             ->from('draft_orders o')
             ->innerJoin('customers c', 'o.customer_id = c.id')
             ->where('o.updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')
             ->union($currentOrders)
             ->union($completedOrders)
             ->orderBy(['created_at'], 'DESC')
             ->limit(100);

echo "SQL: " . $recentOrders->getQuery() . "\n\n";

// Example 5: UNION with aggregations
echo "5. UNION with aggregations:\n";
$monthlySales = new SQLSelect($db);
$monthlySales->select(['YEAR(sale_date) as year', 'MONTH(sale_date) as month', 'SUM(amount) as total', '"sales" as type'])
             ->from('sales')
             ->where('sale_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
             ->groupBy(['YEAR(sale_date)', 'MONTH(sale_date)']);

$monthlyReturns = new SQLSelect($db);
$monthlyReturns->select(['YEAR(return_date) as year', 'MONTH(return_date) as month', 'SUM(amount) as total', '"returns" as type'])
               ->from('returns')
               ->where('return_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
               ->groupBy(['YEAR(return_date)', 'MONTH(return_date)']);

$monthlyData = new SQLSelect($db);
$monthlyData->select(['year', 'month', 'total', 'type'])
            ->from('(')  // This would need special handling in real implementation
            ->union($monthlySales)
            ->union($monthlyReturns)
            ->orderBy(['year', 'month', 'type'], 'ASC');

echo "SQL (conceptual): " . $monthlySales->getQuery() . " UNION " . $monthlyReturns->getQuery() . "\n\n";

// Example 6: UNION with raw SQL
echo "6. UNION with raw SQL:\n";
$userQuery = new SQLSelect($db);
$userQuery->select(['id', 'name', 'email'])
          ->from('users')
          ->where('active = 1')
          ->union('SELECT admin_id as id, admin_name as name, admin_email as email FROM admins WHERE status = "active"')
          ->unionAll('SELECT guest_id as id, guest_name as name, guest_email as email FROM guest_users WHERE session_active = 1')
          ->orderBy(['name'], 'ASC');

echo "SQL: " . $userQuery->getQuery() . "\n\n";

// Example 7: UNION with pagination
echo "7. UNION with pagination:\n";
$activeUsers = new SQLSelect($db);
$activeUsers->select(['id', 'name', 'email', 'created_at'])
            ->from('users')
            ->where('status = "active"');

$inactiveUsers = new SQLSelect($db);
$inactiveUsers->select(['id', 'name', 'email', 'created_at'])
              ->from('users')
              ->where('status = "inactive"')
              ->where('last_login > DATE_SUB(NOW(), INTERVAL 90 DAY)');

$allUsers = new SQLSelect($db);
$allUsers->select(['id', 'name', 'email', 'created_at'])
         ->from('users')
         ->where('status = "pending"')
         ->union($activeUsers)
         ->union($inactiveUsers)
         ->orderBy(['created_at'], 'DESC')
         ->paginate(1, 25);  // First page, 25 per page

echo "SQL: " . $allUsers->getQuery() . "\n\n";

echo "=== UNION Usage Tips ===\n";
echo "• UNION removes duplicates, UNION ALL keeps them (faster)\n";
echo "• All SELECT statements must have the same number of columns\n";
echo "• Column types should be compatible across UNIONs\n";
echo "• ORDER BY applies to the entire UNION result\n";
echo "• Use parentheses for complex UNION combinations\n";
echo "• UNION ALL is generally faster than UNION\n";
echo "• Consider indexing columns used in WHERE clauses of UNIONed queries\n";