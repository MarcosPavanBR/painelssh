<?php $GLOBALS
["crljwfpmy"]="local";
$GLOBALS
["lvefemryi"]="file";
$GLOBALS
["npnvpluv"]="arquivo";
$GLOBALS
["ntrfoubc"]="conta";
$GLOBALS
["wjoqdhu"]="id";
$GLOBALS
["dsdvduyvibbi"]="SQLSubSSH";
$GLOBALS
["zgdabtr"]="alert";
$GLOBALS
["eqdghcyh"]="dirt";
require_once("../pages/system/seguranca.php");
require_once("../pages/system/config.php");
require_once("../pages/system/funcoes.php");
require_once("../pages/system/classe.ssh.php");
require_once("../pages/system/funcoes.system.php");
function alertinfo($status,$msgalert,$dirt){
$nruesvwfgng="myalert";
$GLOBALS
["wcwbmi"]="status";
$GLOBALS
["ypjplzthyb"]="msgalert";
$GLOBALS
["ikhsnxbu"]="alert";
$GLOBALS
["iokhiprwnu"]="myalert";
${
$nruesvwfgng}
="\nlet timerInterval\nSwal.fire({\nicon: '".${
$GLOBALS
["wcwbmi"]}
."',\ntitle: '".${
$GLOBALS
["ypjplzthyb"]}
."',\ntimer: 2000,\ntimerProgressBar: true,\nwillClose: () => {\nclearInterval(timerInterval)\n}\n}).then((result) => {\nif (result.dismiss === Swal.DismissReason.timer) {\nwindow.location='".${
$GLOBALS
["eqdghcyh"]}
."';\n} else {\nwindow.location='".${
$GLOBALS
["eqdghcyh"]}
."';\n}\n})\n";
${
$GLOBALS
["ikhsnxbu"]}
="<script type=\"text/javascript\">".${
$GLOBALS
["iokhiprwnu"]}
."</script>";
return${
$GLOBALS
["zgdabtr"]}
;
}
if(isset($_GET["id"])){
$GLOBALS
["kxvrfqgdeq"]="id";
${
$GLOBALS
["kxvrfqgdeq"]}
=anti_sql_injection($_GET["id"]);
$SQLSubSSH=$conn->prepare("SELECT * FROM arquivo_download WHERE id=:id");
$SQLSubSSH->execute([":id"=>(int)${
$GLOBALS
["wjoqdhu"]}
]);
${
$GLOBALS
["ntrfoubc"]}
=$SQLSubSSH->rowCount();
if(${
$GLOBALS
["ntrfoubc"]}
>0){
${
$GLOBALS
["npnvpluv"]}
=$SQLSubSSH->fetch();
$bcsxjfjqehtl="file";
${
$bcsxjfjqehtl}
=${
$GLOBALS
["npnvpluv"]}
["nome_arquivo"];
if(file_exists("../admin/pages/download/".${
$GLOBALS
["lvefemryi"]}
."")){
$GLOBALS
["bssowp"]="local";
$gdxhmukmsw="file";
${
$GLOBALS
["bssowp"]}
="../admin/pages/download/".${
$gdxhmukmsw}
;
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream;");
$wmxtkwyssm="local";
header("Content-Length:".filesize(${
$GLOBALS
["crljwfpmy"]}
));
header("Content-disposition: attachment; filename=".basename(${
$wmxtkwyssm}
));
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: 0");
ob_clean();
flush();
readfile(${
$GLOBALS
["crljwfpmy"]}
);
exit(0);
}
else{
$GLOBALS
["gsudhxv"]="file";
echo alertinfo("warning","Arquivo ".${
$GLOBALS
["gsudhxv"]}
." não foi encontrado na pasta do servidor!","/apps");
}
}
else{
echo alertinfo("warning","Arquivo não foi encontrado no servidor!","/apps");
}
}

?>