<?php
//<!-- ce qu'on doit obtenir avec le GET -->
/*
{
"albumname":"daticool",
"filecount":"5",
"files":[
	{
	"file":"IMG_6246.JPG"
	},
	{
	"file":"IMG_6259.JPG"
	}]
}
*/
//<!-- ce qu'on doit obtenir avec le POST avec le nom de l'album , en retour on pourrait avoir le nom du fichier traité-->
error_reporting(E_ALL | E_STRICT);
require('../filemanager.php');
$file_manager = new FileManager();
print_r($file_manager->options['debug']);


?>


