<?php

use App\Factories\ChatBotFactory;

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Для CORS preflight
}
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

try {
    // Кэширование конфигов
    $config = (static function () {
        static $config;
        return $config ??= include __DIR__ . '/../config/config.php';
    })();

    // Create bot
    $bot = ChatBotFactory::create();

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $message = $input['message'] ?? '';

    if (empty($message)) {
        throw new InvalidArgumentException('Сообщение не может быть пустым');
    }

    echo json_encode(
        ['response' => $bot->handleMessage($message)],
        JSON_UNESCAPED_UNICODE
    );

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(
        ['error' => $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}