<?php

namespace App\Services;

use App\Contracts\MessagePreparerInterface;

class MessagePreparationService implements MessagePreparerInterface
{
    private $topicsConfig;

    public function __construct(
        private FaqService $faqService,
        private TopicService $topicService,
        private HistoryService $historyService,
        private SearchService $searchService,
        private ProductService $productService,
        array $topicsConfig
    ) {
        $this->topicsConfig = $topicsConfig;
    }

    public function prepare(string $userMessage): array
    {
        // 1. Проверка FAQ
//        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
//            return $this->prepareFaqResponse($answer);
//        }

        // 2. Проверка товаров
        if ($product = $this->productService->findProduct($userMessage)) {
            return $this->prepareProductResponse($product);
        }

        // 3. Проверка тематики
        /*
        if (!$this->topicService->isAboutShopping($userMessage)) {
            return $this->prepareRejectionResponse();
        }*/

        // 4. Подготовка полного контекста
        //return $this->prepareFullContext($userMessage);

        //return $this->performSearch($userMessage);
    }

    private function prepareProductResponse(array $productInfo): array
    {
        $answer = "🔹 *{$productInfo['name']}*\n\n";
        $answer .= "📋 *Описание:* {$productInfo['description']}\n\n";

        if (!empty($productInfo['application'])) {
            $answer .= "🛠 *Где можно применять:* {$productInfo['application']}\n\n";
        }

        $answer .= "💰 *Цена:* {$productInfo['price']} руб.\n";
        $answer .= "🔗 *Ссылка:* {$productInfo['link']}";

        return [
            ['role' => 'assistant', 'text' => $answer]
        ];
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
        //        $messages = [
        //            [
        //                'role' => 'system',
        //                'text' => 'Ты — помощник интернет-магазина. ' .
        //                    'Отвечай на вопросы о заказах, оплате и доставке.'
        //            ]
        //        ];


        //file_put_contents('config.txt', print_r($this->allowed, true));

        $messages = [[
            'role' => 'system',
            'text' => 'Ты — помощник без тематических ограничений. ' .
                'Не отвечай только на вопросы про: ' .
                implode(', ', $this->topicsConfig['forbidden']) . '. ' .
                'На запрещённые темы говори: "Этот вопрос не в моей компетенции".'
        ]];

        foreach ($this->historyService->getHistory() as $item) {
            $messages[] = ['role' => $item['role'], 'text' => $item['text']];
        }

        $messages[] = ['role' => 'user', 'text' => $userMessage];

        return $messages;
    }

    private function performSearch(string $userMessage): array
    {
        $searchResults = $this->searchService->search($userMessage);

        $messages = [
            ['role' => 'system', 'text' => 'Ты — помощник, отвечающий на вопросы, включая поиск в Интернете. С сайта https://компаниябогатая.рф/'],
            ['role' => 'user', 'text' => $userMessage],
        ];

        foreach ($searchResults as $result) {
            $messages[] = [
                'role' => 'assistant',
                'text' => sprintf("Результат: %s (%s) - %s", $result['title'], $result['url'], $result['snippet'])
            ];
        }

        return $messages;
    }
}
