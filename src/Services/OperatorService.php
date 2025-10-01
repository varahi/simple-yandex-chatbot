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

        if (preg_match('/^\/reply\s+(\S+)\s+(.+)/u', $text, $matches)) {
            $userId = $matches[1];
            $reply  = $matches[2];

            // сохраняем ответ в историю
            $this->historyService->updateHistory($userId, 'operator', $reply);

            // закрываем сессию, т.к. оператор ответил
            $this->historyService->closeOperatorSession($userId);

            $this->sendTelegramMessage($chatId, "✅ Ответ отправлен пользователю $userId. Сессия закрыта.");
        } elseif (preg_match('/^\/end\s+(\S+)/u', $text, $matches)) {
            $userId = $matches[1];
            $this->historyService->closeOperatorSession($userId);

            $this->sendTelegramMessage($chatId, "🚪 Сессия с пользователем $userId завершена.");
        } else {
            $this->sendTelegramMessage($chatId, "Неверный формат. Используйте: /reply user_<id> ваш текст или /end user_<id>");
        }
    }

    private function sendTelegramMessage($chatId, $text): void
    {
        $token = getenv('TELEGRAM_TOKEN');
        file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" .
            http_build_query(['chat_id' => $chatId, 'text' => $text]));
    }
}