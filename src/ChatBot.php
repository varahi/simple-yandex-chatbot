<?php

namespace App;

use App\Contracts\MessagePreparerInterface;
use App\Exceptions\ForbiddenTopicException;
use App\Services\HistoryService;
use App\Services\TopicService;

class ChatBot
{
    private $client;

    private HistoryService $historyService;

    private TopicService $topicService;

    private MessagePreparerInterface $messagePreparer;

    public function __construct(
        YandexGptClient $client,
        HistoryService $historyService,
        TopicService $topicService,
        MessagePreparerInterface $messagePreparer
    ) {
        $this->client = $client;
        $this->historyService = $historyService;
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
        if (count($messages) === 1 && $messages[0]['role'] === 'assistant') {
            $response = $messages[0]['text']; // ← Готовый ответ из FAQ
        } else {
            $response = $this->client->sendRequest($messages); // ← Запрос к YandexGPT
        }

        //$response = $this->client->sendRequest($messages);

        $this->historyService->updateHistory('user', $userMessage);
        $this->historyService->updateHistory('assistant', $response);

        return $response;
    }
}
