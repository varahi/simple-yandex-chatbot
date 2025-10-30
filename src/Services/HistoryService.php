<?php

namespace App\Services;

use Exception;

class HistoryService
{
    private $history = [];

    private $config;

    private $storageFile;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storageFile = __DIR__ . '/../../storage/history.json';

        if (file_exists($this->storageFile)) {
            $this->history = json_decode(file_get_contents($this->storageFile), true) ?: [];
        }
    }

    public function updateHistory(string $userId, string $role, string $text): void
    {
        if (!isset($this->history[$userId])) {
            $this->history[$userId] = [];
        }

        file_put_contents('updateHistory.log', "Role: " . $role . "\n", FILE_APPEND);

        if($role == 'operator') {
            $this->history[$userId][] = [
                'role' => $role,
                'text' => $text,
                'time' => date('H:i:s'),
            ];

            // Оставляем только последние $maxHistory сообщений для этого пользователя
            if (count($this->history[$userId]) > $this->config['max_history']) {
                array_shift($this->history[$userId]);
            }

            $this->persist();
        }

    }

    public function getHistory(string $userId): array
    {
        return $this->history[$userId] ?? [];
    }

    public function clearHistory(string $userId): void
    {
        unset($this->history[$userId]);
        $this->persist();
    }

    private function persist(): void
    {
        file_put_contents($this->storageFile, json_encode($this->history, JSON_UNESCAPED_UNICODE));
    }

    public function isOperatorSession(string $userId): bool
    {
        $history = $this->getHistory($userId);
        foreach (array_reverse($history) as $item) {
            if ($item['role'] === 'operator' && !empty($item['text'])) {
                return true;
            }
            // Можно добавить условие: если бот дал нормальный ответ, операторская сессия закрывается
            if ($item['role'] === 'assistant') {
                break;
            }
        }
        return false;
    }
}
