function startProcessFile(albumname) {
	$.ajax({
	  url: "../include/processeasyupload.php",
	  dataType: 'json',
	  type : 'GET',
	  data : {'albumname': $("albumname").first().contents().text()}, // a regler : rendre dynamique la génération json
	  context: $(".page-body")
	}).done(function(data) {
		$(this).data("processFiles",data);
		if ($(this).data("processFiles")['filecount']!=0)
			{
				startingpoint=$.Deferred();
				startingpoint.resolve();
				$.each($(this).data("processFiles")['files'],function(ix,file) {
					startingpoint=startingpoint.pipe( function() {
						$(".process-file-result").html(" "+file['file']+" ");
						return processFile(file);
					});
				});
				startingpoint=startingpoint.pipe( function() {
					$(".process-file-result").html("G&eacute;n&eacute;ration termin&eacute;e");
				});
		}
		})
	.fail(function() { alert("Erreur : impossible de contacter le serveur"); });
}

function processFile(index){
	return $.ajax({
	  url: "../include/processeasyupload.php",
	  dataType: 'json',
	  type : 'POST',
	  data : {'name':$("albumname").first().contents().text()},  // a régler : rendre dynamique la génération du json
	  context: $(".page-body"),
	  success: function(data) {
		$(this).data("processFiles",data);
		if ($(this).data("processFiles")['filecount']!=0) 
		{$(".process-file-result").html(" "+index['file']+" ");}
		},
	  fail: function() { alert("Erreur : impossible de contacter le serveur"); }
	  });
}