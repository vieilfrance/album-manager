<?php

class FileManager
{
    public $options;
	protected $xmlvars = array (
		'xml_flag' => 0,
		'tag' => null,
		'current_album' => null
		);
	function __construct($options = null, $initialize = true) {
		$this->options = array(
			'full_url' => $this->get_full_url().'/',
			'base_path' => $this->get_base_path(),
			'param_path' => $this->get_base_path()."/param/",
			'param_file' => "param.xml",
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
			'debug' => null,
			'files' => array(
				'albumname' => null,
				'filecount' =>0,
				'files' => array()
				),
			'albums' => array(
				'albumcount' =>0,
				'albums' => array()
				),
			'month' => array( 
				'01' => 'Janvier',
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
				'12' => 'Decembre')
		);
		
        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
		
		if ($initialize) {
		$this->initialize();
		}
	}

    protected function initialize() {
		$this->load();
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }
	
    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_base_path() { // TODO : trouver le moyen d'envlever le slash de fin qui fait chier
		$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
		$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
		$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
		$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
		return $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé
	}

	protected function startElement_Full($parser, $name, $attrs)
	{
	list($key, $val) = each($attrs);
	if ($name=="GALLERY")
		{
		$this->xmlvars['current_album'] = null;
		$this->xmlvars['current_album']['name']=$val;
		$this->xmlvars['tag']=$name;
		$this->options['albums']['albumcount'] +=1;
		if ($this->xmlvars['xml_flag']==0)
			{
			$this->xmlvars['xml_flag']=1;
			}
		}
	else
		{
		if ($this->xmlvars['xml_flag']==1)
			{
			$this->xmlvars['tag']=$name;
			}
		}
	}
	
	protected function endElement_Full($parser, $name) {
	if ($this->xmlvars['xml_flag']==1)
		{
		if ($name=="GALLERY")
			{
			array_push($this->options['albums']['albums'] , $this->xmlvars['current_album']);
			$this->xmlvars['xml_flag']=0;
			$this->xmlvars['tag']="";
		}
		}
	}

	protected function dataElement($parser, $name) {
	if ($this->xmlvars['xml_flag']==1)
		{
		$name = utf8_decode($name);
		if ($this->xmlvars['tag']=="TITLE")
			{
			$this->xmlvars['current_album']['title']=$name;
			}
		if ($this->xmlvars['tag']=="NAME")
			{
			$this->xmlvars['current_album']['name']=$name;
			}
		if ($this->xmlvars['tag']=="CAPTION")
			{
			$this->xmlvars['current_album']['caption']=$name;
			}
		if ($this->xmlvars['tag']=="CSS")
			{
			$this->xmlvars['current_album']['css']=$name;
			}
		if ($this->xmlvars['tag']=="SCRIPT")
			{
			$this->xmlvars['current_album']['script']=$name;
			}
		}
	}

	protected function load() {
		$xml_parseur=xml_parser_create();
		xml_set_object($xml_parseur, $this);
		xml_parser_set_option($xml_parseur, XML_OPTION_CASE_FOLDING, 1);
		xml_set_element_handler($xml_parseur, 'startElement_Full' , 'endElement_Full');
		xml_set_character_data_handler($xml_parseur, "dataElement");
		
		if (!file_exists($this->options['param_path'].$this->options['param_file'])) 
			return false ;
		$fp = fopen($this->options['param_path'].$this->options['param_file'], "r") or die ("Fichier de paramètrage introuvable.");
		while ($fdata = fread($fp,2048))
			{
			XML_parse($xml_parseur, $fdata , feof($fp)) or die ("Probleme de traitement du fichier de paramètrage");
			}
		xml_parser_free($xml_parseur);

		return true ;
	}

    protected function header($str) {
        header($str);
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    protected function body($str) {
        echo $str;
    }
	
    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }
	
	protected function genere_response($reponse) {
		$json=json_encode($reponse);
		$this->head();
		$this->body($json);
		return $reponse;
	}

	protected function exists($albumName) {
	$exist = False ;
	for ($index=0; $index<$this->options['albums']['albumcount']; $index++)
		{
		if ($this->options['albums']['albums'][$index]['name'] == $albumName)
			{
			$exist = True;
			break;
			}
		}
	return $exist;
	}
	
	public function get($print_response = true) {
		$filelist=null;
		if ($print_response)
			{
			if (isset($_GET['albumname'] ))
				{
				if ($this->exists($_GET['albumname']))
					{
					$this->options['files']['albumname']=$_GET['albumname'];
					$filelist=$this->easyuploadFileList($this->options['files']['albumname']);
					$filelist=$this->filter($filelist);
					if ($filelist)
						{
						$this->options['files']['files']=$filelist;
						$this->options['files']['filecount']=count($filelist);
						}
					return $this->genere_response($this->options['files']);
					}
				}
			}
		return false;
	}

	protected function filter($fileArray) {
	$filteredFileArray=array();

	foreach($fileArray as $file)
		{
		$extension=$this->extractExtension($file['file']);
		if($extension != 'php' and $extension != 'html' and $extension != 'db' and $extension != 'png' ) // on filtre aussi les png pour le moment
			{ //utile pour exclure certains types de fichiers a ne pas lister
			// TODO : plutot que d'exclure une liste qui risque de s'etendre, il faudrait mieux inclure la liste des format voulu
			array_push($filteredFileArray, $file);
			}
		}
	return $filteredFileArray;
	}
	
	protected function extractExtension($file) {
	$filename=null;
	$extension=null;
	$fileparts=null;
	$fileparts = explode('.', $file); //sort l'extension du fichier
	$filename = $fileparts[0];
		for ($i = 1; $i < count($fileparts)-1; $i++) // si eventuellement le nom de fichier est composé de '.' avant l'extension
			{
			$filename .= '.'.$fileparts[$i];
			}
		if(isset($fileparts[count($fileparts)-1]))
			{
			$extension = $fileparts[count($fileparts)-1];
			}
	return $extension;
	}
	
	protected function easyuploadFileList($albumName) {
	$fileList = array() ;
	$file = null;
	
	if (is_dir($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload")) // Test s'il s'agit d'un repertoire
		{
		$dir = opendir($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload"); //ouvre le repertoire courant d?gn?ar la variable
		while(false!==($file = readdir($dir)))
			{
			if (is_dir($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload/".$file) == false) // on filtre les repertoire - hack : il faut mettre le full path sinon le test ne fonctionne pas
				{
				if(!in_array($file, array('.','..','Thumbs.db','thumbs.db')) ) //on enleve le parent et le courant '. et ..' il faudra le mettre en param
					{
					//echo $this->options['base_path']."/".$this->options['files']['albumname']."/easyupload";
					//echo $file." ";
					array_push($fileList , array("file"=>$file));
					}
				}
			}
		}
	return $fileList;
	}
	
	protected function getExifs($filename) {
	$exifs=array();
	if ($fileExif=exif_read_data($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload/".$filename)) {
		$fileExplodeExif=explode(":",$fileExif['DateTimeOriginal']);
		$exifs['year']=$fileExplodeExif[0];
		$exifs['month']=$fileExplodeExif[1];
		}
	return $exifs;
	}
	
	protected function pickaFile() {
	$file=array();
	$fileList=$this->easyuploadFileList($this->options['files']['albumname']);
	$fileList=$this->filter($fileList);
	if ($fileList)
		{
		$this->options['files']['files']=$fileList;
		$this->options['files']['filecount']=count($fileList);
		$fileArray=array_pop($fileList);
		$file['filename']=$fileArray['file'];
		$exifData=$this->getExifs($file['filename']);
		if (isset($exifData['year'])) $file['year']=$exifData['year'];
		if (isset($exifData['month'])) $file['month']=$exifData['month'];
		}
	return $file;
	}
	
	protected function listDirectories($path) {
	$list = array();
	$unwanted= array('.','..','images','original','thumbs','admin','contact','svcore','easyupload','.git'); // on déclare le nom des repertoires qui devront être exclus

	$directory = opendir($path) or die('Erreur de listage : le répertoire n\'existe pas'); // on ouvre le contenu du dossier courant
	while($element = readdir($directory)) 
		{
		if (!in_array($element,$unwanted))
			{
			if (is_dir($path."/".$element)) 
				array_push($list , $element);
			}
		}
	closedir($directory);
	return $list;
	}
	
	protected function updateTopGalleryIndex($directoryArray) {
		$directoryList="";
		arsort($directoryArray);
		foreach ($directoryArray as $d)
			$directoryList.="\n<h2><a href=\"./".$d."\">$d</a></h2>";
		
		// ajout de l'index des années (topgallery)
		$fp=fopen($this->options['base_path']."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$this->options['base_path']."/admin/base/index_newgallery.php manquant");
		$indexcontent=fread($fp, filesize($this->options['base_path']."/admin/base/index_newgallery.php"));
		fclose($fp);
		$indexcontent=str_replace('@@REP@@',$this->options['files']['albumname'],$indexcontent);
		$fp=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/index.php",'wb') or die("Fichier index manquant");
		fwrite($fp,$indexcontent);
		fclose($fp);

		// Preparation de la mise à jour de l'index des années
		$text=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/index.php",'a+') or die("Fichier index manquant en lecture");
		$contents = "";
		while (!feof($text)) {
		  $contents .= fread($text, 8192);
		}
		$position=strrpos($contents,"<br/>");
		$position=$position+strlen("<br/>");

		$longeur=strlen($contents)-$position;
		$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
		fclose($text);

		// Mise à jour de l'index des années
		$text2=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/index.php",'w+') or die("Fichier index manquant en ecriture");
		fwrite($text2,$str1);
		fclose($text2);	
	}
	
	protected function addTopGallery($topGallery) {
		mkdir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery, 0777); // on crée le repertoire de l'année
		// on ajoute l'index des galleries par avance
		$fp=fopen($this->options['base_path']."/admin/base/index2.php",'rb') or die("Fichier ".$this->options['base_path']."/admin/base/index2.php manquant en lecture"); // recupération du fichier de base
		$indexcontent=fread($fp, filesize($this->options['base_path']."/admin/base/index2.php"));
		$indexcontent=str_replace('@@REP@@',$this->options['files']['albumname'],$indexcontent); // remplacement des infomrations parametrables
		fclose($fp);
		$fp=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/index.php",'wb') or die("Fichier ".$this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/index.php manquant en ecriture"); // copie dans le bon repertoire
		fwrite($fp,$indexcontent);
		fclose($fp);
		}
	
	protected function checkTopGallery($topGallery) {
		if (!file_exists($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery)) {
			$this->addTopGallery($topGallery);
		}
		$directoryArray=$this->listDirectories($this->options['base_path']."/".$this->options['files']['albumname']); // liste le repertoire des années
		$this->updateTopGalleryIndex($directoryArray);
		}

	protected function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
	} 
	
	protected function updateHeader($topGallery, $gallery, $directoryArray) {
		$directoryList="";
		foreach ($directoryArray as $d) {
			$directoryList.="\n<a href=\"../".$d."/\">".$this->options['month'][$d]."</a>";
		}
		$text=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/header.php",'w+') or die("Fichier header manquant en ecriture");
		fwrite($text,"<a href=\"..\">Retour</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$directoryList);
		fclose($text);
	}
	
	protected function updateGalleryIndex($topGallery, $gallery, $directoryArray) {
		$title=$gallery." - ".$this->options['month'][$gallery];
		$headerContent="\n<a href=\"../".$gallery."/\">".$this->options['month'][$gallery]."</a>";
		$directoryList="";
		
		foreach ($directoryArray as $d)
			$directoryList.="\n<h2><a href=\"./".$d."\">".$d." - ".$this->options['month'][$d]."</a></h2>";
		
		//ouverture en lecture et modification de l'index du mois
		$text=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/index.php",'r') or die("Fichier index manquant en lecture");
		$contenu=file_get_contents($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/index.php");
		fclose($text);
		if (strstr($contenu,'@@REP@'))
			{
			$contenuMod=str_replace('<title>','<title>'.$title, $contenu);
			$contenuMod=str_replace('@@REP@@',$this->options['files']['albumname'],$contenuMod);
			
			//ouverture en écriture			
			$text2=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/index.php",'w+') or die("Fichier index manquant en ecriture");
			fwrite($text2,$contenuMod);
			fclose($text2);
			}		
			
		// il manque la génération de l'index qui liste les mois		
		$fp=fopen($this->options['base_path']."/admin/base/index2.php",'rb') or die("Fichier ".$this->options['base_path']."/admin/base/index2.php manquant");
		$indexcontent=fread($fp, filesize($this->options['base_path']."/admin/base/index2.php"));
		fclose($fp);
		
		$indexcontent=str_replace('@@REP@@',$this->options['files']['albumname'],$indexcontent);
		$fp=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/index.php",'wb') or die("Fichier index manquant");
		fwrite($fp,$indexcontent);
		fclose($fp);

		$text=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/index.php",'a+') or die("Fichier index manquant en lecture");
		$contents = '';
		while (!feof($text)) {
			$contents .= fread($text, 8192);
			}
		$position=strrpos($contents,"<br/>");
		$position=$position+strlen("<br/>");

		$longeur=strlen($contents)-$position;
		$str1=substr($contents,0,$position).$directoryList.substr($contents,$position,$longeur);
		fclose($text);

		$text2=fopen($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/index.php",'w+') or die("Fichier index manquant en ecriture");
		fwrite($text2,$str1);
		fclose($text2);	
	}
		
	protected function addGallery($topGallery,$gallery) {
		mkdir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery, 0777);
		
		// Ajout des repertoires des images
		mkdir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/images/", 0777);
		mkdir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/thumbs/", 0777);
		mkdir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/original/", 0777);
		
		// Ajout du repertoire SVCORE et de son contenu
		$this->recurse_copy($this->options['base_path'].'/admin/base/svcore',$this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/svcore");

		// Ajout de l'index du mois
		copy($this->options['base_path'].'/admin/base/index.php', $this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/index.php");
		}
		
	protected function checkGallery($topGallery, $gallery) {
		if (!file_exists($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery)) {// test d'existance du repertoire du mois
			$this->addGallery($topGallery,$gallery);
		}
		$directoryArray=$this->listDirectories($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery);
		arsort($directoryArray);
		$this->updateGalleryIndex($topGallery, $gallery, $directoryArray);
		$this->updateHeader($topGallery, $gallery, $directoryArray);
		}
	
	protected function streamFile($file) {
		$input=$this->options['base_path']."/".$this->options['files']['albumname']."/".$file['year']."/".$file['month']."/original/".$file['filename'];
		$original = @imagecreatefromjpeg($input);
		return $original;
	}
	
	protected function resizeAndCopy($originalStream,$file, $size,$target) {
		$original_w = ImagesX($originalStream);
		$original_h = ImagesY($originalStream);
		
		if ( $original_w > $original_h)
		  {
		  $scaled_w=$size;
		  $scaled_h=round(($original_h/$original_w)*$scaled_w);
		  }
		else
		  {
		  $scaled_h=$size;
		  $scaled_w=round(($original_w/$original_h)*$scaled_h);
		  }

		$scaled = ImageCreateTrueColor($scaled_w, $scaled_h);
		ImageCopyResampled($scaled, $originalStream,
						   0, 0, // dst (x,y) 
						   0, 0, // src (x,y) 
						   $scaled_w, $scaled_h,
						   $original_w, $original_h);
		imagejpeg($scaled,$this->options['base_path']."/".$this->options['files']['albumname']."/".$file['year']."/".$file['month']."/".$target."/".$file['filename']);
		imagedestroy($scaled);
	}
	
	protected function addFile($file) {
		copy($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload/".$file['filename'],$this->options['base_path']."/".$this->options['files']['albumname']."/".$file['year']."/".$file['month']."/original/".$file['filename']);
		//$this->updateOrientation($file);
		if ($originalStream = $this->streamFile($file))
			{
			$this->resizeAndCopy($originalStream,$file, 720,"images");
			$this->resizeAndCopy($originalStream, $file, 172,"thumbs");
			imagedestroy($originalStream);
			unlink($this->options['base_path']."/".$this->options['files']['albumname']."/easyupload/".$file['filename']); 
			}
	}
	
	protected function generatePictureXml($topGallery,$gallery) {
		$pictureXml="";
		$file = null;
		
		if (is_dir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/original")) // Test s'il s'agit d'un repertoire
			{
			$dir = opendir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/original"); //ouvre le repertoire courant d?gn?ar la variable
			while(false!==($file = readdir($dir)))
				{
				if (is_dir($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/original/".$file) == false) // on filtre les repertoire - hack : il faut mettre le full path sinon le test ne fonctionne pas
					{
					if(!in_array($file, array('.','..','Thumbs.db','thumbs.db')) ) //on enleve le parent et le courant '. et ..' il faudra le mettre en param
						{
						$datetime="";
						if($exif = exif_read_data($this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/original/".$file)) // Si le fichier $img contient des infos Exif
							$datetime=$exif['DateTimeOriginal'];
						$pictureXml.="<image imageURL=\"images/".$file."\" thumbURL=\"thumbs/".$file."\">\n\t<caption>".$file." le ".$datetime."</caption>\n</image>\n";
						}
					}
				}
			}
		return $pictureXml;
	}
	
	protected function updateGalleryXml($topGallery,$gallery) {
	$xmlFile="";
	$title="";
	$xmlFile.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlFile.="<simpleviewergallery showOpenButton=\"FALSE\" maxImageWidth=\"720\" maxImageHeight=\"570\" textColor=\"0x000000\" frameColor=\"0xA8908D\" frameWidth=\"0\" stagePadding=\"0\" navPadding=\"10\" thumbnailColumns=\"4\" thumbnailRows=\"5\" navPosition=\"left\" vAlign=\"center\" hAlign=\"center\" title=\"".$title."\" enableRightClickOpen=\"false\" backgroundImagePath=\"\" imagePath=\"\" thumbPath=\"\">\n";
	$xmlFile.=$this->generatePictureXml($topGallery,$gallery);
	$xmlFile.="</simpleviewergallery>";

	$file=$this->options['base_path']."/".$this->options['files']['albumname']."/".$topGallery."/".$gallery."/"."gallery.xml";
	
	$fp=fopen($file,"w");
	fwrite($fp,$xmlFile);
	fclose($fp);
	}
	
	protected function processFile($data) {
		$this->options['xmlvars']['current_album']=$data['name'];
		$this->options['files']['albumname']=$_POST['name'];
		$file=$this->pickaFile(); // the function returns filename , month and year
		if (isset($file['month']) && isset($file['year']) && isset($file['filename'])) {
			$this->checkTopGallery($file['year']);
			$this->checkGallery($file['year'], $file['month']);
			$this->addFile($file);
			$this->updateGalleryXml($file['year'],$file['month']);
			
			}
	}
	
	public function post($print_response = true) {
			if ($print_response)
				{
				$data = $_POST;

				if ($this->exists($data['name']))
					$this->processFile($data);
				return $this->genere_response("");
				}
	}
}

?>