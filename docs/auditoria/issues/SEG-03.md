# SEG-03 — Login Conecta4G migra de MD5 para password_verify e regenera sessão

**Severidade:** alta  
**Status:** confirmado

## Evidência
`gestorssh/conecta4g/login.php:11-31`

```text
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
```

SHA-256: `ebc5c92d44dc2bb86199c1de5c7732f601cf065f2954f22007396ea94b0c6f3d`

## Descrição
O login usa prepared statement, password_verify, migração de MD5 legado após sucesso e session_regenerate_id(true).

## Correção
Hash moderno, migração única, ID aleatório e regeneração.
