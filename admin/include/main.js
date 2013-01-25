function feed(index) {
var uploadUrl = "";

album=$(".page-body").data("albums")['albums'][index];
$('form[editgallery] #form_name').val(album['name']);
$('form[editgallery] #form_name_display ').html(album['name']);
$('form[editgallery] #form_title').val(album['title']);
$('form[editgallery] #form_caption').val(album['caption']);
$('form[editgallery] #form_css').val(album['css']);
$('form[editgallery] #form_script').val(album['script']);
$("albumname").html(album['name']);

uploadUrl="../"+album['name']+"/easyupload";
$('#uploadlink').attr("href",uploadUrl);
$('#uploadlink').attr("target",album['name']);

$(".process-file-result").empty();

return true;
}

function albumList(){
	albumcount=0;
	$(".dropdown-menu").empty();
	$.each($(".page-body").data("albums")['albums'], function(index,value){
		$(".dropdown-menu").append('<li><a tabindex="-1" href="#" id="album'+albumcount+'" onclick="return feed('+albumcount+');">'+value['name']+'</li>');
		albumcount++;
	});

return true;
}
$.ajax({
  url: "../include/albummanagement.php",
  dataType: 'json',
  context: $(".page-body")
}).done(function(data) {
	$(this).data("albums",data);
	if ($(this).data("albums")['albumcount']==0) { $(".page-body").append($(".no-album"));}
	else {$(".page-body").append($(".album"));}
	})
.fail(function() { alert("Erreur : impossible de contacter le serveur"); });

jQuery(function($) {
    $('form[newgallery]').live('submit', function(event) {
		event.preventDefault();
			});
        });
		

$("#modal-edit").on('show', function(){
  albumList();
 $("albumname").html($(".page-body").data("albums")['albums'][0]['name']);
 feed(0);
});

$("#modal-admin").on('show', function(){
  albumList();
 $("albumname").html($(".page-body").data("albums")['albums'][0]['name']);
 feed(0);
});

$("#modal-easyupload").on('show', function(){
$("#modal-admin").modal('toggle');
});

$("#modal-easyupload").on('hide', function(){
$("#modal-admin").modal('show');
});
