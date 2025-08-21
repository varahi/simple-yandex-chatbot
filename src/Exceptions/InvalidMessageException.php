<?php

namespace App\Exceptions;

class InvalidMessageException extends ChatException
{
    public function __construct(string $message = 'Некорректное сообщение')
    {
        parent::__construct($message, 422); // HTTP 422 Unprocessable Entity
    }
}
