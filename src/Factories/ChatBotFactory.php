<?php

namespace App\Factories;

use App\ChatBot;
use App\Services\FaqService;
use App\Services\HistoryService;
use App\Services\MessagePreparationService;
use App\Services\Product\ProductService;
use App\Services\SessionService;
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

        //$history = new HistoryService($config['history_file'] ?? __DIR__ . '/../../storage/history.json', $config['max_history'] ?? 5);

        return new ChatBot(
            new HistoryService($config),
            new TopicService($config),
            new MessagePreparationService(
                new FaqService(include __DIR__ . '/../../config/faq.php'),
                new TopicService($config),
                new HistoryService($config),
                new ProductService(),
                //new TelegramService($config['telegram_token'], $config['telegram_chat_id'], $history)
            ),
            new SessionService()
        );
    }
}
