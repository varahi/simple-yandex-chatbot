<?php

namespace App\Services\Product;

use App\Database\PDOConnection;
use PDO;
use PDOException;

class ProductUrlGenerator
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
    }

    public function generateProductUrl(array $product): string
    {
        $productId = $product['ID'] ?? 0;
        $sectionId = $product['IBLOCK_SECTION_ID'] ?? 0;
        //file_put_contents('section_id.log', "Section id: " . $sectionId . "\n", FILE_APPEND);

        if (!$productId || !$sectionId) {
            return "https://компаниябогатая.рф/catalog/";
        }

        // Получаем полный путь категорий
        $categoryPath = $this->getCategoryPath($sectionId);
        $productCode = $product['CODE'] ?? '';

        if (empty($categoryPath) || empty($productCode)) {
            return "https://компаниябогатая.рф/catalog/";
        }

        return "https://компаниябогатая.рф/catalog/" . $categoryPath . "/" . $productCode;
    }

    private function getCategoryPath(int $sectionId): string
    {
        try {
            $stmt = $this->pdo->prepare("
                WITH RECURSIVE category_path AS (
                    -- Базовый случай: начальная категория
                    SELECT 
                        s.ID,
                        s.CODE,
                        s.IBLOCK_SECTION_ID as PARENT_ID,
                        s.CODE as PATH,
                        1 as LEVEL
                    FROM b_iblock_section s 
                    WHERE s.ID = :section_id
                    
                    UNION ALL
                    
                    -- Рекурсивный случай: идем к родителям
                    SELECT 
                        s.ID,
                        s.CODE,
                        s.IBLOCK_SECTION_ID,
                        CONCAT(s.CODE, '/', cp.PATH),
                        cp.LEVEL + 1
                    FROM b_iblock_section s
                    INNER JOIN category_path cp ON s.ID = cp.PARENT_ID
                    WHERE s.IBLOCK_SECTION_ID IS NOT NULL
                )
                SELECT PATH FROM category_path 
                ORDER BY LEVEL DESC 
                LIMIT 1
            ");

            $stmt->execute(['section_id' => $sectionId]);
            $result = $stmt->fetch();

            return $result['PATH'] ?? '';

        } catch (PDOException $e) {
            error_log("Category path error: " . $e->getMessage());
            return '';
        }
    }
}
