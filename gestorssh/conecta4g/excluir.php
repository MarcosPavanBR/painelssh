<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/config/funcoes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/conecta4g/modais.php");
isLogged($sid);

if ($acao == "servidor") :

    if (isset($_POST['excluir_servidor'])) :

        $id = $_POST['id'];
        $id_owner = $_POST['id_owner'];

        $sql = db_query($conn, 'DELETE FROM servidores WHERE id=:id AND id_owner=:id_owner', [':id' => $id, ':id_owner' => $id_owner]);

        if ($sql) :
            echo "<script>
    alert('Servidor excluido com sucesso !');
    window.location='" . getConfig('link') . "/conecta4g/app.php';
    </script>";
        else :
            echo "<script>
        alert('Falha ao excluir servidor !');
        window.location='" . getConfig('link') . "/conecta4g/app.php';
        </script>";
        endif;
    else :
        header("location: /conecta4g/index.php");
    endif;

elseif ($acao == "payload") :

    if (isset($_POST['excluir_payload'])) :

        $id = $_POST['id'];
        $id_owner = $_POST['id_owner'];

        $sql = db_query($conn, 'DELETE FROM payloads WHERE id=:id AND id_owner=:id_owner', [':id' => $id, ':id_owner' => $id_owner]);

        if ($sql) :
            echo "<script>
            alert('Payload excluida com sucesso !');
            window.location='" . getConfig('link') . "/conecta4g/app.php';
            </script>";
        else :
            echo "<script>
                alert('Falha ao excluir Payload !');
                window.location='" . getConfig('link') . "/conecta4g/app.php';
                </script>";
        endif;
    else :
        header("location: /conecta4g/index.php");
    endif;

elseif ($acao == "porta") :

    if (isset($_POST['excluir_porta'])) :

        $id = $_POST['id'];
        $id_owner = $_POST['id_owner'];

        $sql = db_query($conn, 'DELETE FROM portas WHERE id=:id AND id_owner=:id_owner', [':id' => $id, ':id_owner' => $id_owner]);

        if ($sql) :
            echo "<script>
                alert('Porta excluida com sucesso !');
                window.location='" . getConfig('link') . "/conecta4g/app.php';
                </script>";
        else :
            echo "<script>
                    alert('Falha ao excluir Porta !');
                    window.location='" . getConfig('link') . "/conecta4g/app.php';
                    </script>";
        endif;
    else :
        header("location: /conecta4g/index.php");
    endif;
elseif ($acao == "usuario") :

    if (getOwner($uid) == false) :
        header("location: /conecta4g/index.php");
    endif;

    if (isset($_GET['id'])) :

        $id = $_GET['id'];
        $pasta = getData('pasta_att', $id);

        $sql = db_query($conn, 'SELECT nivel FROM usuarios WHERE id=:id', [':id' => $id])->fetch();

        if ($sql[0] >= 3) :
            echo "<script>
            alert('Você não pode excluir o dono do site !');
            window.location='" . getConfig('link') . "/conecta4g/adicionar.php';
            </script>";
        else :

            db_query($conn, 'DELETE FROM usuarios WHERE id=:id', [':id' => $id]);
            db_query($conn, 'DELETE FROM configuracoes WHERE id_owner=:id', [':id' => $id]);
            db_query($conn, 'DELETE FROM servidores WHERE id_owner=:id', [':id' => $id]);
            db_query($conn, 'DELETE FROM payloads WHERE id_owner=:id', [':id' => $id]);
            db_query($conn, 'DELETE FROM portas WHERE id_owner=:id', [':id' => $id]);
            $sql = db_query($conn, 'DELETE FROM mensagens WHERE id_owner=:id', [':id' => $id]);

            delTree("update/$pasta");

            if ($sql) :
                echo "<script>
                    alert('Usuário excluido com sucesso !');
                    window.location='" . getConfig('link') . "/conecta4g/adicionar.php';
                    </script>";
            else :
                echo "<script>
                        alert('Falha ao excluir usuário !');
                        window.location='" . getConfig('link') . "/conecta4g/adicionar.php';
                        </script>";
            endif;
        endif;
    else :
        header("location: /conecta4g/index.php");
    endif;
endif;
