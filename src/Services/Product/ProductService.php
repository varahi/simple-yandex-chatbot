<?php

namespace App\Services\Product;

use App\Repository\ProductRepository;

class ProductService
{
    private ProductAnswerGenerator $answerGenerator;

    private ProductUrlGenerator $urlGenerator;

    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->answerGenerator = new ProductAnswerGenerator();
        $this->urlGenerator = new ProductUrlGenerator();
        $this->productRepository = new ProductRepository();
    }

    public function generateProductAnswer(string $question, array $products, string $title): string
    {
        return $this->answerGenerator->generateAnswer($question, $products, $title);
    }

    public function getProductUrl(array $product): string
    {
        return $this->urlGenerator->generateProductUrl($product);
    }

    public function getNewRandomProducts()
    {
        return $this->productRepository->findNewRandomProducts($_ENV['PRODUCT_RESULT_LIMIT'], $_ENV['NEW_PRODUCT_CATEGORY']);
    }

    public function getProductsByQuery(string $userMessage)
    {
        return $this->productRepository->findProductsByQuery($userMessage, $_ENV['PRODUCT_RESULT_LIMIT']);
    }
}
