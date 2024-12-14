<?php
class SelectExamples {
    public function exampleSelectAllUsers() {
        $db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $select = new SQLSelect($db);
        $result = $select->from('Users')->execute();
        print_r($result);
    }

    public function exampleSelectAllPosts() {
        $db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $select = new SQLSelect($db);
        $result = $select->from('Posts')->execute();
        print_r($result);
    }
}
