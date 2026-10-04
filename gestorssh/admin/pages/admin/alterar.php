<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../pages/system/seguranca.php';
require_once __DIR__ . '/../../../pages/system/config.php';
protegePagina('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Método não permitido.');}
csrf_verify($_POST['_csrf']??null);
$nome=trim((string)($_POST['nome']??''));$email=trim((string)($_POST['email']??''));$site=trim((string)($_POST['site']??''));
if($nome===''||!filter_var($email,FILTER_VALIDATE_EMAIL))exit('Dados inválidos.');
$stmt=$conn->prepare('SELECT senha FROM admin WHERE id_administrador=:id LIMIT 1');$stmt->execute([':id'=>(int)$_SESSION['usuarioID']]);$admin=$stmt->fetch();if(!$admin)exit('Não permitido.');
$senhaat=(string)($_POST['senhaantiga']??'');$senhanew=(string)($_POST['novasenha']??'');
if($senhaat!==''){
 if(!password_verify($senhaat,(string)$admin['senha']))exit('Senha atual incorreta.');
 if($senhanew===''||strlen($senhanew)<12||strlen($senhanew)>128)exit('Nova senha inválida.');
 if($senhaat===$senhanew)exit('As senhas devem ser diferentes.');
 $stmt=$conn->prepare('UPDATE admin SET senha=:senha,nome=:nome,email=:email,site=:site WHERE id_administrador=:id');
 $stmt->execute([':senha'=>password_hash($senhanew,PASSWORD_DEFAULT),':nome'=>$nome,':email'=>$email,':site'=>$site,':id'=>(int)$_SESSION['usuarioID']]);
}else{
 $stmt=$conn->prepare('UPDATE admin SET nome=:nome,email=:email,site=:site WHERE id_administrador=:id');
 $stmt->execute([':nome'=>$nome,':email'=>$email,':site'=>$site,':id'=>(int)$_SESSION['usuarioID']]);
}
header('Location: ../../home.php?page=admin/dados',true,303);exit;
