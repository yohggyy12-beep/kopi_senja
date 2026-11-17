<?php

namespace App\Exceptions;

use Exception;

class Pesanan_exception extends Exception
{
    public function __construct(string $message, public array $details = [] )
    {
        parent::__construct($message);
    }
}
