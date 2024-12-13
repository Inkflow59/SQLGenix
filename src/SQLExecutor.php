<?php
/**
 * Class SQLExecutor
 *
 * This class is responsible for executing SQL queries against a database.
 * It provides methods to perform various SQL operations such as SELECT, INSERT, UPDATE, and DELETE.
 *
 * @package SQLFlow
 */
require_once 'Database.php';

class SQLExecutor {
    /**
     * The PDO instance used for database connections.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * The logger instance used for logging.
     *
     * @var Logger
     */
    private $logger;

    /**
     * Constructs a new SQLExecutor instance.
     *
     * @param Database $db The Database instance to use for executing queries.
     */
    public function __construct(Database $db) {
        $this->pdo = $db->getConnection(); // Assuming getConnection() returns a PDO instance
        $this->logger = new Logger(); // Initialize a default logger or pass it as a parameter if needed
    }

    /**
     * Executes a SQL query.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for the SQL query.
     * @return mixed The result of the executed query.
     * @throws Exception If the query fails to execute.
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            // Log the executed query
            $this->logger->log("Executed query: $query");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle the exception (e.g., log it, rethrow it, etc.)
            $this->logger->log("Error executing query: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Handle any other exceptions
            $this->logger->log("An error occurred: " . $e->getMessage());
            throw $e;
        }
    }
}