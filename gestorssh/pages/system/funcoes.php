<?php
declare(strict_types=1);
require_once __DIR__ . '/seguranca.php';

function Pinga($IP,$PORTA){
    $IP = filter_var($IP, FILTER_VALIDATE_IP);
    $PORTA = filter_var($PORTA, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>65535]]);
    if (!$IP || !$PORTA) return 1;
    $fp = @fsockopen($IP, $PORTA, $errno, $errstr, 0.5);
    if (!$fp) return 1;
    fclose($fp); return 0;
}
function tempo_corrido($time) {
    $diff = time() - strtotime((string)$time);
    if ($diff <= 60) return $diff == 1 ? '1 seg ' : $diff.' seg ';
    $minutes = (int)round($diff/60); if ($minutes <= 60) return $minutes == 1 ? '1 min ' : $minutes.' min ';
    $hours = (int)round($diff/3600); if ($hours <= 24) return $hours == 1 ? '1 hrs ' : $hours.' hrs ';
    $days = (int)round($diff/86400); if ($days <= 7) return $days == 1 ? '1 dia atrás' : $days.' dias ';
    $weeks = (int)round($diff/604800); if ($weeks <= 4) return $weeks == 1 ? '1 semana ' : $weeks.' semanas ';
    $months = (int)round($diff/2419200); if ($months <= 12) return $months == 1 ? '1 mês ' : $months.' meses ';
    $years = (int)round($diff/29030400); return $years == 1 ? 'um ano ' : $years.' anos ';
}
function tempo_final($time,$time_f){ return tempo_corrido(date('Y-m-d H:i:s', strtotime($time) - time() + strtotime($time_f))); }
function removerEspeciais($palavra){ return preg_replace('/[^a-zA-Z0-9_]/u','',strtr((string)$palavra,'áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ ','aaaaeeiooouucAAAAEEIOOOUUC_')); }
function validarNumero($palavra){ return is_numeric($palavra) ? 0 : 1; }
function getIDUsuario(){ return (int)($_SESSION['usuarioID'] ?? 0); }
function getUsuario($id){
    global $conn;
    $stmt = $conn->prepare('SELECT * FROM usuario WHERE id_usuario = :id LIMIT 1');
    $stmt->execute([':id'=>(int)$id]);
    return $stmt->fetch() ?: null;
}
