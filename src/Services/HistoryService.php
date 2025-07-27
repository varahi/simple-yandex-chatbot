<?php

namespace src\Services;

use Exception;

class HistoryService
{
    private $history = [];

    private $config;

    private $storageFile = __DIR__.'/history.json';

    public function __construct(
        array $config,
    ) {
        $this->config = $config;
        if (file_exists($this->storageFile)) {
            $this->history = json_decode(file_get_contents($this->storageFile), true) ?: [];
        }
    }

    public function updateHistory(string $role, string $text): void
    {
        try {
            $this->history[] = [
                'role' => $role,
                'text' => $text,
                'time' => date('H:i:s') // Формат: "14:30:22"
            ];

            // Оставляем только последние 5 сообщений
            if (count($this->history) > $this->config['max_history']) {
                array_shift($this->history);
            }

        } catch (Exception $e) {
            error_log('History save error: ' . $e->getMessage());
        }
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function clearHistory(): void
    {
        $this->history = [];
        file_put_contents($this->storageFile, '[]'); // Очищаем файл
    }
}
