<?php

namespace App\Services;

class FaqService
{
    private $faq;

    public function __construct(
        array $faq,
    ) {
        $this->faq = $faq;
    }

    public function getPredefinedAnswer(string $question): ?string
    {
        $question = mb_strtolower($question);

        // Ищем как точные совпадения, так и частичные
        foreach ($this->faq as $pattern => $answer) {
            // Если ключ - регулярное выражение
            if (str_starts_with($pattern, '/')) {
                if (preg_match($pattern, $question)) {
                    return $answer;
                }
            }
            // Обычный текст
            elseif (str_contains($question, $pattern)) {
                return $answer;
            }
        }

        return null;
    }
}
