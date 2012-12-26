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
// -- Fonction qui génère le fichier xml necessaire à l'affichage d'une gallerie
function generatexmlG($xmlg_rep,$gallery_name) {
$param=getParameters($gallery_name); 

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

$rep2=$base."/".$_GET['rep'];

generatexmlG($rep2,$name); 


?>