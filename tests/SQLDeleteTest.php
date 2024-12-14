<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once '../src/SQLDelete.php';
require_once '../src/Database.php';
require_once '../src/SQLSelect.php';

class SQLDeleteTest extends TestCase {
    private $db;
    private $delete;

    protected function setUp(): void {
        $this->db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
        $this->delete = new SQLDelete($this->db);
    }

    public function testDelete() {
        // Assume a record with ID 1 exists
        $this->delete->from('test_table')->where('id = 1')->execute($this->db);

        // Verify that the record has been deleted
        $select = new SQLSelect($this->db);
        $result = $select->select(['*'])->from('test_table')->where('id = 1')->execute();
        $this->assertEmpty($result, 'Record with ID 1 should be deleted.');
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
