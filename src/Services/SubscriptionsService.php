<?php

namespace NowPayments\Services;

use NowPayments\Exception\ValidationException;

/**
 * Service for subscription operations
 */
class SubscriptionsService extends AbstractService
{
    /**
     * Create a subscription plan
     *
     * @param array $data Plan data
     * @return array
     * @throws ValidationException
     */
    public function createPlan(array $data): array
    {
        $this->validateRequiredFields($data, ['title', 'interval_day', 'amount', 'currency']);
        
        return $this->post('subscriptions/plans', $data);
    }

    /**
     * Update a subscription plan
     *
     * @param string $planId Plan ID
     * @param array $data Update data
     * @return array
     */
    public function updatePlan(string $planId, array $data): array
    {
        return $this->patch("subscriptions/plans/{$planId}", $data);
    }

    /**
     * Get a subscription plan
     *
     * @param string $planId Plan ID
     * @return array
     */
    public function getPlan(string $planId): array
    {
        return $this->get("subscriptions/plans/{$planId}");
    }

    /**
     * List all subscription plans
     *
     * @return array
     */
    public function listPlans(): array
    {
        return $this->get('subscriptions/plans');
    }

    /**
     * Create a subscription
     *
     * @param array $data Subscription data
     * @return array
     * @throws ValidationException
     */
    public function create(array $data): array
    {
        $this->validateRequiredFields($data, ['plan_id', 'email']);
        
        return $this->post('subscriptions', $data);
    }

    /**
     * Get a subscription
     *
     * @param string $subscriptionId Subscription ID
     * @return array
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->get("subscriptions/{$subscriptionId}");
    }

    /**
     * List subscriptions with optional filters
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function list(array $filters = []): array
    {
        return $this->get('subscriptions', $filters);
    }

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId Subscription ID
     * @return array
     */
    public function cancel(string $subscriptionId): array
    {
        return $this->delete("subscriptions/{$subscriptionId}");
    }

    /**
     * Create a subscription plan with validation
     *
     * @param string $title Plan title
     * @param int $intervalDay Interval in days
     * @param float $amount Amount in fiat
     * @param string $currency Fiat currency
     * @param array $options Additional options
     * @return array
     */
    public function createSubscriptionPlan(
        string $title,
        int $intervalDay,
        float $amount,
        string $currency,
        array $options = []
    ): array {
        $data = array_merge([
            'title' => $title,
            'interval_day' => $intervalDay,
            'amount' => $amount,
            'currency' => $currency
        ], $options);

        return $this->createPlan($data);
    }

    /**
     * Create a subscription with validation
     *
     * @param string $planId Plan ID
     * @param string $email Customer email
     * @param array $options Additional options
     * @return array
     */
    public function createSubscription(string $planId, string $email, array $options = []): array
    {
        $data = array_merge([
            'plan_id' => $planId,
            'email' => $email
        ], $options);

        return $this->create($data);
    }

    /**
     * List subscriptions by plan
     *
     * @param string $planId Plan ID
     * @param int $limit Number of subscriptions to return
     * @param int $page Page number
     * @return array
     */
    public function listByPlan(string $planId, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'plan_id' => $planId,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * List subscriptions by status
     *
     * @param string $status Subscription status
     * @param int $limit Number of subscriptions to return
     * @param int $page Page number
     * @return array
     */
    public function listByStatus(string $status, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'status' => $status,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * Update subscription plan amount
     *
     * @param string $planId Plan ID
     * @param float $amount New amount
     * @return array
     */
    public function updatePlanAmount(string $planId, float $amount): array
    {
        return $this->updatePlan($planId, ['amount' => $amount]);
    }

    /**
     * Update subscription plan interval
     *
     * @param string $planId Plan ID
     * @param int $intervalDay New interval in days
     * @return array
     */
    public function updatePlanInterval(string $planId, int $intervalDay): array
    {
        return $this->updatePlan($planId, ['interval_day' => $intervalDay]);
    }
} 