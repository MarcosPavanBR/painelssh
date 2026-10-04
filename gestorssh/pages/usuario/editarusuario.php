<?php
declare(strict_types=1);
require_once __DIR__ . '/../system/seguranca.php';
require_once __DIR__ . '/../system/config.php';
protegePagina('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);
$id=filter_var($_POST['id_usuario'] ?? null,FILTER_VALIDATE_INT);
$login=trim((string)($_POST['login'] ?? ''));
$senha=(string)($_POST['senha'] ?? '');
$email=trim((string)($_POST['email'] ?? ''));
$celular=trim((string)($_POST['celular'] ?? ''));
$diretorio=(string)($_POST['diretorio'] ?? '../../home.php');
if (!$id || !preg_match('/^[A-Za-z0-9._-]{4,60}$/',$login) || !filter_var($email,FILTER_VALIDATE_EMAIL) || ($senha!=='' && strlen($senha)<10)) exit('Dados inválidos.');
$stmt=$conn->prepare('SELECT id_usuario FROM usuario WHERE id_usuario=:id AND id_mestre=:owner LIMIT 1');
$stmt->execute([':id'=>$id, ':owner'=>(int)$_SESSION['usuarioID']]);
if (!$stmt->fetch()) { http_response_code(403); exit('Sem permissão.'); }
if ($senha!=='') {
  $stmt=$conn->prepare('UPDATE usuario SET login=:login,senha=:senha,email=:email,celular=:celular,permitir_demo=:demo WHERE id_usuario=:id');
  $stmt->execute([':login'=>$login,':senha'=>password_hash($senha,PASSWORD_DEFAULT),':email'=>$email,':celular'=>$celular,':demo'=>(int)($_POST['acesso']??0),':id'=>$id]);
} else {
  $stmt=$conn->prepare('UPDATE usuario SET login=:login,email=:email,celular=:celular,permitir_demo=:demo WHERE id_usuario=:id');
  $stmt->execute([':login'=>$login,':email'=>$email,':celular'=>$celular,':demo'=>(int)($_POST['acesso']??0),':id'=>$id]);
}
header('Location: '.filter_var($diretorio,FILTER_SANITIZE_URL),true,303); exit;
