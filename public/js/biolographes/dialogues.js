var dialogues = {
		"noeud":document.getElementById('dlgNoeud')
		,"lieu":document.getElementById('dlgLieu')
		,"event":document.getElementById('dlgEvent')
		,"acteur":document.getElementById('dlgActeur')
		,"login":document.getElementById('dlgLogin')
		,"sauveDoc":document.getElementById('dlgSauveDoc')
		,"message":document.getElementById('dlgMess')
		
		};

function showMessage(mess) {
	document.getElementById('mess').innerHTML = mess; 			
	dialogues.message.showModal();
}
//gestion de la boite acteur
function showDialogActeur(selectItem){
	 //on affiche le formulaire
	 initFormAuteur();				 
	 document.querySelector('#nomActeur').value=selectItem;
	 document.querySelector('#addActeur').innerHTML = "Ajouter";	
	 dialogues.acteur.showModal();		
}
function initFormAuteur(){
	//document.querySelector('#nomActeur').value = "";
	document.querySelector('#prenomActeur').value = "";
	document.querySelector('#isniActeur').value = "";	
	document.querySelector('#professionActeur').value = "";
	document.querySelector('#specialiteActeur').value = "";
	document.querySelector('#fonctionActeur').value = "";
	document.querySelector('#dtNait').value = "";
	document.querySelector('#dtMort').value = "";
	initFindAuteur();	    				
}
function initFindAuteur(){
	d3.selectAll("#resultActeur").style("display","none");
	d3.selectAll("#resultActeurAjout tbody tr").remove();
	d3.selectAll("#resultActeurLiens tbody tr").remove();	
	d3.select("#resultActeurNb").text("");	
}
function setFindAuteur(){
	if(dtAuteurFind.length==0)showMessage("Aucun auteur trouvé.");
	d3.select("#resultActeurNb").text(dtAuteurFind[0].category);
	dtAuteurFind.forEach(function(d, i){
		//affiche le résultat
		d3.selectAll("#resultActeur").style("display","inline");
		$( "#resultActeurAjout tbody" ).append( "<tr>" +
				"<td>" + d.label + "</td>" +
				"<td style='text-align:center;'><img class='imgButton' alt='selectionne l'auteur' onclick='selectAuteur("+i+")' src='../img/document96.png'></td>" +					
				"<td style='text-align:center;'><a href='" + d.value + "' target='_blank'><img class='imgButton' alt='affiche infos auteur' src='../img/cloud181.png'></a></td>" +					
			"</tr>" );					 			
	})	
}
function setSelectAuteur(dt){
	if(dt.length==0)
		showMessage("Aucunne référence dans data.bnf.fr");
	else{
		document.querySelector('#nomActeur').value = dt.nom;		
		document.querySelector('#prenomActeur').value = dt.prenom;
		if(dt.nait)document.querySelector('#dtNait').value = dt.nait;
		if(dt.mort)document.querySelector('#dtMort').value = dt.mort;
		document.querySelector('#isniActeur').value = dt.isni;
		if(dt.profession)document.querySelector('#professionActeur').value = dt.profession;
		if(dt.specialite)document.querySelector('#specialiteActeur').value = dt.specialite;
		if(dt.fonction)document.querySelector('#fonctionActeur').value = dt.fonction;
		dt.liens.forEach(function(d, i){
			//affiche les liens
			$( "#resultActeurLiens tbody" ).append( "<tr>" +
					"<td><a target='_blank' href='" + d + "'>" + d + "</a></td>" +
				"</tr>" );					 			
		})			
	}
}
//gestion de la boite lieu
function showDialogLieu(selectItem){
	 //on affiche le formulaire
	 initFormLieu();				 
	 document.querySelector('#dtLieuAjout').value=selectItem;
	 document.querySelector('#addLieu').innerHTML = "Ajouter";	
	 dialogues.lieu.showModal();		
}
function initFormLieu(){
	document.querySelector('#dtPaysAjout').value="";
	document.querySelector('#dtVilleAjout').value="";
	document.querySelector('#dtAdresseAjout').value="";
	document.querySelector('#dtLatAjout').value="";
	document.querySelector('#dtLngAjout').value="";
	document.querySelector('#dtZoomAjout').value="";
}
function initFindLieu(){
	/*
	d3.selectAll("#resultActeur").style("display","none");
	d3.selectAll("#resultActeurAjout tbody tr").remove();
	d3.selectAll("#resultActeurLiens tbody tr").remove();	
	d3.select("#resultActeurNb").text("");	
	*/
}
function setFindLieu(){
	/*
	if(dtAuteurFind.length==0)showMessage("Aucun lieu trouvé.");
	d3.select("#resultActeurNb").text(dtAuteurFind[0].category);
	dtAuteurFind.forEach(function(d, i){
		//affiche le résultat
		d3.selectAll("#resultActeur").style("display","inline");
		$( "#resultActeurAjout tbody" ).append( "<tr>" +
				"<td>" + d.label + "</td>" +
				"<td style='text-align:center;'><img class='imgButton' alt='selectionne l'auteur' onclick='selectAuteur("+i+")' src='../img/document96.png'></td>" +					
				"<td style='text-align:center;'><a href='" + d.value + "' target='_blank'><img class='imgButton' alt='affiche infos auteur' src='../img/cloud181.png'></a></td>" +					
			"</tr>" );					 			
	})
	*/	
}
function setSelectLieu(dt){
	if(dt.length==0)
		showMessage("Aucunne référence dans geo api");
	else{
		document.querySelector('#dtLieuAjout').value=dt.nom;
		document.querySelector('#dtPaysAjout').value=dt.pays;
		document.querySelector('#dtVilleAjout').value=dt.ville;
		document.querySelector('#dtAdresseAjout').value=dt.adresse;
		document.querySelector('#dtLatAjout').value=dt.lat;
		document.querySelector('#dtLngAjout').value=dt.lng;
		document.querySelector('#dtZoomAjout').value=dt.zoom;
	}
}
//gestion autocomplete
var selectItem;

function setAutocompleteNoeud(val){
	
	//ajoute l'autocompletion
	$('#autocomplete').autocomplete({source: datas[val]
	, select: function( event, ui ) {
	        selectItem = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgNoeud");			
}

function setAutocompleteActeur(){
	
	//ajoute l'autocompletion
	$('#professionActeur').autocomplete({source: datas["Professions"]
	, select: function( event, ui ) {
	        selectProf = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgActeur");			

	$('#specialiteActeur').autocomplete({source: datas["Spécialités scientifiques"]
	, select: function( event, ui ) {
	        selectSpecia = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgActeur");			
	
	$('#fonctionActeur').autocomplete({source: datas["Fonctions"]
	, select: function( event, ui ) {
	        selectFonction = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgActeur");			
	
}

function setAutocompleteLieu(val){
	
	$("#dtLieu").value = "";
	$("#dtAjout").value = "";
	
	//construction de l'autocomplétion avec catégories
    $("#dtLieu").autocomplete({
		source: datas[val],
		select: function( event, ui ) {
	        selectLieux = ui.item.value;
			return false;
		}
	})
	.autocomplete("option", "appendTo", "#dlgEvent")
	.data( "ui-autocomplete" )
		._renderItem = function( ul, item ) {
			return $( "<li>" )
				.append( "<a>" + item.value + "</a>" )
				.appendTo( ul );
		};	   
    $("#dtLieuAjout").autocomplete({
		source: datas[val],
		select: function( event, ui ) {
	        selectLieux = ui.item.value;
			return false;
		}
	})
	.autocomplete("option", "appendTo", "#dlgEvent")
	.data( "ui-autocomplete" )
		._renderItem = function( ul, item ) {
			return $( "<li>" )
				.append( "<a>" + item.value + "</a>" )
				.appendTo( ul );
		};	   
		
}
function setAutocompletePays(data){
	//		
	$("#dtPays").autocomplete({
		minLength: 0,
		source: data,
		focus: function( event, ui ) {
			$( "#dtPays" ).val( ui.item.label );
			return false;
		},
		select: function( event, ui ) {
	        selectPays = ui.item.value;
			return false;
		}
	})
	.autocomplete("option", "appendTo", "#dlgEvent")
	.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
			.append( "<a>" + item.label + " (" + item.iso + ")</a>" )
			.appendTo( ul );
	};			
}
function setListeDocs(){
	var select  = d3.select("#lstGraph")
		.on("change", 
			function (){
				//supprime le contenu du svg
				if(viz.childNodes){
		    			var childs=viz.childNodes;
		    			for(var i=0;i<childs.length;i++){
		    				viz.removeChild(childs[i]);
		    			}
		    		}
	    	  		//recalcule le svg
				var	selectGraph = arrGraphs[this.selectedIndex];
	    	  		nodes = selectGraph.data.nodes;
	    	  		links = selectGraph.data.links;
	    	  		lastNodeId = nodes.length;
	    	  		initForceLayout();
			}					
		);
    var options = select.selectAll('option').data(arrGraphs);
	//ajoute les options
	options.enter().append("option").text(function(d) { 
		//var dt = new Date(d.maj);
		return d.titre+" - "+d.login; 
		//return d.titre; 
		});
	//ajout de l'option de sélection
	select.append("option")
		.text("Choisissez un graph")
		.attr("selected",true); 
		
}
function setListeCribles(){
	var select  = d3.select("#lstCrible")
		.on("change", 
			function (){
				//intialise les tableaux du crible
				setDataByCrible(this.value);
			}					
		);
    var options = select.selectAll('option').data(dtCrible);
	//ajoute les options
	options.enter().append("option")
		.text(function(d) { 
			return d; 
		})
		.attr("value",function(d) { 
			return d; 
		});
	//ajout de l'option de sélection
	select.append("option")
		.text("Aucun")
		.attr("value","oui")
		.attr("selected",true); 
		
}
//fonction pour les liens
function setTypeLien(type){
	//ajoute les type de lien
	document.getElementById("dtTypeRapport").innerHTML = type;
	var select = document.getElementById("dtRapport"); 
	var opts = select.getElementsByTagName('option');
	for (var i = 0; i < opts.length; i++) {
		select.removeChild(opts[i]);
	}	
	datas[type].forEach(function(d){
	    var el = document.createElement("option");
	    el.textContent = d.value;
	    el.value = d.value;
	    select.appendChild(el);			
	});
}
function suppSpatioTempo(i){
	$( "#eventST_"+i).remove();
}

function showSpatioTempo(d){
	document.querySelector('#dtDeb').value="";
	document.querySelector('#dtFin').value="";
	document.querySelector('#dtLieu').value="";
	document.querySelector('#dtPays').value="";
	document.querySelector('#dtVille').value="";
	document.querySelector('#dtAdresse').value="";
	$( "#events-ajout tbody tr" ).remove();
	//affiche les titres
	document.querySelector('#titreEventSource').innerHTML=d.source.desc;
	document.querySelector('#titreEventDest').innerHTML=d.target.desc;
	//ajoute les event enregistré
	if(d.spatiotempo){
		var t = $( "#events-ajout tbody" );
		d.spatiotempo.forEach(function(e, i){
			t.append( "<tr id='eventST_"+i+"'>" +
					"<td>" + e.debut + "</td>" +
					"<td>" + e.fin + "</td>" +
					"<td>" + e.lieu + "</td>" +
					"<td>" + e.pays + "</td>" +
					"<td>" + e.ville + "</td>" +
					"<td>" + e.adresse + "</td>" +
					"<td>" + e.rapport + "</td>" +
					"<td><span onclick='suppSpatioTempo("+i+")' class='ui-icon ui-icon-trash'></span>" +					
				"</tr>" );			 			
		})		
	}

	dialogues.event.showModal();	
}
function synchroniser(){
	if(lstGraph.selectedIndex >= lstGraph.length){
		document.getElementById('docTitre').value = arrGraphs[lstGraph.selectedIndex].titre;			
		document.getElementById('btnEdit').visible = true;						
	}else{
		document.getElementById('docTitre').value = "";			
		document.getElementById('btnEdit').visible = false;			
	}
	dialogues.sauveDoc.showModal();
}