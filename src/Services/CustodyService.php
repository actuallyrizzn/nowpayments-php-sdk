<?php

namespace NowPayments\Services;

use NowPayments\Exception\ValidationException;

/**
 * Service for custody (sub-account) operations
 */
class CustodyService extends AbstractService
{
    /**
     * Create a new user account
     *
     * @param array $data User data
     * @return array
     */
    public function createUser(array $data = []): array
    {
        return $this->post('sub-partner/balance', $data);
    }

    /**
     * Get user balance
     *
     * @param int $userId User ID
     * @return array
     */
    public function getBalance(int $userId): array
    {
        return $this->get("sub-partner/balance/{$userId}");
    }

    /**
     * List all user accounts
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function listUsers(array $filters = []): array
    {
        return $this->get('sub-partner', $filters);
    }

    /**
     * Create a payment (deposit) for user
     *
     * @param array $data Payment data
     * @return array
     * @throws ValidationException
     */
    public function createPayment(array $data): array
    {
        $this->validateRequiredFields($data, ['user_id', 'currency']);
        
        return $this->post('sub-partner/payment', $data);
    }

    /**
     * Transfer funds between accounts
     *
     * @param array $data Transfer data
     * @return array
     * @throws ValidationException
     */
    public function transfer(array $data): array
    {
        $this->validateRequiredFields($data, ['from_id', 'to_id', 'currency', 'amount']);
        
        return $this->post('sub-partner/transfer', $data);
    }

    /**
     * List transfers
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function listTransfers(array $filters = []): array
    {
        return $this->get('sub-partner/transfers', $filters);
    }

    /**
     * Get transfer details
     *
     * @param string $transferId Transfer ID
     * @return array
     */
    public function getTransfer(string $transferId): array
    {
        return $this->get("sub-partner/transfer/{$transferId}");
    }

    /**
     * Withdraw funds from user (write-off)
     *
     * @param array $data Withdrawal data
     * @return array
     * @throws ValidationException
     */
    public function withdraw(array $data): array
    {
        $this->validateRequiredFields($data, ['user_id', 'currency', 'amount']);
        
        return $this->post('sub-partner/write-off', $data);
    }

    /**
     * Create a user with validation
     *
     * @param string|null $externalId External ID
     * @param string|null $email Email address
     * @return array
     */
    public function createUserAccount(?string $externalId = null, ?string $email = null): array
    {
        $data = [];
        
        if ($externalId !== null) {
            $data['external_id'] = $externalId;
        }
        
        if ($email !== null) {
            $data['email'] = $email;
        }
        
        return $this->createUser($data);
    }

    /**
     * Create a deposit payment for user
     *
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float|null $amount Amount (optional)
     * @param string|null $trackId Track ID (optional)
     * @return array
     */
    public function createDepositPayment(int $userId, string $currency, ?float $amount = null, ?string $trackId = null): array
    {
        $data = [
            'user_id' => $userId,
            'currency' => $currency
        ];
        
        if ($amount !== null) {
            $data['amount'] = $amount;
        }
        
        if ($trackId !== null) {
            $data['track_id'] = $trackId;
        }
        
        return $this->createPayment($data);
    }

    /**
     * Transfer funds between users
     *
     * @param int $fromUserId Source user ID
     * @param int $toUserId Target user ID
     * @param string $currency Currency
     * @param float $amount Amount
     * @return array
     */
    public function transferBetweenUsers(int $fromUserId, int $toUserId, string $currency, float $amount): array
    {
        return $this->transfer([
            'from_id' => $fromUserId,
            'to_id' => $toUserId,
            'currency' => $currency,
            'amount' => $amount
        ]);
    }

    /**
     * Transfer funds from user to master account
     *
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $amount Amount
     * @return array
     */
    public function transferToMaster(int $userId, string $currency, float $amount): array
    {
        return $this->transfer([
            'from_id' => $userId,
            'to_id' => 0, // Master account
            'currency' => $currency,
            'amount' => $amount
        ]);
    }

    /**
     * Transfer funds from master account to user
     *
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $amount Amount
     * @return array
     */
    public function transferFromMaster(int $userId, string $currency, float $amount): array
    {
        return $this->transfer([
            'from_id' => 0, // Master account
            'to_id' => $userId,
            'currency' => $currency,
            'amount' => $amount
        ]);
    }

    /**
     * Withdraw funds from user to external address
     *
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $amount Amount
     * @param string $address External address
     * @param array $options Additional options
     * @return array
     */
    public function withdrawToAddress(int $userId, string $currency, float $amount, string $address, array $options = []): array
    {
        $data = array_merge([
            'user_id' => $userId,
            'currency' => $currency,
            'amount' => $amount,
            'address' => $address
        ], $options);

        return $this->withdraw($data);
    }

    /**
     * Withdraw funds from user to master account
     *
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $amount Amount
     * @return array
     */
    public function withdrawToMaster(int $userId, string $currency, float $amount): array
    {
        return $this->withdraw([
            'user_id' => $userId,
            'currency' => $currency,
            'amount' => $amount
        ]);
    }

    /**
     * List transfers by user
     *
     * @param int $userId User ID
     * @param int $limit Number of transfers to return
     * @param int $offset Offset
     * @return array
     */
    public function listTransfersByUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->listTransfers([
            'id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * List transfers by status
     *
     * @param string $status Transfer status
     * @param int $limit Number of transfers to return
     * @param int $offset Offset
     * @return array
     */
    public function listTransfersByStatus(string $status, int $limit = 10, int $offset = 0): array
    {
        return $this->listTransfers([
            'status' => $status,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
} 