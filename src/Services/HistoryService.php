<?php

namespace src\Services;

class HistoryService
{
    private $history = [];

    private $storageFile = __DIR__.'/history.json';

    public function __construct()
    {
        if (file_exists($this->storageFile)) {
            $this->history = json_decode(file_get_contents($this->storageFile), true) ?: [];
        }
    }

    public function updateHistory(string $role, string $text): void
    {
        $this->history[] = [
            'role' => $role,
            'text' => $text,
            'time' => date('H:i:s') // Формат: "14:30:22"
        ];

        // Оставляем только последние 5 сообщений
        if (count($this->history) > 5) {
            array_shift($this->history);
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
