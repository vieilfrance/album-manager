<?
//header ("content-type: text/xml");

$rep=$_SERVER['DOCUMENT_ROOT']."/".$_GET['rep'];

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base.'/include');
include 'functions.php';

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
$dir=$base."/".$_GET['rep'];

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

switch ($type) {
	case "album" :
		genereAlbumIndex($name,$base,$dossier,$_GET['rep'],$self);
		break;
	case "subalbum" :
		genereSubAlbumIndex($name,$base,$dossier,$_GET['rep'],$self);
		genereHeader($name,$base,$dossier,$_GET['rep'],$self);
		break;
	case "gallery" :
		//genereGaleryIndex($name,$base,mb_substr($_GET['rep'],mb_strlen($_GET['rep'])-mb_strlen(strrchr($_GET['rep'],"/"))+1),,$_GET['rep']);
		echo mb_substr($_GET['rep'],mb_strlen($_GET['rep'])-mb_strlen(strrchr($_GET['rep'],"/"))+1);
		break;
	}
?>