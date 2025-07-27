<?php

use src\Factories\ChatBotFactory;
use src\Services\FaqService;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ChatBot.php';
require_once __DIR__ . '/../src/Services/FaqService.php';
require_once __DIR__ . '/../src/Factories/ChatBotFactory.php';

try {
    // Кэширование конфигов
    $config = (static function () {
        static $config;
        return $config ??= include __DIR__ . '/../config/config.php';
    })();

    $faq = include __DIR__ . '/../config/faq.php';
    $faqService = new FaqService($faq);

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