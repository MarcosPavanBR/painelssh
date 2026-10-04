<?php $GLOBALS
["fsejnuxj"]="administrador";
$GLOBALS
["rfobkjbrmbc"]="administrador";
$GLOBALS
["vqfcgki"]="SQLAdministrador";
$vglsrqujrco="SQLAdministrador";
require_once("../pages/system/funcoes.php");
require_once("../pages/system/seguranca.php");
require_once("../pages/system/config.php");
require_once("../pages/system/classe.ssh.php");
${
$GLOBALS
["vqfcgki"]}
="SELECT * FROM admin WHERE id_administrador = '1'";
${
$GLOBALS
["vqfcgki"]}
=$conn->prepare(${
$vglsrqujrco}
);
$SQLAdministrador->execute();
$GLOBALS
["utasmcj"]="administrador";
${
$GLOBALS
["rfobkjbrmbc"]}
=$SQLAdministrador->fetch();
echo "<!doctype html>\n<html lang=\"pt-br\">\n<head>\n<!-- META SECTION -->\n<title>Apps ";
echo(${
$GLOBALS
["rfobkjbrmbc"]}
["site"]);
echo "</title>\n<meta name=\"title\" content=\"";
echo(${
$GLOBALS
["rfobkjbrmbc"]}
["site"]);
echo " - Internet Ilimitada 5G\">\n<meta name=\"description\" content=\"";
echo(${
$GLOBALS
["utasmcj"]}
["site"]);
echo " - Internet ilimitada de alta qualidade.\">\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n<!-- END META SECTION -->\n</head>\n<body style=\"\nbackground:#2a2b25;\ncolor:#fff;\nfont-size:16px;\nfont-family: 'Roboto', sans-serif;\">\n<style>\na:link {\ncolor: white;  text-decoration: none;\n}\na:visited {\ncolor: white;  text-decoration: none;\n}\n@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300&amp;display=swap');\n</style>\n<center>\n<img src=\"./img/giphy.gif\" style=\"height:140px;\">\n<div style=\"\nfont-size: 18px;\ncolor: red;\nborder-bottom: 1px solid;\nwidth: 246px;\npadding: 4px; \">\n<h1>";
echo(${
$GLOBALS
["fsejnuxj"]}
["site"]);
echo "<br>\n<br>\n<h2>TERMOS DE USO<br>\n</div></h2><br>\n<h4>\n• AO USAR NOSSO SERVIÇO, VOCÊ DEVE CONCORDAR COM TODOS OS NOSSOS TERMOS E CONDIÇÕES. VOCÊ DEVE ESTAR CIENTE DE QUE:<br><br>\n• O NOSSO SERVIÇO SE ISENTA DE QUAISQUER PROBLEMAS E LIMITAÇÕES DOS MODOS DE CONEXÃO AQUI UTILIZADOS, SEJA ELAS QUAIS FOR, COMO LENTIDÃO E INSTABILIDADES, OU MESMO QUEDAS, SEM DIREITO A POSSÍVEIS REEMBOLSOS, POIS O USUÁRIO ESTARÁ CIENTE DO QUÊ, E PARA QUE ESTÁ UTILIZANDO O APLICATIVO.<br><br>\n• CORREMOS SEMPRE ATRÁS DE MELHORAR, SE HOUVER QUEDA, VAMOS CORRER ATRÁS PRA CORRIGIR, MAS SE NÃO CONSEGUIMOS IREI AVISAR.<br><br>\n• SE VOCÊ REPASSAR SEU ACESSO A TERCEIROS E ENCONTRAMOS NO SISTEMA ULTRAPASSANDO O LIMITE CONTRATADO, VOCÊ TERA SEU ACESSO SUSPENSO SEM DIREITO A DEVOLUÇÃO DE DINHEIRO.<br><br>\n• PORQUE NÃO TEM DIREITO A REEMBOLSO? PORQUE NÃO TEMOS CULPA DA OPERADORA DERRUBAR O MÉTODO, E DAMOS SUPORTE A SERVIDOR  E APLICATIVO, E NÃO NA OPERADORA.<br><br>\n• VOCÊ CONCORDA QUE VOCÊ ACESSA E USA O SERVIÇO A SEU CRITÉRIO E RISCO.<br><br>\n• TODA RESPONSABILIDADE DE USO É SUA, NÃO SOMOS RESPONSÁVEL PELA REDE QUE VOCÊ ESTÁ A ACESSAR O SERVIÇO.\n</h4>\n<!-- RODAPE -->\n<footer class=\"footer\">\n<div class=\"container-fluid\">\n<div class=\"row\">\n<div class=\"col-sm-6\">\n2021 - <script> document.write(new Date().getFullYear())</script> ";
echo${
$GLOBALS
["rfobkjbrmbc"]}
["site"];
echo "©\n</div>\n<div class=\"col-sm-6\">\n<div class=\"text-sm-right\">\nDesenvolvido por <b><a href=\"https://t.me/paineis\" target=\"_blank\"></i>NTECH SYSTEM</b></a>\n</div>\n</div>\n</div>\n</div>\n</footer>\n<!-- FIM RODAPE -->\n</body>\n</html>\n";

?>