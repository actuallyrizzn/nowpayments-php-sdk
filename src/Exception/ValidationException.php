<?php

namespace NowPayments\Exception;

/**
 * Exception thrown when validation fails
 */
class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
} 