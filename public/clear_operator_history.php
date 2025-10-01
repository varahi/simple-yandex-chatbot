<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\HistoryService;
use App\Services\SessionService;

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../config/config.php';
$history = new HistoryService($config);

// Берём userId из сессии
$userId = SessionService::getUserId();

$history->clearHistory($userId);

echo json_encode([
    'status' => 'ok',
    'message' => "История оператора для $userId очищена"
], JSON_UNESCAPED_UNICODE);