<?php
require_once 'MySQL/MySQLAdapter.php';
require_once 'PostgreSQL/PostgreSQLAdapter.php';

/**
 * Database Adapter Factory for SQLGenix.
 * 
 * This factory class creates and manages database adapter instances
 * for different database systems (MySQL, PostgreSQL, SQLite, etc.).
 */
class DatabaseAdapterFactory {
    
    /**
     * @var array Registry of created adapter instances.
     */
    private static $adapters = [];
    
    /**
     * @var array Supported database types and their adapter classes.
     */
    private static $supportedTypes = [
        'mysql' => 'MySQLAdapter',
        'postgresql' => 'PostgreSQLAdapter',
        'postgres' => 'PostgreSQLAdapter', // Alias
        'pgsql' => 'PostgreSQLAdapter',    // Alias
    ];
    
    /**
     * Create a database adapter instance.
     * 
     * @param string $type Database type (mysql, postgresql, etc.)
     * @param array $config Database connection configuration
     * @return DatabaseAdapterInterface Database adapter instance
     * @throws InvalidArgumentException If database type is not supported
     * @throws Exception If adapter creation fails
     */
    public static function create($type, $config) {
        $type = strtolower($type);
        
        if (!isset(self::$supportedTypes[$type])) {
            throw new InvalidArgumentException("Database type '$type' is not supported. Supported types: " . implode(', ', array_keys(self::$supportedTypes)));
        }
        
        $adapterClass = self::$supportedTypes[$type];
        $configKey = md5(serialize($config));
        
        // Return existing adapter if already created with same config
        if (isset(self::$adapters[$configKey])) {
            return self::$adapters[$configKey];
        }
        
        try {
            $adapter = self::createAdapter($adapterClass, $config);
            self::$adapters[$configKey] = $adapter;
            return $adapter;
        } catch (Exception $e) {
            throw new Exception("Failed to create $type adapter: " . $e->getMessage());
        }
    }
    
    /**
     * Create adapter instance based on class name and configuration.
     * 
     * @param string $adapterClass Adapter class name
     * @param array $config Configuration array
     * @return DatabaseAdapterInterface Adapter instance
     */
    private static function createAdapter($adapterClass, $config) {
        // Validate required configuration parameters
        $required = ['host', 'database', 'username', 'password'];
        foreach ($required as $param) {
            if (!isset($config[$param])) {
                throw new InvalidArgumentException("Missing required configuration parameter: $param");
            }
        }
        
        // Set default values
        $defaults = [
            'port' => ($adapterClass === 'PostgreSQLAdapter') ? 5432 : 3306,
            'charset' => 'utf8mb4',
            'options' => []
        ];
        $config = array_merge($defaults, $config);
        
        // Create adapter based on type
        switch ($adapterClass) {
            case 'MySQLAdapter':
                return new MySQLAdapter(
                    $config['host'],
                    $config['database'],
                    $config['username'],
                    $config['password'],
                    $config['port'],
                    $config['charset'],
                    $config['options']
                );
                
            case 'PostgreSQLAdapter':
                return new PostgreSQLAdapter(
                    $config['host'],
                    $config['database'],
                    $config['username'],
                    $config['password'],
                    $config['port'],
                    $config['options']
                );
                
            default:
                throw new InvalidArgumentException("Unknown adapter class: $adapterClass");
        }
    }
    
    /**
     * Create a MySQL adapter.
     * 
     * @param array $config MySQL configuration
     * @return MySQLAdapter MySQL adapter instance
     */
    public static function createMySQL($config) {
        return self::create('mysql', $config);
    }
    
    /**
     * Create a PostgreSQL adapter.
     * 
     * @param array $config PostgreSQL configuration
     * @return PostgreSQLAdapter PostgreSQL adapter instance
     */
    public static function createPostgreSQL($config) {
        return self::create('postgresql', $config);
    }
    
    /**
     * Create adapter from DSN string.
     * 
     * @param string $dsn Data Source Name
     * @param string $username Database username
     * @param string $password Database password
     * @param array $options Additional options
     * @return DatabaseAdapterInterface Adapter instance
     * @throws InvalidArgumentException If DSN format is invalid
     */
    public static function createFromDSN($dsn, $username, $password, $options = []) {
        $parsed = self::parseDSN($dsn);
        
        $config = [
            'host' => $parsed['host'],
            'database' => $parsed['database'],
            'username' => $username,
            'password' => $password,
            'port' => $parsed['port'],
            'options' => $options
        ];
        
        if (isset($parsed['charset'])) {
            $config['charset'] = $parsed['charset'];
        }
        
        return self::create($parsed['type'], $config);
    }
    
    /**
     * Parse DSN string into components.
     * 
     * @param string $dsn Data Source Name
     * @return array Parsed DSN components
     * @throws InvalidArgumentException If DSN format is invalid
     */
    private static function parseDSN($dsn) {
        if (!preg_match('/^(\w+):(.+)$/', $dsn, $matches)) {
            throw new InvalidArgumentException("Invalid DSN format: $dsn");
        }
        
        $type = $matches[1];
        $params = $matches[2];
        
        $result = ['type' => $type];
        
        // Parse parameters
        if (strpos($params, ';') !== false) {
            $pairs = explode(';', $params);
            foreach ($pairs as $pair) {
                if (strpos($pair, '=') !== false) {
                    list($key, $value) = explode('=', $pair, 2);
                    $result[$key] = $value;
                }
            }
        } else {
            // Handle simple format like "mysql://host/database"
            if (preg_match('/\/\/([^\/]+)\/(.+)$/', $params, $urlMatches)) {
                $result['host'] = $urlMatches[1];
                $result['database'] = $urlMatches[2];
            }
        }
        
        // Set default ports if not specified
        if (!isset($result['port'])) {
            $result['port'] = ($type === 'pgsql' || $type === 'postgresql') ? 5432 : 3306;
        }
        
        return $result;
    }
    
    /**
     * Get list of supported database types.
     * 
     * @return array Supported database types
     */
    public static function getSupportedTypes() {
        return array_keys(self::$supportedTypes);
    }
    
    /**
     * Check if a database type is supported.
     * 
     * @param string $type Database type
     * @return bool Whether the type is supported
     */
    public static function isSupported($type) {
        return isset(self::$supportedTypes[strtolower($type)]);
    }
    
    /**
     * Register a new database adapter type.
     * 
     * @param string $type Database type identifier
     * @param string $adapterClass Adapter class name
     * @throws InvalidArgumentException If adapter class doesn't implement interface
     */
    public static function registerAdapter($type, $adapterClass) {
        if (!class_exists($adapterClass)) {
            throw new InvalidArgumentException("Adapter class '$adapterClass' does not exist");
        }
        
        $reflection = new ReflectionClass($adapterClass);
        if (!$reflection->implementsInterface('DatabaseAdapterInterface')) {
            throw new InvalidArgumentException("Adapter class '$adapterClass' must implement DatabaseAdapterInterface");
        }
        
        self::$supportedTypes[strtolower($type)] = $adapterClass;
    }
    
    /**
     * Clear adapter registry.
     */
    public static function clearRegistry() {
        self::$adapters = [];
    }
    
    /**
     * Get adapter instance count.
     * 
     * @return int Number of registered adapter instances
     */
    public static function getAdapterCount() {
        return count(self::$adapters);
    }
}