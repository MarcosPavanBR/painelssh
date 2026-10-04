<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: '';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'sshplus';
if ($dbUser === '' || $dbPass === '') {
    http_response_code(503);
    exit('Banco não configurado.');
}
$mysqli = mysqli_init();
mysqli_real_connect($mysqli, $dbHost, $dbUser, $dbPass, $dbName, (int)(getenv('DB_PORT') ?: 3306));
if (!$mysqli) {
    error_log('MySQLi connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    exit('Banco indisponível.');
}
mysqli_set_charset($mysqli, 'utf8mb4');
