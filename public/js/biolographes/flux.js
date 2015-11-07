/**
fonctions pour la gestion des flux en lecture/ecriture
**/
function getAuth(type, login, mdp){
	var login = $("#iptLogin")[0].value;
	var mdp = $("#iptMdp")[0].value;
	if (login != "" || mdp != "") {
		var p = {"idBase":idBase, "login":login, "mdp":mdp, "ajax":1};
		$.post("../auth/"+type, p,
				 function(data){
			 		if(data.erreur){
			 			showMessage(data.erreur);
			 		}else{
						//charge les graphs enregistrés
						getDocs();
				 		//enregistre les infos de l'uti
				 		uti = data.uti;
						//affichage des infos de l'utilisateur
						document.getElementById("utiConnect").innerHTML = uti.login;
						//affichage des boutons de traitement
				  		d3.selectAll(".imgButton").style("display","inline");
													
						diagLogin.close();						 		
			 		}					 		
				 }, "json");
	}else{
		showMessage("Veillez remplir tous les champs.");
	}
}
function getDocs(){
	var p = {"idBase":idBase, "tronc":"graph"};
	$.post("../flux/getdocs", p,
		 function(data){
	 		if(data.erreur){
	 			showMessage(data.erreur);
	 		}else{
	 			arrGraphs = data;
	 			//parse les json inclus
				arrGraphs.forEach(function(d){
					if(d.data){
						d.data = JSON.parse(d.data);
						//converti les chaines de caractères
						//en attendant le passage à PHP >= 5.3.3
						d.data.links.forEach(function(l){
							castJSON(l.source);
							castJSON(l.target);
							//défixe les position pour laisser faire la force
							l.fixed = true;								
							l.left == "false" ? l.left = false : l.left = true;
							l.right == "false" ? l.right = false : l.right = true;
						})
						d.data.nodes.forEach(function(n){
							castJSON(n);	
							n.fixed = true;								
						})
					}
				});			 			
	 			setListeDocs();
	 		}					 		
		 }, "json");
}
function castJSON(n){
	n.px = Number(n.px);
	n.py = Number(n.py);
	n.x = Number(n.x);
	n.y = Number(n.y);
	n.weight = Number(n.weight);
	n.id = Number(n.id);
	n.index = Number(n.index);
	n.reflexive == "false" ? n.reflexive = false : n.reflexive = true;
	n.bb.height = Number(n.bb.height);
	n.bb.width = Number(n.bb.width);
	n.bb.x = Number(n.bb.x);
	n.bb.y = Number(n.bb.y);
}
function saveDoc(){
	var p = getDocParams();
	if (p.titre) {
		$.post("../flux/sauvedoc", p,
				 function(data){
			 		if(data.erreur){
			 			showMessage(data.erreur);
			 		}else{
			 			getDocs();							 		
						showMessage("Graph ajouté.");
				  		diagSauvDoc.close();						 		
			 		}					 		
				 }, "json");
	}else{
		showMessage("Veillez remplir tous les champs.");
	}
}
function editDoc(){
	var p = getDocParams();
	p.idDoc = arrGraphs[lstGraph.selectedIndex].doc_id;
	$.post("../flux/sauvedoc", p,
			 function(data){
		 		if(data.erreur){
		 			showMessage(data.erreur);
		 		}else{
		 			getDocs();							 		
					showMessage("Graph enregistré.");
			  		diagSauvDoc.close();						 		
		 		}					 		
			 }, "json");
}			
function findAuteur(nom){
	//supprime les résultats
	initFormAuteur()
	$.post("../flux/databnfterm?term="+nom, null,
			 function(data){
		 		//ne récupère que les personnes
		 		dtAuteurFind = data.filter(function(d){
			 		return d.raw_category=="Person";
		 			});
		 		setFindAuteur();
			 }, "json");
}
function selectAuteur(i){
	//récupère la bio de l'auteur
	//"http://data.bnf.fr/10945257"
	var idBNF = dtAuteurFind[i].value.substring(19);
	$.post("../flux/databnfbio?idBNF="+idBNF, null,
			 function(data){
				setSelectAuteur(data);
			 }, "json");	
}

