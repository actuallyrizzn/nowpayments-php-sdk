<?php

namespace NowPayments\Services;

use NowPayments\Exception\ApiException;
use NowPayments\Exception\ValidationException;

/**
 * Service for conversion operations
 */
class ConversionsService extends AbstractService
{
    /**
     * Create a conversion
     *
     * @param array $data Conversion data
     * @return array
     * @throws ValidationException|ApiException
     */
    public function create(array $data): array
    {
        $this->validateRequiredFields($data, ['from_currency', 'to_currency', 'amount']);
        
        return $this->post('conversion', $data);
    }

    /**
     * Get conversion status
     *
     * @param string $conversionId Conversion ID
     * @return array
     * @throws ApiException
     */
    public function getStatus(string $conversionId): array
    {
        return $this->get("conversion/$conversionId");
    }

    /**
     * List conversions with optional filters
     *
     * @param array $filters Optional filters
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        return $this->get('conversion', $filters);
    }

    /**
     * Create a conversion with validation
     *
     * @param string $fromCurrency Source currency
     * @param string $toCurrency Target currency
     * @param float $amount Amount to convert
     * @return array
     * @throws ValidationException|ApiException
     */
    public function createConversion(string $fromCurrency, string $toCurrency, float $amount): array
    {
        return $this->create([
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'amount' => $amount
        ]);
    }

    /**
     * Convert BTC to ETH
     *
     * @param float $amount Amount in BTC
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertBtcToEth(float $amount): array
    {
        return $this->createConversion('btc', 'eth', $amount);
    }

    /**
     * Convert ETH to BTC
     *
     * @param float $amount Amount in ETH
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertEthToBtc(float $amount): array
    {
        return $this->createConversion('eth', 'btc', $amount);
    }

    /**
     * Convert BTC to USDT
     *
     * @param float $amount Amount in BTC
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertBtcToUsdt(float $amount): array
    {
        return $this->createConversion('btc', 'usdt', $amount);
    }

    /**
     * Convert ETH to USDT
     *
     * @param float $amount Amount in ETH
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertEthToUsdt(float $amount): array
    {
        return $this->createConversion('eth', 'usdt', $amount);
    }

    /**
     * Convert USDT to BTC
     *
     * @param float $amount Amount in USDT
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertUsdtToBtc(float $amount): array
    {
        return $this->createConversion('usdt', 'btc', $amount);
    }

    /**
     * Convert USDT to ETH
     *
     * @param float $amount Amount in USDT
     * @return array
     * @throws ValidationException|ApiException
     */
    public function convertUsdtToEth(float $amount): array
    {
        return $this->createConversion('usdt', 'eth', $amount);
    }

    /**
     * List conversions by status
     *
     * @param string $status Conversion status
     * @param int $limit Number of conversions to return
     * @param int $offset Offset
     * @return array
     * @throws ApiException
     */
    public function listByStatus(string $status, int $limit = 10, int $offset = 0): array
    {
        return $this->list([
            'status' => $status,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * List conversions by currency
     *
     * @param string $currency Currency filter
     * @param int $limit Number of conversions to return
     * @param int $offset Offset
     * @return array
     * @throws ApiException
     */
    public function listByCurrency(string $currency, int $limit = 10, int $offset = 0): array
    {
        return $this->list([
            'currency' => $currency,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Check if conversion is completed
     *
     * @param string $conversionId Conversion ID
     * @return bool
     * @throws ApiException
     */
    public function isCompleted(string $conversionId): bool
    {
        $status = $this->getStatus($conversionId);
        return ($status['status'] ?? '') === 'completed';
    }

    /**
     * Check if conversion is pending
     *
     * @param string $conversionId Conversion ID
     * @return bool
     * @throws ApiException
     */
    public function isPending(string $conversionId): bool
    {
        $status = $this->getStatus($conversionId);
        return ($status['status'] ?? '') === 'pending';
    }

    /**
     * Check if conversion failed
     *
     * @param string $conversionId Conversion ID
     * @return bool
     * @throws ApiException
     */
    public function isFailed(string $conversionId): bool
    {
        $status = $this->getStatus($conversionId);
        return ($status['status'] ?? '') === 'failed';
    }

    /**
     * Get conversion rate from response
     *
     * @param array $conversionData Conversion response data
     * @return float|null
     */
    public function getRate(array $conversionData): ?float
    {
        return $conversionData['rate'] ?? null;
    }

    /**
     * Get converted amount from response
     *
     * @param array $conversionData Conversion response data
     * @return float|null
     */
    public function getConvertedAmount(array $conversionData): ?float
    {
        return $conversionData['to_amount'] ?? null;
    }
} 