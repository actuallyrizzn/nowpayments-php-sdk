<?php

namespace NowPayments\Exception;

/**
 * Exception thrown when the API returns an error
 */
class ApiException extends \Exception
{
    private array $responseData;

    public function __construct(string $message, int $code = 0, array $responseData = [])
    {
        parent::__construct($message, $code);
        $this->responseData = $responseData;
    }

    /**
     * Get the response data from the API
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->getCode();
    }
} 