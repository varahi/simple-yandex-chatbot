<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\SessionService;

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'userId' => SessionService::getUserId()
], JSON_UNESCAPED_UNICODE);