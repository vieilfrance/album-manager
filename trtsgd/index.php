<? 
$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (� partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier execut� (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlev� le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier execut�

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';
$param=getParameters("/trtsgd");
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><? echo $param[1]; ?></title>
		<link href="<? echo $self."/include/".$param[4]; ?>.css" rel="stylesheet" type="text/css" />
	
		<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
		</script>
		<script type="text/javascript">
		_uacct = "UA-89485-3";
		urchinTracker();
		</script>
	</head>
	<body>
	
		<h1><? echo $param[2]; ?></h1>
		
		<br/>

		<? echo $param[3]; ?>
		
		<br/><br/>
		
	</body>
</html>