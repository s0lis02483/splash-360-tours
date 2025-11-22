<?php
// FILE: /app/models/User.php

/**
 * User Model
 *
 * Handles user authentication and management
 */
class User extends Model {
    protected $table = 'users';

    /**
     * Find user by email
     *
     * @param string $email User email
     * @return array|false
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Verify user password
     *
     * @param string $email User email
     * @param string $password Password to verify
     * @return array|false User data if valid, false otherwise
     */
    public function verifyCredentials($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Create new user
     *
     * @param array $data User data
     * @return int|false User ID or false
     */
    public function create($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->insert($data);
    }

    /**
     * Update user password
     *
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        return $this->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Get users by tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getByTenant($tenantId) {
        $sql = "SELECT id, name, email, role, status, created_at
                FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get platform admins
     *
     * @return array
     */
    public function getPlatformAdmins() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'platform_admin' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update user status
     *
     * @param int $userId User ID
     * @param string $status Status (active/inactive)
     * @return bool
     */
    public function updateStatus($userId, $status) {
        return $this->update($userId, ['status' => $status]);
    }
}
