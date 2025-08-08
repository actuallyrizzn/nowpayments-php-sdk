<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\AbstractService;
use NowPayments\NowPaymentsClient;
use NowPayments\Exception\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * Concrete implementation of AbstractService for testing
 */
class TestService extends AbstractService
{
    public function testGet(string $endpoint, array $query = []): array
    {
        return $this->get($endpoint, $query);
    }

    public function testPost(string $endpoint, array $data = []): array
    {
        return $this->post($endpoint, $data);
    }

    public function testPatch(string $endpoint, array $data = []): array
    {
        return $this->patch($endpoint, $data);
    }

    public function testDelete(string $endpoint): array
    {
        return $this->delete($endpoint);
    }

    public function testValidateRequiredFields(array $data, array $requiredFields): void
    {
        $this->validateRequiredFields($data, $requiredFields);
    }
}

class AbstractServiceTest extends TestCase
{
    private TestService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new TestService($this->client);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(TestService::class, $this->service);
    }

    public function testGetRequest()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testGet('/test-endpoint', ['param' => 'value']);
        
        $this->assertEquals(['success' => true], $result);
    }

    public function testPostRequest()
    {
        $mockResponse = new Response(200, [], json_encode(['id' => 123]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testPost('/test-endpoint', ['amount' => 100]);
        
        $this->assertEquals(['id' => 123], $result);
    }

    public function testPatchRequest()
    {
        $mockResponse = new Response(200, [], json_encode(['updated' => true]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testPatch('/test-endpoint', ['status' => 'updated']);
        
        $this->assertEquals(['updated' => true], $result);
    }

    public function testDeleteRequest()
    {
        $mockResponse = new Response(200, [], json_encode(['deleted' => true]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testDelete('/test-endpoint');
        
        $this->assertEquals(['deleted' => true], $result);
    }

    public function testRequestWithApiError()
    {
        $mockResponse = new Response(400, [], json_encode([
            'message' => 'Bad Request',
            'error' => 'Invalid parameters'
        ]));
        $mock = new MockHandler([
            new RequestException('Bad Request', new Request('GET', 'test'), $mockResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad Request');
        
        $service->testGet('/test-endpoint');
    }

    public function testRequestWithGuzzleException()
    {
        $mock = new MockHandler([
            new \GuzzleHttp\Exception\ConnectException('Connection failed', new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection failed');
        
        $service->testGet('/test-endpoint');
    }

    public function testValidateRequiredFieldsSuccess()
    {
        $data = ['field1' => 'value1', 'field2' => 'value2'];
        $requiredFields = ['field1', 'field2'];
        
        // Should not throw an exception
        $this->service->testValidateRequiredFields($data, $requiredFields);
        
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function testValidateRequiredFieldsWithMissingField()
    {
        $data = ['field1' => 'value1'];
        $requiredFields = ['field1', 'field2'];
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: field2');
        
        $this->service->testValidateRequiredFields($data, $requiredFields);
    }

    public function testValidateRequiredFieldsWithEmptyField()
    {
        $data = ['field1' => 'value1', 'field2' => ''];
        $requiredFields = ['field1', 'field2'];
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: field2');
        
        $this->service->testValidateRequiredFields($data, $requiredFields);
    }

    public function testValidateRequiredFieldsWithNullField()
    {
        $data = ['field1' => 'value1', 'field2' => null];
        $requiredFields = ['field1', 'field2'];
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: field2');
        
        $this->service->testValidateRequiredFields($data, $requiredFields);
    }

    public function testValidateRequiredFieldsWithMultipleMissingFields()
    {
        $data = ['field1' => 'value1'];
        $requiredFields = ['field1', 'field2', 'field3'];
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: field2, field3');
        
        $this->service->testValidateRequiredFields($data, $requiredFields);
    }

    public function testRequestWithEmptyResponse()
    {
        $mockResponse = new Response(200, [], '');
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testGet('/test-endpoint');
        
        $this->assertEquals([], $result);
    }

    public function testRequestWithInvalidJsonResponse()
    {
        $mockResponse = new Response(200, [], 'invalid json');
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new TestService($client);
        $result = $service->testGet('/test-endpoint');
        
        $this->assertEquals([], $result);
    }
} 