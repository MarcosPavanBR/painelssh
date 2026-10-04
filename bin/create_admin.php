#!/usr/bin/env php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../gestorssh/config/bootstrap.php';

if (PHP_SAPI !== 'cli') exit("CLI only\n");
$login = $argv[1] ?? '';
$password = $argv[2] ?? '';
$name = $argv[3] ?? 'Administrador';
$email = $argv[4] ?? '';
if (!preg_match('/^[A-Za-z0-9._-]{3,60}$/',$login) || strlen($password)<12 || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR,"Uso: bin/create_admin.php LOGIN SENHA NOME EMAIL\nSenha mínima: 12 caracteres.\n"); exit(2);
}
$stmt=$conn->prepare('SELECT id_administrador FROM admin WHERE login=:login LIMIT 1');$stmt->execute([':login'=>$login]);
if($stmt->fetch()){fwrite(STDERR,"Login já existe.\n");exit(3);}
$stmt=$conn->prepare('INSERT INTO admin (login,senha,nome,email,celular,accessKEY,site,textocon,textorev) VALUES (:login,:senha,:nome,:email,:celular,NULL,:site,:textocon,:textorev)');
$stmt->execute([':login'=>$login,':senha'=>password_hash($password,PASSWORD_DEFAULT),':nome'=>$name,':email'=>$email,':celular'=>'',':site'=>getenv('APP_NAME')?:'SSH WEB',':textocon'=>'',':textorev'=>'']);
echo "Administrador criado com hash seguro.\n";
