<?php

namespace src;

use src\Services\HistoryService;
use src\Services\MessagePreparationService;
use src\Services\TopicService;

class ChatBot
{
    private $client;

    private HistoryService $historyService;

    private TopicService $topicService;

    private MessagePreparationService $messagePreparer;

    public function __construct(
        YandexGptClient $client,
        HistoryService $historyService,
        TopicService $topicService,
        MessagePreparationService $messagePreparer
    ) {
        $this->client = $client;
        $this->historyService = $historyService;
        $this->topicService = $topicService;
        $this->messagePreparer = $messagePreparer;
    }

    public function handleMessage(string $userMessage): string
    {
        if ($this->topicService->isForbidden($userMessage)) {
            return 'Эта тема не поддерживается.';
        }

        $messages = $this->messagePreparer->prepare($userMessage);
        $response = $this->client->sendRequest($messages);

        $this->historyService->updateHistory('user', $userMessage);
        $this->historyService->updateHistory('assistant', $response);

        return $response;
    }
}
