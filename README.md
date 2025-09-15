# SQLGenix 🚀

## Supercharge Your Database Interactions
SQLGenix is your go-to SQL query generator that takes the pain out of database operations. Say goodbye to manual SQL writing and hello to elegant, chainable methods that make database interactions a breeze. Perfect for both rapid application development and sophisticated database management tasks.

## Table of Contents
- [Key Features](#key-features)
- [Quick Start](#quick-start)
  - [Installation](#installation)
  - [Usage Examples](#usage-examples)
    - [Create New Records](#create-new-records)
    - [Fetch Data](#fetch-data)
    - [Update Records](#update-records)
    - [Remove Data](#remove-data)
- [Requirements](#requirements)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Get in Touch](#get-in-touch)

## ✨ Key Features
- 🎯 **Smart Query Generation** - Build complex SQL queries with simple, intuitive methods
- 🔄 **Full CRUD Support** - Handle SELECT, INSERT, UPDATE, and DELETE operations effortlessly
- 🔌 **Multi-Database Support** - Works seamlessly with MySQL, PostgreSQL, SQLite, and more
- 🛡️ **Robust Error Handling** - Graceful exception management keeps your application stable
- ⚡ **Lightning Fast** - Optimized for performance without sacrificing flexibility
- 📄 **Pagination Support** - Built-in LIMIT, OFFSET, and page-based pagination
- 🔄 **Query Caching** - Intelligent caching system for improved performance
- 🔗 **UNION Operations** - Combine multiple queries with UNION and UNION ALL
- 📦 **Bulk Operations** - Efficient batch INSERT, UPDATE, and DELETE operations
- 🔍 **Advanced Joins** - Support for INNER, LEFT, RIGHT joins with complex conditions
- 🏗️ **Database Adapters** - Adapter pattern for database-specific optimizations

## 🚀 Quick Start

### Installation
Get started with Composer:
```bash
composer require sqlgenix/sqlgenix
```

Or clone the repository manually:
```bash
git clone https://github.com/Inkflow59/SQLGenix.git
cd SQLGenix
composer install
```

### 📚 Usage Examples

#### Create New Records
```php
require 'src/SQLInsert.php';

$db = new Database();
$insert = new SQLInsert($db);
$insert->into('users')
       ->set(['name', 'email'], ['John Doe', 'john@example.com'])
       ->execute();
```

#### Fetch Data
```php
require 'src/SQLSelect.php';

$db = new Database();
$select = new SQLSelect($db);
$result = $select->select(['name', 'email'])
                 ->from('users')
                 ->where('email = "john@example.com"')
                 ->execute();
```

#### Update Records
```php
require 'src/SQLUpdate.php';

$db = new Database();
$update = new SQLUpdate($db);
$update->table('users')
       ->set('name', 'Jane Doe')
       ->where('email = "john@example.com"')
       ->execute();
```

#### Remove Data
```php
require 'src/SQLDelete.php';

$db = new Database();
$delete = new SQLDelete($db);
$delete->from('users')
       ->where('email = "john@example.com"')
       ->execute();
```

#### Pagination
```php
require 'src/SQLSelect.php';

$db = new Database();
$select = new SQLSelect($db);
$result = $select->select(['*'])
                 ->from('users')
                 ->where('status = "active"')
                 ->orderBy(['created_at'], 'DESC')
                 ->paginate(2, 20) // Page 2, 20 per page
                 ->execute();
```

#### Query Caching
```php
require 'src/QueryCache.php';
require 'src/SQLSelect.php';

$cache = new QueryCache(100, 3600); // 100 items, 1 hour TTL
$db = new Database();
$select = new SQLSelect($db, $cache);
$result = $select->select(['*'])
                 ->from('users')
                 ->where('active = 1')
                 ->execute(); // Cached automatically
```

#### UNION Operations
```php
require 'src/SQLSelect.php';

$db = new Database();
$activeUsers = new SQLSelect($db);
$activeUsers->select(['name', 'email'])->from('active_users');

$inactiveUsers = new SQLSelect($db);
$inactiveUsers->select(['name', 'email'])->from('inactive_users');

$allUsers = new SQLSelect($db);
$result = $allUsers->select(['name', 'email'])
                  ->from('current_users')
                  ->union($activeUsers)
                  ->unionAll($inactiveUsers)
                  ->execute();
```

#### Bulk Operations
```php
require 'src/BulkOperations.php';

$db = new Database();
$bulk = new BulkOperations($db);

// Bulk insert 1000 records
$data = []; // Your data array
$result = $bulk->bulkInsert('users', ['name', 'email'], $data);
echo "Inserted: " . $result['inserted_rows'] . " rows";
```

#### Multi-Database Support
```php
require 'src/DatabaseAdapterFactory.php';
require 'src/UniversalSQLSelect.php';

// Create MySQL adapter
$mysqlAdapter = DatabaseAdapterFactory::createMySQL([
    'host' => 'localhost',
    'database' => 'myapp',
    'username' => 'user',
    'password' => 'pass'
]);

// Create PostgreSQL adapter
$postgresAdapter = DatabaseAdapterFactory::createPostgreSQL([
    'host' => 'localhost', 
    'database' => 'myapp',
    'username' => 'user',
    'password' => 'pass'
]);

// Same query works on both databases
$mysqlQuery = new UniversalSQLSelect($mysqlAdapter);
$postgresQuery = new UniversalSQLSelect($postgresAdapter);

$result = $mysqlQuery->select(['*'])->from('users')->execute();
```

## ⚙️ Requirements
- PHP 7.0+
- Any major SQL database (MySQL, PostgreSQL, SQLite)
- Composer for dependency management

## 🧪 Testing
Run the test suite with:
```bash
composer test
```

## 🤝 Contributing
We love contributions! Here's how you can help:

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License
SQLGenix is MIT licensed. See the [LICENSE](LICENSE) file for details.

## 📬 Get in Touch
Questions or suggestions? [Drop me a line](mailto:tomcucherosset@hotmail.fr)

---
Made with ❤️ by SQLGenix Team