<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\CustodyService;
use NowPayments\NowPaymentsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class CustodyServiceTest extends TestCase
{
    private CustodyService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new CustodyService($this->client);
    }

    public function testCreateUser()
    {
        $mockResponse = new Response(200, [], json_encode([
            'user_id' => 123,
            'external_id' => 'ext_123',
            'email' => 'test@example.com'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createUser([
            'external_id' => 'ext_123',
            'email' => 'test@example.com'
        ]);
        
        $this->assertEquals([
            'user_id' => 123,
            'external_id' => 'ext_123',
            'email' => 'test@example.com'
        ], $result);
    }

    public function testCreateUserWithEmptyData()
    {
        $mockResponse = new Response(200, [], json_encode([
            'user_id' => 123
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createUser();
        
        $this->assertEquals([
            'user_id' => 123
        ], $result);
    }

    public function testGetBalance()
    {
        $mockResponse = new Response(200, [], json_encode([
            'user_id' => 123,
            'balances' => [
                'BTC' => 0.001,
                'ETH' => 0.01
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->getBalance(123);
        
        $this->assertEquals([
            'user_id' => 123,
            'balances' => [
                'BTC' => 0.001,
                'ETH' => 0.01
            ]
        ], $result);
    }

    public function testListUsers()
    {
        $mockResponse = new Response(200, [], json_encode([
            'users' => [
                [
                    'user_id' => 123,
                    'external_id' => 'ext_123'
                ],
                [
                    'user_id' => 124,
                    'external_id' => 'ext_124'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->listUsers();
        
        $this->assertEquals([
            'users' => [
                [
                    'user_id' => 123,
                    'external_id' => 'ext_123'
                ],
                [
                    'user_id' => 124,
                    'external_id' => 'ext_124'
                ]
            ]
        ], $result);
    }

    public function testListUsersWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'users' => [
                [
                    'user_id' => 123,
                    'external_id' => 'ext_123'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->listUsers(['limit' => 10]);
        
        $this->assertEquals([
            'users' => [
                [
                    'user_id' => 123,
                    'external_id' => 'ext_123'
                ]
            ]
        ], $result);
    }

    public function testCreatePayment()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createPayment([
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]);
        
        $this->assertEquals([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testCreatePaymentWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: currency');
        
        $this->service->createPayment([
            'user_id' => 123
            // Missing currency
        ]);
    }

    public function testTransfer()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->transfer([
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ]);
        
        $this->assertEquals([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testTransferWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: currency, amount');
        
        $this->service->transfer([
            'from_id' => 123,
            'to_id' => 124
            // Missing currency and amount
        ]);
    }

    public function testListTransfers()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'from_id' => 123,
                    'to_id' => 124,
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
        
        $service = new CustodyService($client);
        $result = $service->listTransfers();
        
        $this->assertEquals([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'from_id' => 123,
                    'to_id' => 124,
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ], $result);
    }

    public function testListTransfersWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'status' => 'completed'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->listTransfers(['status' => 'completed']);
        
        $this->assertEquals([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'status' => 'completed'
                ]
            ]
        ], $result);
    }

    public function testGetTransfer()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001,
            'status' => 'completed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->getTransfer('trans_123');
        
        $this->assertEquals([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001,
            'status' => 'completed'
        ], $result);
    }

    public function testWithdraw()
    {
        $mockResponse = new Response(200, [], json_encode([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001,
            'address' => 'test-address'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->withdraw([
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001,
            'address' => 'test-address'
        ]);
        
        $this->assertEquals([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001,
            'address' => 'test-address'
        ], $result);
    }

    public function testWithdrawWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: amount');
        
        $this->service->withdraw([
            'user_id' => 123,
            'currency' => 'BTC'
            // Missing amount
        ]);
    }

    public function testCreateUserAccount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'user_id' => 123,
            'external_id' => 'ext_123',
            'email' => 'test@example.com'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createUserAccount('ext_123', 'test@example.com');
        
        $this->assertEquals([
            'user_id' => 123,
            'external_id' => 'ext_123',
            'email' => 'test@example.com'
        ], $result);
    }

    public function testCreateUserAccountWithNullValues()
    {
        $mockResponse = new Response(200, [], json_encode([
            'user_id' => 123
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createUserAccount(null, null);
        
        $this->assertEquals([
            'user_id' => 123
        ], $result);
    }

    public function testCreateDepositPayment()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createDepositPayment(123, 'BTC', 0.001, 'track_123');
        
        $this->assertEquals([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testCreateDepositPaymentWithNullValues()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->createDepositPayment(123, 'BTC', null, null);
        
        $this->assertEquals([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC'
        ], $result);
    }

    public function testTransferBetweenUsers()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->transferBetweenUsers(123, 124, 'BTC', 0.001);
        
        $this->assertEquals([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testTransferToMaster()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 0,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->transferToMaster(123, 'BTC', 0.001);
        
        $this->assertEquals([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 0,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testTransferFromMaster()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 0,
            'to_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->transferFromMaster(123, 'BTC', 0.001);
        
        $this->assertEquals([
            'transfer_id' => 'trans_123',
            'from_id' => 0,
            'to_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testWithdrawToAddress()
    {
        $mockResponse = new Response(200, [], json_encode([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001,
            'address' => 'test-address'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->withdrawToAddress(123, 'BTC', 0.001, 'test-address', [
            'extra_id' => 'memo123'
        ]);
        
        $this->assertEquals([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001,
            'address' => 'test-address'
        ], $result);
    }

    public function testWithdrawToMaster()
    {
        $mockResponse = new Response(200, [], json_encode([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->withdrawToMaster(123, 'BTC', 0.001);
        
        $this->assertEquals([
            'withdrawal_id' => 'with_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ], $result);
    }

    public function testListTransfersByUser()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'from_id' => 123,
                    'to_id' => 124
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->listTransfersByUser(123, 5, 1);
        
        $this->assertEquals([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'from_id' => 123,
                    'to_id' => 124
                ]
            ]
        ], $result);
    }

    public function testListTransfersByStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'status' => 'completed'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new CustodyService($client);
        $result = $service->listTransfersByStatus('completed', 5, 1);
        
        $this->assertEquals([
            'transfers' => [
                [
                    'transfer_id' => 'trans_123',
                    'status' => 'completed'
                ]
            ]
        ], $result);
    }
} 