var prefUrl = "";

function deconnexion(){
	window.location.assign(prefUrl+'auth/deconnexion?redir=valarnum');
}


function saveEmo(p) {
    
	$.post("valarnum/save"
		, p,
		 function(data){
			console.log(data);
			idDoc = data;
		 });
}