<html>
<head>
<title>Edition d'album</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="./include/admin.css" rel="stylesheet" type="text/css" />
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
    // les donn�es ne sont pas ok, et on indique de ne pas envoyer le formulaire 
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

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (� partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier execut� (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlev� le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier execut�

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';

$galleries=getGalleries(); // recuperation des albums dans le XML de param�tres

if (isset($_POST['name']) && $_POST['name']!="")
{
$name=$_POST['name'];
$title=$_POST['title'];
$caption=$_POST['caption'];
$css=$_POST['css'];
$script=$_POST['script'];

$xmlgallery="<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><galleries>";

foreach($galleries as $g) 
	{
	$param=getparameters($g);
	if ($param[1]== $name)
		$xmlgallery.=generateXMLGallery($name,$title,$caption,$css,$script);
	else
		$xmlgallery.=generateXMLGallery($param[1],$param[2],$param[3],$param[4],$param[5]);
	}
$xmlgallery.="</galleries>";

$fp=fopen($base."/param/param.xml","wb");
fwrite($fp,$xmlgallery);
fclose($fp);

echo "<FORM name =\"g_choice\" method=\"POST\" action=\"index.php\">";
echo "</FORM>";
echo "<SCRIPT>";
echo "document.forms[\"g_choice\"].submit();";
echo "</SCRIPT>";

}

$galleriesCount=count($galleries); // on compte le nombre d'albums


// Si il n'y a qu'un album, on l'affiche sans proposer de liste de choix
if ($galleriesCount==1)
	{
	$_POST['dirr']=$galleries[0];
	echo "Nom de l'album : ".$galleries[0]."<br/><br/>";
	}
else // Sinon le premier de la liste est selectionn� par d�faut et on affiche une liste
	{
	echo "<FORM name =\"g_choice\" method=\"POST\" action=\"gallery.php\">";
    echo "Nom de l'album : <select name=\"dirr\" onchange=\"document.forms['g_choice'].submit();\" >";
	for ($i = 0; $i < $galleriesCount; $i++) 
		{
		echo "<OPTION VALUE=\"$galleries[$i]\"";				
			if (isset($_POST['dirr']))
				{
				if ($_POST['dirr'] == $galleries[$i]) echo " SELECTED";
				}
			else
				if ($i==0)
					echo " SELECTED";
        echo " >$galleries[$i]</OPTION>";
		}
	echo "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "</FORM>";
	}
	
if (!isset($_POST['dirr']))
	$_POST['dirr']=$galleries[0]; // petit hack pour �viter de trainer des conditions avec la variable $_POST selon les cas
		
if (isset($_POST['dirr']))
	{
	$param=getparameters($_POST['dirr']);

	echo "<div class=\"textcenter\">";
	echo "<FORM id=\"newgallery\" name=\"newgallery\" style=\" border: 1px solid black; padding-top:20px; padding-bottom:20px; background-color:#6C0; \" method=\"POST\" action=\"gallery.php\" onsubmit=\"return checkForm()\">";
		echo "<input type=\"hidden\"  name=\"dirr\"  value=\"".$_POST['dirr']."\">";		
		echo "<fieldset><legend>Informations n�cessaires</legend>";
		echo "<p><label for=\"form_name\" class=\"obl\">Nom de l'album : </label><span id=\"namestatut\"></span><input size=\"37\" id=\"form_name\" name=\"name\" class=\"obl\" value=\"".$param[1]."\"></p>";
		echo "<p><label for=\"form_title\">Titre : </label><input size=\"37\" id=\"form_title\" name=\"title\"class=\"fac\" value=\"".$param[2]."\"><span id=\"titlestatut\"></span></p>";
		echo "<p><label for=\"form_caption\">Texte descritpif : </label><textarea rows=\"5\" cols=\"30\" id=\"form_caption\" name=\"caption\"class=\"fac\">".$param[3]."</textarea><span id=\"captionstatut\"></span></p>";
		echo "</fieldset>";

		echo "<fieldset><legend><a href=\"#\" onclick=\"displayGroup();\">Informations compl�mentaires</a></legend>";
		echo "<div id=\"displaygroup_id\" style=\"display:none;\">";
		echo "<p><label for=\"form_css\">Feuille de style : </label><input size=\"37\" id=\"form_css\" name=\"css\"  value=\"".$param[4]."\" onfocusout=\"checkInput(this)\" onfocus=\"clearDefaultandCSS(this)\"><span id=\"cssstatut\"></span></p>";
		echo "<p><label for=\"form_script\">Fichier de script : </label><input size=\"37\" id=\"form_script\" name=\"script\"  value=\"".$param[5]."\" onfocusout=\"checkInput(this)\" onfocus=\"clearDefaultandCSS(this)\"><span id=\"scriptstatut\"></span></p>";
		echo "</div>";
		echo "</fieldset>";
	    echo "<p><INPUT type=submit value=\"ENREGISTRER\"></p>";
	echo "</FORM>";
	echo "</div>";	
	}
?>  

</body>
</html>