<?php
use PHPUnit\Framework\TestCase;

require_once '../src/Database.php';
require_once '../src/Logger.php';

class DatabaseTest extends TestCase {
    private $db;

    protected function setUp(): void {
        // Set up a mock database connection for testing
        $this->db = new Database('localhost', 'test_db', 'test_user', 'test_pass');
    }

    public function testConnection() {
        $this->assertTrue($this->db->isConnected(), 'Database should be connected.');
    }

    public function testExecuteQuery() {
        $result = $this->db->executeQuery('SELECT 1');
        $this->assertNotNull($result, 'Query execution should return a result.');
    }

    public function testTransaction() {
        $this->db->beginTransaction();
        $this->assertTrue($this->db->isConnected(), 'Database should be connected during transaction.');
        $this->db->commit();
    }

    public function testErrorHandling() {
        $result = $this->db->executeQuery('INVALID SQL');
        $this->assertNull($result, 'Executing an invalid query should return null.');
    }

    public function testDisconnect() {
        $this->db->disconnect();
        $this->assertFalse($this->db->isConnected(), 'Database should be disconnected.');
    }

    public function testLastErrorRetrieval() {
        $this->db->executeQuery('INVALID SQL');
        $lastError = $this->db->getLastError();
        $this->assertNotEmpty($lastError, 'Last error information should not be empty.');
    }

    public function testTransactionRollback() {
        $this->db->beginTransaction();
        $this->db->executeQuery('INVALID SQL'); // This should fail
        $this->db->rollback();
        $this->assertTrue($this->db->isConnected(), 'Database should still be connected after rollback.');
    }

    public function testMultipleQueryExecutions() {
        $this->db->executeQuery('CREATE TEMPORARY TABLE test (id INT)');
        $result = $this->db->executeQuery('INSERT INTO test (id) VALUES (1)');
        $this->assertNotNull($result, 'Insert query should return a result.');
        $result = $this->db->executeQuery('SELECT * FROM test');
        $this->assertNotNull($result, 'Select query should return a result.');
    }

    protected function tearDown(): void {
        // Clean up the database connection
        $this->db->disconnect();
    }
}
