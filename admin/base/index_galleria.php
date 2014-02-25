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
        <script src="svcore/js/jquery-1.4.4.min.js"></script>
        <script src="galleria/galleria-1.2.9.min.js"></script>
		<link type="text/css" rel="stylesheet" href="galleria/themes/classic/galleria.classic.css">
		        <style>

            /* Demo styles */
          //  html,body{background:#222;margin:0;}
         //   body{border-top:4px solid #000;}
            .content{color:#777;font:12px/1.4 "helvetica neue",arial,sans-serif;width:620px;margin:20px auto;}
            h1{font-size:12px;font-weight:normal;color:#ddd;margin:0;}
            p{margin:0 0 20px}
          //  a {color:#22BCB9;text-decoration:none;}
            .cred{margin-top:20px;font-size:11px;}

            /* This rule is read by Galleria to define the gallery height: */
            #galleria{height:700px;}

        </style>
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
</body>
		<script src="imagesload.js"></script>
        <script>
		$("body").html("<div id='header'></div><div id='body'><div id='galleria'></div></div>");
		$("#header").load("./../header.html");
		//$("#body").load("/gallery.html");
		Galleria.loadTheme('galleria/themes/classic/galleria.classic.js');
		Galleria.configure({
			transition: 'fade',
			transitionSpeed: 800,
			swipe: true
		});
            //Galleria.run('#galleria');
		Galleria.run('#galleria', {
		dataSource: data
		});
		$('body').on('contextmenu', 'img', function(e){ return false; });
        </script>
</html>