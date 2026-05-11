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
        $dsn = "pgsql:host={$config['host']};port={$port};dbname={$config['database']};sslmode=require";

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

        $this->runSchemaMigrations();
    }

    /**
     * Idempotent schema migrations — runs on every cold start.
     * PostgreSQL's ADD COLUMN IF NOT EXISTS makes this safe and cheap.
     */
    private function runSchemaMigrations() {
        $statements = [
            // Property listing details (added 2026-05)
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS bedrooms SMALLINT DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS bathrooms SMALLINT DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS rooms_total SMALLINT DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS building_type VARCHAR(20) DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor SMALLINT DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS has_parking BOOLEAN DEFAULT FALSE",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS monthly_rent NUMERIC(10,2) DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS deposit NUMERIC(10,2) DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS monthly_utilities NUMERIC(10,2) DEFAULT NULL",
            "ALTER TABLE properties ADD COLUMN IF NOT EXISTS specialties TEXT DEFAULT NULL",
        ];

        foreach ($statements as $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                error_log("Schema migration warning: " . $e->getMessage());
                // continue — don't block app boot
            }
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
