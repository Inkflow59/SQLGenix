<?php

namespace SQLFlow\Tests;

use PHPUnit\Framework\TestCase;
use SQLFlow\DatabaseConnexion;

require_once __DIR__ . '/../src/DatabaseConnexion.php';

class DatabaseConnexionTest extends TestCase {
    private ?DatabaseConnexion $db;

    protected function setUp(): void {
        // Using test database credentials
        $this->db = new DatabaseConnexion(
            'localhost',     // host
            'test_db',      // database name
            'test_user',    // username
            'test_pass'     // password
        );
    }

    public function testConnection(): void {
        $this->assertNotNull($this->db->getConnection(), 'Database connection should not be null.');
    }

    public function testSuccessfulConnection(): void {
        $this->assertTrue($this->db->isConnected(), 'Database connection should be successful.');
    }
}