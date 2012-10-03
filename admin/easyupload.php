<?
////////////////////////////////////////////////////////////////////////////////////////////////
// Préparation du script
////////////////////////////////////////////////////////////////////////////////////////////////
// Activer / Désactiver le rapport d'erreurs 
error_reporting(1);  

// Activer le rapport d'erreurs PHP       
//if ($_SERVER['SERVER_NAME'] == "localhost" )  
	error_reporting(E_ALL);

set_time_limit(0); //0 => Illimité 

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

//$rep=$_SERVER['DOCUMENT_ROOT'].$_GET['rep'];
$rep=$_SERVER['DOCUMENT_ROOT'];
$urlbase=$_SERVER['SERVER_NAME'];
$portbase=$_SERVER['SERVER_PORT'];

$rep_main=$rep;

$xmlgallery_array=array();

set_include_path(get_include_path().PATH_SEPARATOR.$base.'/include');

include 'functions.php';
//
////////////////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////
// ---- Verifier l'orientation d'une photo et corriger au besoin ----
function checkorientation ($nom_du_fichier, $rep, $rep_fichier, $url_fichier)
{
if($exif = exif_read_data($rep."/easyupload/".$nom_du_fichier)) // Si le fichier $img contient des infos Exif
{
$orientation=$exif['Orientation'];

    switch ($orientation) {
      case 2:
        //return $img->mirror();
        break;

      case 3:
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 180);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
        break;

      case 4:
        //return $img->rotate(180)->mirror();
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 180);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
        break;

      case 5:
        //return $img->rotate(90)->mirror();
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 270);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
        break;

      case 6:
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 270);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
        break;

      case 7:
        //return $img->rotate(-90)->mirror();
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 90);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
        break;

      case 8: {
        $img=rotate("http://".$url_fichier."/images/".$nom_du_fichier, 90);
	    imagejpeg($img,$rep_fichier."/images/".$nom_du_fichier);
	    imagedestroy($img);		
	    break;
		}
}
}
}

//////////////////////////////////////////////////////////////////////////////////////////
// ---- Fonction de listage des fichiers d'un repertoire et de création des copies ----
function listing($repertoire){
// TODO : Faire une fonction de suppression de fichier à partir d'une liste plutôt que de supprimer tout un repertoire
global $base;
global $urlbase;
global $portbase;
global $root;
global $self; 
global $gallery;
global $xmlgallery_array;
$fichier = array();
$nom = array();
$date = array();
$galleries = array();
$directories = array();
$tmpsortie="";
$urlrepertoire=$urlbase.":".$portbase."/".$self."/".$gallery;

$xmlgallery_array=array();
//echo "<br/> listing de ".$repertoire."/easyupload";

if (is_dir($repertoire."/easyupload")) // Test s'il s'agit d'un repertoire
	{
	$dir = opendir($repertoire."/easyupload"); //ouvre le repertoire courant d?gn?ar la variable
	$compteur=-1;
	while(false!==($file = readdir($dir)))
		{ //on lit tout et on recupere tout les fichiers dans $file

		if(!in_array($file, array('.','..','Thumbs.db','thumbs.db'))) //on enleve le parent et le courant '. et ..'
			{ 
			$compteur=$compteur+1;
			$page = $file; 
			$page = explode('.', $page); //sort l'extension du fichier
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
					$file = '/'.$file; 
					} //on rajoute un "/" devant les dossier pour qu'ils soient trie au debut
				$ext_fichier = '';
				}
				
			if($ext_fichier != 'php' and $ext_fichier != 'html' and $ext_fichier != 'db') 
				{ //utile pour exclure certains types de fichiers a ne pas lister
				// TODO : plutot que d'exclure une liste qui risque de s'etendre, il faudrait mieux inclure la liste des format voulu
				array_push($fichier, $file);
				}

		/* test - intégration de la date de prise de vue de la photo */

			$img=$repertoire."/easyupload/".$file;
			if($exif = exif_read_data($img)) // Si le fichier $img contient des infos Exif
				{
				$nom[$compteur]=$fichier[$compteur];
				$date[$compteur]=$exif['DateTimeOriginal'];

				$rep_temp=explode(":",$date[$compteur]);
				$nom['album'][$compteur]=$rep_temp[0];
				$nom['gallerie'][$compteur]=$rep_temp[1];
				$galleries[$compteur]=$rep_temp[0];  // on stocke les albums dans un tableau
				if (count($directories[$rep_temp[0]])>0)
					{
					if (!in_array($rep_temp[1],$directories[$rep_temp[0]]))
						$directories[$rep_temp[0]][]=$rep_temp[1];
					}
				else
					{
					$directories[$rep_temp[0]][]=$rep_temp[1];
					}
					//$directories[$rep_temp[0]][$compteur]=$rep_temp[1]; // et les galleries dans un autre tableau à double entrée
				//$directories['gallerie'][$compteur]=$rep_temp[1];
				}
				else
					{
					// TODO : que faire si une photo n'a pas d'info exif pour pouvoir le classer ?
					}
			}
		}
	}

$nbfichier=sizeof($date);
//print_r($nom);


/*
echo "Galleries : ";
print_r($galleries);
echo "Repertoires : ";
print_r($directories);

$galleries=array_unique($galleries);
$directories=array_unique($directories);

echo "Apres doublon :<br/>";
echo "Galleries : ";
print_r($galleries);
echo "Repertoires : ";
print_r($directories);
*/

for ($i=0 ; $i<sizeof($galleries) ; $i++) // on boucle sur la liste des albums correspondant aux photos à intégrer (extrait des exifs)
//for ($i=0 ; $i<$nbfichier ; $i++) // on boucle sur la liste des fichiers
{
//echo $nom[$i]." g : ".$galleries[$i]." rep : ".$directories[$i];
//echo "===>".strstr($repertoire."/".$galleries[$i],$root)."<br/>";
//echo "====>".str_replace($root,"",$repertoire."/".$galleries[$i]);
if (!file_exists($repertoire."/".$galleries[$i])) // Est-ce que l'album existe ? 
	{ // NON
	// on crée le repertoire des années
//	echo "on crée le repertoire ".$repertoire."/".$galleries[$i];

echo "album inexistant : ".$repertoire."/".$galleries[$i]; //album = année	
	$title=$galleries[$i];
	mkdir($repertoire."/".$galleries[$i], 0777); // On crée le repertoire
	// on crée un index.php vide dans le repertoire
//	$fp=fopen("./base/index2.php",'rb') or die("Fichier manquant");
//	$indexcontent=fread($fp, filesize("./base/index2.php"));
	$fp=fopen($base."/admin/base/index2.php",'rb') or die("Fichier ".$base."/admin/base/index2.php manquant en lecture"); // recupération du fichier de base
	$indexcontent=fread($fp, filesize($base."/admin/base/index2.php"));
//	$indexcontent="";
	$indexcontent=str_replace('@@REP@@',$gallery,$indexcontent); // remplacement des infomrations parametrables
	fclose($fp);
	$fp=fopen($repertoire."/".$galleries[$i]."/index.php",'wb') or die("Fichier ".$repertoire."/".$galleries[$i]."/index.php manquant en ecriture"); // copie dans le bon repertoire
	fwrite($fp,$indexcontent);
	fclose($fp);
	
	// ensuite on met à jour l'index global qui liste les années
    // Il faut mettre à jour le fichier index listant les galleries (dont celle-ci). Si il n'existe pas il faut le créer
	
	if (file_exists($repertoire."/index.php")) 
		{ // OUI
		//S'il existe on le met a jour
		// TODO prévoir que l'on mette à jour une année qui ne suis pas l'année précédente (ex: 2010, 2012 ouis 2011)
		$text=fopen($repertoire."/index.php",'a+') or die("Fichier ".$repertoire."/index.php manquant");
		$contents = '';
		while (!feof($text)) 
			{
			$contents .= fread($text, 8192);
			}
		$position=strrpos($contents,"<br/><br/>");
		$position=$position+strlen("<br/><br/>");
		
		$longeur=strlen($contents)-$position;
		$str1=substr($contents,0,$position)."\n<h2>";
		$str1.="<a href=\"".str_replace($root,"",$repertoire."/".$galleries[$i])."\">$title</a></h2>\n";
		$str1.=substr($contents,$position,$longeur);
		fclose($text);
		
		$text2=fopen($repertoire."/index.php",'w+') or die("Fichier ".$repertoire."/index.php manquant");
		fwrite($text2,$str1);
		fclose($text2);
		
		}
	//sinon on le crée
	else 
		{
		// TODO : hmm y a t'il besoin de cela ? quand est-ce que l'on crée l'index des années ? au moment de la création de la gallerie non ? 
		}

	}
else
	{
	// TODO : que fait-on si l'album existe ??? 
	}
} // on referme sur les albums

foreach($directories as $job_album => $job_album_array)
	{
	foreach($job_album_array as $job_dir)
		{
		if (!file_exists($repertoire."/".$job_album."/".$job_dir)) // test d'existance du repertoire du mois
			{ // il n'existe pas
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

			$title=$job_dir." - ".$tab_mois[$job_dir];
			$contenu_header="\n<a href=\"".str_replace($root,"",$repertoire."/".$job_album)."/".$job_dir."/\">".$tab_mois[$job_dir]."</a>";
			
			mkdir($repertoire."/".$job_album."/".$job_dir, 0777);
			
			// Ajout des repertoires des images
			mkdir($repertoire."/".$job_album."/".$job_dir."/images/", 0777);
			mkdir($repertoire."/".$job_album."/".$job_dir."/thumbs/", 0777);
			mkdir($repertoire."/".$job_album."/".$job_dir."/original/", 0777);
			
			// Ajout du repertoire SVCORE et de son contenu
			recurse_copy($base.'/admin/base/svcore',$repertoire."/".$job_album."/".$job_dir."/svcore");
			
			// Ajout de l'index du mois
			$file = $base.'/admin/base/index.php';
			copy($file, $repertoire."/".$job_album."/".$job_dir."/index.php");
			
			//ouverture en lecture et modification de l'index du mois
			$text=fopen($repertoire."/".$job_album."/".$job_dir."/index.php",'r') or die("Fichier index manquant en lecture");
			$contenu=file_get_contents($repertoire."/".$job_album."/".$job_dir."/index.php");
			$contenuMod=str_replace('<title>','<title>'.$title, $contenu);
			$contenuMod=str_replace('@@REP@@',$gallery,$contenuMod);
			fclose($text);
			$text2=fopen($repertoire."/".$job_album."/".$job_dir."/index.php",'w+') or die("Fichier index manquant en ecriture");
			fwrite($text2,$contenuMod);
			fclose($text2);

			// on s'occupe du header de l'année maintenant  (liste des mois existants)
			if (file_exists($repertoire."/".$job_album."/header.php")) 
				{
				//S'il existe on le met a jour
				// TODO prévoir que l'on mette à jour un mois qui ne suis pas le mois précédent (ex: Mai, puis juillet , puis juin)
				$text=fopen($repertoire."/".$job_album."/header.php",'a+') or die("Fichier header manquant en lecture");
				fwrite($text,$contenu_header);
				fclose($text);
				}
			//sinon on le crée
			else 
				{
				$text=fopen($repertoire."/".$job_album."/header.php",'w+') or die("Fichier header manquant en ecriture");
				fwrite($text,"<a href=\"..\">Retour</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$contenu_header);
				fclose($text);
				}
			// mise à  jour de l'index des mois de l'année
			if (file_exists($repertoire."/".$job_album."/index.php")) 
				{
				//S'il existe on le met a jour
				// TODO prévoir que l'on mette à jour un mois qui ne suis pas le mois précédent (ex: Mai, puis juillet , puis juin)
				$text=fopen($repertoire."/".$job_album."/index.php",'a+') or die("Fichier index manquant en lecture");
				$contents = '';
				while (!feof($text)) 
					{
					$contents .= fread($text, 8192);
					}
				$position=strrpos($contents,"<br/>");
				$position=$position+strlen("<br/>");
		
				$longeur=strlen($contents)-$position;
				$str1=substr($contents,0,$position)."\n<h2><a href=\"".str_replace($root,"",$repertoire."/".$job_album."/".$job_dir)."\">$title</a></h2>".substr($contents,$position,$longeur);
				fclose($text);
		
				$text2=fopen($repertoire."/".$job_album."/index.php",'w+') or die("Fichier index manquant en ecriture");
				fwrite($text2,$str1);
				fclose($text2);
				}
			//sinon on le crée
			else 
				{
		// TODO : hmm y a t'il besoin de cela ? quand est-ce que l'on crée l'index des années ? au moment de la création de la gallerie non ? 
				}

			}
		else
			{
			// TODO : que fait-on si le mois n'existe pas ??? 
			}
		}
	}

// fin de la gestion des repertoires d'années et de mois + index.php et header.php
// maintenant on s'occupe des fichiers. On met les images dans les bons repertoires.
if ($nbfichier>2) // TODO : là pour le moment j'ai bloqué le process à 2 photos par execution du script car sinon je passe en timeout
	$nbfichier=2;
	
for ($i=0 ; $i<$nbfichier ; $i++) // on boucle sur la liste des fichiers
{
copycoll($nom[$i], $repertoire."/easyupload/", $repertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i].'/original/'); // Copie dans le repertoire Original
//copycoll($nom[$i], $repertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i].'/original/', $repertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i].'/images/'); // Copie dans le repertoire Image
checkorientation($nom[$i], $repertoire, $repertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i], $urlrepertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i]); // Mise à jour de l'orientation de l'image

//sizeandcopy($nom[$i], $repertoire."/".$galleries[$i]."/".$directories[$i]);
sizeandcopy($nom[$i], $repertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i], $urlrepertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i]); // Mise à la bonne taille de la photo
echo "\t\t\t<li>$nom[$i] <img width=\"100px\" src=\"http://".$urlrepertoire."/".$nom['album'][$i]."/".$nom['gallerie'][$i]."/thumbs/".$nom[$i]."\">";
unlink($repertoire."/easyupload/".$nom[$i]); // suppression du fichier du repertoire easyupload

if (is_array($xmlgallery_array))
	{
	$xmlgallery_array[]="/".$nom['album'][$i]."/".$nom['gallerie'][$i];
	}
	
if (!in_array("/".$nom['album'][$i]."/".$nom['gallerie'][$i],$xmlgallery_array)) // TODO ; je n'ai pas compris l'interêt de cette condition
	{
	echo "pas dans le tableau<br/>";
	//array_push($xmlgallery_array,"/".$galleries[$i]."/".$directories[$i]);
	$xmlgallery_array[]="/".$nom['album'][$i]."/".$nom['gallerie'][$i];
	}
echo "</li>\n";
	
}

return $xmlgallery_array;
}
// Fin de la fonction 'listing'
////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////
// ---- Fonction de pre-traitement du fichier xml necessaire à l'affichage des photos d'un album donné en paramètre ----
function imgList($repertoire){

$fichier = array();
$nom = array();
$date = array();
$tmpsortie="";

if (is_dir($repertoire)){

$dir = opendir($repertoire); //ouvre le repertoire courant designe par la variable
$compteur=-1;
while(false!==($file = readdir($dir))){ //on lit tout et on recupere tous les fichiers dans $file

if(!in_array($file, array('.','..','Thumbs.db','thumbs.db'))){ //on eleve le parent et le courant '. et ..'
$compteur=$compteur+1;
$page = $file; //sort l'extension du fichier
$page = explode('.', $page);
$nb = count($page);
$nom_fichier = $page[0];
for ($i = 1; $i < $nb-1; $i++){
$nom_fichier .= '.'.$page[$i];
}
if(isset($page[1])){
$ext_fichier = $page[$nb-1];
//if(!is_file($file)) { $file = '/'.$file; }
}
else {
if(!is_file($file)) { $file = '/'.$file; } //on rajoute un "/" devant les dossier pour qu'ils soient tries au debut
$ext_fichier = '';
}

if($ext_fichier != 'php' and $ext_fichier != 'html' and $ext_fichier != 'db') { //utile pour exclure certains types de fichiers a ne pas lister
array_push($fichier, $file);
}

/* test */
$img=$repertoire.$file;
if($exif = exif_read_data($img)) // Si le fichier $img contient des infos Exif
{
$nom[$compteur]=$fichier[$compteur];
$date[$compteur]=$exif['DateTimeOriginal'];
}


}
}
}

/* tri */

$nbfichier=sizeof($date);

$inversion=0;
do {
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
/* fin tri */

for ($i=0 ; $i<$nbfichier ; $i++)
{
//$nomreduit=str_replace(array("0","1","2","3","4","5","6","7","8","9",".jpeg",".jpg"),"",$nom[$i]);
$nomreduit=str_replace(array(".jpeg",".jpg",".JPEG",".JPG"),"",$nom[$i]);
//$date[$i]=substr($date[$i],0, -9);
//echo "<image imageURL=\"images/".$nom[$i]."\" thumbURL=\"thumbs/".$nom[$i]."\">\n\t<caption>".$nomreduit." le ".$date[$i]."</caption>\n</image>\n";
$tmpsortie=$tmpsortie."<image imageURL=\"images/".$nom[$i]."\" thumbURL=\"thumbs/".$nom[$i]."\">\n\t<caption>".$nomreduit." le ".$date[$i]."</caption>\n</image>\n";
}

return $tmpsortie;
}

// Fin de la fonction 'imgList'
////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////
// -- Fonction qui efface tous les fichiers d'un repertoire donné
function unlinkrep($repertoire) {
$fichier = array();
$nom = array();
$date = array();
$tmpsortie="";

if (is_dir($repertoire)){    // verification qu'il s'agit bien d'un repertoire

$dir = opendir($repertoire); //ouvre le repertoire courant d?gn?ar la variable
$compteur=-1;
while(false!==($file = readdir($dir))){ //on lit tout et on r?pere tout les fichiers dans $file

	if(!in_array($file, array('.','..','Thumbs.db','thumbs.db'))){ //on eleve le parent et le courant '. et ..'
		$compteur=$compteur+1;
		$page = $file; //sort l'extension du fichier
		$page = explode('.', $page);
		$nb = count($page);
		$nom_fichier = $page[0];
		
		for ($i = 1; $i < $nb-1; $i++){
			$nom_fichier .= '.'.$page[$i];
		}
		
		if(isset($page[1])){
			$ext_fichier = $page[$nb-1];			
		}
		else {
			if(!is_file($file)) { 
			array_push($fichier, $file);
			$nom[$compteur]=$fichier[$compteur];
			} //on rajoute un "/" devant les dossier pour qu'ils soient tri?au d?t
			$ext_fichier = '';
		}

		if($ext_fichier != 'php' and $ext_fichier != 'html' and $ext_fichier != 'db' and $ext_fichier != 'bak' and $ext_fichier != 'txt') { //utile pour exclure certains types de fichiers ?e pas lister
			array_push($fichier, $file);
		$nom[$compteur]=$fichier[$compteur];
		}

	}
}
}

$nbfichier=sizeof($nom);
for ($i=0 ; $i<$nbfichier ; $i++)
{
	unlink($repertoire.$nom[$i]);
}

}
// Fin de la fonction 'unlinkrep'
///////////////////// 

//////////////////////////////////////////////////////////////
// -- Fonction qui génère le fichier xml necessaire à l'affichage d'une gallerie
function generatexmlG($xmlg_rep,$gallery_name) {
$param=getParameters($gallery_name); 
//echo "generation xml ";

//print_r($param);
$sortie="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$sortie=$sortie."<simpleviewergallery showOpenButton=\"FALSE\" maxImageWidth=\"720\" maxImageHeight=\"570\" textColor=\"0x000000\" frameColor=\"0xA8908D\" frameWidth=\"0\" stagePadding=\"0\" navPadding=\"10\" thumbnailColumns=\"4\" thumbnailRows=\"5\" navPosition=\"left\" vAlign=\"center\" hAlign=\"center\" title=\"".$param[2]."\" enableRightClickOpen=\"false\" backgroundImagePath=\"\" imagePath=\"\" thumbPath=\"\">\n";
//$sortie=$sortie.imgList($xmlg_rep.'/images/'); //chemin du dossier
$sortie=$sortie.imgList($xmlg_rep.'/original/'); //chemin du dossier
$sortie=$sortie."</simpleviewergallery>";

$file=$xmlg_rep."/"."gallery.xml";

$fp=fopen($file,"w");
fwrite($fp,$sortie);
fclose($fp);

//echo $file."<br/>";
}
// Fin de la fonction 'generatexmlG'
///////////////////// 

////////////////////////////////////////////////////////////////////////////////
//// Début du script ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$g=getGalleries(); // récupère la liste des galleries inscrites dans le fichier param.xml

foreach ($g as $gallery) { // pour chaque galleries on met à jour
//	echo "Mise à jour de l'album :".$gallery."<br/>";
	$rep=$base."/".$gallery;
	//$rep=$urlbase."/".$gallery;
//	echo "gallery : ".$gallery." (".$rep.")<br/>";
	$xmlgallery_array=listing($rep); // verifie la création (et crée au besoin) des bons repertoires et crée les bons fichiers avec les bonnes tailles
//$xmlgallery_array[]="/2012/01";
//$xmlgallery_array[]="/2012/02";
	$xmlgallery_array = array_unique($xmlgallery_array); // supprime les doublons du tableau des repertoires de la gallerie (mois et années)
//print_r($xmlgallery_array);
	$liste_gallerie_maj="";

	foreach ($xmlgallery_array as $xmlg) {  // on met à jour tous les gallery.xml
		generatexmlG($rep.$xmlg,$gallery);   // on génére le fichier xml qui supporte la gallerie de l'album
		$liste_gallerie_maj.="\n\r".$urlbase."/".$gallery.$xmlg;
	}
	//unlinkrep($rep."/easyupload/"); // TODO a enlever a la fin
	
	// gestion des images qui se trouverai dans une boite e-mail
	if ($liste_gallerie_maj)
	{
	$account="beckfanelli.listen";
	$password="beckfanelli0610";

	//Tout d'abord, on ouvre une boite mail
	$mail = imap_open("{pop.gmail.com:995/pop3/ssl/novalidate-cert}INBOX",$account,$password);
	//initialisation du header
	$headers  = "MIME-Version: 1.0 \n";
	$headers .='Content-Transfer-Encoding: 8bit';
	$headers .="Content-type: text/html; charset=utf-8 \n";
	$headers .='From: "Beck Fanelli" <beckfanelli.listen@gmail.com>  \n';
	
	// initialisation du contenu du mail
	$liste_gallerie_maj="Bonjour à tous,
	
Le site des filles à été mis à jour avec de nouvelles photos :)
	
Pour les voir vous pouvez cliquer directement sur les liens de la liste :\n\r".$liste_gallerie_maj."
Bonne journée.

Gaëlle & David";
	
	//Envoi du message
//	mail ("vieilfrance@gmail.com", "maj du site des filles", $liste_gallerie_maj, $headers);
	//Fermeture de la boite mail
	imap_close($mail);
	}
}

?>