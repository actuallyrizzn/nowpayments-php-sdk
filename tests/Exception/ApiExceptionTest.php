<?php

namespace NowPayments\Tests\Exception;

use PHPUnit\Framework\TestCase;
use NowPayments\Exception\ApiException;

class ApiExceptionTest extends TestCase
{
    public function testExceptionCreation()
    {
        $message = 'API error occurred';
        $statusCode = 400;
        $responseData = ['error' => 'Bad Request'];
        
        $exception = new ApiException($message, $statusCode, $responseData);
        
        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals($responseData, $exception->getResponseData());
    }

    public function testExceptionCreationWithDefaultValues()
    {
        $message = 'API error occurred';
        $exception = new ApiException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getStatusCode());
        $this->assertEquals([], $exception->getResponseData());
    }

    public function testExceptionInheritance()
    {
        $exception = new ApiException('Test message');
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testGetStatusCode()
    {
        $statusCode = 500;
        $exception = new ApiException('Server error', $statusCode);
        
        $this->assertEquals($statusCode, $exception->getStatusCode());
    }

    public function testGetResponseData()
    {
        $responseData = ['error' => 'Validation failed', 'field' => 'amount'];
        $exception = new ApiException('Validation error', 422, $responseData);
        
        $this->assertEquals($responseData, $exception->getResponseData());
    }
} 