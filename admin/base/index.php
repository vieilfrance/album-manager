<? 
$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$self = strstr($self,"/@@REP@@",TRUE);
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';
$param=getParameters("/@@REP@@");
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title></title>

<script type="text/javascript" src="svcore/js/simpleviewer.js"></script>
<script type="text/javascript" src="<? echo $self."/include/".$param[5]; ?>.js"></script>
<link href="<? echo $self."/include/".$param[4]; ?>.css" rel="stylesheet" type="text/css" />

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-89485-3";
urchinTracker();
</script>

</head>
<body>

<div id="header">
<h2>

<?
include('../header.php'); 
?>

</h2>
</div>
<div id="header_smartphone">
<h2><a href="..">Retour</a></h2>
</div>

<body>

<div id="sv-container"></div>

</body>
</html>