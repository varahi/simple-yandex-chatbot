<?php

namespace App\Factories;

use App\ChatBot;
use App\Services\FaqService;
use App\Services\HistoryService;
use App\Services\MessagePreparationService;
use App\Services\TopicService;
use App\YandexGptClient;

class ChatBotFactory
{
    public static function create(): ChatBot
    {
        $config = (static function () {
            static $config;
            return $config ??= include __DIR__ . '/../../config/config.php';
        })();
        $topicsConfig = include __DIR__ . '/../../config/topic.php';

        return new ChatBot(
            new YandexGptClient($config['yandex']),
            new HistoryService($config),
            new TopicService($config),
            new MessagePreparationService(
                new FaqService(include __DIR__ . '/../../config/faq.php'),
                new TopicService($topicsConfig),
                new HistoryService($config),
                $config
            )
        );
    }
}
