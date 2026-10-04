<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';

$endereco_web = getenv('APP_URL') ?: 'http://localhost';
$data_hora_atual = date('Y-m-d H:i:s');
$accessKEY = getenv('APP_ACCESS_KEY') ?: '';
$DirBackup = getenv('BACKUP_DIR') ?: '/var/backups/painel';

$ClickAtellEnabled = (int)(getenv('CLICKATELL_ENABLED') ?: 0);
$UserClickAtell = urlencode(getenv('CLICKATELL_USER') ?: '');
$PassClickAtell = urlencode(getenv('CLICKATELL_PASSWORD') ?: '');
$APIClickAtell = urlencode(getenv('CLICKATELL_API') ?: '');
$LocaSMSEnabled = (int)(getenv('LOCASMS_ENABLED') ?: 0);
$UserLocaSMS = urlencode(getenv('LOCASMS_USER') ?: '');
$PassLocaSMS = urlencode(getenv('LOCASMS_PASSWORD') ?: '');

function muda_mes($Mes) {
    $Mes = (int)$Mes;
    $mes = [1=>'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    return $mes[$Mes] ?? '';
}
function muda_mes2($Meses) {
    $Meses = (int)$Meses;
    $mes = [1=>'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    return $mes[$Meses] ?? '';
}
function formataTelefone($numero) {
    $numero = preg_replace('/\D+/', '', (string)$numero);
    if (strlen($numero) === 10) $numero = substr($numero,0,2).'9'.substr($numero,2);
    return strlen($numero) === 11 ? '(' . substr($numero,0,2) . ') ' . substr($numero,2,5) . '-' . substr($numero,7) : $numero;
}
