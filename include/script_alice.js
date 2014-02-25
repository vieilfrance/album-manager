jQuery(document).ready(function () {
	SV.simpleviewer.load('sv-container', '100%', '95%', '804F4A', true);
});


var flashvars = {};
flashvars.galleryURL = "gallery.xml";
var params = {};			
params.allowfullscreen = true;
params.allowscriptaccess = "always";
params.bgcolor = "804F4A";
swfobject.embedSWF("simpleviewer.swf", "flashContent", "100%", "95%", "9.0.124", true, flashvars, params);