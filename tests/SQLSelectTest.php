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

    public function testSelectUser() {
        $result = $this->select->from('Users')->execute();
        $this->assertNotEmpty($result, 'User selection should return results.');
    }

    public function testSelectPost() {
        $result = $this->select->from('Posts')->execute();
        $this->assertNotEmpty($result, 'Post selection should return results.');
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
