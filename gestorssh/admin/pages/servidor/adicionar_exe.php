<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../pages/system/seguranca.php';
require_once __DIR__ . '/../../../pages/system/config.php';
require_once __DIR__ . '/../../../pages/system/classe.ssh.php';
require_once __DIR__ . '/../../../security/secrets.php';
protegePagina('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Método não permitido.');}
csrf_verify($_POST['_csrf']??null);

$nome=trim((string)($_POST['nomesrv']??''));$ip=filter_var($_POST['ip']??'',FILTER_VALIDATE_IP);$login=trim((string)($_POST['login']??''));$senha=(string)($_POST['senha']??'');
$validade=filter_var($_POST['validade']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>3650]]);
$limite=filter_var($_POST['limite']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>100000]]);
$regiaoMap=[1=>'asia',2=>'america',3=>'europa',4=>'australia'];$regiao=$regiaoMap[(int)($_POST['regiao']??0)]??null;
if($nome===''||!$ip||$login===''||$senha===''||$validade===false||$limite===false||!$regiao){exit('Dados inválidos.');}
if(!preg_match('/^[A-Za-z0-9._@-]{1,64}$/',$login))exit('Login SSH inválido.');
$stmt=$conn->prepare('SELECT id_servidor FROM servidor WHERE ip_servidor=:ip LIMIT 1');$stmt->execute([':ip'=>$ip]);if($stmt->fetch())exit('Já existe um servidor com esse IP.');
try{
  $ssh=new SSH2($ip,(int)($_POST['porta']??22));
  if(!$ssh->auth($login,$senha))exit('Não foi possível autenticar no servidor.');
  $stmt=$conn->prepare('INSERT INTO servidor (ip_servidor,nome,login_server,senha,site_servidor,localizacao,localizacao_img,validade,limite,tipo,regiao) VALUES (:ip,:nome,:login,:senha,:site,:loc,:img,:validade,:limite,:tipo,:regiao)');
  $stmt->execute([':ip'=>$ip,':nome'=>$nome,':login'=>$login,':senha'=>encrypt_secret($senha),':site'=>trim((string)($_POST['siteserver']??'')),':loc'=>trim((string)($_POST['localiza']??'')),':img'=>trim((string)($_POST['localiza_ico']??'')),':validade'=>$validade,':limite'=>$limite,':tipo'=>'premium',':regiao'=>$regiao]);
  $id=(int)$conn->lastInsertId();
  header('Location: ../../home.php?page=servidor/servidor&id_servidor='.$id,true,303);exit;
}catch(Throwable $e){error_log('Server registration: '.$e->getMessage());http_response_code(400);exit('Não foi possível cadastrar o servidor.');}
