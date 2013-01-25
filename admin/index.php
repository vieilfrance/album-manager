<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Ecran d'administration</title>
		<link href="./include/admin.css" rel="stylesheet" /> 
		<!-- Bootstrap CSS Toolkit styles -->
		<link rel="stylesheet" href="./public/css/bootstrap.min.css">
		<!-- Bootstrap styles for responsive website layout, supporting different screen sizes -->
		<link rel="stylesheet" href="./public/css/bootstrap-responsive.min.css">
	</head>
<body>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
        </div>
    </div>
</div>
<div class="container">
<ul class="breadcrumb">
  <li class="active">Home</li>
</ul>
    <div class="page-header">
        <h1>Administration des albums</h1>
    </div>
    <br>
<?php

//set_include_path(get_include_path().PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']."/".'include'); // inclure le repertoire "include" en flottant (laisser le choix du repertoire racine)
// il faut utiliser le php_self et supprimer le nom de fichier ainsi que le dernier repertoire pour le remplacer par le repertoire "include"

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (� partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier execut� (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlev� le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier execut�

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';

// objectif : appeler en javascript le serveur pour obtenir les infos des galleries via json + le nombre de galleries 
// en fonction de ces info, r�utiliser les parties de html d�j� existantes dans l'index de base, toujours en html et javascript.
// L'objectif � terme est de d�porter le + possible les traitements "client" cot� client justement. Le serveur n'ayant � fournir que des donn�es et des traitements m�tier
// Ca permettra �galement par la suite de faciliter les tests unitaires � produire pour la version 1.3

?>
	<div class="page-body"></div>
	</div>

	<!-- Modal creation d'un album -->
	<div class="modal hide fade" data-width="800px" style="width: 800px; margin-left: -300px; display: none; margin-top: 0px;" id="modal-create" >
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>Nouvel album</h3>
	</div>
	<div class="modal-body">
		<FORM name="newgallery" newgallery class="form-horizontal" method="POST" action="#" onsubmit="return checkForm('POST','new')">
		<fieldset><legend>Informations n&eacute;cessaires</legend>
		<div class="control-group"><label for="form_name" class="obl , control-label"><b>Nom de l'album : </b></label><div class="controls"><span id="namestatut"></span><input type="text" id="form_name" name="name" class="input-xlarge" required></div></div>
		<div class="control-group"><label for="form_title" class="control-label">Titre : </label><div class="controls"><input type="text" id="form_title" name="title" class="fac, input-xlarge"><span id="titlestatut"></span></div></div>
		<div class="control-group"><label for="form_caption" class="control-label">Texte descritpif : </label><div class="controls"><textarea id="form_caption" name="caption" class="fac, input-xxlarge"></textarea><span id="captionstatut"></span></div></div>
		</fieldset>
		<fieldset><legend><a href="#" onclick="displayGroup('displaygroup_create');">Informations compl&eacute;mentaires</a></legend>
		<div id="displaygroup_create" style="display:none;">
		<div class="control-group"><label for="form_css" class="control-label">Feuille de style : </label><div class="controls"><input type="text" id="form_css" name="css" value="default" class="input-small" onfocusout="checkInput(this)" onfocus="clearDefaultandCSS(this)"><span id="cssstatut"></span></div></div>
		<div class="control-group"><label for="form_script" class="control-label">Fichier de script : </label><div class="controls"><input  type="text" id="form_script" name="script" value="default" class="input-small" onfocusout="checkInput(this)" onfocus="clearDefaultandCSS(this)"><span id="scriptstatut"></span></div></div>
		</div>
		</fieldset>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary">Enregistrer</button>
		</FORM>
	</div>
	</div>

	<!-- Modal edition d'un album -->
	<div class="modal hide fade" data-width="800px" style="width: 800px; margin-left: -300px; display: none; margin-top: 0px;" id="modal-edit" >
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>Edition de l'album <albumname></albumname><div class="dropdown">
  <a class="dropdown-toggle" id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="/page.html">
    Albums
    <b class="caret"></b>
  </a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel1">
  </ul>
</div>
	
	</h3>
	</div>
	<div class="modal-body">
		<FORM name="editgallery" editgallery class="form-horizontal" method="POST" action="#" onsubmit="return checkForm('POST','edit')">
		<fieldset><legend>Informations n&eacute;cessaires</legend>
		<div class="control-group" style="display:none;"><label for="form_name" class="obl , control-label"><b>Nom de l'album : </b></label><div class="controls"><span id="namestatut"></span><input type="text" id="form_name" name="name" class="input-xlarge uneditable-input" required></div></div>
		<div class="control-group"><label for="form_name_display" class="obl , control-label"><b>Nom de l'album : </b></label><div class="controls"><span id="form_name_display" class="input-xlarge uneditable-input"></span></div></div>
		<div class="control-group"><label for="form_title" class="control-label">Titre : </label><div class="controls"><input type="text" id="form_title" name="title" class="fac, input-xlarge"><span id="titlestatut"></span></div></div>
		<div class="control-group"><label for="form_caption" class="control-label">Texte descritpif : </label><div class="controls"><textarea id="form_caption" name="caption" class="fac, input-xxlarge"></textarea><span id="captionstatut"></span></div></div>
		</fieldset>
		<fieldset><legend><a href="#" onclick="displayGroup('displaygroup_edit');">Informations compl&eacute;mentaires</a></legend>
		<div id="displaygroup_edit" style="display:none;">
		<div class="control-group"><label for="form_css" class="control-label">Feuille de style : </label><div class="controls"><input type="text" id="form_css" name="css" value="default" class="input-small" onfocusout="checkInput(this)" onfocus="clearDefaultandCSS(this)"><span id="cssstatut"></span></div></div>
		<div class="control-group"><label for="form_script" class="control-label">Fichier de script : </label><div class="controls"><input  type="text" id="form_script" name="script" value="default" class="input-small" onfocusout="checkInput(this)" onfocus="clearDefaultandCSS(this)"><span id="scriptstatut"></span></div></div>
		</div>
		</fieldset>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary">Enregistrer</button>
		</FORM>
	</div>
	</div>

	<!-- Modal administrer un album -->
	<div class="modal hide fade" data-width="800px" style="width: 800px; margin-left: -300px; display: none; margin-top: 0px;" id="modal-admin" >
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>Administration de l'album <albumname></albumname><div class="dropdown">
  <a class="dropdown-toggle" id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="/page.html">
    Albums
    <b class="caret"></b>
  </a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel1">
  </ul>
</div>
	
	</h3>
	</div>
	<div class="modal-body">

	<a id="uploadlink" class="btn btn-primary newWindow" href="#popupref" ><i class="icon-eye-open icon-white"></i> Charger des photos</a>
    <!--<a class="btn btn-primary" href="/".$_POST['dirr']."easyupload"><i class="icon-upload icon-white\"></i>Charger des photos</a>-->
    <a class="btn btn-primary" href="#" role="button" onclick="startProcessFile();"><i class="icon-refresh icon-white"></i> G&eacute;n&eacute;rer les galleries</a>
	<span class="process-file-result"></span>
	<!--<a class="btn btn-primary" href="#" onclick="processEasyupload();"><i class="icon-refresh icon-white"></i>G&eacute;n&eacute;rer les galleries</a>-->
	
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary" data-dismiss="modal">OK</button>
	</div>
	</div>
	
	<div class="tmpl">

		<!-- Template en cas d'absence d'album -->
		<div class="textcenter no-album">Il semble que vous n'ayiez cr&eacute;&eacute; aucun album pour le moment.<br/>Vous pouvez d&eacute;marrer la cr&eacute;ation d'un album si vous le souhaitez.<br/><br/>
		<a class="btn btn-success" href="#modal-create" data-toggle="modal" role="button">
		<i class="icon-plus icon-white"></i>
		<span>DEMARRER</span>
		</a>
		</div>
		
		<!-- Template en cas de presence d'albums -->
		<div class="album">
		<a class="btn btn-primary" href="#modal-admin" data-toggle="modal" role="button"><i class="icon-eye-open icon-white"></i> Administrer un album</a>
		<a class="btn btn-primary" href="#modal-edit" data-toggle="modal" role="button"><i class="icon-edit icon-white"></i> Editer un album</a>
		<a class="btn btn-success" href="#modal-create" data-toggle="modal" role="button"><i class="icon-plus icon-white"></i> Nouvel album</a>		
		</div>
		</div>
		
	</div>
<script>
function checkInput(el) {
if (el.value =="")
	el.value = "default"
return true;
}

function clearForm() {
    $('form[newgallery] input').each(function() {
        $(this).val("");
    });
}

function clearDefaultandCSS(el) {
	if (el.defaultValue==el.value) el.value = ""
	// If Dynamic Style is supported, clear the style
	if (el.style) el.style.cssText = ""
}

function displayGroup(el) {
var group=document.getElementById(el);
if (group.style.display=="none")
	group.style.display="block";
else
	group.style.display="none";
return true;
}

function checkForm(methodType,action) {
var checkstatus ;
var nomgallery;
var targetForm;
var modal;
var index = null;

event.preventDefault(); 

if (action=='new') {
	targetForm=$('form[newgallery]');
	targetInputForm=$('form[newgallery] input ');
	modal=$( '#modal-create' );
}

if (action=='edit') {
	targetForm=$('form[editgallery]');
	targetInputForm=$('form[editgallery] input, textarea');
	modal=$( '#modal-edit' );
}

$.ajax({
  url: "../include/albummanagement.php",
  dataType: 'json',
  type: methodType ,
  async:   true,
  data: targetForm.serialize(),
  context: $(".page-body")
})
.done(function(data) {
	if (action=='new') {
		var newValues = {};
		$(this).html($(".album"));
		targetInputForm.each(function() {
			newValues[this.name] = $(this).val();
		});
		newValues['title'] = newValues['name'];
		$(this).data("albums")['albums'].push(newValues);
		$(this).data("albums")['albumcount']++;
		clearForm();
		modal.modal('hide');
		}
	if (action=='edit') {
		var editValues = {};
		targetInputForm.each(function() {
			editValues[this.name] = $(this).val();
		});
		$.each($(this).data("albums")['albums'], function(index,value){
		if (value['name'] == editValues['name'] )
			{
			value['title'] = editValues['title'];
			value['caption'] = editValues['caption'];
			value['css'] = editValues['css'];
			value['script'] = editValues['script'];
			}
		});
		clearForm();
		modal.modal('hide');		
	}
	})
.fail(function(data) { 
	alert("Erreur : impossible de contacter le serveur : "+data); 
	modal.modal('hide');
	});

return true;
}


</SCRIPT>
<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script> -->
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="./include/jquery-1.8.3-min.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="./public/js/bootstrap.min.js"></script>
<script src="./include/filemanager.js"></script>
<script src="./include/main.js"></script>
</body>
</html>