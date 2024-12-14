<?php
use PHPUnit\Framework\TestCase;

require_once '../src/SQLInsert.php';
require_once '../src/Database.php';
require_once '../src/SQLSelect.php';

class SQLInsertTest extends TestCase {
    private $db;
    private $insert;

    protected function setUp(): void {
        $this->db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $this->insert = new SQLInsert($this->db);
    }

    public function testInsertUser() {
        $this->insert->into('Users')
                     ->set(['username', 'email'], ['testuser', 'test@example.com'])
                     ->execute($this->db);
        $this->assertTrue(true, 'User should be inserted successfully.');
    }

    public function testInsertPost() {
        $this->insert->into('Posts')
                     ->set(['user_id', 'content'], [1, 'This is a test post.'])
                     ->execute($this->db);
        $this->assertTrue(true, 'Post should be inserted successfully.');
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
