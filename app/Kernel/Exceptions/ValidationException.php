<?php

namespace LaraSlim\Kernel\Exceptions;

/**
 * Validation Exception
 * 
 * Exception for form validation errors.
 */
class ValidationException extends HttpException
{
    public function __construct(array $errors = [], string $message = 'Validation failed')
    {
        parent::__construct(422, $message, $errors);
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create from Laravel Validator
     */
    public static function fromValidator($validator): self
    {
        $errors = [];
        
        if (method_exists($validator, 'errors')) {
            $errors = $validator->errors()->toArray();
        } elseif (is_array($validator)) {
            $errors = $validator;
        }

        return new self($errors);
    }

    /**
     * Create with a single field error
     */
    public static function forField(string $field, string $message): self
    {
        return new self([$field => [$message]]);
    }
}
