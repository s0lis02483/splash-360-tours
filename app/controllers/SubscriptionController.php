<?php
// FILE: /app/controllers/SubscriptionController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * SubscriptionController
 *
 * Handles subscription and billing management
 */
class SubscriptionController extends Controller {

    /**
     * Show subscription details
     */
    public function index() {
        $this->requireAuth();

        $tenantId = $this->auth->tenantId();

        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getActiveByTenant($tenantId);

        $planModel = new Plan();
        $availablePlans = $planModel->getActive();

        $usageTrackerModel = new UsageTracker();
        $usageData = $usageTrackerModel->getUsageWithLimits($tenantId);

        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getByTenant($tenantId, 10, 0);

        $data = [
            'subscription' => $subscription,
            'available_plans' => $availablePlans,
            'usage_data' => $usageData,
            'invoices' => $invoices
        ];

        $this->view('subscriptions/index', $data);
    }

    /**
     * Show available plans
     */
    public function plans() {
        $this->requireAuth();

        $planModel = new Plan();
        $plans = $planModel->getActive();

        $data = ['plans' => $plans];

        $this->view('subscriptions/plans', $data);
    }

    /**
     * Change subscription plan
     */
    public function changePlan() {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/subscriptions');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/subscriptions');
        }

        $planId = $this->request->post('plan_id');

        if (!$planId) {
            $this->session->setFlash('error', 'Please select a plan');
            $this->redirect('/subscriptions/plans');
        }

        $planModel = new Plan();
        $plan = $planModel->getById($planId);

        if (!$plan) {
            $this->session->setFlash('error', 'Invalid plan selected');
            $this->redirect('/subscriptions/plans');
        }

        $tenantId = $this->auth->tenantId();

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Create new subscription
            $subscriptionModel = new Subscription();
            $subscriptionData = [
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 month'))
            ];

            $subscriptionId = $subscriptionModel->create($subscriptionData);

            if (!$subscriptionId) {
                throw new Exception('Failed to create subscription');
            }

            // Create invoice
            $invoiceModel = new Invoice();
            $invoiceData = [
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'amount' => $plan['price'],
                'status' => 'pending',
                'due_date' => date('Y-m-d', strtotime('+7 days'))
            ];

            $invoiceId = $invoiceModel->create($invoiceData);

            if (!$invoiceId) {
                throw new Exception('Failed to create invoice');
            }

            $db->commit();

            $this->session->setFlash('success', 'Plan changed successfully. Please complete payment.');
            $this->redirect('/subscriptions');

        } catch (Exception $e) {
            $db->rollback();
            $this->session->setFlash('error', 'Failed to change plan: ' . $e->getMessage());
            $this->redirect('/subscriptions/plans');
        }
    }

    /**
     * Process payment for invoice (simulated)
     */
    public function pay($invoiceId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/subscriptions');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/subscriptions');
        }

        $tenantId = $this->auth->tenantId();

        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->getById($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            $this->session->setFlash('error', 'Invoice not found');
            $this->redirect('/subscriptions');
        }

        if ($invoice['status'] === 'paid') {
            $this->session->setFlash('info', 'Invoice already paid');
            $this->redirect('/subscriptions');
        }

        // Process payment (simulated)
        $paymentModel = new Payment();
        $result = $paymentModel->processPayment($invoiceId, $tenantId, $invoice['amount'], 'dummy');

        if ($result['success']) {
            $this->session->setFlash('success', 'Payment processed successfully');
        } else {
            $this->session->setFlash('error', $result['message']);
        }

        $this->redirect('/subscriptions');
    }
}
