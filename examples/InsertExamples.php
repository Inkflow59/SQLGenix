<?php
class InsertExamples {
    public function exampleInsertUser() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $insert = new SQLInsert($db);
        $result = $insert->into('users')
            ->set(['name', 'email'], ['Alice', 'alice@example.com'])
            ->execute($db);
        echo 'Insertion successful.';
    }
}
