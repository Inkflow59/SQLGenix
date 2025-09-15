<?php
require_once '../src/MySQL/Database.php';
require_once '../src/MySQL/SQLSelect.php';

/**
 * Pagination Examples for SQLGenix
 * 
 * This file demonstrates how to use pagination features with SQLSelect
 */

// Database connection
$db = new Database('localhost', 'test_db', 'username', 'password');

echo "=== SQLGenix Pagination Examples ===\n\n";

// Example 1: Basic LIMIT
echo "1. Basic LIMIT (first 10 records):\n";
$select = new SQLSelect($db);
$query = $select->select(['*'])
               ->from('users')
               ->limit(10)
               ->getQuery();
echo "SQL: $query\n\n";

// Example 2: LIMIT with OFFSET
echo "2. LIMIT with OFFSET (skip first 20, get next 10):\n";
$select = new SQLSelect($db);
$query = $select->select(['id', 'name', 'email'])
               ->from('users')
               ->limit(10)
               ->offset(20)
               ->getQuery();
echo "SQL: $query\n\n";

// Example 3: Pagination helper (page-based)
echo "3. Pagination helper (page 3, 15 per page):\n";
$select = new SQLSelect($db);
$query = $select->select(['*'])
               ->from('products')
               ->where('active = 1')
               ->orderBy(['created_at'], 'DESC')
               ->paginate(3, 15)  // Page 3, 15 items per page
               ->getQuery();
echo "SQL: $query\n\n";

// Example 4: Complex query with pagination
echo "4. Complex query with joins and pagination:\n";
$select = new SQLSelect($db);
$query = $select->select(['u.name', 'u.email', 'p.title as profile_title'])
               ->from('users u')
               ->leftJoin('profiles p', 'u.id = p.user_id')
               ->where('u.status = "active"')
               ->orderBy(['u.created_at'], 'DESC')
               ->paginate(1, 25)
               ->getQuery();
echo "SQL: $query\n\n";

// Example 5: Pagination for search results
echo "5. Pagination for search results:\n";
$searchTerm = 'john';
$select = new SQLSelect($db);
$query = $select->select(['id', 'name', 'email'])
               ->from('users')
               ->where("name LIKE '%{$searchTerm}%' OR email LIKE '%{$searchTerm}%'")
               ->orderBy(['name'], 'ASC')
               ->limit(20)
               ->offset(0)
               ->getQuery();
echo "SQL: $query\n\n";

// Example 6: Count total records for pagination info
echo "6. Count total records for pagination metadata:\n";
$countSelect = new SQLSelect($db);
$countQuery = $countSelect->select(['COUNT(*) as total'])
                         ->from('users')
                         ->where('status = "active"')
                         ->getQuery();
echo "Count SQL: $countQuery\n";

$dataSelect = new SQLSelect($db);
$dataQuery = $dataSelect->select(['*'])
                       ->from('users')
                       ->where('status = "active"')
                       ->orderBy(['name'], 'ASC')
                       ->paginate(2, 10)  // Page 2, 10 per page
                       ->getQuery();
echo "Data SQL: $dataQuery\n\n";

echo "=== Pagination Usage Tips ===\n";
echo "• Use paginate(page, perPage) for simple page-based pagination\n";
echo "• Use limit() and offset() for custom pagination logic\n";
echo "• Always ORDER BY when using pagination for consistent results\n";
echo "• Consider creating a count query to show total pages\n";
echo "• For large datasets, use indexed columns in WHERE clauses\n";