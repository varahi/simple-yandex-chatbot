<?php

namespace src\Services;

use src\Services\FaqService;
use src\Services\TopicService;
use src\Services\HistoryService;

class MessagePreparationService
{
    public function __construct(
        private FaqService $faqService,
        private TopicService $topicService,
        private HistoryService $historyService,
        private array $config
    ) {
    }

    public function prepare(string $userMessage): array
    {
        // 1. Проверка FAQ
        //        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
        //            return $this->prepareFaqResponse($answer);
        //        }

        // 2. Проверка тематики
        //        if (!$this->topicService->isAboutShopping($userMessage)) {
        //            return $this->prepareRejectionResponse();
        //        }

        // 3. Подготовка полного контекста
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
        //        $messages = [
        //            [
        //                'role' => 'system',
        //                'text' => 'Ты — помощник интернет-магазина. ' .
        //                    'Отвечай на вопросы о заказах, оплате и доставке.'
        //            ]
        //        ];


        $messages = [[
            'role' => 'system',
            'text' => 'Ты — помощник без тематических ограничений. ' .
                'Не отвечай только на вопросы про: ' .
                implode(', ', $this->config['topics']['forbidden']) . '. ' .
                'На запрещённые темы говори: "Этот вопрос не в моей компетенции".'
        ]];

        foreach ($this->historyService->getHistory() as $item) {
            $messages[] = ['role' => $item['role'], 'text' => $item['text']];
        }

        $messages[] = ['role' => 'user', 'text' => $userMessage];

        return $messages;
    }
}
