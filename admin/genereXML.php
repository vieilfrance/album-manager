<?
header ("content-type: text/xml");
// Pour la lecture des exif, pensez Ã  mettre Ã  jour votre php.ini et activer les dll exif et mbstring (mbstring devant se trouver AVANT exif)	
// Le script attend comme paramètre 'rep' le nom d'un repertoire contenant des images
$sortie="";
$rep=$_SERVER['DOCUMENT_ROOT']."/".$_GET['rep'];
	
	
////////////////////////////////////////////////////////////////////////////////////////////////
function listing($repertoire){

$fichier = array();
$nom = array();
$date = array();
$tmpsortie="";

if (is_dir($repertoire)) // Test de vérification de repertoire
	{
	$dir = opendir($repertoire); //ouvre le repertoire courant dè²©gnçŸ°ar la variable
	$compteur=-1;
	while(false!==($file = readdir($dir))) //on lit tout et on rè¢µpere tout les fichiers dans $file
		{ 

		if(!in_array($file, array('.','..','Thumbs.db','thumbs.db')))
			{ //on eleve le parent et le courant '. et ..'
			$compteur=$compteur+1;
			$page = $file; //sort l'extension du fichier
			$page = explode('.', $page);
			$nb = count($page);
			$nom_fichier = $page[0];
			for ($i = 1; $i < $nb-1; $i++)
				{
				$nom_fichier .= '.'.$page[$i];
				}
			if(isset($page[1]))
				{
				$ext_fichier = $page[$nb-1];
				//if(!is_file($file)) { $file = '/'.$file; }
				}
			else 
				{
				if(!is_file($file)) 
					{ 
					$file = '/'.$file;  //on rajoute un "/" devant les dossier pour qu'ils soient triè± au dè¡µt
					} 
				$ext_fichier = '';
				}
			
			if($ext_fichier != 'php' and $ext_fichier != 'html' and $ext_fichier != 'db') 
				{ //utile pour exclure certains types de fichiers ï¿½e pas lister
				array_push($fichier, $file);
				}
			
			// Test de récupération des valeurs EXIF
			
			$img=$repertoire.$file;
			if($exif = exif_read_data($img, EXIF, true)) // Si le fichier $img contient des infos Exif
				{
				$nom[$compteur]=$fichier[$compteur];
				$date[$compteur]=$exif['EXIF']['DateTimeOriginal'];
				}
			}
		}
	}

// fonction de tri des dates récupérées dans les EXIF

$nbfichier=sizeof($date); // Combien de photo avec EXIF ai-je ? 
// TODO : que fait-on des fichiers sans EXIF ??? 

$inversion=0;
do 
	{
	$inversion=0;
	for ($i=0 ; $i<$nbfichier-1 ; $i++)
		{	
		if ($date[$i]<$date[$i+1])
			{
			$tmp=$date[$i];
			$date[$i]=$date[$i+1];
			$date[$i+1]=$tmp;
			
			$tmp=$nom[$i];
			$nom[$i]=$nom[$i+1];
			$nom[$i+1]=$tmp;	
			
			$inversion=1;
			}	
		}
	} while($inversion);
// fin du tri

// Préparation du XML pour chaque fichier avec EXIF
for ($i=0 ; $i<$nbfichier ; $i++)
	{
	//$nomreduit=str_replace(array("0","1","2","3","4","5","6","7","8","9",".jpeg",".jpg"),"",$nom[$i]);
	$nomreduit=str_replace(array(".jpeg",".jpg",".JPEG",".JPG"),"",$nom[$i]);
	//$date[$i]=substr($date[$i],0, -9);
	echo "<image>\n\t<filename>".$nom[$i]."</filename>\n\t<caption>".$nomreduit." le ".$date[$i]."</caption>\n</image>\n";
	$tmpsortie=$tmpsortie."<image>\n\t<filename>".$nom[$i]."</filename>\n\t<caption>".$nomreduit."  le  ".$date[$i]."</caption>\n</image>\n";
	}

return $tmpsortie;
}
// Fin de la fonction 'listing'
///////////////////////////////////////////////////////////////////////////////

// Préparation du header du fichier XML
$sortie="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

// Balise SimpleViewerGallery avec info de taille et de couleur
$sortie=$sortie."<simpleviewergallery maxImageWidth=\"720\" maxImageHeight=\"570\" textColor=\"0x000000\" frameColor=\"0xA8908D\" frameWidth=\"0\" stagePadding=\"0\" navPadding=\"10\" thumbnailColumns=\"4\" thumbnailRows=\"5\" navPosition=\"left\" vAlign=\"center\" hAlign=\"center\" title=\"Bienvenue a Louise\" enableRightClickOpen=\"false\" backgroundImagePath=\"\" imagePath=\"\" thumbPath=\"\">\n";

// Préparation du XML des photos
$sortie=$sortie.listing($rep.'./images/'); //chemin du dossier

// Préparation du footer du fichier XML
$sortie=$sortie."</simpleviewergallery>";

// Ecriture du fichier XML
$fp=fopen($rep."/"."gallery.xml","w");
fwrite($fp,$sortie);
fclose($fp);

?>