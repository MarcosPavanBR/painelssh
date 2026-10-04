<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../pages/system/seguranca.php';
require_once __DIR__ . '/../../../pages/system/config.php';
protegePagina('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Método não permitido.');}
csrf_verify($_POST['_csrf']??null);
$senha=(string)($_POST['senha']??'');
if(strlen($senha)<12||strlen($senha)>128)exit('Senha inválida.');
$stmt=$conn->prepare('UPDATE admin SET senha=:senha WHERE id_administrador=:id');
$stmt->execute([':senha'=>password_hash($senha,PASSWORD_DEFAULT),':id'=>(int)$_SESSION['usuarioID']]);
header('Location: ../../home.php?page=admin/dados',true,303);exit;
