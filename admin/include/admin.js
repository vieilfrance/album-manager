// fonction javascript de mise � jour d'une gallerie � partir d'un param�te 'rep'
function g_update(rep) {
	new Ajax.Request('update.php',
	{
		method:'get',
		parameters: {  
			rep: rep
		},
		onSuccess: function(transport){
			alert('ok');
		},
		onFailure: function(transport){ alert('Something went wrong...'+ transport.responseText) }
	});
}
