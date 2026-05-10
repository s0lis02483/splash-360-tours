<?php
// FILE: /app/models/Invoice.php

/**
 * Invoice Model
 *
 * Handles invoice management for billing
 */
class Invoice extends Model {
    protected $table = 'invoices';
    protected $tenantIsolation = false; // We handle tenant context manually

    /**
     * Get invoices by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT i.*, p.name as plan_name
                FROM {$this->table} i
                LEFT JOIN plans p ON i.plan_id = p.id
                WHERE i.tenant_id = ?
                ORDER BY i.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Generate invoice number
     *
     * @return string
     */
    public function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get last invoice number for this month
        $sql = "SELECT invoice_number FROM {$this->table}
                WHERE invoice_number LIKE ?
                ORDER BY id DESC
                LIMIT 1";

        $pattern = "$prefix-$year$month%";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pattern]);
        $result = $stmt->fetch();

        if ($result) {
            // Extract sequence number and increment
            $lastNumber = $result['invoice_number'];
            $parts = explode('-', $lastNumber);
            $sequence = (int)$parts[1] + 1;
        } else {
            $sequence = 1;
        }

        return sprintf("$prefix-%s%s%04d", $year, $month, $sequence);
    }

    /**
     * Create invoice
     *
     * @param array $data Invoice data
     * @return int|false
     */
    public function create($data) {
        // Generate invoice number if not provided
        if (!isset($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $values = array_values($data);

        $fieldsList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders) RETURNING id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : false;
    }

    /**
     * Update invoice status
     *
     * @param int $invoiceId Invoice ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($invoiceId, $status) {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $invoiceId]);
    }

    /**
     * Get invoice by ID
     *
     * @param int $invoiceId Invoice ID
     * @return array|false
     */
    public function getById($invoiceId) {
        $sql = "SELECT i.*, p.name as plan_name, t.name as tenant_name, t.email as tenant_email
                FROM {$this->table} i
                LEFT JOIN plans p ON i.plan_id = p.id
                LEFT JOIN tenants t ON i.tenant_id = t.id
                WHERE i.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        return $stmt->fetch();
    }
}
