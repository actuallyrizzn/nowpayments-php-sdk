<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\PaymentsService;
use NowPayments\NowPaymentsClient;
use NowPayments\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class PaymentsServiceTest extends TestCase
{
    private PaymentsService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new PaymentsService($this->client);
    }

    public function testCreatePayment()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->create([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]);
        
        $this->assertEquals([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ], $result);
    }

    public function testCreatePaymentWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: price_currency, pay_currency');
        
        $this->service->create([
            'price_amount' => 100
            // Missing price_currency and pay_currency
        ]);
    }

    public function testCreatePaymentWithEmptyRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: price_currency, pay_currency');
        
        $this->service->create([
            'price_amount' => 100,
            'price_currency' => '',
            'pay_currency' => null
        ]);
    }

    public function testGetStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->getStatus(123);
        
        $this->assertEquals([
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address'
        ], $result);
    }

    public function testListPayments()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ],
                [
                    'payment_id' => 124,
                    'payment_status' => 'waiting'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->list();
        
        $this->assertEquals([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ],
                [
                    'payment_id' => 124,
                    'payment_status' => 'waiting'
                ]
            ]
        ], $result);
    }

    public function testListPaymentsWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->list(['payment_status' => 'finished', 'limit' => 10]);
        
        $this->assertEquals([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ]
            ]
        ], $result);
    }

    public function testUpdateEstimate()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->updateEstimate(123);
        
        $this->assertEquals([
            'payment_id' => 123,
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ], $result);
    }

    public function testCreateInvoice()
    {
        $mockResponse = new Response(200, [], json_encode([
            'invoice_id' => 'inv_123',
            'invoice_status' => 'new',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'order_id' => 'order-123'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->createInvoice([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'order_id' => 'order-123'
        ]);
        
        $this->assertEquals([
            'invoice_id' => 'inv_123',
            'invoice_status' => 'new',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'order_id' => 'order-123'
        ], $result);
    }

    public function testCreateInvoiceWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: price_currency, order_id');
        
        $this->service->createInvoice([
            'price_amount' => 100
            // Missing price_currency and order_id
        ]);
    }

    public function testGetInvoiceStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'invoice_id' => 'inv_123',
            'invoice_status' => 'paid',
            'price_amount' => 100,
            'price_currency' => 'USD'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->getInvoiceStatus('inv_123');
        
        $this->assertEquals([
            'invoice_id' => 'inv_123',
            'invoice_status' => 'paid',
            'price_amount' => 100,
            'price_currency' => 'USD'
        ], $result);
    }

    public function testCreateInvoicePayment()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'iid' => 'inv_123',
            'pay_currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->createInvoicePayment([
            'iid' => 'inv_123',
            'pay_currency' => 'BTC'
        ]);
        
        $this->assertEquals([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'iid' => 'inv_123',
            'pay_currency' => 'BTC'
        ], $result);
    }

    public function testCreateInvoicePaymentWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: pay_currency');
        
        $this->service->createInvoicePayment([
            'iid' => 'inv_123'
            // Missing pay_currency
        ]);
    }

    public function testCreatePaymentConvenienceMethod()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->createPayment(100.0, 'USD', 'BTC', [
            'order_id' => 'order-123'
        ]);
        
        $this->assertEquals([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ], $result);
    }

    public function testGetPayment()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->getPayment(123);
        
        $this->assertEquals([
            'payment_id' => 123,
            'payment_status' => 'finished'
        ], $result);
    }

    public function testListByStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->listByStatus('finished', 5, 1);
        
        $this->assertEquals([
            'payments' => [
                [
                    'payment_id' => 123,
                    'payment_status' => 'finished'
                ]
            ]
        ], $result);
    }

    public function testListByCurrency()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payments' => [
                [
                    'payment_id' => 123,
                    'pay_currency' => 'BTC'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->listByCurrency('BTC', 5, 1);
        
        $this->assertEquals([
            'payments' => [
                [
                    'payment_id' => 123,
                    'pay_currency' => 'BTC'
                ]
            ]
        ], $result);
    }

    public function testListByDateRange()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payments' => [
                [
                    'payment_id' => 123,
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
        
        $service = new PaymentsService($client);
        $result = $service->listByDateRange('2023-01-01', '2023-01-31', 5, 1);
        
        $this->assertEquals([
            'payments' => [
                [
                    'payment_id' => 123,
                    'created_at' => '2023-01-01T00:00:00Z'
                ]
            ]
        ], $result);
    }

    public function testCreatePaymentWithDecimalAmount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'price_amount' => 99.99,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new PaymentsService($client);
        $result = $service->createPayment(99.99, 'USD', 'BTC');
        
        $this->assertEquals([
            'payment_id' => 123,
            'price_amount' => 99.99,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ], $result);
    }
} 