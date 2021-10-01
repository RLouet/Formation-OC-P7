<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RessourceValidationException extends HttpException
{
    private array $errors;
    public function __construct()
    {
        $message = "The JSON sent contains invalid data. Here are the errors you need to correct:";
        parent::__construct("400", $message);
    }

    public function addError(string $field, string $message)
    {
        $this->errors[] = [
            "field" => $field,
            "message" => $message
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}