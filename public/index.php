<?php

use src\ChatBot;
use src\Services\MessagePreparationService;
use src\YandexGptClient;
use src\Services\HistoryService;
use src\Services\FaqService;
use src\Services\TopicService;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/YandexGptClient.php';
require_once __DIR__ . '/../src/ChatBot.php';
require_once __DIR__ . '/../src/Services/HistoryService.php';
require_once __DIR__ . '/../src/Services/TopicService.php';
require_once __DIR__ . '/../src/Services/FaqService.php';
require_once __DIR__ . '/../src/Services/MessagePreparationService.php';

try {
    $config = include __DIR__ . '/../config/config.php';
    $faq = include __DIR__ . '/../config/faq.php';
    $client = new YandexGptClient($config['yandex']);
    $topicService = new TopicService($config);
    $historyService = new HistoryService();
    $faqService = new FaqService($faq);
    $messagePreparer = new MessagePreparationService(
        $faqService,
        $topicService,
        $historyService,
        $config
    );

    // Create bot
    $bot = new ChatBot($client, $historyService, $topicService, $messagePreparer);

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