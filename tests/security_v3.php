<?php
declare(strict_types=1);

function assert_true(bool $ok, string $msg): void { if (!$ok) { fwrite(STDERR, "FAIL: $msg\n"); exit(1); } echo "PASS: $msg\n"; }
$root=dirname(__DIR__);
$routerUser=file_get_contents($root.'/gestorssh/home.php');
$routerAdmin=file_get_contents($root.'/gestorssh/admin/home.php');
assert_true(str_contains($routerUser,'$allowedPages') && str_contains($routerUser,'isset($allowedPages[$requestedPage])'), 'roteador do usuário usa allowlist');
assert_true(str_contains($routerAdmin,'$allowedPages') && str_contains($routerAdmin,'isset($allowedPages[$requestedPage])'), 'roteador admin usa allowlist');
$login=file_get_contents($root.'/gestorssh/conecta4g/login.php');
assert_true(str_contains($login,'password_verify') && !str_contains($login,'md5($senha);'), 'login Conecta4G não usa MD5 como armazenamento');
assert_true(str_contains($login,'session_regenerate_id(true)'), 'login Conecta4G regenera sessão');
$edit=file_get_contents($root.'/gestorssh/admin/pages/usuario/editar_exe.php');
assert_true(str_contains($edit,'password_hash') && str_contains($edit,'csrf_verify'), 'edição admin usa hash e CSRF');
foreach (['123.sh','ubuinst.sh','ubuinst1.sh','verifatt.sh','whatsapp.sh'] as $f) assert_true(!is_file($root.'/'.$f), "$f removido da raiz executável");
echo "PASS: checks de segurança V3 concluídos\n";
