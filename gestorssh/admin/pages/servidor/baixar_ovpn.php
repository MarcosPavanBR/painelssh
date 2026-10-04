<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../pages/system/seguranca.php';
require_once __DIR__ . '/../../../pages/system/config.php';
protegePagina('admin');

$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
if(!$id){http_response_code(400);exit('ID inválido.');}
$stmt=$conn->prepare('SELECT arquivo FROM ovpn WHERE id=:id LIMIT 1');$stmt->execute([':id'=>$id]);$row=$stmt->fetch();
if(!$row){http_response_code(404);exit('Arquivo não encontrado.');}
$file=basename((string)$row['arquivo']);
$base=realpath(__DIR__.'/../ovpn');
$local=$base ? realpath($base.'/'.$file) : false;
if(!$base||!$local||!is_file($local)||!str_starts_with($local,$base.DIRECTORY_SEPARATOR)){http_response_code(404);exit('Arquivo não encontrado.');}
header('Cache-Control: private, no-store');header('Content-Type: application/octet-stream');header('Content-Length: '.filesize($local));header('Content-Disposition: attachment; filename="'.basename($local).'"');readfile($local);exit;
