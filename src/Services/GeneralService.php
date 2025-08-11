<?php

namespace NowPayments\Services;

use NowPayments\Exception\ApiException;

/**
 * Service for general API operations
 */
class GeneralService extends AbstractService
{
    /**
     * Check API status
     *
     * @return array
     * @throws ApiException
     */
    public function getStatus(): array
    {
        return $this->get('status');
    }

    /**
     * Get all available currencies
     *
     * @return array
     * @throws ApiException
     */
    public function getCurrencies(): array
    {
        return $this->get('currencies');
    }

    /**
     * Get merchant active currencies
     *
     * @return array
     * @throws ApiException
     */
    public function getMerchantCurrencies(): array
    {
        return $this->get('merchant/coins');
    }

    /**
     * Get full currency details
     *
     * @return array
     * @throws ApiException
     */
    public function getFullCurrencies(): array
    {
        return $this->get('full-currencies');
    }

    /**
     * Get minimum payment amount for a currency pair
     *
     * @param string $currencyFrom Source currency
     * @param string $currencyTo Target currency
     * @return array
     * @throws ApiException
     */
    public function getMinAmount(string $currencyFrom, string $currencyTo): array
    {
        return $this->get('min-amount', [
            'currency_from' => $currencyFrom,
            'currency_to' => $currencyTo
        ]);
    }

    /**
     * Get estimated price for a currency conversion
     *
     * @param float $amount Amount to convert
     * @param string $currencyFrom Source currency
     * @param string $currencyTo Target currency
     * @return array
     * @throws ApiException
     */
    public function getEstimate(float $amount, string $currencyFrom, string $currencyTo): array
    {
        return $this->get('estimate', [
            'amount' => $amount,
            'currency_from' => $currencyFrom,
            'currency_to' => $currencyTo
        ]);
    }
} 