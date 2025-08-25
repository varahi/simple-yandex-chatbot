<?php

namespace App\Services\Product;

class ProductAnswerGeneratorExtend
{
    private ProductUrlGenerator $urlGenerator;

    public function __construct()
    {
        $this->urlGenerator = new ProductUrlGenerator();
    }

    public function generateAnswer(string $question, array $product): string
    {
        $question = mb_strtolower(trim($question));

        // Определяем тип вопроса и формируем ответ
        if ($this->isPriceQuestion($question)) {
            file_put_contents('question.log', "[" . date('Y-m-d H:i:s') . "] Original query: " . $question . "\n", FILE_APPEND);
            return $this->generatePriceAnswer($product);
        }

        if ($this->isDescriptionQuestion($question)) {
            return $this->generateDescriptionAnswer($product);
        }

        if ($this->isUsageQuestion($question)) {
            return $this->generateUsageAnswer($product);
        }

        if ($this->isAvailabilityQuestion($question)) {
            return $this->generateAvailabilityAnswer($product);
        }

        // Общий ответ по умолчанию
        return $this->generateGeneralAnswer($product);
    }

    private function isPriceQuestion(string $question): bool
    {
        return preg_match('/цена|стоимость|сколько стоит|цен[уы]|прайс/ui', $question);
    }

    private function isDescriptionQuestion(string $question): bool
    {
        return preg_match('/описание|что это|о товаре|расскажи|покажи/ui', $question);
    }

    private function isUsageQuestion(string $question): bool
    {
        return preg_match('/применение|как использовать|для чего|использование|инструкция/ui', $question);
    }

    private function isAvailabilityQuestion(string $question): bool
    {
        return preg_match('/наличие|есть ли|доступен|в наличии|можно купить/ui', $question);
    }

    private function generatePriceAnswer(array $product): string
    {
        // TODO: Добавить получение цены из другого источника (iblock_element_price)
        return "📦 {$product['NAME']}\n\n".
            "💰 Цену уточняйте у менеджера\n".
            "📞 Для получения актуальной цены и наличия позвоните нам +7 (914) 70-170-09";
    }

    private function generateDescriptionAnswer(array $product): string
    {
        $text = $product['DETAIL_TEXT'] ?? $product['PREVIEW_TEXT'] ?? '';
        $url = "https://компаниябогатая.рф/{$product['CODE']}/";

        return "📦 {$product['NAME']}\n\n".
            "📖 Описание:\n".
            $this->truncateText($text, 300) . "\n\n".
            "🔗 Подробнее: https://компаниябогатая.рф/{$product['CODE']}/";
    }

    private function generateUsageAnswer(array $product): string
    {
        $text = $product['DETAIL_TEXT'] ?? $product['PREVIEW_TEXT'] ?? '';

        return "📦 {$product['NAME']}\n\n".
            "🎯 Применение:\n".
            $this->extractUsageInfo($text) . "\n\n".
            "📋 Особенности: " . $this->extractFeatures($text);
    }

    private function generateAvailabilityAnswer(array $product): string
    {
        $status = $product['ACTIVE'] === 'Y' ? '✅ В наличии' : '⏳ Под заказ';

        return "📦 {$product['NAME']}\n\n".
            "{$status}\n".
            "📞 Уточнить наличие и сроки: позвоните нам +7 (914) 70-170-09";
    }

    private function generateGeneralAnswer(array $product): string
    {
        $text = $product['PREVIEW_TEXT'] ?? $product['DETAIL_TEXT'] ?? '';
        //$url = "https://компаниябогатая.рф/catalog/{$product['CODE']}/";
        $url = $this->urlGenerator->generateProductUrl($product);
        $link = $this->formatMarkdownLink($url, 'перейти на страницу товара');

        return "📦 {$product['NAME']}\n\n".
            "📖 " . $this->truncateText($text, 200) . "\n\n".
            "🔗 Подробнее: {$link}\n".
            //"🔗 Подробнее: <a href=\"{$url}\">перейти на страницу товара</a>\n".
            "📞 Консультация: позвоните нам +7 (914) 70-170-09";
    }

    private function truncateText(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }

    private function extractUsageInfo(string $text): string
    {
        // Простая логика извлечения информации о применении
        if (preg_match('/(для|применяется|используется)[^.!?]{10,100}/ui', $text, $matches)) {
            return trim($matches[0]) . '.';
        }

        return $this->truncateText($text, 150);
    }

    private function extractFeatures(string $text): string
    {
        $features = [];

        if (strpos($text, 'детский') !== false) {
            $features[] = '👶 Детское средство';
        }
        if (strpos($text, 'аэрозоль') !== false) {
            $features[] = '💨 Аэрозоль';
        }
        if (strpos($text, 'клещ') !== false) {
            $features[] = '🕷️ Защита от клещей';
        }
        if (strpos($text, 'комаров') !== false) {
            $features[] = '🦟 Защита от комаров';
        }

        return $features ? implode(', ', $features) : 'Средство защиты';
    }

    private function formatMarkdownLink(string $url, string $text): string
    {
        $encodedUrl = str_replace('_', '%5F', $url);
        return "[{$text}]({$encodedUrl})";
    }
}