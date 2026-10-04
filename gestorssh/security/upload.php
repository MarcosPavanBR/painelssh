<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../pages/system/seguranca.php';

function require_admin_upload(): void {
    protegePagina('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
    csrf_verify($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null));
}
function save_uploaded_file_secure(array $file, array $mimeExt, string $destDir, string $fixedName, int $maxBytes): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload inválido.');
    if (($file['size'] ?? 0) < 1 || $file['size'] > $maxBytes) throw new RuntimeException('Arquivo excede o limite.');
    if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Origem do upload inválida.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    if (!isset($mimeExt[$mime])) throw new RuntimeException('Tipo de arquivo não permitido.');
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($ext !== $mimeExt[$mime]) throw new RuntimeException('Extensão incompatível com o conteúdo.');
    if (!is_dir($destDir) && !mkdir($destDir, 0750, true) && !is_dir($destDir)) throw new RuntimeException('Diretório indisponível.');
    $tmp = $destDir . '/.' . bin2hex(random_bytes(12)) . '.upload';
    if (!move_uploaded_file($file['tmp_name'], $tmp)) throw new RuntimeException('Falha ao salvar upload.');
    chmod($tmp, 0640);
    $target = $destDir . '/' . $fixedName;
    if (!rename($tmp, $target)) { @unlink($tmp); throw new RuntimeException('Falha ao finalizar upload.'); }
    chmod($target, 0640);
    return $target;
}
