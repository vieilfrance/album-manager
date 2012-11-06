// fonction javascript de mise à jour d'une gallerie à partir d'un paramète 'rep'
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

function g_index(rep,name,type) {
	new Ajax.Request('genereindex.php',
	{
		method:'get',
		parameters: {  
			rep: rep ,
			name: name ,
			type: type
		},
		onSuccess: function(transport){
			alert('ok');
		},
		onFailure: function(transport){ alert('Something went wrong...'+ transport.responseText) }
	});
}
