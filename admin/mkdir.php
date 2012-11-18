<html>
<head>
<title>Ajout d'une gallerie</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="./include/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?
$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base.'/include');

include 'functions.php';

////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['rep']) && isset($_POST['gallery'])) // création d'une gallerie - un mois
{
	$path=$_SERVER['DOCUMENT_ROOT'].$self.$_GET['rep'];
	$rep=$_POST['rep'];

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

	$title=$rep." - ".$tab_mois[$rep];
	$contenu_header="\n<a href=\"../".$rep."/\">".$tab_mois[$rep]."</a>";

	// condition pour verifier que le mois en question n'existe pas déjà
	if (!file_exists($path."/".$rep)) // test d'existance du repertoire du mois
		{ // création des repertoires basiques
		mkdir($path."/".$rep, 0777);
		mkdir($path."/".$rep."/images/", 0777);
		mkdir($path."/".$rep."/thumbs/", 0777);
		mkdir($path."/".$rep."/original/", 0777);
		
		// création du repertoire contenant le player des photos
		recurse_copy($base.'/admin/base/svcore',$path."/".$rep."/svcore");
		
		// création du fichier index de mois
		$file = $base.'/admin/base/index.php';
		copy($file, $path."/".$rep."/index.php");

		// on récupère la liste des dossiers (et donc des années ou des mois)
		$dossier=listeDirectory($path);

		//ouverture en lecture et modification du fichier index du mois
		$text=fopen($path."/".$rep."/index.php",'r') or die("Fichier index manquant en lecture");
		$contenu=file_get_contents($path."/".$rep."/index.php");
		$contenuMod=str_replace('<title>','<title>'.$title, $contenu);
		$contenuMod=str_replace('@@REP@@',$_POST['gallery'],$contenuMod);
		fclose($text);

		//ouverture en écriture
		$text2=fopen($path."/".$rep."/index.php",'w+') or die("Fichier index manquant en ecriture");
		fwrite($text2,$contenuMod);
		fclose($text2);

		// on s'occupe du header maintenant
		genereHeader($_POST['gallery'],$base,$dossier,$_GET['rep'],$self);

		// et enfin on s'occuppe de l'index de l'année
		genereSubAlbumIndex($_POST['gallery'],$base,$dossier,$_GET['rep'],$self);
		}
	echo "<FORM name =\"g_choice\" method=\"POST\" action=\"galleries.php\">";
	echo "<INPUT type=\"hidden\" name=\"dirr\" value=\"".$_POST['gallery']."\" ></INPUT>";
	echo "</FORM>";
	echo "<SCRIPT>";
	echo "document.forms[\"g_choice\"].submit();";
	echo "</SCRIPT>";
}
else
{

  if (isset($_POST['g']) && isset($_POST['gallery']))
	{
	$path=$_SERVER['DOCUMENT_ROOT'].$_GET['g'];
	$rep=$_POST['g'];
	$title=$rep;
	
	if (!file_exists($path."/".$rep)) // test d'existance du repertoire des années
		{
		mkdir($path."/".$rep, 0777);
		// on crée un index.php vide dans le repertoire
		$fp=fopen($base."/admin/base/index2.php",'rb') or die("Fichier ".$base."/admin/base/index2.php manquant en lecture");
		$indexcontent=fread($fp, filesize($base."/admin/base/index2.php"));
		$indexcontent=str_replace('@@REP@@',$_POST['gallery'],$indexcontent);
		fclose($fp);
		$fp=fopen($path."/".$rep."/index.php",'wb') or die("Fichier ".$path."/".$rep."/index.php manquant en ecriture");
		fwrite($fp,$indexcontent);
		fclose($fp);

		
	// Il faut mettre à jour le fichier index listant les galleries (dont celle-ci). Si il n'existe pas il faut le créer
		// on récupère la liste des dossiers (et donc des années ou des mois)
		$dossier=listeDirectory($path);
		$g_rep=$_GET['g'];
		genereAlbumIndex($_POST['gallery'],$base,$dossier,$g_rep,$self);
		}

	echo "<FORM name =\"g_choice\" method=\"POST\" action=\"galleries.php\">";
	echo "<INPUT type=\"hidden\" name=\"dirr\" value=\"".$_POST['gallery']."\" ></INPUT>";
	echo "</FORM>";
	echo "<SCRIPT>";
	echo "document.forms[\"g_choice\"].submit();";
	echo "</SCRIPT>";

	}
	else
		{
		if (isset($_GET['g']))
			{
			$path=$_GET['g'];
			echo "<FORM method=\"POST\" action=\"mkdir.php?g=".$path."\">";
				echo "Nom de la gallerie : ";
				echo "<INPUT type=text name=\"g\" size=5 maxlength=5>";
				echo "<br/>";
				echo "<INPUT type=\"hidden\" name=\"gallery\" value=\"".$_GET['gallery']."\">";
				echo "<INPUT type=submit value=\"OK\">";
				echo "</FORM>";
			}
			else
				{
	$path=$_GET['rep'];
	echo "<FORM method=\"POST\" action=\"mkdir.php?rep=".$path."\">";
        echo "Nom de la gallerie : <select name=\"rep\">";
        echo "<OPTION VALUE=\"01\">01</OPTION>";
        echo "<OPTION VALUE=\"02\">02</OPTION>";
        echo "<OPTION VALUE=\"03\">03</OPTION>";
        echo "<OPTION VALUE=\"04\">04</OPTION>";
        echo "<OPTION VALUE=\"05\">05</OPTION>";
        echo "<OPTION VALUE=\"06\">06</OPTION>";
        echo "<OPTION VALUE=\"07\">07</OPTION>";
        echo "<OPTION VALUE=\"08\">08</OPTION>";
        echo "<OPTION VALUE=\"09\">09</OPTION>";
        echo "<OPTION VALUE=\"10\">10</OPTION>";
        echo "<OPTION VALUE=\"11\">11</OPTION>";
        echo "<OPTION VALUE=\"12\">12</OPTION>";
        echo "</SELECT><br/>";
		echo "<INPUT type=\"hidden\" name=\"gallery\" value=\"".$_GET['gallery']."\">";
        echo "<INPUT type=submit value=\"OK\">";
	echo "</FORM>";

				}
			}
		}
?>


</body>
</html>