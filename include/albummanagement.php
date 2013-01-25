<?php

error_reporting(E_ALL | E_STRICT);
require('../albummanager.php');
$album_manager = new AlbumManager();
print_r($album_manager->options['debug']);
/*

{
"albumcount":"2",
"albums":[
	{
	"name":"louise",
	"title":"blipblop c'est cool la life. espece",
	"caption":"georges l'escargot etait tres content de manger des pates"
	},
	{
	"name":"alice",
	"title":"zjfekgkfpogjfsdigjfgipsf",
	"caption":"f,kfgkfghdglkhglhpfdlgfpfgfdsgfdgfds"
	}]
}

*/
?>

