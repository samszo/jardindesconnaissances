
function deconnexion(){
	window.location.assign('auth/deconnexion?redir=valarnum');
}


function saveEmo(p) {
    
	$.post("valarnum/save"
		, p,
		 function(data){
			console.log(data);
			idDoc = data;
		 });
}