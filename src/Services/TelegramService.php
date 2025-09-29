<?php

namespace App\Services;

class TelegramService
{
    private string $token;
    private string $operatorChatId;
    private string $apiUrl = 'https://api.telegram.org';
    private HistoryService $history;

    public function __construct(string $token, string $operatorChatId, HistoryService $history)
    {
        $this->token = $token;
        $this->operatorChatId = $operatorChatId;
        $this->history = $history;
    }

    public function sendMessage(string|int $chatId, string $text, bool $html = true): bool
    {
        $url = "{$this->apiUrl}/bot{$this->token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($html) {
            $payload['parse_mode'] = 'HTML';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return $resp !== false && $err === '';
    }

    /**
     * Уведомить оператора о новом сообщении — передаём userId + контекст последних сообщений.
     */
    public function notifyOperatorNewMessage(string $userId, string $userMessage): bool
    {
        $history = $this->history->getHistory($userId);
        $context = "";
        foreach ($history as $m) {
            // избавляемся от HTML — оператору показывает "чистый" текст
            $context .= sprintf("[%s] %s: %s\n", $m['time'], $m['role'], strip_tags($m['text']));
        }

        $text = "<b>Новое сообщение от {$userId}</b>\n\n";
        $text .= $context . "\n";
        $text .= "Сообщение: " . strip_tags($userMessage) . "\n\n";
        $text .= "Ответьте командой:\n<code>/reply {$userId} Ваш текст ответа</code>";

        return $this->sendMessage($this->operatorChatId, $text, true);
    }

    /**
     * Обработать update от Telegram (webhook). Только минимальная логика для /reply.
     */
    public function processUpdate(array $update): void
    {
        $message = $update['message'] ?? null;
        if (!$message) {
            return;
        }

        $chatId = $message['chat']['id'] ?? null;
        $fromId = $message['from']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        // безопасность: принимаем команды только от оператора (его chat_id)
        if ((string)$chatId !== (string)$this->operatorChatId) {
            // игнорируем
            return;
        }

        // команда: /reply user_XXX Текст ответа
        if (preg_match('/^\/reply\s+(\S+)\s+(.+)$/s', $text, $m)) {
            $targetUser = $m[1];
            $replyText = $m[2];

            // сохраняем в истории как 'operator'
            $this->history->updateHistory($targetUser, 'operator', $replyText);

            // подтверждаем оператору
            $this->sendMessage($chatId, "Ответ пользователю <b>{$targetUser}</b> сохранён.", true);

            // (опционально) — можно пушить на сайт /notify endpoint, если нужен realtime
        } else {
            $this->sendMessage($chatId, "Команда не распознана. Чтобы ответить используйте:\n/reply user_123 Ваш ответ", true);
        }
    }

    public function setWebhook(string $webhookUrl): bool
    {
        $url = "{$this->apiUrl}/bot{$this->token}/setWebhook";
        $payload = ['url' => $webhookUrl];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return $resp !== false && $err === '';
    }
}