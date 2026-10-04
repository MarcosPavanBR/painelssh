<?php
declare(strict_types=1);
require_once __DIR__ . "/security/mailer.php";
require_once __DIR__ . '/pages/system/seguranca.php';

$stmt=$conn->query('SELECT * FROM smtp ORDER BY id ASC LIMIT 1');
$mp=$stmt->fetch();
if(!$mp){ exit; }
$hostsmtp=(string)$mp['servidor']; $portasmtp=(int)$mp['porta']; $emailsmtp=(string)$mp['email']; $senhasmtp=(string)$mp['senha']; $sslsmtp=(string)$mp['ssl_secure'];
$mail=new PainelMailer();
$mail->isSMTP();
$mail->SMTPSecure=$sslsmtp;
$mail->Host=$hostsmtp;
$mail->Port=$portasmtp;
$mail->SMTPDebug=0;
$mail->SMTPAuth=true;
$mail->Username=$emailsmtp;
$mail->Password=$senhasmtp;
$mail->From=$emailsmtp;
$mail->FromName='Suporte';
$mail->addAddress($destino,$destinatario);
$mail->isHTML(true);
$mail->CharSet='UTF-8';
$mail->Subject=$assunto;
$mail->Body=$corpo;
$enviado=$mail->send();
$mail->clearAllRecipients();
$mail->clearAttachments();
if(!$enviado){ error_log('Password reset mail failed: '.$mail->ErrorInfo); }
