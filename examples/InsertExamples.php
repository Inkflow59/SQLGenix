<?php
class InsertExamples {
    public function exampleInsertUser() {
        $db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $insert = new SQLInsert($db);
        $result = $insert->into('Users')
            ->set(['username', 'email'], ['Alice', 'alice@example.com'])
            ->execute($db);
        echo 'User insertion successful.';
    }
}
