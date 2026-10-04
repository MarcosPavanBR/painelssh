<?php
declare(strict_types=1);

// Disponibiliza a leitura de configurações antes de funcoes.php terminar de carregar.
// O helper usa a mesma conexão PDO criada pelo bootstrap.
if (!function_exists('getConfig')) {
    function getConfig($name)
    {
        global $conn;
        $stmt = $conn->prepare('SELECT valor FROM configs WHERE nome = :nome LIMIT 1');
        $stmt->execute([':nome' => (string)$name]);
        return $stmt->fetchColumn();
    }
}
