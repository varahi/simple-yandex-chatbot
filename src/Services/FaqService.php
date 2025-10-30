<?php

namespace App\Services;

class FaqService
{
    private $faq;

    public function __construct(
        array $faq
    ) {
        $this->faq = $faq;
    }

    public function getPredefinedAnswer(string $question): ?string
    {
        $question = mb_strtolower(trim($question));

        // Логируем вопрос для отладки
        //file_put_contents('faq_debug.log', "Вопрос: " . $question . "\n", FILE_APPEND);

        foreach ($this->faq as $faqItem) {
            foreach ($faqItem['patterns'] as $pattern) {
                if (preg_match($pattern, $question)) {
                    // Логируем найденное совпадение
                    return $faqItem['answer'];
                }
            }
        }

        //file_put_contents('faq_debug.log', "Не найдено совпадений\n", FILE_APPEND);
        return null;
    }
}
