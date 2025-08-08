<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\IpnService;
use NowPayments\NowPaymentsClient;

class IpnServiceTest extends TestCase
{
    private IpnService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key', ['ipn_secret' => 'test-secret']);
        $this->service = new IpnService($this->client);
    }

    public function testVerifySignatureWithValidSignature()
    {
        $requestBody = json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD'
        ]);
        
        // Sort the data and create signature
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->verifySignature($requestBody, $signature, 'test-secret');
        
        $this->assertTrue($result);
    }

    public function testVerifySignatureWithInvalidSignature()
    {
        $requestBody = json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished'
        ]);
        
        $signature = 'invalid-signature';
        
        $result = $this->service->verifySignature($requestBody, $signature, 'test-secret');
        
        $this->assertFalse($result);
    }

    public function testVerifySignatureWithInvalidJson()
    {
        $requestBody = 'invalid-json';
        $signature = 'test-signature';
        
        $result = $this->service->verifySignature($requestBody, $signature, 'test-secret');
        
        $this->assertFalse($result);
    }

    public function testVerifySignatureWithClientSecret()
    {
        $requestBody = json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished'
        ]);
        
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->verifySignatureWithClientSecret($requestBody, $signature);
        
        $this->assertTrue($result);
    }

    public function testVerifySignatureWithClientSecretThrowsExceptionWhenNoSecret()
    {
        $client = new NowPaymentsClient('test-api-key'); // No IPN secret
        $service = new IpnService($client);
        
        $requestBody = json_encode(['payment_id' => 123]);
        $signature = 'test-signature';
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IPN secret not configured in client');
        
        $service->verifySignatureWithClientSecret($requestBody, $signature);
    }

    public function testProcessIpnWithValidSignature()
    {
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address'
        ];
        $requestBody = json_encode($ipnData);
        
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->processIpn($requestBody, $signature, 'test-secret');
        
        $this->assertEquals($ipnData, $result);
    }

    public function testProcessIpnWithInvalidSignature()
    {
        $requestBody = json_encode(['payment_id' => 123]);
        $signature = 'invalid-signature';
        
        $result = $this->service->processIpn($requestBody, $signature, 'test-secret');
        
        $this->assertNull($result);
    }

    public function testProcessIpnWithClientSecret()
    {
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished'
        ];
        $requestBody = json_encode($ipnData);
        
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->processIpnWithClientSecret($requestBody, $signature);
        
        $this->assertEquals($ipnData, $result);
    }

    public function testProcessIpnWithClientSecretThrowsExceptionWhenNoSecret()
    {
        $client = new NowPaymentsClient('test-api-key'); // No IPN secret
        $service = new IpnService($client);
        
        $requestBody = json_encode(['payment_id' => 123]);
        $signature = 'test-signature';
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IPN secret not configured in client');
        
        $service->processIpnWithClientSecret($requestBody, $signature);
    }

    public function testExtractPaymentData()
    {
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC',
            'order_id' => 'order-123',
            'order_description' => 'Test order',
            'purchase_id' => 'purchase-123',
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-01T01:00:00Z',
            'outcome_amount' => 0.001,
            'outcome_currency' => 'BTC',
            'actually_paid' => 0.001,
            'commission_fee' => 0.0001
        ];
        
        $result = $this->service->extractPaymentData($ipnData);
        
        $this->assertEquals($ipnData, $result);
    }

    public function testExtractPaymentDataWithMissingFields()
    {
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished'
        ];
        
        $result = $this->service->extractPaymentData($ipnData);
        
        $expected = [
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => null,
            'price_amount' => null,
            'price_currency' => null,
            'pay_amount' => null,
            'pay_currency' => null,
            'order_id' => null,
            'order_description' => null,
            'purchase_id' => null,
            'created_at' => null,
            'updated_at' => null,
            'outcome_amount' => null,
            'outcome_currency' => null,
            'actually_paid' => null,
            'commission_fee' => null
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function testIsPaymentCompleted()
    {
        $ipnData = ['payment_status' => 'finished'];
        $this->assertTrue($this->service->isPaymentCompleted($ipnData));
        
        $ipnData = ['payment_status' => 'waiting'];
        $this->assertFalse($this->service->isPaymentCompleted($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentCompleted($ipnData));
    }

    public function testIsPaymentWaiting()
    {
        $ipnData = ['payment_status' => 'waiting'];
        $this->assertTrue($this->service->isPaymentWaiting($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentWaiting($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentWaiting($ipnData));
    }

    public function testIsPaymentConfirming()
    {
        $ipnData = ['payment_status' => 'confirming'];
        $this->assertTrue($this->service->isPaymentConfirming($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentConfirming($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentConfirming($ipnData));
    }

    public function testIsPaymentConfirmed()
    {
        $ipnData = ['payment_status' => 'confirmed'];
        $this->assertTrue($this->service->isPaymentConfirmed($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentConfirmed($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentConfirmed($ipnData));
    }

    public function testIsPaymentPartiallyPaid()
    {
        $ipnData = ['payment_status' => 'partially_paid'];
        $this->assertTrue($this->service->isPaymentPartiallyPaid($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentPartiallyPaid($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentPartiallyPaid($ipnData));
    }

    public function testIsPaymentFailed()
    {
        $ipnData = ['payment_status' => 'failed'];
        $this->assertTrue($this->service->isPaymentFailed($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentFailed($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentFailed($ipnData));
    }

    public function testIsPaymentExpired()
    {
        $ipnData = ['payment_status' => 'expired'];
        $this->assertTrue($this->service->isPaymentExpired($ipnData));
        
        $ipnData = ['payment_status' => 'finished'];
        $this->assertFalse($this->service->isPaymentExpired($ipnData));
        
        $ipnData = [];
        $this->assertFalse($this->service->isPaymentExpired($ipnData));
    }

    public function testVerifySignatureWithComplexData()
    {
        $requestBody = json_encode([
            'z_field' => 'value3',
            'a_field' => 'value1',
            'm_field' => 'value2',
            'payment_id' => 123,
            'payment_status' => 'finished'
        ]);
        
        // Sort the data and create signature
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->verifySignature($requestBody, $signature, 'test-secret');
        
        $this->assertTrue($result);
    }

    public function testVerifySignatureWithEmptyData()
    {
        $requestBody = json_encode([]);
        
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');
        
        $result = $this->service->verifySignature($requestBody, $signature, 'test-secret');
        
        $this->assertTrue($result);
    }
} 