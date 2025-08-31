<?php

namespace App\Services;

class YandexSearchService
{
    private string $apiKey;
    private string $searchEngineId;

    public function __construct(string $apiKey, string $searchEngineId)
    {
        $this->apiKey = $apiKey;
        $this->searchEngineId = $searchEngineId;
    }

    public function search(string $query, int $limit = 3): array
    {
        try {
            $url = "https://api.yandex.com/v1/search?apikey={$this->apiKey}&text=" . urlencode($query) . "&count={$limit}";

            file_put_contents('yandex.log', "Result: " . $url . "\n", FILE_APPEND);

            $client = new \GuzzleHttp\Client();
            $response = $client->get($url, [
                'timeout' => 3,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return array_map(function($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'url' => $item['url'] ?? '',
                    'snippet' => $item['snippet'] ?? '',
                    'domain' => parse_url($item['url'] ?? '', PHP_URL_HOST)
                ];
            }, $data['results'] ?? []);

        } catch (\Exception $e) {
            error_log("Yandex search error: " . $e->getMessage());
            return [];
        }
    }
}