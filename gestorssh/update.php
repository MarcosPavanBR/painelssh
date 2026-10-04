<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/pages/system/seguranca.php';

header('Content-Type: application/json; charset=utf-8');

function cfg(string $name): ?string {
    global $conn;
    $stmt=$conn->prepare('SELECT valor FROM configs WHERE nome=:nome LIMIT 1');
    $stmt->execute([':nome'=>$name]);
    $v=$stmt->fetchColumn(); return $v===false?null:(string)$v;
}

$servidores=[];
// Deliberately omit USER/PASS from this public configuration endpoint.
$stmt=$conn->query('SELECT Name, TYPE, FLAG, ServerIP, CheckUser, ServerPort, SSLPort FROM servidores');
while($row=$stmt->fetch(PDO::FETCH_ASSOC)) $servidores[]=$row;
$payloads=[];
$stmt=$conn->query('SELECT Name, FLAG, Payload, SNI, TlsIP, ProxyIP, ProxyPort, Info FROM payloads');
while($row=$stmt->fetch(PDO::FETCH_ASSOC)) $payloads[]=$row;
$portas=[];
$stmt=$conn->query('SELECT Porta FROM portas');
while($row=$stmt->fetch(PDO::FETCH_ASSOC)) $portas[]=$row;

$dados=[
 'Version'=>cfg('versao'),'ReleaseNotes'=>cfg('notas'),'Sms'=>cfg('sms'),
 'UrlUpdate'=>cfg('update'),'EmailFeedback'=>cfg('email'),'UrlContato'=>cfg('contato'),
 'UrlTermos'=>cfg('termos'),'CheckUser'=>cfg('checkuser'),'Udp'=>$portas,
 'Servers'=>$servidores,'Networks'=>$payloads,
];

if(isset($_GET['download'])){
    protegePagina('admin');
    $json=json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
    header('Content-Disposition: attachment; filename="config.json"');
    header('Content-Type: application/json');
    echo $json; exit;
}

echo json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
