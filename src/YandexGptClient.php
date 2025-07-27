<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class YandexGptClient
{
    private $client;
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => 'https://llm.api.cloud.yandex.net',
            'timeout' => 10.0
        ]);
    }

    public function sendRequest(array $messages): string
    {
        $payload = [
            'modelUri' => $this->config['model_uri'],
            'completionOptions' => [
                'stream' => false,
                'temperature' => 0.6,
                'maxTokens' => 2000
            ],
            'messages' => $messages
        ];

        try {
            $response = $this->client->post('/foundationModels/v1/completion', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['iam_token'],
                    'x-folder-id' => $this->config['folder_id'],
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['result']['alternatives'][0]['message']['text'] ?? 'Ошибка: не удалось получить ответ';

        } catch (GuzzleException $e) {
            error_log('API Error: ' . $e->getMessage());
            return 'Ошибка сервиса. Пожалуйста, попробуйте позже.';
        }
    }
}
