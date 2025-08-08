<?php

namespace NowPayments\Services;

use NowPayments\Exception\ValidationException;

/**
 * Service for payout operations
 */
class PayoutsService extends AbstractService
{
    /**
     * Create a payout batch
     *
     * @param array $data Payout data
     * @return array
     * @throws ValidationException
     */
    public function create(array $data): array
    {
        $this->validateRequiredFields($data, ['withdrawals']);
        
        // Add authorization header for payouts
        $options = ['json' => $data];
        if (isset($data['auth_token'])) {
            $options['headers'] = ['Authorization' => 'Bearer ' . $data['auth_token']];
            unset($data['auth_token']);
            $options['json'] = $data;
        }
        
        return $this->request('POST', 'payout', $options);
    }

    /**
     * Verify payout with 2FA code
     *
     * @param string $batchId Batch ID
     * @param string $code 2FA code
     * @return array
     */
    public function verify(string $batchId, string $code): array
    {
        return $this->post("payout/{$batchId}/verify", ['code' => $code]);
    }

    /**
     * Get payout status
     *
     * @param string $batchId Batch ID
     * @return array
     */
    public function getStatus(string $batchId): array
    {
        return $this->get("payout/{$batchId}");
    }

    /**
     * List payouts with optional filters
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function list(array $filters = []): array
    {
        return $this->get('payout', $filters);
    }

    /**
     * Validate payout address
     *
     * @param string $address Wallet address
     * @param string $currency Currency
     * @param string|null $extraId Extra ID (memo/tag)
     * @return array
     */
    public function validateAddress(string $address, string $currency, ?string $extraId = null): array
    {
        $data = [
            'address' => $address,
            'currency' => $currency
        ];
        
        if ($extraId !== null) {
            $data['extra_id'] = $extraId;
        }
        
        return $this->post('payout/validate-address', $data);
    }

    /**
     * Create a payout with validation
     *
     * @param array $withdrawals Array of withdrawal objects
     * @param array $options Additional options
     * @return array
     */
    public function createPayout(array $withdrawals, array $options = []): array
    {
        $data = array_merge([
            'withdrawals' => $withdrawals
        ], $options);

        return $this->create($data);
    }

    /**
     * Create a single payout
     *
     * @param string $address Wallet address
     * @param string $currency Currency
     * @param float $amount Amount
     * @param array $options Additional options
     * @return array
     */
    public function createSinglePayout(string $address, string $currency, float $amount, array $options = []): array
    {
        $withdrawal = array_merge([
            'address' => $address,
            'currency' => $currency,
            'amount' => $amount
        ], $options);

        return $this->createPayout([$withdrawal]);
    }

    /**
     * Create a payout with fiat amount
     *
     * @param string $address Wallet address
     * @param string $currency Currency
     * @param float $fiatAmount Fiat amount
     * @param string $fiatCurrency Fiat currency
     * @param array $options Additional options
     * @return array
     */
    public function createPayoutWithFiatAmount(
        string $address,
        string $currency,
        float $fiatAmount,
        string $fiatCurrency,
        array $options = []
    ): array {
        $withdrawal = array_merge([
            'address' => $address,
            'currency' => $currency,
            'fiat_amount' => $fiatAmount,
            'fiat_currency' => $fiatCurrency
        ], $options);

        return $this->createPayout([$withdrawal]);
    }

    /**
     * List payouts by status
     *
     * @param string $status Payout status
     * @param int $limit Number of payouts to return
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
     * List payouts by date range
     *
     * @param string $dateFrom Start date (YYYY-MM-DD)
     * @param string $dateTo End date (YYYY-MM-DD)
     * @param int $limit Number of payouts to return
     * @param int $page Page number
     * @return array
     */
    public function listByDateRange(string $dateFrom, string $dateTo, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * Check if payout is finished
     *
     * @param string $batchId Batch ID
     * @return bool
     */
    public function isFinished(string $batchId): bool
    {
        $status = $this->getStatus($batchId);
        return ($status['status'] ?? '') === 'finished';
    }

    /**
     * Check if payout is sending
     *
     * @param string $batchId Batch ID
     * @return bool
     */
    public function isSending(string $batchId): bool
    {
        $status = $this->getStatus($batchId);
        return ($status['status'] ?? '') === 'sending';
    }

    /**
     * Check if payout failed
     *
     * @param string $batchId Batch ID
     * @return bool
     */
    public function isFailed(string $batchId): bool
    {
        $status = $this->getStatus($batchId);
        return ($status['status'] ?? '') === 'failed';
    }
} 