/**
fonctions pour la gestion des flux en lecture/ecriture

**/
function finsession(js){
	if(js.finsession) window.location = prefUrl+'auth/login';	
}

function getAuth(type, login, mdp){
	var login = $("#iptLogin")[0].value;
	var mdp = $("#iptMdp")[0].value;
	if (login != "" || mdp != "") {
		var p = {"idBase":idBase, "login":login, "mdp":mdp, "ajax":1};
		$.post(prefUrl+"auth/"+type, p,
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
	$.post(prefUrl+"flux/getdocs", p,
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
		$.post(prefUrl+"flux/sauvedoc", p,
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
	$.post(prefUrl+"flux/sauvedoc", p,
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
	//initFormAuteur()
	w2popup.lock("Veuillez patienter", true);
	$.post(prefUrl+"flux/databnf?obj=term&term="+nom, null,
			 function(data){
		 		//ne récupère que les personnes
		 		dtAuteurFind = data.filter(function(d){
			 		return d.raw_category=="Person";
		 			});
		 		setFindAuteur();
		 	    w2popup.unlock();		 		
			 }, "json");
}

function selectAuteur(i){
	//récupère la bio de l'auteur
	//"http://data.bnf.fr/10945257"
	var idBNF = dtAuteurFind[i].value.substring(19);
	$.post(prefUrl+"flux/databnf?obj=bio&idBNF="+idBNF, null,
			 function(data){
				setSelectAuteur(data);
			 }, "json");	
}

function findTag(code){
	//supprime les résultats
	//initFormAuteur()
	w2popup.lock("Veuillez patienter", true);
	$.post(prefUrl+"flux/databnf?obj=term&term="+code, null,
			 function(data){
		 		//ne récupère que les notions
		 		dtTagFind = data.filter(function(d){
			 		return d.raw_category=="Rameau";
		 			});
		 		setFindTag();
		 	    w2popup.unlock();		 		
			 }, "json");
}

function selectTag(i){
	//récupère le détail de la notion
	//"http://data.bnf.fr/10945257"
	var idBNF = dtTagFind[i].value.substring(19);
	//charge les détails de la notion
	$('#ifTag').attr("src","biolographes/navigrameau?idBNF="+idBNF);
		
	
}


function chargeCrible(crible){
	//récupère les données du crible
	var data = {"obj":"crible","idExi":crible.exi_id,"idDoc":crible.doc_id};	
	$.get(prefUrl+"biolographes/get",
			data,
        		function(js){
    				finsession(js);
    				datas = {}, rsTags = [];
    				js.rs.forEach(function(d){
    					/*hiérarchie 3 niveaux
    					var p1 = d.parent1, p2 = d.parent2;
        				if(!datas[p2])datas[p2]=[];
        				if(!datas[p2][p1]){
        					datas[p2][p1]=[];
        				}
        				datas[p2][p1].push(d);    					
        				*/
    					//hiérarchie 2 niveaux
    					var p1 = d.parent1;
        				if(!datas[p1])datas[p1]=[];
        				datas[p1].push(d);
        				//enregistre les tag références
        				if(d.parent1=="Matière Rameau")idTagRameau=d.tId1;
        				if(d.parent1=="Notions")idTagNotion=d.tId1;
        				//cumul les notions
        				if(d.parent2=="Catégories de notion")rsTags.push(d);
    				});
    				datas["Acteurs"] = rsActeurs;
    				w2alert(js.message);
       		},"json");		
	
}
