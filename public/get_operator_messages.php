<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\HistoryService;

header('Content-Type: application/json; charset=utf-8');

session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'User not identified']);
    exit;
}

$operatorHistory = new HistoryService();
$messages = $operatorHistory->getHistory($userId);

echo json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
