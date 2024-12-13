<?php
use PHPUnit\Framework\TestCase;

require_once '../src/SQLUpdate.php';
require_once '../src/SQLSelect.php';
require_once '../src/SQLInsert.php';
require_once '../src/SQLExecutor.php';
require_once '../src/Database.php';

class SQLGenixTest extends TestCase {
    protected $db;
    protected $sqlUpdate;
    protected $sqlSelect;
    protected $sqlInsert;
    protected $sqlExecutor;

    protected function setUp(): void {
        $this->db = new Database('localhost', 'your_database_name', 'your_username', 'your_password'); 
        $this->sqlUpdate = new SQLUpdate($this->db);
        $this->sqlSelect = new SQLSelect($this->db);
        $this->sqlInsert = new SQLInsert($this->db);
        $this->sqlExecutor = new SQLExecutor($this->db);
    }

    public function testInsertData() {
        $result = $this->sqlInsert
            ->into('table_name')
            ->set(['column1'], ['value1'])
            ->execute($this->db);
        $this->assertTrue($result);
    }

    public function testSelectData() {
        $result = $this->sqlSelect
            ->select(['*'])
            ->from('table_name')
            ->execute();
        $this->assertIsArray($result);
    }

    public function testUpdateData() {
        $result = $this->sqlUpdate
            ->table('table_name')
            ->set(['column1' => 'new_value'])
            ->where('id = 1')
            ->execute();
        $this->assertIsInt($result);
    }

    public function testExecuteSQL() {
        $result = $this->sqlExecutor->execute('SELECT * FROM table_name');
        $this->assertIsArray($result);
    }

    public function testDatabaseConnection() {
        $this->assertNotNull($this->db->getConnection());
    }
}
