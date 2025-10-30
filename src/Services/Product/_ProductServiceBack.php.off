<?php

namespace App\Services\Product;

use App\Database\PDOConnection;
use PDO;
use PDOException;

class _ProductServiceBack
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

    //    public function findProductByQuery(string $query, int $limit = 4): ?array
    //    {
    //        try {
    //            $searchQuery = '%' . $query . '%';
    //
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Original query: " . $query . "\n", FILE_APPEND);
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Search pattern: " . $searchQuery . "\n", FILE_APPEND);
    //
    //            /*
    //            $sql = "
    //            SELECT * FROM b_iblock_element
    //            WHERE
    //                NAME LIKE :query1 OR
    //                PREVIEW_TEXT LIKE :query2 OR
    //                DETAIL_TEXT LIKE :query3 OR
    //                SEARCHABLE_CONTENT LIKE :query4
    //            LIMIT :limit";
    //            */
    //
    //
    //            $sql = "
    //            SELECT * FROM b_iblock_element
    //            WHERE
    //                SEARCHABLE_CONTENT LIKE :query4
    //                AND ACTIVE = :active
    //                LIMIT :limit";
    //
    //
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] SQL: " . $sql . "\n", FILE_APPEND);
    //
    //            $stmt = $this->pdo->prepare($sql);
    //
    //            // Явно биндим параметры с логированием
    ////            $stmt->bindValue(':query1', $searchQuery, PDO::PARAM_STR);
    ////            $stmt->bindValue(':query2', $searchQuery, PDO::PARAM_STR);
    ////            $stmt->bindValue(':query3', $searchQuery, PDO::PARAM_STR);
    //            $stmt->bindValue(':query4', $searchQuery, PDO::PARAM_STR);
    //            $stmt->bindValue(':active', 'Y', PDO::PARAM_STR);
    //            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    //
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Parameters bound\n", FILE_APPEND);
    //
    //            $result = $stmt->execute();
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Execute result: " . ($result ? 'true' : 'false') . "\n", FILE_APPEND);
    //
    //            if (!$result) {
    //                $error = $stmt->errorInfo();
    //                //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Error: " . json_encode($error, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    //                return null;
    //            }
    //
    //            //$rowCount = $stmt->rowCount();
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Row count: " . $rowCount . "\n", FILE_APPEND);
    //
    //            $product = $stmt->fetch();
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Fetch result: " . json_encode($product, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    //            //return $product ?: null;
    //
    //            return $stmt->fetchAll() ?: [];
    //
    //        } catch (PDOException $e) {
    //            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] PDOException: " . $e->getMessage() . "\n", FILE_APPEND);
    //            //return null;
    //            return [];
    //        }
    //    }

    public function findProductByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM b_iblock_element 
            WHERE name = :name
        ");

        $stmt->execute(['name' => $name]);

        return $stmt->fetch() ?: null;
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
