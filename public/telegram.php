<?php
// public/telegram.php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\HistoryService;
use App\Services\TelegramService;

// Подгрузи конфиг с токеном и chat_id оператора
$config = require __DIR__ . '/../config/config.php';

$history = new HistoryService($config['history_file'] ?? __DIR__ . '/../storage/history.json', $config['max_history'] ?? 5);
$tg = new TelegramService($config['telegram_token'], $config['operator_chat_id'], $history);

$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $tg->processUpdate($input);
}

http_response_code(200);
echo 'ok';