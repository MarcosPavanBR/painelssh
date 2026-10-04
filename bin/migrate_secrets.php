#!/usr/bin/env php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../gestorssh/config/bootstrap.php';
require_once __DIR__ . '/../gestorssh/security/secrets.php';
if(PHP_SAPI!=='cli') exit("CLI only\n");
$conn->beginTransaction();
try {
    $count=0;
    foreach ($conn->query("SELECT id_servidor,senha FROM servidor") as $row) {
        if (!str_starts_with((string)$row['senha'],'v1:')) {
            $st=$conn->prepare('UPDATE servidor SET senha=:senha WHERE id_servidor=:id');
            $st->execute([':senha'=>encrypt_secret((string)$row['senha']),':id'=>(int)$row['id_servidor']]);$count++;
        }
    }
    foreach ($conn->query("SELECT id_usuario_ssh,senha FROM usuario_ssh") as $row) {
        if (!str_starts_with((string)$row['senha'],'v1:')) {
            $st=$conn->prepare('UPDATE usuario_ssh SET senha=:senha WHERE id_usuario_ssh=:id');
            $st->execute([':senha'=>encrypt_secret((string)$row['senha']),':id'=>(int)$row['id_usuario_ssh']]);$count++;
        }
    }
    $conn->commit();
    echo "Segredos SSH migrados: {$count}\n";
} catch(Throwable $e) { $conn->rollBack(); fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
