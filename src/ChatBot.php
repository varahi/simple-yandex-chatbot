<?php

namespace App;

use App\Contracts\MessagePreparerInterface;
use App\Exceptions\ForbiddenTopicException;
use App\Services\HistoryService;
use App\Services\SessionService;
use App\Services\TopicService;

class ChatBot
{
    private TopicService $topicService;

    private MessagePreparerInterface $messagePreparer;

    public function __construct(
        TopicService $topicService,
        MessagePreparerInterface $messagePreparer,
    ) {
        $this->topicService = $topicService;
        $this->messagePreparer = $messagePreparer;
    }

    public function handleMessage(string $userMessage): string
    {
        if ($this->topicService->isForbidden($userMessage)) {
            throw new ForbiddenTopicException();
        }

        $messages = $this->messagePreparer->prepare($userMessage);

        // Если messages содержит только ответ из FAQ - не обращаемся к YandexGPT
//        if (count($messages) === 1 && $messages[0]['role'] === 'assistant') {
//            $response = $messages[0]['text']; // ← Готовый ответ из FAQ
//        } else {
//            $response = $this->client->sendRequest($messages); // ← Запрос к YandexGPT
//        }

        $response = $messages[0]['text'];
        return $response;
    }
}
