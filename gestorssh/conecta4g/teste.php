<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../pages/system/seguranca.php';
protegePagina('admin');
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_verify($_POST['_csrf']??null);
 $items=json_decode((string)($_POST['texto']??''),true);
 if(!is_array($items)) exit('JSON inválido.');
 $stmt=$conn->prepare('INSERT INTO testes (Name,FLAG,Payload,SNI,TlsIP,ProxyIP,ProxyPort,Info) VALUES (:name,:flag,:payload,:sni,:tlsip,:proxyip,:proxyport,:info)');
 $conn->beginTransaction();
 try{foreach($items as $obj){if(!is_array($obj)) continue;$stmt->execute([':name'=>(string)($obj['Name']??''),':flag'=>(string)($obj['FLAG']??''),':payload'=>(string)($obj['Payload']??''),':sni'=>(string)($obj['SNI']??''),':tlsip'=>(string)($obj['TlsIP']??''),':proxyip'=>(string)($obj['ProxyIP']??''),':proxyport'=>(int)($obj['ProxyPort']??0),':info'=>(string)($obj['Info']??'')]);}$conn->commit();echo 'OK';}catch(Throwable $e){$conn->rollBack();http_response_code(400);echo 'Falha';}
}
?><form method="post"><?php echo csrf_field(); ?><textarea name="texto" cols="80" rows="20"></textarea><br><button type="submit">Adicionar</button></form>
