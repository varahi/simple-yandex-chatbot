<?php
// test.php в корне проекта
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Проверьте синтаксис ErrorHandler
require_once __DIR__ . '../src/Handlers/ErrorHandler.php';

echo "ErrorHandler загружен успешно";