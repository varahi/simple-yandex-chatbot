<?php

namespace App\Exceptions;

class ForbiddenTopicException extends ChatException
{
    protected $message = 'Эта тема не поддерживается';
    protected $code = 403; // HTTP 403 Forbidden
}
