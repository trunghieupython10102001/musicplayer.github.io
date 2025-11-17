<?php
/**
 * Database Connection Handler
 * 
 * Provides a singleton PDO database connection
 * Uses prepared statements to prevent SQL injection
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error and return user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please try again later.'
            ]));
        }
    }
    
    /**
     * Get singleton instance of database connection
     * 
     * @return Database Single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection object
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a SELECT query and return all results
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array Result set
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Single row or false
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute an INSERT, UPDATE, or DELETE query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return bool Success status
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     * 
     * @return string Last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database instance quickly
 * 
 * @return PDO Database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

