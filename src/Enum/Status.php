<?php

namespace App\Enum;

enum Status: string
{
    case available = 'available';
    case borrowed = 'borrowed';
}
