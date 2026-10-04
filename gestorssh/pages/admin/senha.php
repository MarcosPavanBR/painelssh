<?php
declare(strict_types=1);
require_once __DIR__ . '/../system/seguranca.php';
require_once __DIR__ . '/../system/config.php';
protegePagina('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);
$senha=(string)($_POST['senha'] ?? '');
if (strlen($senha)<10 || strlen($senha)>128) exit('Senha inválida.');
$stmt=$conn->prepare('UPDATE usuario SET senha=:senha WHERE id_usuario=:id');
$stmt->execute([':senha'=>password_hash($senha,PASSWORD_DEFAULT), ':id'=>(int)$_SESSION['usuarioID']]);
header('Location: ../../home.php?page=admin/dados',true,303); exit;
