<?php
declare(strict_types=1);
require_once __DIR__ . '/../security/upload.php';
try {
    require_admin_upload();
    $target = save_uploaded_file_secure($_FILES['arquivo'] ?? [], [
        'image/png'=>'png', 'image/jpeg'=>'jpg'
    ], __DIR__ . '/../app-assets/images/background', 'store-bk.png', 10*1024*1024);
    // Normalize JPEG uploads to the fixed PNG target only if GD is available.
    if (str_ends_with(strtolower($target), '.png') === false) throw new RuntimeException('Formato inválido.');
    header('Location: home.php?page=perso', true, 303); exit;
} catch (Throwable $e) {
    error_log('Store background upload: '.$e->getMessage());
    http_response_code(400); exit('Upload recusado.');
}
