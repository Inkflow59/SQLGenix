<?php
use PHPUnit\Framework\TestCase;

require_once '../src/Logger.php';

class LoggerTest extends TestCase {
    private $logger;

    protected function setUp(): void {
        $this->logger = new Logger(); // Initialize logger
    }

    public function testLogMessage() {
        $this->logger->log('Test message');
        // Add assertions to verify logging functionality
    }

    protected function tearDown(): void {
        // Clean up if necessary
    }
}
