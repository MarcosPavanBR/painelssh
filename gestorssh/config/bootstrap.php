<?php
declare(strict_types=1);

// Central security bootstrap for Painel Web V2.
if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    exit('PHP 8.1+ é obrigatório.');
}

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo');

$debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL);
define('DEBUG', $debug);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_trans_sid', '0');

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; object-src 'none'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; connect-src 'self' https:; font-src 'self' https: data:;");
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        filter_var(getenv('FORCE_SECURE_COOKIES') ?: '1', FILTER_VALIDATE_BOOL);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!function_exists('env_required')) {
    function env_required(string $name): string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new RuntimeException("Variável de ambiente obrigatória ausente: {$name}");
        }
        return $value;
    }
}

if (!function_exists('env_optional')) {
    function env_optional(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        return ($value === false || $value === '') ? $default : $value;
    }
}

if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof PDO)) {
    $dbHost = env_optional('DB_HOST', '127.0.0.1');
    $dbPort = env_optional('DB_PORT', '3306');
    $dbName = env_optional('DB_NAME', 'sshplus');
    $dbUser = env_required('DB_USER');
    $dbPass = env_required('DB_PASSWORD');

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    try {
        $GLOBALS['conn'] = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(503);
        exit('Serviço temporariamente indisponível.');
    }
}

$conn = $GLOBALS['conn'];

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function csrf_verify(?string $token): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return;
    }
    if (!$token || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(419);
        exit('Requisição inválida.');
    }
}

function client_ip(): string
{
    // Never trust X-Forwarded-For unless a trusted reverse proxy is configured.
    return filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

function enforce_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify($_POST['_csrf'] ?? null);
    }
}
