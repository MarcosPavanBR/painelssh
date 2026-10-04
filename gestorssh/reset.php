<?php
declare(strict_types=1);
require_once __DIR__ . '/pages/system/seguranca.php';
$token=trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
if(!preg_match('/^[a-f0-9]{64}$/',$token)){http_response_code(400);exit('Token inválido.');}
$hash=hash('sha256',$token);
$stmt=$conn->prepare('SELECT id,usuario_id FROM password_reset_tokens WHERE token_hash=:hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
$stmt->execute([':hash'=>$hash]);$row=$stmt->fetch();
if(!$row){http_response_code(400);exit('Token expirado ou inválido.');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify($_POST['_csrf'] ?? null);
    $senha=(string)($_POST['senha']??'');
    if(strlen($senha)<12){http_response_code(422);exit('A senha deve ter pelo menos 12 caracteres.');}
    $conn->beginTransaction();
    try{
        $up=$conn->prepare('UPDATE usuario SET senha=:senha WHERE id_usuario=:id');$up->execute([':senha'=>password_hash($senha,PASSWORD_DEFAULT),':id'=>(int)$row['usuario_id']]);
        $conn->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE id=:id')->execute([':id'=>(int)$row['id']]);
        $conn->commit();
        header('Location: login.php?reset=ok',true,303);exit;
    }catch(Throwable $e){$conn->rollBack();error_log($e->getMessage());http_response_code(500);exit('Não foi possível redefinir a senha.');}
}
?>
<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Redefinir senha</title></head><body>
<main style="max-width:420px;margin:60px auto;font-family:Arial"><h1>Redefinir senha</h1><form method="post">
<?php echo csrf_field(); ?><input type="hidden" name="token" value="<?php echo h($token); ?>">
<label>Nova senha</label><input type="password" name="senha" minlength="12" maxlength="128" autocomplete="new-password" required style="display:block;width:100%;padding:10px;margin:8px 0 16px">
<button type="submit">Salvar nova senha</button></form></main></body></html>
