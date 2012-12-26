<?

$xml="";
$xml_flag=0;
$dir="";
$tag="";
$xml_title="";
$xml_directory="";
$xml_name="";
$xml_caption="";
$xml_css="";
$xml_script="";
$names=array();
$galleries=array();

// ---- Fonction faire de la copie recursive ----

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 
//
////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ---- Fonction de copie d'un fichier d'un répertoire à un autre ----
function copycoll ($nom_du_fichier, $chemin_original, $chemin_destination)
{
//global $rep;
copy ($chemin_original.$nom_du_fichier , $chemin_destination.$nom_du_fichier);
}
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ---- Fonction permettant de réaliser un mirroir d'une image ----

function _mirrorImage ( $imgsrc )
{
    $width = imagesx ( $imgsrc );
    $height = imagesy ( $imgsrc );

    $src_x = $width -1;
    $src_y = 0;
    $src_width = -$width;
    $src_height = $height;

    $imgdest = imagecreatetruecolor ( $width, $height );

    if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height ) )
    {
        return $imgdest;
    }

    return $imgsrc;
}
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ---- Fonction pour retailler et copier une photo ----
function sizeandcopy ($nom_du_fichier, $rep_fichier, $url_fichier)
{
//global $rep;

$chemin_destination = $url_fichier.'/original/'; // chemin de l'image d'origine via http
$chemin_destination_rep = $rep_fichier.'/original/'; // chemin de l'image d'origine via adresse physique
$chemin_destination_output = $rep_fichier.'/images/'; // chemin de destination des images
$chemin_destination_mini_output = $rep_fichier.'/thumbs/';// chemin de destination des petites images 

$scale=0.9;
$input="http://".$chemin_destination.$nom_du_fichier;
echo $input;
$original = @imagecreatefromjpeg($input);
//echo $input;
//$original = ImageCreateFromString($input_jpeg->getBytes());
if (!$original)
	{
	echo "probleme d'ouverture du fichier en http ";
	$input=$chemin_destination_rep.$nom_du_fichier;
	$original = @imagecreatefromjpeg($input);
	if (!$original)
		echo "probleme d'ouverture du fichier selon tous les modes possibles";
	}


$original_w = ImagesX($original);
$original_h = ImagesY($original);

//echo "w: ".$original_w." h: ".$original_h;

///// Copie image 720 
if ( $original_w > $original_h)
  {
  $scaled_w=720;
  $scaled_h=round(($original_h/$original_w)*$scaled_w);
  }
else
  {
  $scaled_h=720;
  $scaled_w=round(($original_w/$original_h)*$scaled_h);
  }

/* Now create the scaled image. */

$scaled = ImageCreateTrueColor($scaled_w, $scaled_h);
ImageCopyResampled($scaled, $original,
                   0, 0, // dst (x,y) 
                   0, 0, // src (x,y) 
                   $scaled_w, $scaled_h,
                   $original_w, $original_h);

echo "<br/>";
echo $chemin_destination_output.$nom_du_fichier;
imagejpeg($scaled,$chemin_destination_output.$nom_du_fichier);

imagedestroy($scaled);

///// Copie image 172

if ( $original_w > $original_h)
  {
  $scaled_w=172;
  $scaled_h=round(($original_h/$original_w)*$scaled_w);
  }
else
  {
  $scaled_h=172;
  $scaled_w=round(($original_w/$original_h)*$scaled_h);
  }


/* Now create the scaled image. */

$scaled = ImageCreateTrueColor($scaled_w, $scaled_h);
ImageCopyResampled($scaled, $original,
                   0, 0, // dst (x,y) 
                   0, 0, // src (x,y) 
                   $scaled_w, $scaled_h,
                   $original_w, $original_h);

echo "<br/>";
echo $chemin_destination_mini_output.$nom_du_fichier;
imagejpeg($scaled,$chemin_destination_mini_output.$nom_du_fichier);

imagedestroy($original);
imagedestroy($scaled);
}
// ---- Fin de la fonction de retailler et de la copie ----
//////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ---- Changer l'orientation d'une image ----
function rotate ($url_read, $degree)
{
	  $original = @imagecreatefromjpeg($url_read);
	  $rotated = imagerotate($original, $degree, 0);
	  imagedestroy($original);
	  return ($rotated);
}
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ---- Génération de l'index des années
function genereAlbumIndex($name,$base,$dossier,$rep,$self){ // la génération de l'index des années
// nom de la gallery / repertoire de base du site (avec les ss rep) / liste des repertoire des années / nom de la gallery (avec les ss rep) / les ss rep seuls

	$directoryList="";
	$path=$base."/".$name;
	arsort($dossier);
	
	foreach ($dossier as $d) {
	//$directoryList.="\n<h2><a href=\"".$self."/".$name."/".$d."\">$d</a></h2>";
	$directoryList.="\n<h2><a href=\"./".$d."\">$d</a></h2>";
	}
	
	$fp=fopen($base."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$base."/admin/base/index_newgallery.php manquant");
	$indexcontent=fread($fp, filesize($base."/admin/base/index_newgallery.php"));
	fclose($fp);
	$indexcontent=str_replace('@@REP@@',$name,$indexcontent);
	$fp=fopen($base."/".$name."/index.php",'wb') or die("Fichier index manquant");
	fwrite($fp,$indexcontent);
	fclose($fp);
	
	$text=fopen($path."/index.php",'a+') or die("Fichier index manquant en lecture");
	$contents = '';
	while (!feof($text)) {
	  $contents .= fread($text, 8192);
	}
	$position=strrpos($contents,"<br/>");
	$position=$position+strlen("<br/>");

	$longeur=strlen($contents)-$position;
	$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
	fclose($text);

	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$str1);
	fclose($text2);
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// ---- Génération de l'index des mois
function genereSubAlbumIndex($name,$base,$dossier,$rep,$self){ // la génération de l'index des mois
	$directoryList="";
	$path=$base.$rep;
	asort($dossier);
	
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
	
	foreach ($dossier as $d) {
//	$directoryList.="\n<h2><a href=\"".$self."/".substr($rep,1)."/".$d."\">".$d." - ".$tab_mois[$d]."</a></h2>";
	$directoryList.="\n<h2><a href=\"./".$d."\">".$d." - ".$tab_mois[$d]."</a></h2>";
	$fp=fopen($base."/admin/base/index2.php",'rb') or die("Fichier ".$base."/admin/base/index2.php manquant");
	$indexcontent=fread($fp, filesize($base."/admin/base/index2.php"));
	fclose($fp);
	$indexcontent=str_replace('@@REP@@',$name,$indexcontent);
	$fp=fopen($path."/index.php",'wb') or die("Fichier index manquant");
	fwrite($fp,$indexcontent);
	fclose($fp);

	$text=fopen($path."/index.php",'a+') or die("Fichier index manquant en lecture");
	$contents = '';
	while (!feof($text)) {
	  $contents .= fread($text, 8192);
	}
	$position=strrpos($contents,"<br/>");
	$position=$position+strlen("<br/>");

	$longeur=strlen($contents)-$position;
	$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
	fclose($text);

	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$str1);
	fclose($text2);
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// ---- Génération du header des mois
function genereHeader($name,$base,$dossier,$rep,$self){ // la génération de l'index des mois
	$directoryList="";
	$path=$base.$rep;
	asort($dossier);
	
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
	
	foreach ($dossier as $d) {
		$directoryList.="\n<a href=\"../".$d."/\">".$tab_mois[$d]."</a>";
	}
	$text=fopen($path."/header.php",'w+') or die("Fichier header manquant en ecriture");
	fwrite($text,"<a href=\"..\">Retour</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$directoryList);
	fclose($text);
}
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
// ---- Génération de l'index d'une gallerie
function genereGaleryIndex($name,$base,$dossier,$rep,$self) { // la génération de l'index de la gallerie
	$file=$base.'/admin/base/index.php';
	$path=$base.$rep;

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

	$d=$dossier;
	$title=$d." - ".$tab_mois[$d];
echo $d;
	copy($file, $path."/index.php");

	//ouverture en lecture et modification du fichier index du mois
	$text=fopen($path."/index.php",'r') or die("Fichier index manquant en lecture");
	$contenu=file_get_contents($path."/index.php");
	$contenuMod=str_replace('<title>','<title>'.$title, $contenu);
	$contenuMod=str_replace('@@REP@@',$name,$contenuMod);
	fclose($text);

	//ouverture en écriture
	$text2=fopen($path."/index.php",'w+') or die("Fichier index manquant en ecriture");
	fwrite($text2,$contenuMod);
	fclose($text2);
}
///////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////
// ---- Lister les dossiers d'un repertoire
/////////////////////////////////////////////////////////////////////////////////////////////////
function listeDirectory($dir) {
	$dossier= array(); // on déclare le tableau contenant le nom des dossiers
	$unwanted= array('.','..','images','original','thumbs','admin','contact','svcore','easyupload','.git'); // on déclare le nom des repertoires qui devront être exclus

	$directory = opendir($dir) or die('Erreur de listage : le répertoire n\'existe pas'); // on ouvre le contenu du dossier courant
	while($element = readdir($directory)) 
		{
		if (!in_array($element,$unwanted))
			{
			if (is_dir($dir."/".$element)) 
				{
				$dossier[] = $element;
				}
			}
		}
	closedir($directory);
	return $dossier;
}
/////////////////////////////////////////////////////////////////////////////////////////////////

function startElement_Full($parser, $name, $attrs)
{
global $xml;
global $xml_flag;
global $dir;
global $tag;
global $xml_directory;
global $names;
global $galleries;

if ($name=="GALLERY")
	{
	list($key, $val) = each($attrs);
	$galleries[]=$val;
	}
}

function startElement($parser, $name, $attrs)
{
global $xml;
global $xml_flag;
global $dir;
global $tag;
global $xml_directory;
global $names;

list($key, $val) = each($attrs);

if ($name=="GALLERY")
	{
//	if ($val==$dir)
	if (in_array ($val, $names))
		{
		$xml_directory=$xml_directory.$val;
		if ($xml_flag==0)
			{
			$xml=$xml.$name;
			$tag=$name;
			$xml_flag=1;
			}
		}
	}
else
	{
	if ($xml_flag==1)
		{
		$xml=$xml.$name;
		$tag=$name;
		}
	}
}


function endElement_Full($parser, $name)
{
}

function endElement($parser, $name)
{
global $xml;
global $xml_flag;
global $tag;

if ($xml_flag==1)
	{
	$xml=$xml.$name;
	if ($name=="GALLERY")
		{
		//echo "pof";
		$xml_flag=0;
		$tag="";
		}
	}
}

function dataElement($parser, $name)
{
global $xml;
global $xml_flag;
global $tag;
global $xml_title;
global $xml_name;
global $xml_caption;
global $xml_css;
global $xml_script;

if ($xml_flag==1)
	{
	$name = utf8_decode($name);
	if ($tag=="TITLE")
		{
		$xml_title=$xml_title.$name;
		}
	if ($tag=="NAME")
		{
		$xml_name=$xml_name.$name;
		}
	if ($tag=="CAPTION")
		{
		$xml_caption=$xml_caption.$name;
		}
	if ($tag=="CSS")
		{
		$xml_css=$xml_css.$name;
		}
	if ($tag=="SCRIPT")
		{
		$xml_script=$xml_script.$name;
		}
	$xml=$xml.$name;
	}
}

function getGalleries()
{
global $galleries;
global $base;

$xml_parseur=xml_parser_create();
xml_parser_set_option($xml_parseur, XML_OPTION_CASE_FOLDING, 1);
xml_set_element_handler($xml_parseur, "startElement_Full" , "endElement_Full");

if (file_exists($base."/param/param.xml")) 
{ 
$fp = fopen($base."/param/param.xml", "r") or die ("Fichier de paramètrage introuvable.");
while ($fdata = fread($fp,2048))
	{
	XML_parse($xml_parseur, $fdata , feof($fp)) or die ("Probleme de traitement du fichier de paramètrage");
	}
xml_parser_free($xml_parseur);
}
return $galleries;
}

function getParameters($rep)
{
global $xml;
global $xml_flag;
global $dir;
global $xml_directory;
global $xml_name;
global $xml_title;
global $xml_caption;
global $xml_css;
global $xml_script;
global $names;
global $base;

$xml_directory ="";
$xml_name ="";
$xml_title ="";
$xml_caption ="";
$xml_css ="";
$xml_script ="";

$dir=$rep;
$parameters=array();

$names=explode("/",$dir);

$xml_parseur=xml_parser_create();
xml_parser_set_option($xml_parseur, XML_OPTION_CASE_FOLDING, 1);
xml_set_element_handler($xml_parseur, "startElement", "endElement");
xml_set_character_data_handler($xml_parseur, "dataElement");

$fp = fopen($base."/param/param.xml", "r") or die ("Fichier de paramètrage introuvable.");
while ($fdata = fread($fp,2048))
	{
	XML_parse($xml_parseur, $fdata , feof($fp)) or die ("Probleme de traitement du fichier de paramètrage");
	}
xml_parser_free($xml_parseur);

$parameters[0]=$xml_directory;
$parameters[1]=$xml_name;
$parameters[2]=$xml_title;
$parameters[3]=$xml_caption;
$parameters[4]=$xml_css;
$parameters[5]=$xml_script;

return $parameters;
}

function generateXMLGalleries () {
$sortie="<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
$sortie.="<galleries>";
$sortie.="</galleries>";

return $sortie;
}

function generateXMLGallery ($name_g,$title_g,$caption_g,$css_g,$script_g) {
$sortie="<gallery directory=\"".$name_g."\">";
$sortie.="<name>".$name_g."</name>";
$sortie.="<title>".$title_g."</title>";
$sortie.="<caption>".$caption_g."</caption>";
$sortie.="<css>".$css_g."</css>";
$sortie.="<script>".$script_g."</script>";
$sortie.="</gallery>";

return $sortie;
}

?>