<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

return [
    'yandex' => [
        'iam_token' => $_ENV['YANDEX_IAM_TOKEN'],
        'folder_id' => $_ENV['YANDEX_FOLDER_ID'],
        'model_uri' => $_ENV['YANDEX_MODEL_URI'],
        'api_url' => 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion'
    ],
    'topics' => [
        'allowed' => explode(',', $_ENV['ALLOWED_TOPICS']),
        'forbidden' => explode(',', $_ENV['FORBIDDEN_WORDS'])
    ]
];
