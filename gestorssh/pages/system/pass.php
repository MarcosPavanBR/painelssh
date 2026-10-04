<?php
declare(strict_types=1);
// Legacy compatibility only. No database credential is stored here.
$pass = getenv('DB_PASSWORD') ?: '';
