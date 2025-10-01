<?php

namespace App\Services;

class OperatorService
{

    public function __construct(
        private readonly HistoryService $historyService,
    )
    {
    }

    public function processOperatorMessage(array $message): void
    {
        $text = $message['text'];
        $chatId = $message['chat']['id'];

        // Ожидаем формат: /reply user_<id> Ответ
        if (preg_match('/^\/reply\s+(\S+)\s+(.+)/u', $text, $matches)) {
            $userId = $matches[1];        // user_011372f852528d9f
            $reply  = $matches[2];        // Тестовый ответ

            // Сохраняем ответ в историю диалога
            $this->historyService->updateHistory($userId, 'operator', $reply);

            // При желании можно отправить оператору подтверждение
            $this->sendTelegramMessage($chatId, "Ответ отправлен пользователю $userId");
        } else {
            $this->sendTelegramMessage($chatId, "Неверный формат. Используйте: /reply user_<id> ваш текст");
        }
    }

    private function sendTelegramMessage($chatId, $text): void
    {
        $token = getenv('TELEGRAM_TOKEN');
        file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" .
            http_build_query(['chat_id' => $chatId, 'text' => $text]));
    }
}