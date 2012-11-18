<html>
<head>
<title>Adminstration des albums</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="./include/admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="./include/admin.js"></script>
<script type="text/javascript" src="./include/prototype.js"></script>
<script type="text/javascript" src="./include/scriptaculous.js?load=effects,builder"></script>

</head>
<body>

<?php

// RESTE A REVOIR LES SEPARATEUR DE REPERTOIRE POUR LINUX !!!
// NETTOYER LE CODE

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';

$galleries=getGalleries(); // recuperation des albums dans le XML de paramètres

$galleriesCount=count($galleries); // on compte le nombre d'albums

//////////////////////////////////////////////////////
function directory($dir , $dir_nom)
{
global $self;
$fichier= array(); // on déclare le tableau contenant le nom des fichiers
$dossier= array(); // on déclare le tableau contenant le nom des dossiers
$unwanted= array('.','..','images','original','thumbs','admin','contact','svcore','easyupload'); // on déclare le nom des repertoires qui devront être exclus

// recuperation dans un tableau de la liste des dossiers et de la liste des fichiers
while($element = readdir($dir)) 
	{ 
	if (!in_array($element,$unwanted))
		{
		if (!is_dir($dir_nom."/".$element)) 
			{
			$fichier[] = $element;
			}
		else {
			$dossier[] = $element;
			}
		}
	}
closedir($dir);

if(!empty($dossier)) 
	{
	rsort($dossier); // tri decroissant des dossiers
	echo "\t\t<ul>\n";
		foreach($dossier as $lien){ // pour chaque dossier, on affiche un lien direct
			echo "\t\t\t<li><a href=\"";
			echo str_replace($_SERVER['DOCUMENT_ROOT'],"",$dir_nom); // on remplace le path "serveur" par le début de l'URL du site
			echo "/".$lien." \">$lien</a>";
			
			if ($lien >0 && $lien <=12) { // Si c'est un mois, on propose les options d'upload et de regeneration du xml
			  echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\"><img src=\"include/images/upload_icon.png\"></img></a>";
			  echo "&nbsp;<a href=\"#\" onclick=\"g_update('".str_replace($self,"",str_replace($_SERVER['DOCUMENT_ROOT'],"",$dir_nom))."/".$lien."');\"><img src=\"include/images/reload.png\"></img></a>";
			  echo "&nbsp;<a href=\"#\" onclick=\"g_index('".str_replace($self,"",str_replace($_SERVER['DOCUMENT_ROOT'],"",$dir_nom))."/".$lien."','".$_POST['dirr']."','gallery');\" title=\"Actualiser l'index\"><img src=\"include/images/upd_index.png\"></img></a>";
			  }	
			if (preg_match('/[0-9][0-9][0-9][0-9]/', $lien)) // Si c'est une annee, on propose la fonction d'ajout d'un mois
			{ 
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"mkdir.php?rep=".str_replace($self,"",str_replace($_SERVER['DOCUMENT_ROOT'],"",$dir_nom))."/".$lien."&gallery=".$_POST['dirr']."\" title=\"Ajouter un sous-repertoire a ".$lien."\">+</a>";
			echo "&nbsp;<a href=\"#\" onclick=\"g_index('".str_replace($self,"",str_replace($_SERVER['DOCUMENT_ROOT'],"",$dir_nom))."/".$lien."','".$_POST['dirr']."','subalbum');\"  title=\"Actualiser l'index\"><img src=\"include/images/upd_index.png\"></img></a>";
			}
			echo "</li>\n";
			
			// dans tous les cas, on prépare à faire la liste des dossiers et fichiers contenu dans ce repertoire
			$subdir_nom=$dir_nom."/".$lien;
			$subdir=opendir($subdir_nom);
			directory($subdir,$subdir_nom); // listing des fichiers et dossiers du repertoire en cours
		}
	echo "\t\t</ul>";
	}
}
// Fin de la fonction 'directory'
////////////////////////////////////////////////////////


// Si il n'y a qu'un album, on l'affiche sans proposer de liste de choix
if ($galleriesCount==1)
	{
	$_POST['dirr']=$galleries[0];
	echo "Nom de l'album : ".$galleries[0]."<br/><br/>";
	}
else // Sinon le premier de la liste est selectionné par défaut et on affiche une liste
	{
	echo "<FORM name =\"g_choice\" method=\"POST\" action=\"galleries.php\">";
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
	$_POST['dirr']=$galleries[0]; // petit hack pour éviter de trainer des conditions avec la variable $_POST selon les cas
		
if (isset($_POST['dirr']))
	{
	echo "Liste des galleries :";
	
	$dirr=$_POST['dirr'];
	$dirr_nom=$base."/".$_POST['dirr'];
	$directory = opendir($dirr_nom) or die('Erreur de listage : le répertoire n\'existe pas'); // on ouvre le contenu du dossier courant

	echo "&nbsp;<a href=\"#\" onclick=\"g_index('".str_replace($self,"",str_replace($_SERVER['DOCUMENT_ROOT'],"",$dirr_nom))."','".$dirr."','album');\"  title=\"Actualiser l'index\"><img src=\"include/images/upd_index.png\"></img></a>";
	
	directory($directory , $dirr_nom);
	
	echo "\t\t<ul>\n";
			echo "<li><a href=\"mkdir.php?g=".$self."/".$dirr."&gallery=".$_POST['dirr']."\" title=\"Ajouter un sous-repertoire a ".$dirr."\">+</a></li>";
	echo "\t\t</ul>";
	}
?>  

</body>
</html>