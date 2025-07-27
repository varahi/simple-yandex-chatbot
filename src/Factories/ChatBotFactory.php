<?php

namespace src\Factories;

use src\ChatBot;
use src\Services\FaqService;
use src\Services\HistoryService;
use src\Services\MessagePreparationService;
use src\Services\TopicService;
use src\YandexGptClient;

class ChatBotFactory
{
    public static function create(): ChatBot
    {
        $config = include __DIR__ . '/../../config/config.php';
        require_once __DIR__ . '/../../src/YandexGptClient.php';
        require_once __DIR__ . '/../../src/Services/HistoryService.php';
        require_once __DIR__ . '/../../src/Services/TopicService.php';
        require_once __DIR__ . '/../../src/Services/MessagePreparationService.php';

        return new ChatBot(
            new YandexGptClient($config['yandex']),
            new HistoryService($config),
            new TopicService($config),
            new MessagePreparationService(
                new FaqService(include __DIR__ . '/../../config/faq.php'),
                new TopicService($config),
                new HistoryService($config),
                $config
            )
        );
    }
}
