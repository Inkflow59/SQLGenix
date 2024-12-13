# SQLGenix

## Overview
SQLGenix is a powerful and flexible SQL query generator designed to simplify database interactions. It allows developers to easily construct SQL queries for various operations such as SELECT, INSERT, UPDATE, and DELETE without needing to write raw SQL code manually. This tool is particularly useful for rapid application development and database management tasks.

## Features
- **Dynamic Query Generation**: Automatically generate SQL queries based on user-defined parameters.
- **Support for Multiple SQL Operations**: Easily perform SELECT, INSERT, UPDATE, and DELETE operations.
- **Database Abstraction**: Interact with different database systems without changing the core code.
- **Error Handling**: Built-in error handling to manage SQL exceptions gracefully.
- **Lightweight and Fast**: Optimized for performance, ensuring quick execution of queries.

## Installation
To install SQLGenix, simply clone the repository and include the necessary files in your project:

```bash
git clone https://github.com/yourusername/SQLGenix.git
```

Make sure to include the PHP files in your project and set up your database connection accordingly.

## Usage
Here’s a quick example of how to use SQLGenix:

### Example: Inserting Data
```php
require 'src/SQLInsert.php';

$insert = new SQLInsert();
$insert->setTable('users');
$insert->setData(['name' => 'John Doe', 'email' => 'john@example.com']);
$insert->execute();
```

### Example: Selecting Data
```php
require 'src/SQLSelect.php';

$select = new SQLSelect();
$select->setTable('users');
$select->addCondition('email', '=', 'john@example.com');
$result = $select->execute();
```

## Dependencies
- PHP 7.0 or higher
- A compatible database (MySQL, PostgreSQL, SQLite, etc.)

## License
This project is licensed under the MIT License. See the LICENSE file for more details.

## Contributing
Contributions are welcome! Please feel free to submit a pull request or open an issue for any suggestions or improvements.

## Contact
For any inquiries, please contact [tomcucherosset@hotmail.fr].