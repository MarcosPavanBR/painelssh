<?php
require_once __DIR__ . "/../../../security/secrets.php";
require_once("../../../pages/system/seguranca.php");
require_once("../../../pages/system/config.php");
require_once("../../../pages/system/classe.ssh.php");
require_once("../../../pages/system/funcoes.system.php");
require_once("../../../pages/system/funcoes.php");

protegePagina('admin');
if ((isset($_GET['op'])) && (isset($_GET['id_servidor']))) {

	$operacao = $_GET["op"];

    $id_servidor = filter_var($_GET['id_servidor'], FILTER_VALIDATE_INT);
    if (!$id_servidor) { http_response_code(400); exit('ID inválido.'); }
    $SQLServidor = $conn->prepare('SELECT * FROM servidor WHERE id_servidor = :id LIMIT 1');
    $SQLServidor->execute([':id'=>$id_servidor]);
	$servidor = $SQLServidor->fetch();



	//Realiza a comunicacao com o servidor
			$ip_servidor= $servidor['ip_servidor'];
		    $loginSSH= $servidor['login_server'];
			$senhaSSH = decrypt_secret($servidor['senha']);
			$ssh = new SSH2($ip_servidor);
			$ssh->auth($loginSSH,$senhaSSH);


	if($operacao == "reiniciar" ){
		$ssh->exec(" reboot ");
	    $ssh->output();
		echo myalertuser('success', 'Comando enviado !', '../../home.php?page=servidor/listar');
	}elseif($operacao == "updateScript" ){
       http_response_code(410); exit('Atualização remota desabilitada na V2.');


	}elseif($operacao == "deligar" ){
		$ssh->exec("shutdown");
		$ssh->output();
		echo myalertuser('success', 'Comando enviado !', '../../home.php?page=servidor/listar');
	}elseif($operacao == "reiniciarSquid" ){
		$ssh->exec("service squid3 restart");
		$ssh->output();
		$ssh->exec("service squid restart");
		$ssh->output();
		echo myalertuser('success', 'Comando enviado !', '../../home.php?page=servidor/listar');
	}elseif($operacao == "deletarGeral" ){
		 // servidor free e premium
		 if($servidor['tipo']=='free'){
		 $SQLUsuarioSSH = "select * from usuario_ssh_free WHERE servidor = '".$servidor['id_servidor']."' ";
		 }else{
		 $SQLUsuarioSSH = "select * from usuario_ssh WHERE id_servidor = '".$servidor['id_servidor']."' ";
		 }
         $SQLUsuarioSSH = $conn->prepare($SQLUsuarioSSH);
         $SQLUsuarioSSH->execute();
		      if (($SQLUsuarioSSH->rowCount()) > 0) {

                   while($row = $SQLUsuarioSSH->fetch()){
					   $ssh->exec('[[ -f "/opt/sshplus/sshplus" ]] && /opt/sshplus/plugin-sync --del_user '.$row['login'].' || ./remover.sh '.$row['login'].'');
		               $mensagem = (string) $ssh->output();

                        if($servidor['tipo']=='free'){
			        	$SQLSSH = "delete  from usuario_ssh_free  WHERE id = '".$row['id']."'  ";
                        $SQLSSH = $conn->prepare($SQLSSH);
                        $SQLSSH->execute();
                        }else{
                        $SQLSSH = "delete  from usuario_ssh  WHERE id_usuario_ssh = '".$row['id_usuario_ssh']."'  ";
                        $SQLSSH = $conn->prepare($SQLSSH);
                        $SQLSSH->execute();
                        }

				   }
			  }

              $SQLSSHacess = "delete from acesso_servidor  WHERE id_servidor = '".$servidor['id_servidor']."'  ";
              $SQLSSHacess = $conn->prepare($SQLSSHacess);
              $SQLSSHacess->execute();

			  $SQLSSH = "delete  from servidor  WHERE id_servidor = '".$servidor['id_servidor']."'  ";
              $SQLSSH = $conn->prepare($SQLSSH);
              $SQLSSH->execute();
			  echo myalertuser('success', 'Servidor deletado com sucesso !', '../../home.php?page=servidor/listar');
		        

	}elseif($operacao == "deletarContas" ){
		 // free e premium
	     if($servidor['tipo']=='free'){
	     $SQLUsuarioSSH = "select * from usuario_ssh_free WHERE servidor = '".$servidor['id_servidor']."' ";
         }else{
         $SQLUsuarioSSH = "select * from usuario_ssh WHERE id_servidor = '".$servidor['id_servidor']."' ";
         }
         $SQLUsuarioSSH = $conn->prepare($SQLUsuarioSSH);
         $SQLUsuarioSSH->execute();
		      if (($SQLUsuarioSSH->rowCount()) > 0) {

                   while($row = $SQLUsuarioSSH->fetch()){
					   $ssh->exec('[[ -f "/opt/sshplus/sshplus" ]] && /opt/sshplus/plugin-sync --del_user '.$row['login'].' || ./remover.sh '.$row['login'].'');
		               $mensagem = (string) $ssh->output();

	                    if($servidor['tipo']=='free'){
			        	$SQLSSH = "delete  from usuario_ssh_free  WHERE id = '".$row['id']."'  ";
                        $SQLSSH = $conn->prepare($SQLSSH);
                        $SQLSSH->execute();
                        }else{
                        $SQLSSH = "delete  from usuario_ssh  WHERE id_usuario_ssh = '".$row['id_usuario_ssh']."'  ";
                        $SQLSSH = $conn->prepare($SQLSSH);
                        $SQLSSH->execute();
                        }

				   }
			  }


			  echo myalertuser('success', 'Contas deletadas com sucesso !', '../../home.php?page=servidor/listar');
			  
	}elseif($operacao == "sincronizar" ){
		 // free e premium
			
	     if($servidor['tipo']=='free'){
	     $SQLUsuarioSSH = "select * from usuario_ssh_free WHERE servidor = '".$servidor['id_servidor']."' ";
         }else{
         $SQLUsuarioSSH = "select * from usuario_ssh WHERE id_servidor = '".$servidor['id_servidor']."' ";
         }
		 
         $SQLUsuarioSSH = $conn->prepare($SQLUsuarioSSH);
         $SQLUsuarioSSH->execute();
		      if (($SQLUsuarioSSH->rowCount()) > 0) {
				  
                   while($row = $SQLUsuarioSSH->fetch()){
					   //Calcula os dias restante
                        $data_atual = date("Y-m-d");
                        $validade = $row['data_validade'];
                        if($validade > $data_atual){
                            $data1 = new DateTime( $validade );
                            $data2 = new DateTime( $data_atual );
                            $dias_acesso = 0;
                            $diferenca = $data1->diff( $data2 );
                            $ano = $diferenca->y * 364 ;
                            $mes = $diferenca->m * 30;
                            $dia = $diferenca->d;
                            $dias_acesso = $ano + $mes + $dia;

                        }else{
                            $dias_acesso = 1;
                        }
						
					   $ssh->exec('[[ -f "/opt/sshplus/sshplus" ]] && /opt/sshplus/plugin-sync --create_user '.$row['login']. ' ' .$row['senha']. ' ' .$row['acesso']. ' ' .$dias_acesso. ' || ./criarusuario.sh '.$row['login']. ' ' .$row['senha']. ' ' .$row['acesso']. ' ' .$dias_acesso. '');
		               $mensagem = (string) $ssh->output();

				   }
			  }


			  echo myalertuser('success', 'Contas sincronizadas com sucesso !', '../../home.php?page=servidor/listar');
		       

	}elseif($operacao == "manutencao" ){
	if($servidor['manutencao']=='nao'){
	$tiramanu = "update servidor set manutencao='sim'  WHERE id_servidor = '".$servidor['id_servidor']."'  ";
    $tiramanu = $conn->prepare($tiramanu);
    $tiramanu->execute();

	echo myalertuser('success', 'Servidor colocado em manutencao!', '../../home.php?page=servidor/listar');
		       

	}elseif($servidor['manutencao']=='sim'){
	$tiramanu = "update servidor set manutencao='nao'  WHERE id_servidor = '".$servidor['id_servidor']."'  ";
    $tiramanu = $conn->prepare($tiramanu);
    $tiramanu->execute();

	echo myalertuser('success', 'Servidor retirado da manutencao!', '../../home.php?page=servidor/listar');
		 
	}

	}

}else{
	echo myalertuser('error', 'Erro !', '../../home.php?page=servidor/listar');
		 

}
