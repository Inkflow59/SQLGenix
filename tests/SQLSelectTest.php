<?php
use PHPUnit\Framework\TestCase;

require_once '../src/SQLSelect.php';
require_once '../src/Database.php';

class SQLSelectTest extends TestCase {
    private $db;
    private $select;

    protected function setUp(): void {
        $this->db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $this->select = new SQLSelect($this->db);
    }

    public function testSelect() {
        $result = $this->select->from('test_table')->execute();
        // Add assertions to verify the select operation
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
