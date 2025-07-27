<?php

namespace src;

use src\Contracts\MessagePreparerInterface;
use src\Services\HistoryService;
use src\Services\TopicService;

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
            return 'Эта тема не поддерживается.';
        }

        $messages = $this->messagePreparer->prepare($userMessage);
        $response = $this->client->sendRequest($messages);

        $this->historyService->updateHistory('user', $userMessage);
        $this->historyService->updateHistory('assistant', $response);

        return $response;
    }
}
