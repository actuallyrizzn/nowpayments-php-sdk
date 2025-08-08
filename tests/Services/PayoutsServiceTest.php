<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\PayoutsService;
use NowPayments\NowPaymentsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class PayoutsServiceTest extends TestCase
{
    private PayoutsService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new PayoutsService($this->client);
    }

    public function testCreatePayout()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending',
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->create([
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ]);
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'pending',
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ], $result);
    }

    public function testCreatePayoutWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: withdrawals');
        
        $this->service->create([
            'currency' => 'BTC'
            // Missing withdrawals
        ]);
    }

    public function testCreatePayoutWithAuthToken()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->create([
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ],
            'auth_token' => '2fa-token'
        ]);
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ], $result);
    }

    public function testVerifyPayout()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'verified'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->verify('batch_123', '123456');
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'verified'
        ], $result);
    }

    public function testGetStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'finished',
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001,
                    'status' => 'finished'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->getStatus('batch_123');
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'finished',
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001,
                    'status' => 'finished'
                ]
            ]
        ], $result);
    }

    public function testListPayouts()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ],
                [
                    'batch_id' => 'batch_124',
                    'status' => 'pending'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->list();
        
        $this->assertEquals([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ],
                [
                    'batch_id' => 'batch_124',
                    'status' => 'pending'
                ]
            ]
        ], $result);
    }

    public function testListPayoutsWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->list(['status' => 'finished', 'limit' => 10]);
        
        $this->assertEquals([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ]
            ]
        ], $result);
    }

    public function testValidateAddress()
    {
        $mockResponse = new Response(200, [], json_encode([
            'address' => 'test-address',
            'currency' => 'BTC',
            'valid' => true
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->validateAddress('test-address', 'BTC');
        
        $this->assertEquals([
            'address' => 'test-address',
            'currency' => 'BTC',
            'valid' => true
        ], $result);
    }

    public function testValidateAddressWithExtraId()
    {
        $mockResponse = new Response(200, [], json_encode([
            'address' => 'test-address',
            'currency' => 'XRP',
            'extra_id' => 'memo123',
            'valid' => true
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->validateAddress('test-address', 'XRP', 'memo123');
        
        $this->assertEquals([
            'address' => 'test-address',
            'currency' => 'XRP',
            'extra_id' => 'memo123',
            'valid' => true
        ], $result);
    }

    public function testCreatePayoutConvenienceMethod()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->createPayout([
            [
                'address' => 'test-address',
                'currency' => 'BTC',
                'amount' => 0.001
            ]
        ], [
            'auth_token' => '2fa-token'
        ]);
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ], $result);
    }

    public function testCreateSinglePayout()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->createSinglePayout('test-address', 'BTC', 0.001, [
            'auth_token' => '2fa-token'
        ]);
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ], $result);
    }

    public function testCreatePayoutWithFiatAmount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->createPayoutWithFiatAmount('test-address', 'BTC', 100.0, 'USD', [
            'auth_token' => '2fa-token'
        ]);
        
        $this->assertEquals([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ], $result);
    }

    public function testListByStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->listByStatus('finished', 5, 1);
        
        $this->assertEquals([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'status' => 'finished'
                ]
            ]
        ], $result);
    }

    public function testListByDateRange()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'created_at' => '2023-01-01T00:00:00Z'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->listByDateRange('2023-01-01', '2023-01-31', 5, 1);
        
        $this->assertEquals([
            'payouts' => [
                [
                    'batch_id' => 'batch_123',
                    'created_at' => '2023-01-01T00:00:00Z'
                ]
            ]
        ], $result);
    }

    public function testIsFinished()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'finished'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isFinished('batch_123');
        
        $this->assertTrue($result);
    }

    public function testIsFinishedReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isFinished('batch_123');
        
        $this->assertFalse($result);
    }

    public function testIsSending()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'sending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isSending('batch_123');
        
        $this->assertTrue($result);
    }

    public function testIsSendingReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isSending('batch_123');
        
        $this->assertFalse($result);
    }

    public function testIsFailed()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'failed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isFailed('batch_123');
        
        $this->assertTrue($result);
    }

    public function testIsFailedReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PayoutsService($client);
        $result = $service->isFailed('batch_123');
        
        $this->assertFalse($result);
    }
} 