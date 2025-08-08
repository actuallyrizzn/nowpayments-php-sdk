<?php

namespace NowPayments\Tests\Exception;

use PHPUnit\Framework\TestCase;
use NowPayments\Exception\ValidationException;

class ValidationExceptionTest extends TestCase
{
    public function testExceptionCreation()
    {
        $message = 'Validation error occurred';
        $exception = new ValidationException($message);
        
        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionInheritance()
    {
        $exception = new ValidationException('Test message');
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 