<?php
declare(strict_types=1);
require_once __DIR__ . '/../system/seguranca.php';
require_once __DIR__ . '/../system/config.php';
protegePagina('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);

$id = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
$login = trim((string)($_POST['login'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');
$nome = trim((string)($_POST['nome'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$celular = trim((string)($_POST['celular'] ?? ''));
if (!$id || !preg_match('/^[A-Za-z0-9._-]{4,60}$/', $login) || $nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) exit('Dados inválidos.');
if ($senha !== '' && (strlen($senha) < 10 || strlen($senha) > 128)) exit('Senha inválida.');

$stmt=$conn->prepare('SELECT id_usuario FROM usuario WHERE id_usuario=:id AND id_mestre=:owner LIMIT 1');
$stmt->execute([':id'=>$id, ':owner'=>(int)$_SESSION['usuarioID']]);
if (!$stmt->fetch()) { http_response_code(403); exit('Sem permissão.'); }

if ($senha !== '') {
    $stmt=$conn->prepare('UPDATE usuario SET login=:login, senha=:senha, nome=:nome, email=:email, celular=:celular WHERE id_usuario=:id');
    $stmt->execute([':login'=>$login, ':senha'=>password_hash($senha,PASSWORD_DEFAULT), ':nome'=>$nome, ':email'=>$email, ':celular'=>$celular, ':id'=>$id]);
} else {
    $stmt=$conn->prepare('UPDATE usuario SET login=:login, nome=:nome, email=:email, celular=:celular WHERE id_usuario=:id');
    $stmt->execute([':login'=>$login, ':nome'=>$nome, ':email'=>$email, ':celular'=>$celular, ':id'=>$id]);
}
header('Location: ../../home.php?page=usuario/perfil&id_usuario='.$id, true, 303); exit;
