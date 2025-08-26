<?php

namespace App\Services\Product;

use App\Database\PDOConnection;
use PDO;
use PDOException;

class ProductService
{
    private PDO $pdo;

    private ProductAnswerGenerator $answerGenerator;

    private ProductUrlGenerator $urlGenerator;

    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
        $this->answerGenerator = new ProductAnswerGenerator();
        $this->urlGenerator = new ProductUrlGenerator();
    }

    public function findProductsByQuery(string $query, int $limit): array
    {
        try {
            $searchQuery = '%' . $query . '%';

            $stmt = $this->pdo->prepare("
            SELECT * FROM b_iblock_element 
            WHERE 
                NAME LIKE :query1 OR
                PREVIEW_TEXT LIKE :query2 OR
                DETAIL_TEXT LIKE :query3 OR
                SEARCHABLE_CONTENT LIKE :query4
                AND ACTIVE = :active
            LIMIT :limit
        ");

            $stmt->bindValue(':query1', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query2', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query3', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query4', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':active', 'Y', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll() ?: [];

        } catch (PDOException $e) {
            error_log("Products search error: " . $e->getMessage());
            return [];
        }
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

    public function generateProductAnswer(string $question, array $products): string
    {
        return $this->answerGenerator->generateAnswer($question, $products);
    }

    public function getProductUrl(array $product): string
    {
        return $this->urlGenerator->generateProductUrl($product);
    }
}
