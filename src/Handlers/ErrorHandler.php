<?php

namespace App\Handlers;

use App\Exceptions\ForbiddenTopicException;
use App\Exceptions\InvalidMessageException;

class ErrorHandler
{
    public function handle(\Throwable $e): array
    {
        return match (true) {
            $e instanceof InvalidMessageException => [
                'error' => $e->getMessage(),
                'code' => 422,
                'details' => ['field' => 'message']
            ],
            $e instanceof ForbiddenTopicException => [
                'error' => $e->getMessage(),
                'code' => 403,
                'suggestions' => ['Попробуйте спросить о доставке или оплате']
            ],
            default => [
                'error' => 'Внутренняя ошибка сервера',
                'code' => 500,
                'request_id' => uniqid()
            ]
        };
    }


    public function sendResponse(array $errorData): void
    {
        http_response_code($errorData['code'] ?? 500);
        header('Content-Type: application/json');
        echo json_encode($errorData, JSON_UNESCAPED_UNICODE);
    }
}
