<?php

namespace App\Factories;

use App\ChatBot;
use App\Services\FaqService;
use App\Services\HistoryService;
use App\Services\MessagePreparationService;
use App\Services\Product\ProductService;
use App\Services\TelegramService;
use App\Services\TopicService;

class ChatBotFactory
{
    public static function create(): ChatBot
    {
        $config = (static function () {
            static $config;
            return $config ??= include __DIR__ . '/../../config/config.php';
        })();

        $history = new HistoryService($config);
        return new ChatBot(
            new TopicService($config),
            new MessagePreparationService(
                new FaqService(include __DIR__ . '/../../config/faq.php'),
                new TopicService($config),
                new HistoryService($config),
                new ProductService(),
                new TelegramService($config['telegram_token'], $config['operator_chat_id'], $history)
            )
        );
    }
}
