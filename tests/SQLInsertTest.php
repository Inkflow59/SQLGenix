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

    public function testInsert() {
        $this->insert->into('test_table')
                     ->set(['column1'], ['value1'])
                     ->execute($this->db);

        // Verify that the record has been inserted
        $select = new SQLSelect($this->db);
        $result = $select->select(['*'])->from('test_table')->where('column1 = "value1"')->execute();
        $this->assertNotEmpty($result, 'Record with column1 = value1 should be inserted.');
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
