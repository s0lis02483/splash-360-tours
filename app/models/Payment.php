<?php
// FILE: /app/models/Payment.php

/**
 * Payment Model
 *
 * Handles payment transaction management
 */
class Payment extends Model {
    protected $table = 'payments';
    protected $tenantIsolation = false; // We handle tenant context manually

    /**
     * Get payments by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get payments by invoice
     *
     * @param int $invoiceId Invoice ID
     * @return array
     */
    public function getByInvoice($invoiceId) {
        $sql = "SELECT * FROM {$this->table} WHERE invoice_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    /**
     * Generate transaction ID
     *
     * @return string
     */
    public function generateTransactionId() {
        return 'TXN' . strtoupper(bin2hex(random_bytes(8)));
    }

    /**
     * Create payment
     *
     * @param array $data Payment data
     * @return int|false
     */
    public function create($data) {
        // Generate transaction ID if not provided
        if (!isset($data['transaction_id'])) {
            $data['transaction_id'] = $this->generateTransactionId();
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
     * Process payment (simulated)
     *
     * @param int $invoiceId Invoice ID
     * @param int $tenantId Tenant ID
     * @param float $amount Amount
     * @param string $method Payment method
     * @return array Result with success status and message
     */
    public function processPayment($invoiceId, $tenantId, $amount, $method = 'dummy') {
        // Simulate payment processing
        // In real application, this would integrate with payment gateway

        $transactionId = $this->generateTransactionId();

        // Create payment record
        $paymentId = $this->create([
            'invoice_id' => $invoiceId,
            'tenant_id' => $tenantId,
            'amount' => $amount,
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'status' => 'completed'
        ]);

        if ($paymentId) {
            // Update invoice status
            $invoiceModel = new Invoice();
            $invoiceModel->updateStatus($invoiceId, 'paid');

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment processing failed'
        ];
    }
}
