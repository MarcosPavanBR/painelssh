<?php
declare(strict_types=1);
require_once __DIR__ . '/../security/upload.php';
try {
    require_admin_upload();
    save_uploaded_file_secure($_FILES['arquivo'] ?? [], ['image/png'=>'png'], __DIR__ . '/../app-assets/images/background', 'painel-ad.png', 10*1024*1024);
    header('Location: home.php?page=perso', true, 303); exit;
} catch (Throwable $e) { error_log('Background upload: '.$e->getMessage()); http_response_code(400); exit('Upload recusado.'); }
