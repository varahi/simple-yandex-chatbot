<?php

namespace App\Handlers;

use App\ChatBot;
use App\Exceptions\InvalidMessageException;
use App\Exceptions\ForbiddenTopicException;

class ChatRequestHandler
{
    public function __construct(
        private ChatBot $bot
    ) {
    }


    public function handle(array $input): array
    {
        $this->validateInput($input);

        try {
            return [
                'response' => $this->bot->handleMessage($input['message'])
            ];
        } catch (ForbiddenTopicException $e) {
            // Можно добавить специфичную обработку
            throw $e;
        }
    }

    private function validateInput(array $input): void
    {
        if (empty($input['message'])) {
            throw new InvalidMessageException('Сообщение не может быть пустым');
        }

        $message = trim($input['message']);
        if (mb_strlen($message) < 2) {
            throw new InvalidMessageException('Сообщение слишком короткое');
        }
    }
}
