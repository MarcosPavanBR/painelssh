<?php
declare(strict_types=1);
require_once __DIR__ . '/pages/system/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo '0'; exit; }
$usuario = trim((string)($_POST['username'] ?? ''));
$senha = (string)($_POST['password'] ?? '');
if ($usuario === '' || $senha === '') { echo '0'; exit; }
csrf_verify($_POST['_csrf'] ?? null);
echo validaUsuario($usuario, $senha, 'user') ? '1' : '0';
