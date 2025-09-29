<?php

namespace App\Services;

use App\Contracts\MessagePreparerInterface;
use App\Services\Product\ProductService;

class MessagePreparationService implements MessagePreparerInterface
{
    public function __construct(
        private FaqService $faqService,
        private TopicService $topicService,
        private HistoryService $historyService,
        private ProductService $productService,
    ) {
    }

    public function prepare(string $userMessage): array
    {

        // 1. Проверка FAQ
        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
            //return $this->prepareFaqResponse($answer);
            return [['role' => 'assistant', 'text' => '<div class="products-card"> ' . $answer . '</div>']]; // ← Только готовый ответ
        }

        // 2. Отображаем новинки
        if ($this->isNewProductQuestion($userMessage)) {
            $products = $this->productService->getNewRandomProducts();
            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши новинки');
            return [['role' => 'assistant', 'text' => $answer]];
        }

        // 3. Берем данные из БД
        if ($products = $this->productService->getProductsByQuery($userMessage)) {
            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши товары');
            return [['role' => 'assistant', 'text' => $answer]];
        }

        // 4. Проверка тематики
        if (!$this->topicService->isAboutShopping($userMessage)) {
            //return [['role' => 'assistant', 'text' => 'Это вопрос к другому специалисту.']];
            // return $this->prepareRejectionResponse();
        }

        // 6. Только если не нашли в FAQ - готовим запрос к YandexGPT
        //return $this->prepareFullContext($userMessage);
        // В данной реализации полностью отключаем ИИ и перенаправляем запрос
        return $this->prepareRejectionResponse();
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

    private function prepareFaqResponse(string $answer): array
    {
        $messages = [
            ['role' => 'system', 'text' => 'Ты — консультант, отвечаешь готовыми шаблонами'],
            ['role' => 'assistant', 'text' => $answer]
        ];

        foreach ($this->historyService->getHistory() as $item) {
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
}
