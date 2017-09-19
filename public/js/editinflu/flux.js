/**
fonctions pour la gestion des flux en lecture/ecriture

**/

var datas={"Tags":[],"Lieux":[],"Rapports":[],"Docs":[]};

var idTagRameau, idTagNotion;
var itemSelect, idUpdate, linkSelect, stSelect, docSelect, docFragSelect;
var dtOrigine, dtDoublons;
var dtCrible = [];
var dtGraph = [];
var dtActeurFind, dtTagFind, dtDocFind;


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
function findActeur(nom){
	itemSelect = false;
	w2popup.lock("Veuillez patienter", true);
	$.post(prefUrl+"flux/databnf?obj=term&idBase="+idBase+"&term="+nom, null,
			 function(data){
		 		//ne récupère que les personnes
		 		dtActeurFind = data.filter(function(d){
			 		return d.raw_category=="Person";
		 			});
		 		setFindActeur();
		 	    w2popup.unlock();		 		
			 }, "json");
}

function findActeurGoogle(nom){
	w2popup.lock("Veuillez patienter", true);
	$.post(prefUrl+"flux/googlekg?q="+nom, null,
		 function(data){
	 		//ne récupère que les personnes
			dtActeurFind = false;
			if(data){
		 		dtActeurFind = data.itemListElement.filter(function(d){
			 		var types = d.result["@type"].filter(function(t){
			 			return t=="Person" || t=="Organization";
			 			});
			 		return types.length;
			 		});
			}
			showFindActeurGoogle();
	 	    w2popup.unlock();		 		
		 }, "json");
}

function selectActeur(i){
	//récupère la bio de l'Acteur
	//"http://data.bnf.fr/10945257"
	var idBNF = dtActeurFind[i].value.substring(19);
	$.post(prefUrl+"flux/databnf?obj=bio&idBase="+idBase+"&idBNF="+idBNF, null,
			 function(data){
				setSelectActeur(data);
			 }, "json");	
}

function selectActeurGoogle(i){
	//récupère la bio de l'Acteur
	//via dbpedia
	//var urlDbpedia = i.data.detailedDescription.url.replace("wikipedia.org/wiki", "dbpedia.org/data/")+".json";	
	if(i.data.detailedDescription){
		w2popup.lock("Veuillez patienter", true);
		var res = i.data.detailedDescription.url.split("/");
		res = res[res.length-1]; 
		$.post(prefUrl+"flux/dbpedia?obj=bio&idBase="+idBase+"&res="+res, null,
				 function(r){
					if(r.error){
						w2popup.unlock();
		  				w2alert("Une erreur s'est produite :<br/>"+r.error.message);
					}
					//fusionne les données
					if(!r.nom)r.nom = i.name;				
					//if(!r.url)r.url = i.data.detailedDescription.url;				
					r.liens.push({"value":i.data.detailedDescription.url,"recid":r.liens.length+1,type:"wikipedia"});
					if(i.data.image)r.liens.push({"value":i.data.image.url,"recid":r.liens.length+1,type:"img"});
				    r.data = i;
					showSelectActeurGoogle(r);
			 	    w2popup.unlock();		 		
				 }, "json")
			  .fail(function(r) {
			 	  w2popup.unlock();
  				  w2alert("Une erreur s'est produite :<br/>"+r);
			  });			
	}else{
		var r = {};
		r.nom = i.name;				
	    r.kg = i;
		if(i.data.image){
			r.liens = [{"value":i.data.image.url,"recid":0,type:"img"}];
		    r.kg.liens = r.liens;
		}
		showSelectActeurGoogle(r);
	}
}


function findTag(code){
	//supprime les résultats
	//initFormActeur()
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
	$('#ifTag').attr("src","Editinflu/navigrameau?idBNF="+idBNF);
		
	
}

function findDoc(code, type){
	//supprime les résultats
	//initFormActeur()
	itemSelect = null;
	w2popup.lock("Veuillez patienter", true);
	if(type=='bnf'){
		$.post(prefUrl+"flux/databnf?obj=term&term="+code, null,
				 function(data){
			 		//ne récupère que les documents
			 		dtDocFind = data.filter(function(d){
				 		return d.raw_category=="Work" || d.raw_category=="Periodic";
			 			});
			 		setFindDoc(type);
			 	    w2popup.unlock();		 		
				 }, "json");
	}
	if(type=='gBook'){
		$.post(prefUrl+"flux/google?type=trouveLivre&q="+code, null,
				 function(data){
			 		//ne récupère que les documents
			 		dtDocFind = data;
			 		setFindDoc(type);
			 	    w2popup.unlock();		 		
				 }, "json");
	}
	
}

function selectDoc(i){
	//récupère le détail de la notion
	//"http://data.bnf.fr/10945257"
	var idBNF = dtDocFind[i].value.substring(19);
	//charge les détails de la notion
	$('#ifDoc').attr("src","Editinflu/navigrameau?idBNF="+idBNF);
		
	
}

function chargeCrible(crible){
	//récupère les données du crible
	var data = {"obj":"crible","idCrible":crible.doc_id};	
	$.get(prefUrl+"Editinflu/get",
			data,
        		function(js){
    				finsession(js);
    		        d3.select("#titreCrible").text("Crible : "+sltCrible.titre);        

    				//initalise les documents
    				js.rs["docs"].forEach(function(d){
    					if(d.data)d.data = JSON.parse(d.data);
    				});
    				datas["Docs"]=js.rs["docs"];

    				//initalise les acteurs    				
    				datas["Acteurs"] = js.rs["acteurs"];   				
    				datas["Acteurs"].forEach(function(d){
    					if(d.data)d.data = JSON.parse(d.data);   					
    				});    				
    				
    				//initalise les notions    				
    				js.rs["notions"].forEach(function(d){
    					/*hiérarchie 3 niveaux
    					var p1 = d.parent1, p2 = d.parent2;
        				if(!datas[p2])datas[p2]=[];
        				if(!datas[p2][p1]){
        					datas[p2][p1]=[];
        				}
        				datas[p2][p1].push(d);    					
        				*/
    					/*hiérarchie 2 niveaux
    					var p1 = d.parent1;
        				if(!datas[p1])datas[p1]=[];
        				datas[p1].push(d);
        				*/
        				//enregistre les tag références
        				if(d.parent1=="Matière Rameau")idTagRameau=d.tId1;
        				if(d.parent1=="Notions")idTagNotion=d.tId1;
        				//cumul les notions
        				if(d.parent2=="Catégories de rapport"){
        					d.id = d.recid;
        					d.text = d.code;
        					datas["Rapports"][d.recid]=d;        				        				
        				}
        				if(d.parent2=="Catégories de notion"){
        					d.id = d.recid;
        					d.text = d.code+" - "+d.parent1;
        					datas["Tags"].push(d);
        				}
        				if(d.parent2=="Catégories de lieu"){
        					d.id = d.recid;
        					d.text = d.code;//+" - "+d.parent1;
        					datas["Lieux"].push(d);
        				}
    				});
    				//w2alert(js.message);
       		},"json");		
	
}
