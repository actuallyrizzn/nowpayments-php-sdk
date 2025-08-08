<?php

namespace NowPayments\Tests;

use PHPUnit\Framework\TestCase;
use NowPayments\NowPaymentsClient;
use NowPayments\Exception\ConfigurationException;

class NowPaymentsClientTest extends TestCase
{
    public function testClientCreationWithValidApiKey()
    {
        $client = new NowPaymentsClient('test-api-key');
        $this->assertInstanceOf(NowPaymentsClient::class, $client);
    }

    public function testClientCreationWithEmptyApiKeyThrowsException()
    {
        $this->expectException(ConfigurationException::class);
        new NowPaymentsClient('');
    }

    public function testClientCreationWithSandboxConfig()
    {
        $client = new NowPaymentsClient('test-api-key', ['sandbox' => true]);
        $this->assertInstanceOf(NowPaymentsClient::class, $client);
    }

    public function testClientCreationWithIpnSecret()
    {
        $client = new NowPaymentsClient('test-api-key', ['ipn_secret' => 'test-secret']);
        $this->assertInstanceOf(NowPaymentsClient::class, $client);
    }

    public function testGetApiKey()
    {
        $apiKey = 'test-api-key';
        $client = new NowPaymentsClient($apiKey);
        $this->assertEquals($apiKey, $client->getApiKey());
    }

    public function testGetBaseUrl()
    {
        $client = new NowPaymentsClient('test-api-key');
        $this->assertStringContainsString('api.nowpayments.io', $client->getBaseUrl());
    }

    public function testGetBaseUrlWithSandbox()
    {
        $client = new NowPaymentsClient('test-api-key', ['sandbox' => true]);
        $this->assertStringContainsString('api-sandbox.nowpayments.io', $client->getBaseUrl());
    }

    public function testGetIpnSecret()
    {
        $ipnSecret = 'test-secret';
        $client = new NowPaymentsClient('test-api-key', ['ipn_secret' => $ipnSecret]);
        $this->assertEquals($ipnSecret, $client->getIpnSecret());
    }

    public function testGetIpnSecretReturnsNullWhenNotSet()
    {
        $client = new NowPaymentsClient('test-api-key');
        $this->assertNull($client->getIpnSecret());
    }

    public function testGetHttpClient()
    {
        $client = new NowPaymentsClient('test-api-key');
        $httpClient = $client->getHttpClient();
        $this->assertInstanceOf(\GuzzleHttp\ClientInterface::class, $httpClient);
    }

    public function testPaymentsService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $paymentsService = $client->payments();
        $this->assertInstanceOf(\NowPayments\Services\PaymentsService::class, $paymentsService);
    }

    public function testSubscriptionsService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $subscriptionsService = $client->subscriptions();
        $this->assertInstanceOf(\NowPayments\Services\SubscriptionsService::class, $subscriptionsService);
    }

    public function testPayoutsService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $payoutsService = $client->payouts();
        $this->assertInstanceOf(\NowPayments\Services\PayoutsService::class, $payoutsService);
    }

    public function testCustodyService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $custodyService = $client->custody();
        $this->assertInstanceOf(\NowPayments\Services\CustodyService::class, $custodyService);
    }

    public function testConversionsService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $conversionsService = $client->conversions();
        $this->assertInstanceOf(\NowPayments\Services\ConversionsService::class, $conversionsService);
    }

    public function testGeneralService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $generalService = $client->general();
        $this->assertInstanceOf(\NowPayments\Services\GeneralService::class, $generalService);
    }

    public function testIpnService()
    {
        $client = new NowPaymentsClient('test-api-key');
        $ipnService = $client->ipn();
        $this->assertInstanceOf(\NowPayments\Services\IpnService::class, $ipnService);
    }
} 