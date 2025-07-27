<?php

namespace src\Services;

use InvalidArgumentException;

class InputValidator
{
    public static function validateMessage(array $input): string
    {
        if (empty($input['message'])) {
            throw new InvalidArgumentException('Сообщение не может быть пустым');
        }

        $message = trim($input['message']);
        if (mb_strlen($message) < 2) {
            throw new InvalidArgumentException('Сообщение слишком короткое');
        }

        return htmlspecialchars($message, ENT_QUOTES);
    }
}
