<?php
class UpdateExamples {
    public function exampleUpdateUser() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $update = new SQLUpdate($db);
        $result = $update->table('users')
            ->set('email', 'newemail@example.com')
            ->where('name = "John Doe"')
            ->execute();
        echo 'Update successful.';
    }
}
