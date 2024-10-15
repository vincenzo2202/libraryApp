<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CustomAuthenticationException extends AuthenticationException
{
    private $customMessage;

    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->customMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    public function getMessageKey(): string
    {
        return $this->customMessage;
    }
}
