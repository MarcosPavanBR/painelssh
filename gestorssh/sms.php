<?php 
include_once 'config.php';
include_once 'lib/Database/Connection.php';

$conn = Connection::getConn();

function config($name)
{
    global $conn;
    $sql = $stmt = $conn->prepare("SELECT valor FROM configs WHERE nome=:nome LIMIT 1");
    $stmt->execute([':nome'=>(string)$name]);
    $sql = $stmt->fetch();
    return $sql['valor'];
}

?>

{"SendMessage":"<?=config('nsms')?>","MyMessage":"<?=config('msg')?>"}