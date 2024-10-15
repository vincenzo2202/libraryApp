<?php

namespace App\Exception;

class ValidationErrorException extends \Exception
{
    public function __construct(string $message = 'Datos inválidos')
    {
        parent::__construct($message, 422);
    }
}
