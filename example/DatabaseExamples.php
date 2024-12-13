<?php

class DatabaseExamples {
    public function testDatabaseConnection() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $connection = $db->getConnection();
        if (!$connection) {
            throw new Exception('The connection to the database failed.');
        }
        echo 'Connection successful.';
    }

    public function testInsertData() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $insert = new SQLInsert($db);
        $result = $insert->into('users')
            ->set(['name', 'email'], ['John Doe', 'john@example.com'])
            ->execute($db);
        if (!$result) {
            throw new Exception('Data insertion failed.');
        }
        echo 'Insertion successful.';
    }

    public function testDeleteData() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $delete = new SQLDelete($db);
        $result = $delete->from('users')
            ->where("email = 'john@example.com'")
            ->execute($db);
        if (!$result) {
            throw new Exception('Data deletion failed.');
        }
        echo 'Deletion successful.';
    }

    public function testExecuteQuery() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $db = new SQLExecutor($db);
        $result = $db->execute("SELECT * FROM users WHERE email = 'john@example.com'");
        if (count($result) !== 1) {
            throw new Exception('The SQL query did not return the expected result.');
        }
        echo 'Query executed successfully.';
    }
}
