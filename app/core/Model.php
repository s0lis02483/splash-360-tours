<?php
// FILE: /app/core/Model.php

/**
 * Base Model Class
 *
 * All models extend this base class
 * Provides common database operations with tenant isolation
 */
class Model {
    protected $db;
    protected $table;
    protected $tenantIsolation = true; // Most models need tenant isolation
    protected $auth;

    /**
     * Constructor - initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * Get current tenant ID from authenticated user
     *
     * @return int|null
     */
    protected function getTenantId() {
        if (!$this->auth->check()) {
            return null;
        }

        $user = $this->auth->user();
        return $user['tenant_id'] ?? null;
    }

    /**
     * Find all records with optional filters
     *
     * @param array $where WHERE conditions
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT
     * @param int $offset OFFSET
     * @return array
     */
    public function findAll($where = [], $orderBy = 'id DESC', $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        // Add tenant isolation if enabled
        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $where['tenant_id'] = $tenantId;
            }
        }

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Add ORDER BY
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        // Add LIMIT and OFFSET
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find single record by ID
     *
     * @param int $id Record ID
     * @return array|false
     */
    public function findById($id) {
        $where = ['id' => $id];

        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $where['tenant_id'] = $tenantId;
            }
        }

        return $this->findOne($where);
    }

    /**
     * Find single record with conditions
     *
     * @param array $where WHERE conditions
     * @return array|false
     */
    public function findOne($where = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $where['tenant_id'] = $tenantId;
            }
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Insert new record
     *
     * @param array $data Data to insert
     * @return int|false Last insert ID or false
     */
    public function insert($data) {
        // Add tenant_id if tenant isolation is enabled
        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $data['tenant_id'] = $tenantId;
            }
        }

        // Add timestamps
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $values = array_values($data);

        $fieldsList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update record by ID
     *
     * @param int $id Record ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        // Add updated_at timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        // Add tenant isolation
        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $sql .= " AND tenant_id = ?";
                $values[] = $tenantId;
            }
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete record by ID
     *
     * @param int $id Record ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $values = [$id];

        // Add tenant isolation
        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $sql .= " AND tenant_id = ?";
                $values[] = $tenantId;
            }
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Count records
     *
     * @param array $where WHERE conditions
     * @return int
     */
    public function count($where = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if ($this->tenantIsolation) {
            $tenantId = $this->getTenantId();
            if ($tenantId) {
                $where['tenant_id'] = $tenantId;
            }
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
