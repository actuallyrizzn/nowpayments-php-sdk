<?php

namespace NowPayments\Tests\Exception;

use PHPUnit\Framework\TestCase;
use NowPayments\Exception\ConfigurationException;

class ConfigurationExceptionTest extends TestCase
{
    public function testExceptionCreation()
    {
        $message = 'Configuration error occurred';
        $exception = new ConfigurationException($message);
        
        $this->assertInstanceOf(ConfigurationException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionInheritance()
    {
        $exception = new ConfigurationException('Test message');
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 