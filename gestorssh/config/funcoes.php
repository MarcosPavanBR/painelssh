<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db_query(PDO $conn, string $sql, array $params = []): PDOStatement {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function isUser($uid): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT 1 FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return (bool)$stmt->fetchColumn();
}
function getNickById($uid) {
    global $conn;
    $stmt = $conn->prepare('SELECT nome FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return $stmt->fetchColumn() ?: null;
}
function getLoginById($uid) {
    global $conn;
    $stmt = $conn->prepare('SELECT login FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return $stmt->fetchColumn() ?: null;
}
function getIdByNick($nick) {
    global $conn;
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE login = :login LIMIT 1');
    $stmt->execute([':login' => (string)$nick]);
    return $stmt->fetchColumn() ?: null;
}
function getFolderById($uid) {
    global $conn;
    $stmt = $conn->prepare('SELECT pasta_att FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return $stmt->fetchColumn() ?: null;
}
function getBan($uid): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT banido FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return ((int)$stmt->fetchColumn()) !== 0;
}
function getData($valor, $uid) {
    global $conn;
    $allowed = ['nome','login','email','celular','ativo','validade','pasta_att','banido','nivel'];
    if (!in_array($valor, $allowed, true)) {
        throw new InvalidArgumentException('Campo não permitido.');
    }
    $stmt = $conn->prepare("SELECT `{$valor}` FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$uid]);
    return $stmt->fetchColumn();
}
function getIdBySid($sid) {
    global $conn;
    if (!$sid) return null;
    $stmt = $conn->prepare('SELECT uid FROM sessao WHERE id = :sid AND expira >= :now LIMIT 1');
    $stmt->execute([':sid' => (string)$sid, ':now' => time()]);
    return $stmt->fetchColumn() ?: null;
}
function getSxtm() {
    global $conn;
    $stmt = $conn->prepare("SELECT valor FROM configs WHERE nome = 'sesexp' LIMIT 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}
function getLogged($sid): bool {
    global $conn;
    $conn->prepare('DELETE FROM sessao WHERE expira < :now')->execute([':now' => time()]);
    $stmt = $conn->prepare('SELECT uid FROM sessao WHERE id = :sid AND expira >= :now LIMIT 1');
    $stmt->execute([':sid' => (string)$sid, ':now' => time()]);
    $uid = $stmt->fetchColumn();
    if (!$uid || !getUser($uid)) return false;
    $conn->prepare('UPDATE sessao SET expira = :exp WHERE id = :sid')->execute([':exp' => time()+2000, ':sid' => (string)$sid]);
    return true;
}
function getAdm($uid): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT nivel FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    $nivel = $stmt->fetchColumn();
    return ((string)$nivel === '2' || (int)$uid === 1);
}
function getOwner($uid): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT nivel FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    $nivel = $stmt->fetchColumn();
    return ((string)$nivel === '3' || (int)$uid === 1);
}
function getUser($uid): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT 1 FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$uid]);
    return (bool)$stmt->fetchColumn();
}
if (!function_exists('getConfig')) {
    function getConfig($name) {
        global $conn;
        $stmt = $conn->prepare('SELECT valor FROM configs WHERE nome = :nome LIMIT 1');
        $stmt->execute([':nome' => (string)$name]);
        return $stmt->fetchColumn();
    }
}
function addVersion($user): void {
    global $conn;
    $stmt = $conn->prepare('UPDATE configuracoes SET versao = COALESCE(versao,0)+1 WHERE id_owner = :id');
    $stmt->execute([':id' => (int)$user]);
}
function addSms($user): void {
    global $conn;
    $stmt = $conn->prepare('UPDATE mensagens SET att = COALESCE(att,0)+1 WHERE id_owner = :id');
    $stmt->execute([':id' => (int)$user]);
}
function getConfigUser($valor, $user) {
    global $conn;
    $allowed = ['versao','site','logo','tema','email','celular','idcliente_mp','tokensecret_mp','dadosdeposito','atualiza_dados'];
    if (!in_array($valor, $allowed, true)) throw new InvalidArgumentException('Campo não permitido.');
    $stmt = $conn->prepare("SELECT `{$valor}` FROM configuracoes WHERE id_owner = :id LIMIT 1");
    $stmt->execute([':id' => (int)$user]);
    return $stmt->fetchColumn();
}
function isLogged($sid): void {
    if (!getLogged($sid)) {
        header('Location: /', true, 302);
        exit;
    }
}
function download($arquivo): void {
    $real = realpath($arquivo);
    $base = realpath(__DIR__ . '/../');
    if (!$real || !$base || !str_starts_with($real, $base . DIRECTORY_SEPARATOR) || !is_file($real)) {
        http_response_code(404); exit('Arquivo não encontrado.');
    }
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($real));
    header('Content-Disposition: attachment; filename="' . basename($real) . '"');
    header('Cache-Control: no-store');
    readfile($real);
    exit;
}
function delTree($dir): bool {
    $real = realpath($dir);
    if (!$real || !is_dir($real)) return false;
    $base = realpath(__DIR__ . '/../');
    if (!$base || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Exclusão fora da área permitida.');
    }
    foreach (array_diff(scandir($real), ['.','..']) as $file) {
        $path = $real . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? delTree($path) : unlink($path);
    }
    return rmdir($real);
}
