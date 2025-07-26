<?php

namespace src;

class ChatBot
{
    private $client;
    private $history = [];
    private $config;

    public function __construct(
        YandexGptClient $client,
        array $config,
    ) {
        $this->client = $client;
        $this->config = $config;
    }

    public function handleMessage(string $userMessage): string
    {
        if ($this->isForbidden($userMessage)) {
            return 'Эта тема не поддерживается.';
        }

        $messages = $this->prepareMessages($userMessage);
        $response = $this->client->sendRequest($messages);

        $this->updateHistory('user', $userMessage);
        $this->updateHistory('assistant', $response);

        return $response;
    }

    private function isForbidden(string $text): bool
    {
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

    private function updateHistory(string $role, string $text): void
    {
        $this->history[] = compact('role', 'text');
        if (count($this->history) > 5) {
            array_shift($this->history);
        }
    }

    public function prepareMessages(string $message): array
    {
        //        file_put_contents('new_messages.log', $message . PHP_EOL, FILE_APPEND);
        //        exit();

        if ($predefinedAnswer = $this->getPredefinedAnswer($message)) {
            return [
                [
                    'role' => 'system',
                    'text' => 'Ты — консультант, отвечаешь только готовыми шаблонами'
                ],
                [
                    'role' => 'assistant',
                    'text' => $predefinedAnswer
                ]
            ];
        }

        if (!$this->isAboutShopping($message)) {
            return [
                [
                    'role' => 'assistant',
                    'text' => 'Это вопрос к другому специалисту. Могу помочь с заказами, оплатой или доставкой.'
                ]
            ];
        }

        //        $messages = [[
        //            'role' => 'system',
        //            'text' => 'Ты — помощник без тематических ограничений. ' .
        //                'Не отвечай только на вопросы про: ' .
        //                implode(', ', $this->config['topics']['forbidden']) . '. ' .
        //                'На запрещённые темы говори: "Этот вопрос не в моей компетенции".'
        //        ]];

        $messages = [
            [
                'role' => 'system',
                'text' => 'Ты — консультант интернет-магазина. Отвечай на вопросы о заказах, оплате и доставке.'
            ]
        ];

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

    public function getPredefinedAnswer(string $question): ?string
    {
        $faq = include __DIR__ . '/../config/faq.php';
        $question = mb_strtolower($question);

        // Ищем как точные совпадения, так и частичные
        foreach ($faq as $key => $answer) {
            if (mb_strpos($question, mb_strtolower($key)) !== false) {
                //file_put_contents('answer.log', $answer . PHP_EOL, FILE_APPEND);
                return $answer;
            }
        }

        return null;
    }

    public function isAboutShopping(string $text): bool
    {
        $keywords = [
            'заказ', 'оплат', 'доставк', 'корзин', 'оформить',
            'куп', 'каталог', 'товар', 'магазин'
        ];

        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
