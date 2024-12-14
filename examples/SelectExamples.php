<?php
class SelectExamples {
    public function exampleSelectAll() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $select = new SQLSelect($db);
        $result = $select->from('users')->execute();
        print_r($result);
    }

    public function exampleSelectWithCondition() {
        $db = new Database('localhost', 'your_database', 'your_username', 'your_password');
        $select = new SQLSelect($db);
        $result = $select->from('users')->where('age > 18')->execute();
        print_r($result);
    }
}
