<?php

namespace src;

class ChatBot
{
    private $client;
    private $history = [];
    private $config;

    public function __construct(YandexGptClient $client, array $config) {
        $this->client = $client;
        $this->config = $config;
    }

    public function handleMessage(string $userMessage): string {
        if ($this->isForbidden($userMessage)) {
            return 'Эта тема не поддерживается.';
        }

        $messages = $this->prepareMessages($userMessage);
        $response = $this->client->sendRequest($messages);

        $this->updateHistory('user', $userMessage);
        $this->updateHistory('assistant', $response);

        return $response;
    }

    private function prepareMessages(string $message): array {

        $messages = [[
            'role' => 'system',
            'text' => 'Ты — помощник без тематических ограничений. ' .
                'Не отвечай только на вопросы про: ' .
                implode(', ', $this->config['topics']['forbidden']) . '. ' .
                'На запрещённые темы говори: "Этот вопрос не в моей компетенции".'
        ]];

        foreach ($this->history as $item) {
            $messages[] = [
                'role' => $item['role'],
                'text' => $item['text']
            ];
        }

        $messages[] = [
            'role' => 'user',
            'text' => $message
        ];

        return $messages;
    }

//    private function isForbidden(string $text): bool {
//        $text = mb_strtolower($text);
//
//        foreach ($this->config['topics']['forbidden'] as $word) {
//            if (str_contains($text, $word)) return true;
//        }
//
//        if (!empty($this->config['topics']['allowed'])) {
//            foreach ($this->config['topics']['allowed'] as $topic) {
//                if (str_contains($text, $topic)) return false;
//            }
//            return true;
//        }
//
//        return false;
//    }

//    private function isForbidden(string $text): bool {
//        $text = mb_strtolower($text);
//
//        // Сначала проверяем запрещённые слова
//        foreach ($this->config['topics']['forbidden'] as $word) {
//            if (str_contains($text, $word)) {
//                return true;
//            }
//        }
//
//        // Если есть разрешённые темы, проверяем соответствие
//        if (!empty($this->config['topics']['allowed'])) {
//            foreach ($this->config['topics']['allowed'] as $topic) {
//                if (str_contains($text, $topic)) {
//                    return false; // Тема разрешена
//                }
//            }
//            return true; // Тема не найдена в разрешённых
//        }
//
//        return false; // Если нет ограничений по темам
//    }

    private function isForbidden(string $text): bool {
        $text = mb_strtolower($text);

        // Если есть явно запрещённые слова — блокируем
        foreach ($this->config['topics']['forbidden'] as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        // Если ALLOWED_TOPICS содержит '*' — разрешаем всё остальное
        if (in_array('*', $this->config['topics']['allowed'])) {
            return false;
        }

        // Стандартная проверка (если нет '*')
        foreach ($this->config['topics']['allowed'] as $topic) {
            if (str_contains($text, $topic)) {
                return false;
            }
        }

        return true; // Тема не разрешена
    }

    private function updateHistory(string $role, string $text): void {
        $this->history[] = compact('role', 'text');
        if (count($this->history) > 5) array_shift($this->history);
    }
}