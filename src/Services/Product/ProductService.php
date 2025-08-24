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

    public function findProductByQuery(string $query): ?array
    {
        try {
            $searchQuery = '%' . $query . '%';

            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Original query: " . $query . "\n", FILE_APPEND);
            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Search pattern: " . $searchQuery . "\n", FILE_APPEND);

            $sql = "
            SELECT * FROM b_iblock_element 
            WHERE 
                NAME LIKE :query1 OR
                PREVIEW_TEXT LIKE :query2 OR
                DETAIL_TEXT LIKE :query3 OR
                SEARCHABLE_CONTENT LIKE :query4
            LIMIT 1
        ";

            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] SQL: " . $sql . "\n", FILE_APPEND);

            $stmt = $this->pdo->prepare($sql);

            // Явно биндим параметры с логированием
            $stmt->bindValue(':query1', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query2', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query3', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':query4', $searchQuery, PDO::PARAM_STR);

            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Parameters bound\n", FILE_APPEND);

            $result = $stmt->execute();
            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Execute result: " . ($result ? 'true' : 'false') . "\n", FILE_APPEND);

            if (!$result) {
                $error = $stmt->errorInfo();
                //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Error: " . json_encode($error, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
                return null;
            }

            $rowCount = $stmt->rowCount();
            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Row count: " . $rowCount . "\n", FILE_APPEND);

            $product = $stmt->fetch();
            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] Fetch result: " . json_encode($product, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            return $product ?: null;

        } catch (PDOException $e) {
            //file_put_contents('product.log', "[" . date('Y-m-d H:i:s') . "] PDOException: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    public function findProductByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM b_iblock_element 
            WHERE name = :name
        ");

        $stmt->execute(['name' => $name]);

        return $stmt->fetch() ?: null;
    }

    public function generateProductAnswer(string $question, array $product): string
    {
        return $this->answerGenerator->generateAnswer($question, $product);
    }

    public function getProductUrl(array $product): string
    {
        return $this->urlGenerator->generateProductUrl($product);
    }
}
