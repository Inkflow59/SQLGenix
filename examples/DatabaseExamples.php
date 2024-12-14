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
        $db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $insert = new SQLInsert($db);
        $resultUser = $insert->into('Users')
            ->set(['username', 'email'], ['John Doe', 'john@example.com'])
            ->execute($db);
        if (!$resultUser) {
            throw new Exception('User insertion failed.');
        }
        $resultPost = $insert->into('Posts')
            ->set(['user_id', 'content'], [1, 'This is a test post.'])
            ->execute($db);
        if (!$resultPost) {
            throw new Exception('Post insertion failed.');
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

    public function testSelectData() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $select = new SQLSelect($db);
        $result = $select->from('users')->where('id = 1')->execute();
        if (!$result) {
            throw new Exception('Data selection failed.');
        }
        print_r($result);
        echo 'Selection successful.';
    }

    public function testUpdateData() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $update = new SQLUpdate($db);
        $result = $update->table('users')
            ->set('name', 'Jane Doe')
            ->where('id = 1')
            ->execute();
        if (!$result) {
            throw new Exception('Data update failed.');
        }
        echo 'Update successful.';
    }
}
