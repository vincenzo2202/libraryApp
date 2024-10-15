<?php

namespace App\Exception;

class AccessDeniedException extends \Exception
{
  public function __construct(string $message)
  {
    parent::__construct($message, 403);
  }
}
