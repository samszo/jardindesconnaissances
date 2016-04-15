function connecte(){
	getAuth("login");
}		
function inscrit(){
	getAuth("inscription");
}		
function deconnexion(){
	window.location.assign(prefUrl+'auth/deconnexion?redir=editinflu');
}
function getAuth(type){
	var login = $("#iptLogin")[0].value;
	var mdp = $("#iptMdp")[0].value;
	if (login != "" || mdp != "") {
		var p = {"login":login, "mdp":mdp,"redir":"#"};
		$.post(prefUrl+"auth/"+type, p,
				 function(data){
			 		if(data.erreur){
			 			w2alert(data.erreur);
			 		}else{
				 		//enregistre les infos de l'uti
				 		uti = data.uti;
				 		utiIsConnect();
						diagLogin.close();						 		
			 		}					 		
				 }, "json");
	}else{
		showMessage("Veillez remplir tous les champs.");
	}
}
function utiIsConnect(){
	/*affichage des bouton d'écriture
	arrIds.forEach(function(d){
		if(d.role=="ecrit")document.getElementById(d.idEle).setAttribute("visibility","inherit");
	  });
	*/
	//on initialise les layout
	if(fctInit){
		fctInit();
		//affichage des infos de l'utilisateur
		var uc = document.getElementById("utiConnect")
		if(uc)uc.innerHTML = uti.login;
	}else{
		//affichage des infos de l'utilisateur
		if(w2ui['mainTB']){
			/*
			w2ui['mainTB'].set('utiLog', { caption: uti.login });
			var bSet = w2ui['mainTB'].refresh();
			le set ne marche pas on recharge la page
			*/
			if(w2ui['mainTB'].items[w2ui['mainTB'].items.length-2].caption != uti.login)location.reload(true);
		}
	}
	
}
		