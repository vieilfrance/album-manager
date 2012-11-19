<html>
<head>
<title>Ecran d'administration</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="./include/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php

//set_include_path(get_include_path().PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']."/".'include'); // inclure le repertoire "include" en flottant (laisser le choix du repertoire racine)
// il faut utiliser le php_self et supprimer le nom de fichier ainsi que le dernier repertoire pour le remplacer par le repertoire "include"

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';

$galleries=getGalleries();
$galleriesCount=count($galleries);

if ($galleriesCount==0)
	{
// Pas d'album actuellement donc proposition d'en créer une directement.
	echo "<div class=\"textcenter\">Il semble que vous n'ayiez créé aucun album pour le moment.<br/>Vous pouvez démarrer la création d'un album si vous le souhaitez.<br/><br/>";
	echo "<div class=\"buttonwrapper2\">";
	echo "<a class=\"boldbuttons\" href=\"newgallery.php\"><span>DEMARRER</span></a>"; 	
	echo "</div></div>";
	}
else
	{
// Au moins un album donc on laisse la possibilité de le gerer ou de créer un nouvel album
	echo "<br/>";
	echo "<div class=\"buttonwrapper\">";
	echo "<a class=\"boldbuttons\" href=\"galleries.php\"><span>Administrer un album</span></a>"; 
	echo "<a class=\"boldbuttons\" href=\"gallery.php\" style=\"margin-left: 6px\"><span>Editer un album</span></a>";
	echo "<a class=\"boldbuttons\" href=\"newgallery.php\" style=\"margin-left: 6px\"><span>Nouvel album</span></a>";
	echo "</div>";
	}

?>

</body>