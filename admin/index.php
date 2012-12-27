<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Ecran d'administration</title>
		<!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
		<link href="./include/admin.css" rel="stylesheet" /> 
		<!-- Bootstrap CSS Toolkit styles -->
		<link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap.min.css">		
		<!-- Bootstrap styles for responsive website layout, supporting different screen sizes -->
		<link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap-responsive.min.css">

	</head>
<body>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
        </div>
    </div>
</div>
<div class="container">
<ul class="breadcrumb">
  <li class="active">Home</li>
</ul>
    <div class="page-header">
        <h1>Administration des albums</h1>
    </div>
    <br>
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
	echo "<div class=\"textcenter\">Il semble que vous n'ayiez cr&eacute;&eacute; aucun album pour le moment.<br/>Vous pouvez d&eacute;marrer la cr&eacute;ation d'un album si vous le souhaitez.<br/><br/>";
    echo "<a class=\"btn btn-success\" href=\"newgallery.php\">";
	echo "<i class=\"icon-plus icon-white\"></i>";
	echo "<span>DEMARRER</span>";
	echo "</a>";
	echo "</div>";
	}
else
	{
// Au moins un album donc on laisse la possibilité de le gerer ou de créer un nouvel album
    echo "<a class=\"btn btn-primary\" href=\"galleries.php\">";
    echo "<i class=\"icon-eye-open icon-white\"></i>";
    echo " Administrer un album";
	echo "</a> ";
	echo "<a class=\"btn btn-primary\" href=\"gallery.php\">";
    echo "<i class=\"icon-edit icon-white\"></i>";
    echo " Editer un album";
	echo "</a> ";
    echo "<a class=\"btn btn-success\" href=\"newgallery.php\">";
    echo "<i class=\"icon-plus icon-white\"></i>";
    echo " Nouvel album";
	echo "</a>";
	}
?>
	</div>
</body>
</html>