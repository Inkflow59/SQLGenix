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
- 🔌 **Database Agnostic** - Works seamlessly with MySQL, PostgreSQL, SQLite, and more
- 🛡️ **Robust Error Handling** - Graceful exception management keeps your application stable
- ⚡ **Lightning Fast** - Optimized for performance without sacrificing flexibility

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