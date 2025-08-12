<?php

namespace NowPayments\Services;

use InvalidArgumentException;

/**
 * Service for IPN (Instant Payment Notification) operations
 */
class IpnService extends AbstractService
{
    /**
     * Verify IPN signature
     *
     * @param string $requestBody The raw request body
     * @param string $signature The signature from X-NOWPAYMENTS-Sig header
     * @param string $secret The IPN secret key
     * @return bool
     */
    public function verifySignature(string $requestBody, string $signature, string $secret): bool
    {
        // Decode the JSON body and sort keys
        $data = json_decode($requestBody, true);
        if ($data === null) {
            return false;
        }

        // Sort the data by keys
        ksort($data);
        
        // Re-encode the sorted data
        $sortedBody = json_encode($data);
        
        // Calculate HMAC-SHA512
        $expectedSignature = hash_hmac('sha512', $sortedBody, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify IPN signature using client's IPN secret
     *
     * @param string $requestBody The raw request body
     * @param string $signature The signature from X-NOWPAYMENTS-Sig header
     * @return bool
     * @throws InvalidArgumentException
     */
    public function verifySignatureWithClientSecret(string $requestBody, string $signature): bool
    {
        $secret = $this->client->getIpnSecret();
        
        if ($secret === null) {
            throw new InvalidArgumentException('IPN secret not configured in client');
        }
        
        return $this->verifySignature($requestBody, $signature, $secret);
    }

    /**
     * Process IPN data safely
     *
     * @param string $requestBody The raw request body
     * @param string $signature The signature from X-NOWPAYMENTS-Sig header
     * @param string $secret The IPN secret key
     * @return array|null Returns the decoded data if signature is valid, null otherwise
     */
    public function processIpn(string $requestBody, string $signature, string $secret): ?array
    {
        if (!$this->verifySignature($requestBody, $signature, $secret)) {
            return null;
        }
        
        return json_decode($requestBody, true);
    }

    /**
     * Process IPN data using client's IPN secret
     *
     * @param string $requestBody The raw request body
     * @param string $signature The signature from X-NOWPAYMENTS-Sig header
     * @return array|null Returns the decoded data if signature is valid, null otherwise
     */
    public function processIpnWithClientSecret(string $requestBody, string $signature): ?array
    {
        $secret = $this->client->getIpnSecret();
        
        if ($secret === null) {
            throw new InvalidArgumentException('IPN secret not configured in client');
        }
        
        return $this->processIpn($requestBody, $signature, $secret);
    }

    /**
     * Extract payment data from IPN
     *
     * @param array $ipnData The decoded IPN data
     * @return array Payment information
     */
    public function extractPaymentData(array $ipnData): array
    {
        return [
            'payment_id' => $ipnData['payment_id'] ?? null,
            'payment_status' => $ipnData['payment_status'] ?? null,
            'pay_address' => $ipnData['pay_address'] ?? null,
            'price_amount' => $ipnData['price_amount'] ?? null,
            'price_currency' => $ipnData['price_currency'] ?? null,
            'pay_amount' => $ipnData['pay_amount'] ?? null,
            'pay_currency' => $ipnData['pay_currency'] ?? null,
            'order_id' => $ipnData['order_id'] ?? null,
            'order_description' => $ipnData['order_description'] ?? null,
            'purchase_id' => $ipnData['purchase_id'] ?? null,
            'created_at' => $ipnData['created_at'] ?? null,
            'updated_at' => $ipnData['updated_at'] ?? null,
            'outcome_amount' => $ipnData['outcome_amount'] ?? null,
            'outcome_currency' => $ipnData['outcome_currency'] ?? null,
            'actually_paid' => $ipnData['actually_paid'] ?? null,
            'commission_fee' => $ipnData['commission_fee'] ?? null
        ];
    }

    /**
     * Check if payment is completed
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentCompleted(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'finished';
    }

    /**
     * Check if payment is waiting
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentWaiting(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'waiting';
    }

    /**
     * Check if payment is confirming
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentConfirming(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'confirming';
    }

    /**
     * Check if payment is confirmed
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentConfirmed(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'confirmed';
    }

    /**
     * Check if payment is partially paid
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentPartiallyPaid(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'partially_paid';
    }

    /**
     * Check if payment failed
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentFailed(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'failed';
    }

    /**
     * Check if payment is expired
     *
     * @param array $ipnData The decoded IPN data
     * @return bool
     */
    public function isPaymentExpired(array $ipnData): bool
    {
        return ($ipnData['payment_status'] ?? '') === 'expired';
    }
} 