<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Nouvel album</title>
		<link href="./include/admin.css" rel="stylesheet" />
	</head>
<body>
<SCRIPT>
function checkInput(el) {
if (el.value =="")
	el.value = "default"
return true;
}

function clearDefaultandCSS(el) {
	if (el.defaultValue==el.value) el.value = ""
	// If Dynamic Style is supported, clear the style
	if (el.style) el.style.cssText = ""
}

function displayGroup() {
var group=document.getElementById('displaygroup_id');
if (group.style.display=="none")
	group.style.display="block";
else
	group.style.display="none";
return true;
}

function validernomgallery() {
  // si la valeur du champ nom est vide
  o = document.getElementById('namestatut');
  if(document.newgallery.form_name.value.length == 0) {
    // les données ne sont pas ok, et on indique de ne pas envoyer le formulaire 
    document.newgallery.form_name.style.backgroundColor="#F99";
    return false;
  }
  else {
    // sinon on peut envoyer le formulaire 
    o.innerHTML = '<span style="font-weight: bold; color: green;">' + ('<img src="./include/images/check.gif">') + '</span>';
    return true;
  }
}

function checkForm() {
var checkstatus ;
var nomgallery;

nomgallery=validernomgallery();
if(nomgallery == false) {
  checkstatus=false;
}

return checkstatus;
}

</SCRIPT>
<?php

// RESTE A REVOIR LES SEPARATEURS DE REPERTOIRE POUR LINUX !!!
// NETTOYER LE CODE

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';

if (isset($_POST['name']) && $_POST['name']!="")
{
$name=$_POST['name'];
$title=$_POST['title'];
$caption=$_POST['caption'];
$css=$_POST['css'];
$script=$_POST['script'];

if (!file_exists($base."/param/param.xml")) 
	{
	// creation du fichier XML
	mkdir($base."/param/", 0777);
	$fp=fopen($base."/param/param.xml","wb");
	$xmlgallery=generateXMLGalleries($self);  // !!! il faut encore ajouter le repertoire de base des galleries !!!
	fwrite($fp,$xmlgallery);
	fclose($fp);
	}
$fp = fopen($base."/param/param.xml", "rb") or die ("Fichier de paramètrage 'param.xml' introuvable.");

$xmlparam="";
$xmlgallery="";

$xmlparam .= fread($fp, filesize($base."/param/param.xml"));
fclose($fp);
$xmlgallery=generateXMLGallery($name,$title,$caption,$css,$script);
$position=strrpos($xmlparam,"</galleries>");
$sortie=substr($xmlparam,0,$position).$xmlgallery."</galleries>";
$fp=fopen($base."/param/param.xml","wb");
fwrite($fp,$sortie);
fclose($fp);

mkdir($base."/".$name, 0777); // on crée le repertoire de la gallerie
mkdir($base."/".$name."/easyupload", 0777); // on crée le repertoire pour easyupload

recurse_copy($base.'/admin/base/easyupload',$base."/".$name."/easyupload"); // ici mettre la copie des fichiers pour l'upload

$fp=fopen($base."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$base."/admin/base/index_newgallery.php manquant");
$indexcontent=fread($fp, filesize($base."/admin/base/index_newgallery.php"));
fclose($fp);
$indexcontent=str_replace('@@REP@@',$_POST['name'],$indexcontent);
$fp=fopen($base."/".$name."/index.php",'wb') or die("Fichier index manquant");
fwrite($fp,$indexcontent);
fclose($fp);

echo "<FORM name =\"g_choice\" method=\"POST\" action=\"galleries.php\">";
echo "<INPUT type=\"hidden\" name=\"dirr\" value=\"".$_POST['name']."\" ></INPUT>";
echo "</FORM>";
echo "<SCRIPT>";
echo "document.forms[\"g_choice\"].submit();";
echo "</SCRIPT>";

}
else
{
	echo "<div class=\"textcenter\">";
	echo "<FORM id=\"newgallery\" name=\"newgallery\" style=\" border: 1px solid black; padding-top:20px; padding-bottom:20px; background-color:#6C0; \" method=\"POST\" action=\"newgallery.php\" onsubmit=\"return checkForm()\">";
		echo "<fieldset><legend>Informations n&eacute;cessaires</legend>";
		echo "<p><label for=\"form_name\" class=\"obl\">Nom de l'album : </label><span id=\"namestatut\"></span><input size=\"37\" id=\"form_name\" name=\"name\" class=\"obl\"></p>";
		echo "<p><label for=\"form_title\">Titre : </label><input size=\"37\" id=\"form_title\" name=\"title\"class=\"fac\"><span id=\"titlestatut\"></span></p>";
		echo "<p><label for=\"form_caption\">Texte descritpif : </label><textarea rows=\"5\" cols=\"30\" id=\"form_caption\" name=\"caption\"class=\"fac\"></textarea><span id=\"captionstatut\"></span></p>";
		echo "</fieldset>";

		echo "<fieldset><legend><a href=\"#\" onclick=\"displayGroup();\">Informations compl&eacute;mentaires</a></legend>";
		echo "<div id=\"displaygroup_id\" style=\"display:none;\">";
		echo "<p><label for=\"form_css\">Feuille de style : </label><input size=\"37\" id=\"form_css\" name=\"css\" value=\"default\" onfocusout=\"checkInput(this)\" onfocus=\"clearDefaultandCSS(this)\"><span id=\"cssstatut\"></span></p>";
		echo "<p><label for=\"form_script\">Fichier de script : </label><input size=\"37\" id=\"form_script\" name=\"script\" value=\"default\" onfocusout=\"checkInput(this)\" onfocus=\"clearDefaultandCSS(this)\"><span id=\"scriptstatut\"></span></p>";
		echo "</div>";
		echo "</fieldset>";
	    echo "<p><INPUT type=submit value=\"ENREGISTRER\"></p>";
	echo "</FORM>";
	echo "</div>";
}
?>

</body>
</html>