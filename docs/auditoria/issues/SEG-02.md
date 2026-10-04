# SEG-02 — Edição administrativa de usuário usa CSRF e hash de senha

**Severidade:** alta  
**Status:** confirmado

## Evidência
`gestorssh/admin/pages/usuario/editar_exe.php:80-95`

```text
csrf_verify($_POST['_csrf'] ?? null);

$id = filter_var($_POST['idusuario'] ?? null, FILTER_VALIDATE_INT);
$nome = trim((string)($_POST['nome'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$celular = trim((string)($_POST['celular'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');
$acesso = (string)($_POST['acesso'] ?? '0');
if (!$id || $nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($nome) > 100 || strlen($celular) > 30 || !in_array($acesso, ['0','1','2'], true)) {
    http_response_code(422); exit('Dados inválidos.');
}
if ($senha !== '' && (strlen($senha) < 10 || strlen($senha) > 128)) { http_response_code(422); exit('Senha inválida.'); }

$check = $conn->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = :id LIMIT 1');
$check->execute([':id' => $id]);
if (!$check->fetch()) { http_response_code(404); exit('Usuário não encontrado.'); }
```

SHA-256: `f828c33b59520e8229e71af6ce1308ea655b1a79dce4ab5c95574d46cd5c0198`

## Descrição
O endpoint administrativo valida POST/CSRF e grava nova senha com password_hash().

## Correção
Prepared statements, password_hash e CSRF.
