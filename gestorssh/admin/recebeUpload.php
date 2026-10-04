<?php
declare(strict_types=1);
require_once __DIR__ . '/../security/upload.php';
try {
    require_admin_upload();
    save_uploaded_file_secure($_FILES['arquivo'] ?? [], [
        'application/vnd.android.package-archive'=>'apk',
        'application/octet-stream'=>'apk',
    ], __DIR__ . '/../apps/apk', 'app_s1.apk', 100*1024*1024);
    header('Location: home.php?page=apps', true, 303); exit;
} catch (Throwable $e) {
    error_log('APK upload: '.$e->getMessage());
    http_response_code(400); exit('Upload recusado.');
}
