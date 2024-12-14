<?php
class DeleteExamples {
    public function exampleDeleteUser() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $delete = new SQLDelete($db);
        $result = $delete->from('users')
            ->where("name = 'Alice'")
            ->execute($db);
        echo 'Deletion successful.';
    }
}
