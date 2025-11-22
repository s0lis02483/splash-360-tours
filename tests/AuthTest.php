<?php
// FILE: /tests/AuthTest.php

/**
 * Authentication Tests
 *
 * Basic functional tests for authentication
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/User.php';

class AuthTest {
    public function testUserCreation() {
        $userModel = new User();

        // Test user creation with password hashing
        $userData = [
            'tenant_id' => 1,
            'name' => 'Test User',
            'email' => 'test' . time() . '@example.com',
            'password' => 'testpassword123',
            'role' => 'user',
            'status' => 'active'
        ];

        $userId = $userModel->create($userData);

        if ($userId) {
            echo "✓ User creation test passed\n";
            return true;
        } else {
            echo "✗ User creation test failed\n";
            return false;
        }
    }

    public function testPasswordVerification() {
        $userModel = new User();

        $email = 'john@luxuryrealty.com';
        $password = 'password';

        $user = $userModel->verifyCredentials($email, $password);

        if ($user && $user['email'] === $email) {
            echo "✓ Password verification test passed\n";
            return true;
        } else {
            echo "✗ Password verification test failed\n";
            return false;
        }
    }

    public function runAll() {
        echo "Running Authentication Tests...\n";
        $this->testUserCreation();
        $this->testPasswordVerification();
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new AuthTest();
    $test->runAll();
}
