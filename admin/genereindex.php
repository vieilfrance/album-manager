<?
//header ("content-type: text/xml");

$rep=$_SERVER['DOCUMENT_ROOT']."/".$_GET['rep'];

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base.'/include');

if (isset($_GET['name']) && $_GET['name']!="")
{
	$name=$_GET['name'];
}

if (isset($_GET['type']) && $_GET['type']!="")
{
	$type=$_GET['type'];
}

// on récupère la liste des dossiers (et donc des années ou des mois)
$dossier= array(); // on déclare le tableau contenant le nom des dossiers
$unwanted= array('.','..','images','original','thumbs','admin','contact','svcore','easyupload','.git'); // on déclare le nom des repertoires qui devront être exclus
$dir=$rep;

$directory = opendir($dir) or die('Erreur de listage : le répertoire n\'existe pas'); // on ouvre le contenu du dossier courant
while($element = readdir($directory)) 
	{
	if (!in_array($element,$unwanted))
		{
		if (is_dir($dir."/".$element)) 
			{
			$dossier[] = $element;
			}
		}
	}
closedir($directory);


	
function genereAlbumIndex($name,$base,$dossier){ // la génération de l'index des années
	$directoryList="";
	$path=$base."/".$name;
	
	foreach ($dossier as $d) {
	$directoryList.="\n<h2><a href=\"/".substr($_GET['rep'],1)."/".$d."\">$d</a></h2>";
	//echo"\n<h2><a href=\"/".substr($_GET['rep'],1)."/".$d."\">$d</a></h2>";
	}
	
	$fp=fopen($base."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$base."/admin/base/index_newgallery.php manquant");
	$indexcontent=fread($fp, filesize($base."/admin/base/index_newgallery.php"));
	fclose($fp);
	$indexcontent=str_replace('@@REP@@',$name,$indexcontent);
	$fp=fopen($base."/".$name."/index.php",'wb') or die("Fichier index manquant");
	fwrite($fp,$indexcontent);
	fclose($fp);
	
	$text=fopen($path."/index.php",'a+') or die("Fichier index manquant en lecture");
	$contents = '';
	while (!feof($text)) {
	  $contents .= fread($text, 8192);
	}
	$position=strrpos($contents,"<br/>");
	$position=$position+strlen("<br/>");

	$longeur=strlen($contents)-$position;
	$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
	fclose($text);

	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$str1);
	fclose($text2);
}

function genereSubAlbumIndex($name,$base,$dossier){ // la génération de l'index des mois
	$directoryList="";
	$path=$base.$_GET['rep'];
	echo $path;
	
	$tab_mois = array( '01' => 'Janvier',
					   '02' => 'Fevrier',
					   '03' => 'Mars',
					   '04' => 'Avril',
					   '05' => 'Mai',
					   '06' => 'Juin',
					   '07' => 'Juillet',
					   '08' => 'Aout',
					   '09' => 'Septembre',
					   '10' => 'Octobre',
					   '11' => 'Novembre',
					   '12' => 'Decembre');
	
	foreach ($dossier as $d) {
	$directoryList.="\n<h2><a href=\"/".substr($_GET['rep'],1)."/".$d."\">".$d." - ".$tab_mois[$d]."</a></h2>";
	//echo"\n<h2><a href=\"/".substr($_GET['rep'],1)."/".$d."\">$d</a></h2>";
// "\n<a href=\"../".$rep."/\">".$tab_mois[$rep]."</a>";
	$fp=fopen($base."/admin/base/index2.php",'rb') or die("Fichier ".$base."/admin/base/index2.php manquant");
	$indexcontent=fread($fp, filesize($base."/admin/base/index2.php"));
	fclose($fp);
	$indexcontent=str_replace('@@REP@@',$name,$indexcontent);
	$fp=fopen($path."/index.php",'wb') or die("Fichier index manquant");
	fwrite($fp,$indexcontent);
	fclose($fp);

	$text=fopen($path."/index.php",'a+') or die("Fichier index manquant en lecture");
	$contents = '';
	while (!feof($text)) {
	  $contents .= fread($text, 8192);
	}
	$position=strrpos($contents,"<br/>");
	$position=$position+strlen("<br/>");

	$longeur=strlen($contents)-$position;
	$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
	fclose($text);

	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$str1);
	fclose($text2);
	}
}

function genereGaleryIndex($name,$base,$dossier) { // la génération de l'index de la gallerie
	$file=$base.'/admin/base/index.php';
	$path=$base.$_GET['rep'];

	$tab_mois = array( '01' => 'Janvier',
					   '02' => 'Fevrier',
					   '03' => 'Mars',
					   '04' => 'Avril',
					   '05' => 'Mai',
					   '06' => 'Juin',
					   '07' => 'Juillet',
					   '08' => 'Aout',
					   '09' => 'Septembre',
					   '10' => 'Octobre',
					   '11' => 'Novembre',
					   '12' => 'Decembre');

	$d=$dossier;
	$title=$d." - ".$tab_mois[$d];
echo $d;
	copy($file, $path."/index.php");

	//ouverture en lecture et modification du fichier index du mois
	$text=fopen($path."/index.php",'r') or die("Fichier index manquant en lecture");
	$contenu=file_get_contents($path."/index.php");
	$contenuMod=str_replace('<title>','<title>'.$title, $contenu);
	$contenuMod=str_replace('@@REP@@',$name,$contenuMod);
	fclose($text);

	//ouverture en écriture
	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$contenuMod);
	fclose($text2);
}

switch ($type) {
	case "album" :
		genereAlbumIndex($name,$base,$dossier);
		break;
	case "subalbum" :
		genereSubAlbumIndex($name,$base,$dossier);
		break;
	case "gallery" :
		//genereGaleryIndex($name,$base,mb_substr($_GET['rep'],mb_strlen($_GET['rep'])-mb_strlen(strrchr($_GET['rep'],"/"))+1));
		echo mb_substr($_GET['rep'],mb_strlen($_GET['rep'])-mb_strlen(strrchr($_GET['rep'],"/"))+1);
		break;
	}
?>