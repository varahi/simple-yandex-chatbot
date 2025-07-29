<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Factories\ChatBotFactory;
use App\Handlers\ChatRequestHandler;
use App\Handlers\ErrorHandler;

// Инициализация обработчика ошибок в первую очередь
$errorHandler = new ErrorHandler();

try {
    // Настройка CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }

    // Инициализация зависимостей
    $bot = ChatBotFactory::create();
    $requestHandler = new ChatRequestHandler($bot);

    // Получение входных данных
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    // Обработка запроса
    $response = $requestHandler->handle($input);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    // Теперь $errorHandler гарантированно существует
    $errorData = $errorHandler->handle($e);
    http_response_code($errorData['code'] ?? 500);
    echo json_encode($errorData, JSON_UNESCAPED_UNICODE);
}
