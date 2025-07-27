<?php

namespace App\Services;

class TopicService
{
    private $config;

    public function __construct(
        array $config,
    ) {
        $this->config = $config;
    }

    public function isForbidden(string $text): bool
    {
        $text = mb_strtolower($text);

        // Если есть явно запрещённые слова — блокируем
        foreach ($this->config['topics']['forbidden'] as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        // Если ALLOWED_TOPICS содержит '*' — разрешаем всё остальное
        if (in_array('*', $this->config['topics']['allowed'])) {
            return false;
        }

        // Стандартная проверка (если нет '*')
        foreach ($this->config['topics']['allowed'] as $topic) {
            if (str_contains($text, $topic)) {
                return false;
            }
        }

        return true; // Тема не разрешена
    }

    public function isAboutShopping(string $text): bool
    {
        $keywords = [
            'заказ', 'оплат', 'доставк', 'корзин', 'оформить',
            'куп', 'каталог', 'товар', 'магазин'
        ];

        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
