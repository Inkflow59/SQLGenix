# SQLGenix

## Description
SQLGenix is a powerful tool designed to facilitate the interaction between SQL databases and data analysis workflows. It allows users to execute SQL queries, visualize results, and integrate with various data sources seamlessly.

## Features

- **CRUD Operations**: Effortlessly perform Create, Read, Update, and Delete operations on your database.
- **Query Execution**: Execute raw SQL queries with minimal overhead.
- **Logging**: Built-in logging functionality to track database interactions and errors.
- **Error Handling**: Comprehensive error handling to ensure robust application performance.

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
3. Install the required dependencies:
   ```bash
   composer install
   ```

## Usage
To use SQLGenix, you can start by connecting to your database:
```php
require 'src/DatabaseConnexion.php';
$connection = new DatabaseConnexion();
$connection->connect();
```
After establishing a connection, you can execute SQL queries and retrieve results.

## Contributing
Contributions are welcome! Please follow these steps to contribute:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request detailing your changes.
