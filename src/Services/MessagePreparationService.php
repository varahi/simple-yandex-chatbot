<?php

namespace App\Services;

use App\Contracts\MessagePreparerInterface;
use App\Services\Product\ProductService;
use App\Services\SessionService;

class MessagePreparationService implements MessagePreparerInterface
{
    public function __construct(
        private FaqService $faqService,
        private TopicService $topicService,
        private HistoryService $historyService,
        private ProductService $productService,
        private TelegramService $telegramService
    ) {
    }

    public function prepare(string $userMessage): array
    {
        $userId = SessionService::getUserId();

        // 0. Если сессия с оператором уже активна — все запросы идут оператору
        if ($this->historyService->isOperatorSession($userId)) {
            $this->historyService->updateHistory($userId, 'operator', $userMessage);
            $this->telegramService->notifyOperatorNewMessage($userId, $userMessage);
            return [['role' => 'operator', 'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>']];
        }

        // 1. Проверка FAQ
        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
            return [['role' => 'assistant', 'text' => '<div class="products-card"> ' . $answer . '</div>']]; // ← Только готовый ответ
        }

        // 2. Проверка триггерных фраз // Вызов оператора
        if ($this->shouldTransferToOperator($userMessage, $userId)) {
            $this->historyService->updateHistory($userId, 'operator', $userMessage);
            $this->telegramService->notifyOperatorNewMessage($userId, $userMessage);
            return [['role' => 'operator', 'text' => '<div class="system-note">✅ Запрос передан оператору — вы получите ответ в чате.</div>']];
        }

        // 3. Отображаем новинки
        if ($this->isNewProductQuestion($userMessage)) {
            $products = $this->productService->getNewRandomProducts();
            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши новинки');
            return [['role' => 'assistant', 'text' => $answer]];
        }

        // 4. Берем данные из БД
        if ($products = $this->productService->getProductsByQuery($userMessage)) {
            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши товары');
            return [['role' => 'assistant', 'text' => $answer]];
        }

        // 5. Вызываем оператора если нет подходящих ответов
        $this->historyService->updateHistory($userId, 'operator', $userMessage);
        $this->telegramService->notifyOperatorNewMessage($userId, $userMessage);
        return [['role' => 'operator', 'text' => '<div class="system-note">✅ Ответ на вопрос не найден, передаем оператору.</div>']];
    }

    private function formatSearchResults(array $results, string $query): string
    {
        $html = "🔍 <strong>По запросу \"{$query}\" найдено:</strong>\n\n";

        foreach ($results as $index => $result) {
            $html .= "<strong>" . ($index + 1) . ". {$result['title']}</strong>\n";
            $html .= "{$result['snippet']}\n";
            $html .= "🌐 <a href=\"{$result['url']}\" target=\"_blank\">{$result['domain']}</a>\n\n";
        }

        $html .= "💡 <em>Это результаты поиска из интернета. Для точной информации о наших товарах уточните запрос или позвоните нам.</em>";

        return $html;
    }

    private function isNewProductQuestion(string $question): bool
    {
        $question = mb_strtolower(trim($question));

        $patterns = [
            '/новинк[иау]?/ui',
            '/новые товары/ui',
            '/новый товар/ui',
            '/что новенького/ui',
            '/последние поступления/ui',
            '/недавно поступившие/ui',
            '/свежие товары/ui',
            '/новое в ассортименте/ui',
            '/наши новинки/ui'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $question)) {
                //file_put_contents('new.log', "Pattern matched: " . $pattern . " for question: " . $question . "\n", FILE_APPEND);
                return true;
            }
        }

        return false;
    }

    private function prepareFaqResponse(string $answer, string $userId): array
    {
        $messages = [
            ['role' => 'system', 'text' => 'Ты — консультант, отвечаешь готовыми шаблонами'],
            ['role' => 'assistant', 'text' => $answer]
        ];

        foreach ($this->historyService->getHistory($userId) as $item) {
            $messages[] = ['role' => $item['role'], 'text' => $item['text']];
        }

        return $messages;
    }

    private function prepareRejectionResponse(): array
    {
        return [
            ['role' => 'assistant', 'text' => 'Это вопрос к другому специалисту.']
        ];
    }

    private function prepareFullContext(string $userMessage): array
    {
        $messages = [
            [
                'role' => 'system',
                'text' => 'Ты — помощник интернет-магазина https://xn--80aaack2amqkhfh0c8lg.xn--p1ai/ ' .
                    'Отвечай на вопросы о заказах, оплате и доставке.'
            ]
        ];

        //        $messages = [[
        //            'role' => 'system',
        //            'text' => 'Ты — помощник без тематических ограничений. ' .
        //                'Не отвечай только на вопросы про: ' .
        //                implode(', ', $this->config['topics']['forbidden']) . '. ' .
        //                'На запрещённые темы говори: "Этот вопрос не в моей компетенции".'
        //        ]];

        foreach ($this->historyService->getHistory() as $item) {
            $messages[] = ['role' => $item['role'], 'text' => $item['text']];
        }

        $messages[] = ['role' => 'user', 'text' => $userMessage];

        return $messages;
    }

    private function shouldTransferToOperator(string $userMessage, string $userId): bool
    {
        $triggerPhrases = [
            'оператор', 'человек', 'менеджер', 'позовите', 'соедините с',
            'не понимаю', 'помогите', 'ваш ответ не помог', 'живой'
        ];

        foreach ($triggerPhrases as $phrase) {
            if (stripos($userMessage, $phrase) !== false) {
                return true;
            }
        }

        // Если бот уже несколько раз не смог помочь
        $history = $this->historyService->getHistory($userId);
        $botResponses = array_filter($history, fn($item) => $item['role'] === 'assistant');
        $userQuestions = array_filter($history, fn($item) => $item['role'] === 'user');

        if (count($userQuestions) >= 3 && count($botResponses) >= 2) {
            return true; // Передаем оператору после 3 вопросов
        }

        return false;
    }
}
