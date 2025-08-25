<?php

namespace App\Services\Product;

use App\Database\PDOConnection;
use PDO;
use PDOException;

class ProductImageService
{
    private PDO $pdo;
    private string $baseUrl;

    public function __construct(string $baseUrl = null)
    {
        $this->pdo = PDOConnection::getInstance();
        $this->baseUrl = $baseUrl ?? $_ENV['BASE_URL'];
    }

    public function getProductImageUrl(array $product, string $size = 'small'): ?string
    {
        // Пробуем сначала превью картинку, потом детальную
        $fileId = $product['PREVIEW_PICTURE'] ?? $product['DETAIL_PICTURE'] ?? null;

        if (!$fileId) {
            return null;
        }

        return $this->getFileUrl($fileId, $size);
    }

    public function getFileUrl(int $fileId, string $size = 'small'): ?string
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM b_file 
                WHERE ID = :file_id
            ");

            $stmt->execute(['file_id' => $fileId]);
            $file = $stmt->fetch();

            if (!$file) {
                return null;
            }

            return $this->generateFileUrl($file, $size);

        } catch (PDOException $e) {
            error_log("File URL error: " . $e->getMessage());
            return null;
        }
    }

    private function generateFileUrl(array $file, string $size): string
    {
        $fileName = $file['FILE_NAME'];
        $subdir = $file['SUBDIR'];
        return $this->baseUrl . '/upload/' . $subdir . '/' . $fileName;
    }


    private function getSubdirFromId(int $fileId): string
    {
        // Bitrix хранит файлы в подпапках по ID
        $strId = (string)$fileId;
        $len = strlen($strId);

        if ($len <= 3) {
            return $strId;
        }

        $parts = [
            substr($strId, 0, 2),
            substr($strId, 2, 2),
            substr($strId, 4)
        ];

        return implode('/', array_filter($parts));
    }

    private function getSizeSuffix(string $size): string
    {
        $sizes = [
            'small' => '_80x80',
            'medium' => '_200x200',
            'large' => '_500x500',
            'xlarge' => '_1000x1000'
        ];

        return $sizes[$size] ?? '';
    }

    public function getProductImages(array $product): array
    {
        $images = [];

        // Основное изображение
        if ($previewUrl = $this->getProductImageUrl($product, 'medium')) {
            $images['preview'] = $previewUrl;
        }

        // Детальное изображение
        if (!empty($product['DETAIL_PICTURE']) && $product['DETAIL_PICTURE'] != $product['PREVIEW_PICTURE']) {
            if ($detailUrl = $this->getFileUrl($product['DETAIL_PICTURE'], 'large')) {
                $images['detail'] = $detailUrl;
            }
        }

        return $images;
    }
}
