

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
			showFindActeur();
	 	    w2popup.unlock();		 		
		 }, "json");
}

function getActeurGoogle(d){
	w2popup.lock("Veuillez patienter", true);
	$.post(prefUrl+"flux/googlekg?id="+d.url, null,
		 function(data){
			if(data.error){
  				w2alert("ERREUR :<br/>"+data.error.message);
  		 	    w2popup.unlock();		 		
			}else{
		 		//vérifie s'il faut forcer l'url
				if(!data.itemListElement.length){
					data.itemListElement = [{'result':{'detailedDescription':{'url':d.data}}}];
				}else if(!data.itemListElement[0].result.detailedDescription){
		 			data.itemListElement[0].result.detailedDescription = {'url':d.data};		 		
		 		}
		 		selectActeurGoogle({data:data.itemListElement[0].result}, true);
			}
		 }, "json")
		  .fail(function(r) {
		 	  w2popup.unlock();
			  w2alert("Une erreur s'est produite :<br/>"+r);
		  });			
}

function selectActeurGoogle(i, showDetail){
	//récupère la bio de l'Acteur
	//via dbpedia
	//var urlDbpedia = i.data.detailedDescription.url.replace("wikipedia.org/wiki", "dbpedia.org/data/")+".json";	
	if(i.data.detailedDescription){
		if(!showDetail)w2popup.lock("Veuillez patienter", true);
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
				    r.data = i.data;
					showSelectActeurGoogle(r);
					if(showDetail)showDetailsLOD(r);
					w2popup.unlock();		 		
				 }, "json")
			  .fail(function(r) {
  				w2alert("Une erreur s'est produite :<br/>"+r);
  				w2popup.unlock();		 		

			  });			
	}else{
		var r = {};
		r.nom = i.name;				
	    r.data = i.data;
		if(i.data.image){
			r.liens = [{"value":i.data.image.url,"recid":0,type:"img"}];
		}
		showSelectActeurGoogle(r);
		if(showDetail)showDetailsLOD(r);		
		w2popup.unlock();		 		

	}
}

function modifierActeur(data){
	
    data.idBase = idBase;
    url = 'valarnum/editacteur';
    
    $.ajax({
    		url: prefUrl+url,
    		dataType: "json",
    		data: data,
    		method: 	"GET",
        	error: function(error){
        		w2alert("Erreur : "+error.responseText);
        	},            	
        	success: function(js) {
			//mise à jour de la data
        		w2ui['grid_acteur'].records.forEach(function(d, i){
				if(d.recid==js.rs.recid){
					w2ui['grid_acteur'].records[i]=js.rs;
					w2ui['grid_acteur'].refreshRow(d.recid);
				}
			});
			if(js.message)w2alert(js.message);

        }
	});	        
		
}

function ajoutActeurLien(data,idDoc,arrTof){
	
    var p = {'idBase':idBase,'data':data,'idDoc':idDoc,'idUti':uti.uti_id,'gpId':arrTof['gpId'],'pId':arrTof['pId'],'tId':arrTof['doc_id']};
    url = 'valarnum/ajoutacteurlien';
    
    $.ajax({
    		url: prefUrl+url,
    		dataType: "json",
    		data: p,
    		method: 	"GET",
        	error: function(error){
        		w2alert("Erreur : "+error.responseText);
        	},            	
        	success: function(js) {
			//mise à jour du grid contecte
        		if(js.rs){
        			if(w2ui['grid_acteur'].select(js.id))
        				w2ui['grid_acteur'].records[js.rs.recid] = js.rs;
        			else
        				w2ui['grid_acteur'].add(js.rs);
				w2ui['grid_acteur'].refreshRow(js.rs.recid);
            		w2ui['grid_acteur'].select(js.rs.recid);
        		}
			if(js.message)w2alert(js.message);

        }
	});	        
		
}
