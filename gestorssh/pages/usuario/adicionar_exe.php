<?php
declare(strict_types=1);
require_once __DIR__ . '/../system/seguranca.php';
require_once __DIR__ . '/../system/config.php';
protegePagina('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);

$login = trim((string)($_POST['login'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');
$nome = trim((string)($_POST['nome'] ?? ''));
$celular = trim((string)($_POST['celular'] ?? ''));

if (!preg_match('/^[A-Za-z0-9._-]{4,60}$/', $login) || strlen($senha) < 10 || strlen($senha) > 128 || $nome === '') {
    exit('<script>alert("Dados inválidos. A senha deve ter pelo menos 10 caracteres.");history.back();</script>');
}

$stmt = $conn->prepare('SELECT id_usuario FROM usuario WHERE login = :login LIMIT 1');
$stmt->execute([':login'=>$login]);
if ($stmt->fetch()) exit('<script>alert("Usuário já existe.");history.back();</script>');

$hash = password_hash($senha, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));
$owner = (int)$_SESSION['usuarioID'];
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare('INSERT INTO usuario (id_mestre, login, senha, data_cadastro, tipo, nome, celular, token_user) VALUES (:owner,:login,:senha,:data,:tipo,:nome,:celular,:token)');
$stmt->execute([
    ':owner'=>$owner, ':login'=>$login, ':senha'=>$hash, ':data'=>$now,
    ':tipo'=>'vpn', ':nome'=>$nome, ':celular'=>$celular, ':token'=>$token
]);
$id = (int)$conn->lastInsertId();
header('Location: ../../home.php?page=usuario/perfil&id_usuario='.$id, true, 303);
exit;
