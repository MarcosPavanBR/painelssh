<?php
declare(strict_types=1);
require_once __DIR__ . '/pages/system/seguranca.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
csrf_verify($_POST['_csrf'] ?? null);
$email = trim((string)($_POST['email'] ?? ''));
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { header('Location: index.php?recovery=1',true,303); exit; }

// Generic response prevents account enumeration.
$stmt=$conn->prepare('SELECT id_usuario,login,email FROM usuario WHERE email=:email LIMIT 1');
$stmt->execute([':email'=>$email]);
$user=$stmt->fetch();
if($user){
    $conn->prepare('DELETE FROM password_reset_tokens WHERE usuario_id=:id OR expires_at < NOW()')->execute([':id'=>(int)$user['id_usuario']]);
    $raw=bin2hex(random_bytes(32));
    $hash=hash('sha256',$raw);
    $ins=$conn->prepare('INSERT INTO password_reset_tokens (usuario_id,token_hash,expires_at,created_at) VALUES (:id,:hash,DATE_ADD(NOW(),INTERVAL 30 MINUTE),NOW())');
    $ins->execute([':id'=>(int)$user['id_usuario'],':hash'=>$hash]);
    $base=rtrim(getenv('APP_URL')?:'','/');
    if($base!==''){
        $dest=$email; $destinatario='Suporte'; $assunto='Redefinição de acesso';
        $link=$base.'/reset.php?token='.rawurlencode($raw);
        $corpo='<p>Recebemos uma solicitação de redefinição de senha.</p><p><a href="'.htmlspecialchars($link,ENT_QUOTES,'UTF-8').'">Redefinir senha</a></p><p>O link expira em 30 minutos e pode ser usado uma única vez.</p>';
        $sucesso='Se o e-mail estiver cadastrado, enviaremos um link de redefinição.';
        @require __DIR__.'/recuperandoclasse.php';
    }
}
header('Location: index.php?recovery=sent',true,303); exit;
