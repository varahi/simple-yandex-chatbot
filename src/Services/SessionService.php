<?php

namespace App\Services;

class SessionService
{
    public static function getUserId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            // пример: user_ + 16 hex символов
            $_SESSION['user_id'] = 'user_' . bin2hex(random_bytes(8));
        }

        return (string) $_SESSION['user_id'];
    }
}