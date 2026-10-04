<?php
declare(strict_types=1);
require_once __DIR__ . '/../system/seguranca.php';
require_once __DIR__ . '/../system/config.php';
protegePagina('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);
$id=filter_var($_POST['id_usuario'] ?? null,FILTER_VALIDATE_INT);
if(!$id){http_response_code(422);exit('ID inválido.');}
$owner=(int)$_SESSION['usuarioID'];
$stmt=$conn->prepare('SELECT id_usuario FROM usuario WHERE id_usuario=:id AND id_mestre=:owner LIMIT 1');
$stmt->execute([':id'=>$id,':owner'=>$owner]);
if(!$stmt->fetch()){http_response_code(403);exit('Sem permissão.');}
$ssh=$conn->prepare('SELECT id_usuario_ssh FROM usuario_ssh WHERE id_usuario=:id'); $ssh->execute([':id'=>$id]);
if($ssh->fetch()){http_response_code(409);exit('Usuário possui contas SSH ativas; use o fluxo de remoção controlada.');}
$conn->prepare('DELETE FROM usuario WHERE id_usuario=:id AND id_mestre=:owner')->execute([':id'=>$id,':owner'=>$owner]);
header('Location: ../../home.php?page=usuario/listar',true,303); exit;
