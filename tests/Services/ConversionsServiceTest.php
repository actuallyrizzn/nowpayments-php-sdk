<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\ConversionsService;
use NowPayments\NowPaymentsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ConversionsServiceTest extends TestCase
{
    private ConversionsService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new ConversionsService($this->client);
    }

    public function testCreateConversion()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->create([
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ]);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'pending'
        ], $result);
    }

    public function testCreateConversionWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: to_currency, amount');
        
        $this->service->create([
            'from_currency' => 'BTC'
            // Missing to_currency and amount
        ]);
    }

    public function testGetStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'completed',
            'rate' => 15.5,
            'to_amount' => 0.0155
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->getStatus('conv_123');
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'completed',
            'rate' => 15.5,
            'to_amount' => 0.0155
        ], $result);
    }

    public function testListConversions()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'from_currency' => 'BTC',
                    'to_currency' => 'ETH',
                    'status' => 'completed'
                ],
                [
                    'conversion_id' => 'conv_124',
                    'from_currency' => 'ETH',
                    'to_currency' => 'USDT',
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
        
        $service = new ConversionsService($client);
        $result = $service->list();
        
        $this->assertEquals([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'from_currency' => 'BTC',
                    'to_currency' => 'ETH',
                    'status' => 'completed'
                ],
                [
                    'conversion_id' => 'conv_124',
                    'from_currency' => 'ETH',
                    'to_currency' => 'USDT',
                    'status' => 'pending'
                ]
            ]
        ], $result);
    }

    public function testListConversionsWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
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
        
        $service = new ConversionsService($client);
        $result = $service->list(['status' => 'completed']);
        
        $this->assertEquals([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'status' => 'completed'
                ]
            ]
        ], $result);
    }

    public function testCreateConversionConvenienceMethod()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->createConversion('BTC', 'ETH', 0.001);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ], $result);
    }

    public function testConvertBtcToEth()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertBtcToEth(0.001);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ], $result);
    }

    public function testConvertEthToBtc()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'ETH',
            'to_currency' => 'BTC',
            'amount' => 0.01
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertEthToBtc(0.01);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'ETH',
            'to_currency' => 'BTC',
            'amount' => 0.01
        ], $result);
    }

    public function testConvertBtcToUsdt()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'USDT',
            'amount' => 0.001
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertBtcToUsdt(0.001);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'USDT',
            'amount' => 0.001
        ], $result);
    }

    public function testConvertEthToUsdt()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'ETH',
            'to_currency' => 'USDT',
            'amount' => 0.01
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertEthToUsdt(0.01);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'ETH',
            'to_currency' => 'USDT',
            'amount' => 0.01
        ], $result);
    }

    public function testConvertUsdtToBtc()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'USDT',
            'to_currency' => 'BTC',
            'amount' => 100
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertUsdtToBtc(100.0);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'USDT',
            'to_currency' => 'BTC',
            'amount' => 100
        ], $result);
    }

    public function testConvertUsdtToEth()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'USDT',
            'to_currency' => 'ETH',
            'amount' => 100
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->convertUsdtToEth(100.0);
        
        $this->assertEquals([
            'conversion_id' => 'conv_123',
            'from_currency' => 'USDT',
            'to_currency' => 'ETH',
            'amount' => 100
        ], $result);
    }

    public function testListByStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
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
        
        $service = new ConversionsService($client);
        $result = $service->listByStatus('completed', 5, 1);
        
        $this->assertEquals([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'status' => 'completed'
                ]
            ]
        ], $result);
    }

    public function testListByCurrency()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'from_currency' => 'BTC'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->listByCurrency('BTC', 5, 1);
        
        $this->assertEquals([
            'conversions' => [
                [
                    'conversion_id' => 'conv_123',
                    'from_currency' => 'BTC'
                ]
            ]
        ], $result);
    }

    public function testIsCompleted()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'completed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isCompleted('conv_123');
        
        $this->assertTrue($result);
    }

    public function testIsCompletedReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isCompleted('conv_123');
        
        $this->assertFalse($result);
    }

    public function testIsPending()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'pending'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isPending('conv_123');
        
        $this->assertTrue($result);
    }

    public function testIsPendingReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'completed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isPending('conv_123');
        
        $this->assertFalse($result);
    }

    public function testIsFailed()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'failed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isFailed('conv_123');
        
        $this->assertTrue($result);
    }

    public function testIsFailedReturnsFalse()
    {
        $mockResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'status' => 'completed'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new ConversionsService($client);
        $result = $service->isFailed('conv_123');
        
        $this->assertFalse($result);
    }

    public function testGetRate()
    {
        $conversionData = [
            'conversion_id' => 'conv_123',
            'rate' => 15.5
        ];
        
        $result = $this->service->getRate($conversionData);
        
        $this->assertEquals(15.5, $result);
    }

    public function testGetRateReturnsNull()
    {
        $conversionData = [
            'conversion_id' => 'conv_123'
            // No rate field
        ];
        
        $result = $this->service->getRate($conversionData);
        
        $this->assertNull($result);
    }

    public function testGetConvertedAmount()
    {
        $conversionData = [
            'conversion_id' => 'conv_123',
            'to_amount' => 0.0155
        ];
        
        $result = $this->service->getConvertedAmount($conversionData);
        
        $this->assertEquals(0.0155, $result);
    }

    public function testGetConvertedAmountReturnsNull()
    {
        $conversionData = [
            'conversion_id' => 'conv_123'
            // No to_amount field
        ];
        
        $result = $this->service->getConvertedAmount($conversionData);
        
        $this->assertNull($result);
    }
} 