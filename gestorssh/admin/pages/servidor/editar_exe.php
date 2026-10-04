<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../pages/system/seguranca.php';
require_once __DIR__ . '/../../../pages/system/config.php';
require_once __DIR__ . '/../../../security/secrets.php';
protegePagina('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);
$id=filter_var($_POST['id_servidor'] ?? null,FILTER_VALIDATE_INT);
$nome=trim((string)($_POST['nomesrv']??''));
$ip=filter_var($_POST['ip']??'',FILTER_VALIDATE_IP);
$login=trim((string)($_POST['login']??''));
$senha=(string)($_POST['senha']??'');
if (!$id||!$ip||$nome===''||$login===''||$senha==='') exit('Dados inválidos.');
$validade=filter_var($_POST['validade']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>3650]]);
$limite=filter_var($_POST['limite']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>100000]]);
if ($validade===false||$limite===false) exit('Dados inválidos.');
$stmt=$conn->prepare('SELECT id_servidor FROM servidor WHERE id_servidor=:id LIMIT 1');$stmt->execute([':id'=>$id]);if(!$stmt->fetch()){http_response_code(404);exit('Servidor não encontrado.');}
$stmt=$conn->prepare('UPDATE servidor SET nome=:nome,ip_servidor=:ip,login_server=:login,senha=:senha,site_servidor=:site,localizacao=:loc,localizacao_img=:img,validade=:validade,limite=:limite WHERE id_servidor=:id');
$stmt->execute([':nome'=>$nome,':ip'=>$ip,':login'=>$login,':senha'=>encrypt_secret($senha),':site'=>trim((string)($_POST['siteserver']??'')),':loc'=>trim((string)($_POST['localiza']??'')),':img'=>trim((string)($_POST['localiza_ico']??'')),':validade'=>$validade,':limite'=>$limite,':id'=>$id]);
header('Location: ../../home.php?page=servidor/servidor&id_servidor='.$id,true,303);exit;
