<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (basename($_SERVER['SCRIPT_NAME'] ?? '') === basename(__FILE__)) {
    header('Location: /conecta4g/index.php', true, 302);
    exit;
}

define('NOME_DB', getenv('DB_NAME') ?: 'sshplus');
define('NOME_SERVER_DB', getenv('DB_HOST') ?: '127.0.0.1');
define('USUARIO_DB', getenv('DB_USER') ?: '');
// Intentionally no password constant. Secrets must never live in source code.
define('SENHA_DB', '');

$sid = $_SESSION['logado'] ?? '';
$acao = $_GET['acao'] ?? '';
$uid = function_exists('getIdBySid') ? getIdBySid($sid) : null;

$detectPath = __DIR__ . '/Mobile_Detect.php';
if (is_file($detectPath)) {
    require_once $detectPath;
    $detect = new Mobile_Detect();
}

require_once __DIR__ . '/conteudo.php';
