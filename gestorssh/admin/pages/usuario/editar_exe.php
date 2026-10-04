<!DOCTYPE html>
<html class="loading bordered-layout" lang="pt" data-layout="bordered-layout" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">
    
    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/css/animate/animate.min.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/css/extensions/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/themes/semi-dark-layout.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/plugins/extensions/ext-component-sweet-alerts.css">
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/pages/ui-feather.css">
    <link rel="stylesheet" type="text/css" href="../../../assets/css/style.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    
</head>

<body class="vertical-layout vertical-menu-modern">
    <script src="../../../app-assets/js/scripts/extensions/ext-component-sweet-alerts.js"></script>
    <script src="../../../app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
    <script src="../../../app-assets/js/scripts/ui/ui-feather.js"></script>
    <script src="../../../app-assets/vendors/js/vendors.min.js"></script>
    <script src="../../../app-assets/js/core/app-menu.js"></script>
    <script src="../../../app-assets/js/core/app.js"></script>
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
    </script>

<?php

require_once("../../../pages/system/seguranca.php");
require_once("../../../pages/system/config.php");

function alertinfo($status, $msgalert, $dirt)
{
    $myalert = "
        let timerInterval
        Swal.fire({
        icon: '" . $status . "',
        title: '" . $msgalert . "',
        timer: 2000,
        timerProgressBar: true,
        willClose: () => {
            clearInterval(timerInterval)
        }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                window.location='" . $dirt . "';
            } else {
                window.location='" . $dirt . "';
            }
        })
        ";
    $alert = '<script type="text/javascript">' . $myalert . '</script>';
    return $alert;
};

protegePagina("admin");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
csrf_verify($_POST['_csrf'] ?? null);

$id = filter_var($_POST['idusuario'] ?? null, FILTER_VALIDATE_INT);
$nome = trim((string)($_POST['nome'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$celular = trim((string)($_POST['celular'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');
$acesso = (string)($_POST['acesso'] ?? '0');
if (!$id || $nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($nome) > 100 || strlen($celular) > 30 || !in_array($acesso, ['0','1','2'], true)) {
    http_response_code(422); exit('Dados inválidos.');
}
if ($senha !== '' && (strlen($senha) < 10 || strlen($senha) > 128)) { http_response_code(422); exit('Senha inválida.'); }

$check = $conn->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = :id LIMIT 1');
$check->execute([':id' => $id]);
if (!$check->fetch()) { http_response_code(404); exit('Usuário não encontrado.'); }

if ($senha !== '') {
    $stmt = $conn->prepare('UPDATE usuario SET nome=:nome,email=:email,senha=:senha,celular=:celular,permitir_demo=:acesso WHERE id_usuario=:id');
    $stmt->execute([':nome'=>$nome, ':email'=>$email, ':senha'=>password_hash($senha, PASSWORD_DEFAULT), ':celular'=>$celular, ':acesso'=>$acesso, ':id'=>$id]);
} else {
    $stmt = $conn->prepare('UPDATE usuario SET nome=:nome,email=:email,celular=:celular,permitir_demo=:acesso WHERE id_usuario=:id');
    $stmt->execute([':nome'=>$nome, ':email'=>$email, ':celular'=>$celular, ':acesso'=>$acesso, ':id'=>$id]);
}
header('Location: ../../home.php?page=usuario/perfil&id_usuario='.$id, true, 303); exit;
?>
</body>
</html>