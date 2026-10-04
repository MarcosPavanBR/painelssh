<?php
declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) require_once $autoload;

if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    class PainelMailer extends \PHPMailer\PHPMailer\PHPMailer {}
} else {
    require_once __DIR__ . '/../phpmailer/class.phpmailer.php';
    require_once __DIR__ . '/../phpmailer/class.smtp.php';
    class PainelMailer extends \PHPMailer {}
}
