<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\GeneralService;
use NowPayments\NowPaymentsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class GeneralServiceTest extends TestCase
{
    private GeneralService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new GeneralService($this->client);
    }

    public function testGetStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'message' => 'OK',
            'result' => 'success'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getStatus();
        
        $this->assertEquals([
            'message' => 'OK',
            'result' => 'success'
        ], $result);
    }

    public function testGetCurrencies()
    {
        $mockResponse = new Response(200, [], json_encode([
            'currencies' => ['BTC', 'ETH', 'USDT'],
            'currencies_names' => [
                'BTC' => 'Bitcoin',
                'ETH' => 'Ethereum',
                'USDT' => 'Tether'
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getCurrencies();
        
        $this->assertEquals([
            'currencies' => ['BTC', 'ETH', 'USDT'],
            'currencies_names' => [
                'BTC' => 'Bitcoin',
                'ETH' => 'Ethereum',
                'USDT' => 'Tether'
            ]
        ], $result);
    }

    public function testGetMerchantCurrencies()
    {
        $mockResponse = new Response(200, [], json_encode([
            'selectedCurrencies' => ['BTC', 'ETH'],
            'currencies' => [
                'BTC' => [
                    'name' => 'Bitcoin',
                    'has_extra_id' => false
                ],
                'ETH' => [
                    'name' => 'Ethereum',
                    'has_extra_id' => false
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getMerchantCurrencies();
        
        $this->assertEquals([
            'selectedCurrencies' => ['BTC', 'ETH'],
            'currencies' => [
                'BTC' => [
                    'name' => 'Bitcoin',
                    'has_extra_id' => false
                ],
                'ETH' => [
                    'name' => 'Ethereum',
                    'has_extra_id' => false
                ]
            ]
        ], $result);
    }

    public function testGetFullCurrencies()
    {
        $mockResponse = new Response(200, [], json_encode([
            'currencies' => [
                'BTC' => [
                    'name' => 'Bitcoin',
                    'has_extra_id' => false,
                    'extra_id_name' => null,
                    'image' => 'https://nowpayments.io/images/coins/btc.svg',
                    'has_networks' => true,
                    'networks' => ['BTC']
                ],
                'ETH' => [
                    'name' => 'Ethereum',
                    'has_extra_id' => false,
                    'extra_id_name' => null,
                    'image' => 'https://nowpayments.io/images/coins/eth.svg',
                    'has_networks' => true,
                    'networks' => ['ETH']
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getFullCurrencies();
        
        $this->assertEquals([
            'currencies' => [
                'BTC' => [
                    'name' => 'Bitcoin',
                    'has_extra_id' => false,
                    'extra_id_name' => null,
                    'image' => 'https://nowpayments.io/images/coins/btc.svg',
                    'has_networks' => true,
                    'networks' => ['BTC']
                ],
                'ETH' => [
                    'name' => 'Ethereum',
                    'has_extra_id' => false,
                    'extra_id_name' => null,
                    'image' => 'https://nowpayments.io/images/coins/eth.svg',
                    'has_networks' => true,
                    'networks' => ['ETH']
                ]
            ]
        ], $result);
    }

    public function testGetMinAmount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'min_amount' => 0.001,
            'currency_from' => 'USD',
            'currency_to' => 'BTC'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getMinAmount('USD', 'BTC');
        
        $this->assertEquals([
            'min_amount' => 0.001,
            'currency_from' => 'USD',
            'currency_to' => 'BTC'
        ], $result);
    }

    public function testGetEstimate()
    {
        $mockResponse = new Response(200, [], json_encode([
            'estimated_amount' => 0.00012345,
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'amount' => 100
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getEstimate(100.0, 'USD', 'BTC');
        
        $this->assertEquals([
            'estimated_amount' => 0.00012345,
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'amount' => 100
        ], $result);
    }

    public function testGetEstimateWithDecimalAmount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'estimated_amount' => 0.00001234,
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'amount' => 10.50
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getEstimate(10.50, 'USD', 'BTC');
        
        $this->assertEquals([
            'estimated_amount' => 0.00001234,
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'amount' => 10.50
        ], $result);
    }

    public function testGetMinAmountWithDifferentCurrencies()
    {
        $mockResponse = new Response(200, [], json_encode([
            'min_amount' => 0.1,
            'currency_from' => 'EUR',
            'currency_to' => 'ETH'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new GeneralService($client);
        $result = $service->getMinAmount('EUR', 'ETH');
        
        $this->assertEquals([
            'min_amount' => 0.1,
            'currency_from' => 'EUR',
            'currency_to' => 'ETH'
        ], $result);
    }
} 