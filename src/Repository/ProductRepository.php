<?php

namespace App\Repository;

use App\Database\PDOConnection;
use PDO;
use PDOException;

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
    }

    public function findProductsByQuery(string $query, int $limit): array
    {
        $keywords = $this->extractKeywords($query);
        if (empty($keywords)) {
            return [];
        }

        $sql = "
        SELECT * FROM b_iblock_element
        WHERE (
            NAME LIKE :q1 OR
            PREVIEW_TEXT LIKE :q2 OR
            DETAIL_TEXT LIKE :q3 OR
            SEARCHABLE_CONTENT LIKE :q4
        ) AND ACTIVE = :active
        LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $results = [];

        foreach ($keywords as $keyword) {
            $search = '%' . $keyword . '%';
            $stmt->bindValue(':q1', $search, PDO::PARAM_STR);
            $stmt->bindValue(':q2', $search, PDO::PARAM_STR);
            $stmt->bindValue(':q3', $search, PDO::PARAM_STR);
            $stmt->bindValue(':q4', $search, PDO::PARAM_STR);
            $stmt->bindValue(':active', 'Y', PDO::PARAM_STR);

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // аккумулируем и устраняем дубликаты по ID
            foreach ($rows as $r) {
                if (!isset($results[$r['ID']])) {
                    $results[$r['ID']] = $r;
                }
            }

            // если нужно ограничить общее количество результатов, можно остановиться
            if (count($results) >= $limit) {
                break;
            }
        }

        return array_values($results);
    }

    public function findNewRandomProducts(int $limit, int $categoryId): array
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT 
                e.*,
                COALESCE(
                    GREATEST(e.DATE_CREATE, e.TIMESTAMP_X),
                    e.DATE_CREATE
                ) as last_activity_date
            FROM b_iblock_element e
            WHERE 
                e.ACTIVE = 'Y'
                AND e.IBLOCK_SECTION_ID = :category_id
                AND (
                    e.DATE_CREATE >= DATE_SUB(NOW(), INTERVAL 120 DAY) 
                    OR e.TIMESTAMP_X >= DATE_SUB(NOW(), INTERVAL 120 DAY)
                )
                AND (e.DETAIL_PICTURE IS NOT NULL OR e.PREVIEW_PICTURE IS NOT NULL)
            ORDER BY 
                RAND(),
                last_activity_date DESC
            LIMIT :limit
        ");

            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $products = $stmt->fetchAll() ?: [];

            // Если не нашли товары с изображениями, ищем любые
            if (empty($products)) {
                return $this->findAnyNewProducts($limit, $categoryId);
            }

            return $products;

        } catch (PDOException $e) {
            error_log("New random products search error: " . $e->getMessage());
            return [];
        }
    }

    private function findAnyNewProducts(int $limit, int $categoryId): array
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT e.*
            FROM b_iblock_element e
            WHERE 
                e.ACTIVE = 'Y'
                AND e.IBLOCK_SECTION_ID = :category_id
                AND (
                    e.DATE_CREATE >= DATE_SUB(NOW(), INTERVAL 120 DAY) 
                    OR e.TIMESTAMP_X >= DATE_SUB(NOW(), INTERVAL 120 DAY)
                )
            ORDER BY RAND()
            LIMIT :limit
        ");

            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];

        } catch (PDOException $e) {
            error_log("Any new products search error: " . $e->getMessage());
            return [];
        }
    }

    private function extractKeywords(string $query): array
    {
        // Удаляем стоп-слова
        $stopWords = [
            'есть', 'ли', 'у', 'вас', 'вам', 'вами', 'ваш', 'ваша', 'ваше', 'ваши',
            'какой', 'какая', 'какое', 'какие',
            'где', 'когда', 'как', 'почему', 'зачем',
            'можно', 'нужно', 'хочу', 'хотел', 'хотела',
            'купить', 'приобрести', 'заказать'
        ];
        $words = preg_split('/\s+/', mb_strtolower(trim($query)));

        // Фильтруем и оставляем только значимые слова (длиннее 2 символов)
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return mb_strlen($word) > 2 && !in_array($word, $stopWords);
        });

        return array_values(array_unique($keywords));
    }

}
