<?php
// FILE: /tests/ApiTest.php

/**
 * API Tests
 *
 * Test public API endpoints
 */

class ApiTest {
    private $apiKey = 'sk_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6';
    private $baseUrl = 'http://localhost/api';

    public function testApiHealth() {
        $url = $this->baseUrl . '/health';
        $response = @file_get_contents($url);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['success']) {
                echo "✓ API health check passed\n";
                return true;
            }
        }

        echo "✗ API health check failed\n";
        return false;
    }

    public function testGetTours() {
        $url = $this->baseUrl . '/tours';
        $context = stream_context_create([
            'http' => [
                'header' => "X-API-KEY: " . $this->apiKey
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['success'] && isset($data['data'])) {
                echo "✓ API get tours test passed\n";
                return true;
            }
        }

        echo "✗ API get tours test failed\n";
        return false;
    }

    public function runAll() {
        echo "Running API Tests...\n";
        $this->testApiHealth();
        $this->testGetTours();
    }
}

if (php_sapi_name() === 'cli') {
    $test = new ApiTest();
    $test->runAll();
}
