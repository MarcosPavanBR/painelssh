<?php
declare(strict_types=1);
require_once($_SERVER['DOCUMENT_ROOT'] . "/config/funcoes.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_login'])) {
    csrf_verify($_POST['_csrf'] ?? null);
    $login = trim((string)($_POST['login'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    if ($login === '' || $senha === '' || strlen($login) > 100 || strlen($senha) > 512) { $_SESSION['erro'] = '<center><div class="alert alert-danger">Usuário ou senha incorretos</div></center>'; return; }
    $stmt = $conn->prepare('SELECT * FROM usuarios WHERE login=:login LIMIT 1');
    $stmt->execute([':login'=>$login]);
    $row = $stmt->fetch();
    $valid = false; $legacy = false;
    if ($row) {
        $stored=(string)($row['senha'] ?? '');
        $valid=password_verify($senha,$stored);
        if (!$valid && preg_match('/^[a-f0-9]{32}$/i',$stored) && hash_equals($stored,md5($senha))) { $valid=true; $legacy=true; }
        if ($valid && $legacy) { $up=$conn->prepare('UPDATE usuarios SET senha=:senha WHERE id=:id'); $up->execute([':senha'=>password_hash($senha,PASSWORD_DEFAULT),':id'=>(int)$row['id']]); }
    }
    if (!$valid) { $_SESSION['erro'] = '<center><div class="alert alert-danger">Usuário ou senha incorretos</div></center>'; return; }
    $uid=(int)$row['id'];
    $conn->prepare('DELETE FROM sessao WHERE uid=:uid')->execute([':uid'=>$uid]);
    session_regenerate_id(true);
    $xtm=time()+1800; $sid=bin2hex(random_bytes(32));
    $conn->prepare('INSERT INTO sessao (id,uid,expira) VALUES (:id,:uid,:expira)')->execute([':id'=>$sid,':uid'=>$uid,':expira'=>$xtm]);
    $_SESSION['logado']=$sid; $_SESSION['_csrf']=bin2hex(random_bytes(32)); $_SESSION['auth_time']=time(); $_SESSION['last_activity']=time();
    header('Location: /conecta4g/home.php', true, 303); exit;
}
