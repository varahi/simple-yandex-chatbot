<?php

namespace App\Exceptions;

class ChatException extends \RuntimeException
{
    protected $code = 400; // HTTP-статус по умолчанию
}
