<?php

// public/telegram_webhook.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\OperatorService;
use App\Services\HistoryService;

$config = require __DIR__ . '/../config/config.php';
$history = new HistoryService($config);


$update = json_decode(file_get_contents("php://input"), true);

// Логируем для отладки
file_put_contents(__DIR__.'/telegram_log.txt', print_r($update,true), FILE_APPEND);

// Достаём данные
if (!empty($update['message'])) {
    $operatorService = new OperatorService($history);
    $operatorService->processOperatorMessage($update['message']);

    http_response_code(200);
    echo 'OK';
}