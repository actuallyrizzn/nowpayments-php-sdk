<?php

namespace NowPayments\Services;

use NowPayments\Exception\ValidationException;

/**
 * Service for payment operations
 */
class PaymentsService extends AbstractService
{
    /**
     * Create a new payment
     *
     * @param array $data Payment data
     * @return array
     * @throws ValidationException
     */
    public function create(array $data): array
    {
        $this->validateRequiredFields($data, ['price_amount', 'price_currency', 'pay_currency']);
        
        return $this->post('payment', $data);
    }

    /**
     * Get payment status by ID
     *
     * @param int $paymentId Payment ID
     * @return array
     */
    public function getStatus(int $paymentId): array
    {
        return $this->get("payment/{$paymentId}");
    }

    /**
     * List payments with optional filters
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function list(array $filters = []): array
    {
        return $this->get('payment', $filters);
    }

    /**
     * Update payment estimate
     *
     * @param int $paymentId Payment ID
     * @return array
     */
    public function updateEstimate(int $paymentId): array
    {
        return $this->post("payment/{$paymentId}/update-merchant-estimate");
    }

    /**
     * Create an invoice
     *
     * @param array $data Invoice data
     * @return array
     * @throws ValidationException
     */
    public function createInvoice(array $data): array
    {
        $this->validateRequiredFields($data, ['price_amount', 'price_currency', 'order_id']);
        
        return $this->post('invoice', $data);
    }

    /**
     * Get invoice status by ID
     *
     * @param string $invoiceId Invoice ID
     * @return array
     */
    public function getInvoiceStatus(string $invoiceId): array
    {
        return $this->get("invoice/{$invoiceId}");
    }

    /**
     * Create payment by invoice
     *
     * @param array $data Payment data for invoice
     * @return array
     * @throws ValidationException
     */
    public function createInvoicePayment(array $data): array
    {
        $this->validateRequiredFields($data, ['iid', 'pay_currency']);
        
        return $this->post('invoice-payment', $data);
    }

    /**
     * Create a payment with validation
     *
     * @param float $priceAmount Price amount in fiat
     * @param string $priceCurrency Fiat currency (e.g., 'usd')
     * @param string $payCurrency Cryptocurrency to pay in (e.g., 'btc')
     * @param array $options Additional options
     * @return array
     */
    public function createPayment(
        float $priceAmount,
        string $priceCurrency,
        string $payCurrency,
        array $options = []
    ): array {
        $data = array_merge([
            'price_amount' => $priceAmount,
            'price_currency' => $priceCurrency,
            'pay_currency' => $payCurrency
        ], $options);

        return $this->create($data);
    }

    /**
     * Get payment by ID with full details
     *
     * @param int $paymentId Payment ID
     * @return array
     */
    public function getPayment(int $paymentId): array
    {
        return $this->getStatus($paymentId);
    }

    /**
     * List payments with specific status
     *
     * @param string $status Payment status filter
     * @param int $limit Number of payments to return
     * @param int $page Page number
     * @return array
     */
    public function listByStatus(string $status, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'payment_status' => $status,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * List payments by currency
     *
     * @param string $currency Currency filter
     * @param int $limit Number of payments to return
     * @param int $page Page number
     * @return array
     */
    public function listByCurrency(string $currency, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'pay_currency' => $currency,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * List payments by date range
     *
     * @param string $dateFrom Start date (YYYY-MM-DD)
     * @param string $dateTo End date (YYYY-MM-DD)
     * @param int $limit Number of payments to return
     * @param int $page Page number
     * @return array
     */
    public function listByDateRange(string $dateFrom, string $dateTo, int $limit = 10, int $page = 0): array
    {
        return $this->list([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'limit' => $limit,
            'page' => $page
        ]);
    }
} 