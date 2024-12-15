# SQLGenix

## Overview
SQLGenix is a powerful and flexible SQL query generator designed to simplify database interactions. It allows developers to easily construct SQL queries for various operations such as SELECT, INSERT, UPDATE, and DELETE without needing to write raw SQL code manually. This tool is particularly useful for rapid application development and database management tasks.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Inserting Data](#inserting-data)
  - [Selecting Data](#selecting-data)
  - [Updating Data](#updating-data)
  - [Deleting Data](#deleting-data)
- [Configuration](#configuration)
- [Dependencies](#dependencies)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

## Features
- **Dynamic Query Generation**: Automatically generate SQL queries based on user-defined parameters.
- **Support for Multiple SQL Operations**: Easily perform SELECT, INSERT, UPDATE, and DELETE operations.
- **Database Abstraction**: Interact with different database systems without changing the core code.
- **Error Handling**: Built-in error handling to manage SQL exceptions gracefully.
- **Lightweight and Fast**: Optimized for performance, ensuring quick execution of queries.

## Installation
To install SQLGenix, follow these steps:
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/SQLGenix.git
   ```
2. Navigate to the project directory:
   ```bash
   cd SQLGenix
   ```
3. Install the dependencies using Composer:
   ```bash
   composer install
   ```

## Usage
Here are some examples of how to use SQLGenix:

### Inserting Data
```php
require 'src/SQLInsert.php';

$db = new Database(); // Assume Database is a class that handles DB connection
$insert = new SQLInsert($db);
$insert->into('users')
       ->set(['name', 'email'], ['John Doe', 'john@example.com'])
       ->execute();
```

### Selecting Data
```php
require 'src/SQLSelect.php';

$db = new Database();
$select = new SQLSelect($db);
$result = $select->select(['name', 'email'])
                 ->from('users')
                 ->where('email = "john@example.com"')
                 ->execute();
```

### Updating Data
```php
require 'src/SQLUpdate.php';

$db = new Database();
$update = new SQLUpdate($db);
$update->table('users')
       ->set('name', 'Jane Doe')
       ->where('email = "john@example.com"')
       ->execute();
```

### Deleting Data
```php
require 'src/SQLDelete.php';

$db = new Database();
$delete = new SQLDelete($db);
$delete->from('users')
       ->where('email = "john@example.com"')
       ->execute();
```

## Configuration
You can configure SQLGenix by modifying the connection settings in your application. Ensure that you have the correct database credentials and connection parameters set up in your environment.

## Dependencies
- PHP 7.0 or higher
- A compatible database (MySQL, PostgreSQL, SQLite, etc.)
- Composer for dependency management

## Testing
To run the tests for SQLGenix, use the following command:
```bash
composer test
```
Ensure that PHPUnit is installed as a development dependency.

## Contributing
Contributions are welcome! Please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and commit them.
4. Push your branch to your forked repository.
5. Submit a pull request.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.

## Contact
For any inquiries, please [contact me](mailto:tomcucherosset@hotmail.fr).