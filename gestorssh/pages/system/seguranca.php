<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/config.php';

$_SG['conectaServidor'] = true;
$_SG['abreSessao'] = true;
$_SG['caseSensitive'] = true;
$_SG['validaSempre'] = true;
$_SG['paginaLogin'] = 'login.php';
$_SG['paginaBloquear'] = 'tela-bloqueada.php';

function my_Sql_regcase($str) { return preg_quote((string)$str, '/'); }

/**
 * Legacy compatibility function. It deliberately does not claim to make SQL safe.
 * New code MUST use prepared statements. Suspicious SQL metacharacters are rejected.
 */
function sql_injector($sql) {
    $sql = (string)$sql;
    if (preg_match('/(?:--|\/\*|\*\/|;|\bunion\s+select\b|\b(?:drop|alter|truncate)\s+table\b)/i', $sql)) {
        throw new InvalidArgumentException('Entrada rejeitada. Use parâmetros SQL.');
    }
    return trim(strip_tags($sql));
}
function anti_sql_injection($sql) { return sql_injector($sql); }

function pega_ip() { return client_ip(); }

function login_rate_limit(string $login): bool {
    $key = hash('sha256', client_ip() . '|' . strtolower(trim($login)));
    $file = sys_get_temp_dir() . '/painel-login-' . $key . '.json';
    $now = time();
    $data = ['count'=>0,'first'=>$now,'blocked_until'=>0];
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $tmp = json_decode((string)$raw, true);
        if (is_array($tmp)) $data = array_merge($data, $tmp);
    }
    if ($data['blocked_until'] > $now) return false;
    if ($now - $data['first'] > 900) $data = ['count'=>0,'first'=>$now,'blocked_until'=>0];
    $data['count']++;
    if ($data['count'] > 8) $data['blocked_until'] = $now + 900;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return $data['blocked_until'] <= $now;
}
function validaUsuario($usuario, $senha, $tipo): bool {
    global $conn;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
    if (!in_array($tipo, ['admin','user'], true)) return false;
    $usuario = trim((string)$usuario);
    $senha = (string)$senha;
    if ($usuario === '' || $senha === '' || strlen($usuario) > 100 || strlen($senha) > 512) return false;
    if (!login_rate_limit($usuario)) return false;

    $table = $tipo === 'admin' ? 'admin' : 'usuario';
    $idColumn = $tipo === 'admin' ? 'id_administrador' : 'id_usuario';
    $stmt = $conn->prepare("SELECT * FROM `{$table}` WHERE login = :login LIMIT 1");
    $stmt->execute([':login' => $usuario]);
    $resultado = $stmt->fetch();
    if (!$resultado || !isset($resultado['senha'])) return false;

    $stored = (string)$resultado['senha'];
    $valid = password_verify($senha, $stored);
    $legacy = false;

    // Legacy migration: admin historically used plaintext; usuario may also be plaintext.
    if (!$valid && !preg_match('/^\$(2y|2b|argon2id|argon2i)\$/', $stored) && hash_equals($stored, $senha)) {
        $valid = true;
        $legacy = true;
    }
    if (!$valid) return false;

    if ($legacy || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $up = $conn->prepare("UPDATE `{$table}` SET senha = :senha WHERE `{$idColumn}` = :id");
        $up->execute([':senha'=>$hash, ':id'=>(int)$resultado[$idColumn]]);
    }

    session_regenerate_id(true);
    $_SESSION['usuarioID'] = (int)$resultado[$idColumn];
    $_SESSION['usuarioNome'] = (string)($resultado['nome'] ?? '');
    $_SESSION['usuarioLogin'] = (string)$resultado['login'];
    $_SESSION['tipo'] = $tipo;
    unset($_SESSION['usuarioSenha']);
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    $_SESSION['auth_time'] = time();
    $_SESSION['last_activity'] = time();
    return true;
}

function protegePagina($tipo = null): void {
    if (empty($_SESSION['usuarioID']) || empty($_SESSION['usuarioLogin']) || empty($_SESSION['tipo'])) {
        expulsaVisitante();
    }
    if ($tipo !== null && $_SESSION['tipo'] !== $tipo) {
        http_response_code(403);
        exit('Acesso negado.');
    }
    if ((time() - (int)($_SESSION['last_activity'] ?? 0)) > 1800) {
        expulsaVisitante();
    }
    $_SESSION['last_activity'] = time();
}

function expulsaVisitante(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    header('Location: /index.php', true, 302);
    exit;
}
function expulsaSair(): void { expulsaVisitante(); }
