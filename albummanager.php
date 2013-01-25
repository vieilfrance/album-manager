<?php

class AlbumManager
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
			'albums' => array(
				'albumcount' =>0,
				'albums' => array()
				)
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
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
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
	
	protected function endElement_Full($parser, $name)
	{
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

	protected function dataElement($parser, $name)
	{	
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
	
	public function get($print_response = true) {
			if ($print_response)
				{
				return $this->genere_response($this->options['albums']);
				}
	}
	
	protected function generateXMLGallery ($album) {
	$output="";
	if ($album) {
		$output.="<gallery directory=\"".$album['name']."\">";
		$output.="<name>".$album['name']."</name>";
		if (isset($album['title'])) $output.="<title>".$album['title']."</title>";
		if (isset($album['caption'])) $output.="<caption>".$album['caption']."</caption>";
		if (isset($album['css'])) $output.="<css>".$album['css']."</css>";
		if (isset($album['script'])) $output.="<script>".$album['script']."</script>";
		$output.="</gallery>";
		}
	return $output;
	}
	
	protected function generateXMLGalleries($xmlalbum=null) {
	$output="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$output.="<galleries>";
	 if ($xmlalbum) {
		$output.=$xmlalbum;
     }
	$output.="</galleries>";
	return $output;
	}

	protected function updateParamFile($data) {
		if (!is_dir($this->options['param_path']))
			{
			mkdir($this->options['param_path'], 0777);
			}
		if ( !file_exists($this->options['param_path'].$this->options['param_file'])) // si le fichier de parametrage n'existe pas on le crée
			{
			$fp=fopen($this->options['param_path'].$this->options['param_file'],"wb");
			$xmlgallery=$this->generateXMLGalleries($this->generateXMLGallery($data));  // !!! il faut encore ajouter le repertoire de base des galleries !!!
			fwrite($fp,$xmlgallery);
			fclose($fp);			
			}
		else	// sinon on le met à jour
			{
			$fp = fopen($this->options['param_path'].$this->options['param_file'], "rb") or die ("Fichier de paramètrage 'param.xml' introuvable.");
			$xmlparam = fread($fp, filesize($this->options['param_path'].$this->options['param_file']));
			fclose($fp);
			$xmlgallery=$this->generateXMLGallery($data);
			$position=strrpos($xmlparam,"</galleries>");
			$sortie=substr($xmlparam,0,$position).$xmlgallery."</galleries>";
			$fp=fopen($this->options['param_path'].$this->options['param_file'],"wb");
			fwrite($fp,$sortie);
			fclose($fp);
			}			
	}

	protected function addAlbum($data) {
		$this->updateParamFile($data);
		mkdir($this->options['base_path']."/".$data['name'], 0777); // on crée le repertoire de la gallerie
		mkdir($this->options['base_path']."/".$data['name']."/easyupload", 0777); // on crée le repertoire pour easyupload
		$this->recurse_copy($this->options['base_path'].'/admin/base/easyupload',$this->options['base_path']."/".$data['name']."/easyupload"); // ici mettre la copie des fichiers pour l'upload
		
		$fp=fopen($this->options['base_path']."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$base."/admin/base/index_newgallery.php manquant");
		$indexcontent=fread($fp, filesize($this->options['base_path']."/admin/base/index_newgallery.php"));
		fclose($fp);
		$indexcontent=str_replace('@@REP@@',$data['name'],$indexcontent);
		$fp=fopen($this->options['base_path']."/".$data['name']."/index.php",'wb') or die("Fichier index manquant");
		fwrite($fp,$indexcontent);
		fclose($fp);
	}

	protected function editAlbum($data) {
		if (!is_dir($this->options['param_path']))
			{
			mkdir($this->options['param_path'], 0777);
			}
		if (!file_exists($this->options['param_path'].$this->options['param_file'])) 
			{
			$xmlgalleries=null;
			$fp=fopen($this->options['param_path'].$this->options['param_file'],"wb");
			for ($index=0; $index<$this->options['albums']['albumcount']; $index++)
				{
				$xmlgalleries.=$this->generateXMLGallery($this->options['albums']['albums'][$index]);
				}
			$xmlgalleries=$this->generateXMLGalleries($xmlgalleries); 
			fwrite($fp,$xmlgalleries);
			fclose($fp);			
			}
		else
			{
			$xmlgalleries=null;
			$fp=fopen($this->options['param_path'].$this->options['param_file'],"wb");
			for ($index=0; $index<$this->options['albums']['albumcount']; $index++)
				{
				if ($this->options['albums']['albums'][$index]['name']==$data['name'])
					$xmlgalleries.=$this->generateXMLGallery($data);
				else
					$xmlgalleries.=$this->generateXMLGallery($this->options['albums']['albums'][$index]);
				}
			$xmlgalleries=$this->generateXMLGalleries($xmlgalleries); 
			fwrite($fp,$xmlgalleries);
			fclose($fp);
			}
			
		// Je deactive mais il faudra faire un test d'existance des dossiers et eventuellement les créer - cas ou ca a merdé préalablement
		/*
		mkdir($this->options['base_path']."/".$data['name'], 0777); // on crée le repertoire de la gallerie
		mkdir($this->options['base_path']."/".$data['name']."/easyupload", 0777); // on crée le repertoire pour easyupload
		$this->recurse_copy($this->options['base_path'].'/admin/base/easyupload',$this->options['base_path']."/".$data['name']."/easyupload"); // ici mettre la copie des fichiers pour l'upload
		*/
		// ici la recréation de l'index va poser un soucis si on a déjà commencé à le mettre à jour avec des liens vers des galleries
		/*
		$fp=fopen($this->options['base_path']."/admin/base/index_newgallery.php",'rb') or die("Fichier ".$base."/admin/base/index_newgallery.php manquant");
		$indexcontent=fread($fp, filesize($this->options['base_path']."/admin/base/index_newgallery.php"));
		fclose($fp);
		$indexcontent=str_replace('@@REP@@',$data['name'],$indexcontent);
		$fp=fopen($this->options['base_path']."/".$data['name']."/index.php",'wb') or die("Fichier index manquant");
		fwrite($fp,$indexcontent);
		fclose($fp);
		*/
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
	
	protected function easyuploadFileList($albumName) {
	$fileList = null ;

	
	return $fileList;
	}
	
	public function post($print_response = true) {
			if ($print_response)
				{
				$data = $_POST;
				// si le nom de l'album existe, on modifie les valeurs sinon on le crée.
				if ($this->exists($data['name']))
					$this->editAlbum($data);
				else
					$this->addAlbum($data);
				return $this->genere_response("");
				}
	}
}

?>