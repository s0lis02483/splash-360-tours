<?php
// FILE: /tests/TenantIsolationTest.php

/**
 * Tenant Isolation Tests
 *
 * Verify that tenant data isolation is working correctly
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/Property.php';

class TenantIsolationTest {
    public function testPropertyIsolation() {
        // Mock authentication for tenant 1
        $session = new Session();
        $session->set('user_id', 2); // John from tenant 1
        $session->set('user', [
            'id' => 2,
            'tenant_id' => 1,
            'role' => 'tenant_admin'
        ]);

        $propertyModel = new Property();
        $properties = $propertyModel->findAll();

        // All properties should belong to tenant 1
        $allBelongToTenant = true;
        foreach ($properties as $property) {
            if ($property['tenant_id'] != 1) {
                $allBelongToTenant = false;
                break;
            }
        }

        if ($allBelongToTenant && count($properties) > 0) {
            echo "✓ Property tenant isolation test passed\n";
            return true;
        } else {
            echo "✗ Property tenant isolation test failed\n";
            return false;
        }
    }

    public function runAll() {
        echo "Running Tenant Isolation Tests...\n";
        $this->testPropertyIsolation();
    }
}

if (php_sapi_name() === 'cli') {
    $test = new TenantIsolationTest();
    $test->runAll();
}
