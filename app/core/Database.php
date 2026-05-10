<?php
// FILE: /app/core/Database.php

/**
 * Database Class
 *
 * Handles PDO database connections and query execution
 * Singleton pattern to ensure single connection instance
 */
class Database {
    private static $instance = null;
    private $pdo;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $config = require __DIR__ . '/../../config/database.php';

        $port = $config['port'] ?? '5432';
        $dsn = "pgsql:host={$config['host']};port={$port};dbname={$config['database']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Execute a query and return results
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a query and return single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute insert/update/delete query
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return bool
     */
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get last insert ID
     *
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->pdo->rollBack();
    }
}
