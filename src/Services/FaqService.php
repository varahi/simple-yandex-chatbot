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

//    public function getPredefinedAnswer(string $question): ?string
//    {
//        $question = mb_strtolower($question);
//
//        // Ищем как точные совпадения, так и частичные
//        foreach ($this->faq as $pattern => $answer) {
//            // Если ключ - регулярное выражение
//            if (str_starts_with($pattern, '/')) {
//                if (preg_match($pattern, $question)) {
//                    //file_put_contents('answer.log', "Pattern: ".$pattern. 'Answer: ' . $answer . 'Question: ' . $question . "\n", FILE_APPEND);
//                    return $answer;
//                }
//            }
//            // Обычный текст
//            elseif (str_contains($question, $pattern)) {
//                return $answer;
//            }
//        }
//
//        return null;
//    }

    public function getPredefinedAnswer(string $question): ?string
    {
        $question = mb_strtolower(trim($question));

        // Логируем вопрос для отладки
        //file_put_contents('faq_debug.log', "Вопрос: " . $question . "\n", FILE_APPEND);

        foreach ($this->faq as $faqItem) {
            foreach ($faqItem['patterns'] as $pattern) {
                if (preg_match($pattern, $question)) {
                    // Логируем найденное совпадение
                    file_put_contents('faq_debug.log',
                        "Найдено: " . $pattern . " -> " . $faqItem['answer'] . "\n",
                        FILE_APPEND
                    );
                    return $faqItem['answer'];
                }
            }
        }

        file_put_contents('faq_debug.log', "Не найдено совпадений\n", FILE_APPEND);
        return null;
    }
}
