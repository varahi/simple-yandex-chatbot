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
        private array $config
    ) {
    }

    public function prepare(string $userMessage): array
    {

        // 1. Проверка FAQ
        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
            //return $this->prepareFaqResponse($answer);
            return [['role' => 'assistant', 'text' => $answer]]; // ← Только готовый ответ
        }

        // 2. Берем данные из БД
        if ($product = $this->productService->findProductByQuery($userMessage)) {

            //file_put_contents('product_result.log', print_r($product, true));

            $answer = $this->productService->generateProductAnswer($userMessage, $product);
            return [['role' => 'assistant', 'text' => $answer]];
        }

        // 3. Проверка тематики
        if (!$this->topicService->isAboutShopping($userMessage)) {
            //return [['role' => 'assistant', 'text' => 'Это вопрос к другому специалисту.']];
            // return $this->prepareRejectionResponse();
        }

        // 4. ToDo:  берем данные из яндекса.

        // 5. Только если не нашли в FAQ - готовим запрос к YandexGPT
        return $this->prepareFullContext($userMessage);
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
