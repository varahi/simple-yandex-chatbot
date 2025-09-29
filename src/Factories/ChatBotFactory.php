<?php

namespace App\Factories;

use App\ChatBot;
use App\Services\FaqService;
use App\Services\HistoryService;
use App\Services\MessagePreparationService;
use App\Services\Product\ProductService;
use App\Services\SessionService;
use App\Services\TopicService;

class ChatBotFactory
{
    public static function create(): ChatBot
    {
        $config = (static function () {
            static $config;
            return $config ??= include __DIR__ . '/../../config/config.php';
        })();

        return new ChatBot(
            new HistoryService($config),
            new TopicService($config),
            new MessagePreparationService(
                new FaqService(include __DIR__ . '/../../config/faq.php'),
                new TopicService($config),
                new HistoryService($config),
                new ProductService(),
            ),
            new SessionService()
        );
    }
}
