var pstyle = 'padding:3px;';
var prefUrl = '../';
var urlVide = prefUrl+'vide.html';
var tofSelect, sltActeur, itemLodSelect, dtActeurFind, htmlIframeVide = '<iframe id="idIf" src="'+urlVide+'" height="100%" width="100%" />';
var dataActeurVide = {'recid':0, 'prenom':'', 'nom':'-', 'nait':'', 'mort':'', 'confiance':0, 'url':'','data':''};
var notAdmin = uti.role != 'admin';

var lyActeur = {
        name: 'layout_acteur',
        panels: [
            { type: 'top', size:"300px", style: pstyle, content: '',resizable: true },
            { type: 'left', size:"30%", maxSize:"400px", style: pstyle, content: '',resizable: true },
            { type: 'main', size:"70%", style: pstyle, content: '', title:'',resizable: true },
        ],
    };
var lyActeurAdd = {
        name: 'layout_acteur_add',
        panels: [
            { type: 'top', size:"140px", style: pstyle, content: '',resizable: false },
            { type: 'left', size:"400px", style: pstyle, content: '',resizable: true },
            { type: 'main', size:"30%", style: pstyle, content: '', resizable: true },
            { type: 'right', size:"40%", style: pstyle, content: '', resizable: true },
        ],
    };

var gridActeur = {
    name: 'grid_acteur', 
	header: 'Acteurs du contexte',		
	show: {toolbar		: true,
		footer			: true,
		toolbarReload   : false,
		toolbarColumns  : false,
        	toolbarSearch   : true,
        	toolbarAdd      : false,
        	toolbarDelete   : false,
        	toolbarSave		: false,
        	header			: true, 
        	columnHeaders	: true,
        	multiSearch		: true
        	},
    searches: [
        { field: 'prenom', caption: 'Prénom', type: 'text' },
        { field: 'nom', caption: 'Nom', type: 'text' },
        { field: 'nait', caption: 'Nait', type: 'date' },
        { field: 'nait', caption: 'Nait', type: 'date' },
        { field: 'url', caption: 'Référence', type: 'text' }
    ],        	
    columns: [      		  		           
        { field: 'recid', caption: 'ID', size: '50px', hidden:true, sortable: true, resizable: true },
        { field: 'prenom', caption: 'Prénom', size: '100px', sortable: true, resizable: true},
        { field: 'nom', caption: 'Nom', size: '100px', sortable: true, resizable: true},
        { field: 'nait', caption: 'Nait', size: '100px', sortable: true, resizable: true},
        { field: 'mort', caption: 'Mort', size: '100px', sortable: true, resizable: true},
        { field: 'nbVote', caption: 'Nb de vote', size: '100px', sortable: true, resizable: true},
        { field: 'confianceVisage', caption: 'Conf. visage', size: '100px', render: 'float:2', sortable: true, resizable: true},
        { field: 'confiancePhoto', caption: 'Conf. photo', size: '100px', render: 'float:2', sortable: true, resizable: true},
        { field: 'pertinence', caption: 'Pertinence', size: '100px', sortable: true, resizable: true},
        { field: 'url', caption: 'Référence', size: '100px', sortable: true, resizable: true},
        { field: 'data', caption: 'Lien', size: '100%', sortable: true, resizable: true},
    ],
    onClick: function (event) {
        sltActeur = this.get(event.recid);
        showDetailsActeur();
    },
    onAdd: function(event) {
        sltActeur = false;
        showAddActeur();
	},
    onDelete: function (event) {
		if(event.force){	
			deleteActeur(sltActeur.recid);
		}
    },
    toolbar: {
        items: [
        ],
        onClick: function (event) {
        		if(event.target == 'w2ui-add')return;
        		var acteur = w2ui.grid_acteur.get(w2ui.grid_acteur.getSelection());
        		idUpdate = false;
        		if(!acteur){
        			w2alert("Veuillez sélectionner un acteur");
        			return;
        		}else acteur = acteur[0];
        }
    },	
}; 

var gridActeurAdd = {
	    name: 'grid_acteur_add', 
		header: "Associer un acteur",		
		show: {toolbar		: true,
			footer			: false,
			toolbarReload   : false,
			toolbarColumns  : false,
			toolbarSearch   : false,
			toolbarInput		: false, 
	        	toolbarAdd      : false,
	        	toolbarDelete   : false,
	        	toolbarSave		: false,
	        	header			: true, 
	        	columnHeaders	: true},
	    columns: [      		  		           
	        { field: 'recid', caption: 'ID', size: '50px', hidden:false, sortable: true, resizable: true },
	        { field: 'prenom', caption: 'Prénom', size: '100px', sortable: true, resizable: true, editable: { type: 'text' }},
	        { field: 'nom', caption: 'Nom', size: '100px', sortable: true, resizable: true, editable: { type: 'text' }},
	        { field: 'nait', caption: 'nait', size: '100px', sortable: true, resizable: true, editable: { type: 'date' }},
	        { field: 'mort', caption: 'mort', size: '100px', sortable: true, resizable: true, editable: { type: 'date' }},
	        { field: 'confiance', caption: 'confiance', size: '100px', sortable: true, resizable: true, editable: { type: 'percent', precision: 1} },
	        { field: 'url', caption: 'référence', size: '100px', sortable: true, resizable: true},
	        { field: 'data', caption: 'lien', size: '100%', sortable: true, resizable: true},
	    ],
	    toolbar: {
	        items: [
	            { id: 'creer_acteur', type: 'button', caption: 'Créer un acteur', icon: 'fa-user' },
	            { id: 'find_acteur', type: 'button', caption: 'Chercher dans le LOD', icon: 'fa-search' },
	            //{ id: 'ajout_contexte', type: 'button', caption: 'Ajouter au contexte', icon: 'fa-users' },
	            { id: 'lier_photo', type: 'button', caption: 'Lier à la photo', icon: 'fa-link' },	       	            
	            { id: 'lier_visage', type: 'button', caption: 'Nommer le visage', icon: 'fa-smile-o' },	       	            
	            { id: 'modif_acteur', type: 'button', caption: "Modifier l'acteur", icon: 'fa-refresh', disabled: notAdmin },     
	        ],
	        onClick: function (event) {
	        		//on affiche le layout original
		        	w2ui['layout_acteur_add'].set('left', { size: '400px' });
		        	w2ui['layout_acteur_add'].set('right', { size: '30%' });
		        	w2ui['layout_acteur_add'].set('main', { size: '40%' });
		        	//on récupère les données du grid	
	        		var acteur = w2ui.grid_acteur_add.records[0];
        			var c = w2ui.grid_acteur_add.getChanges();
        			/*
	        		if(c.nom=='-'){
	        			w2alert("Veuillez modifier le nom de l'acteur");
	        			return;
	        		}
	        		*/	        		
		        if (event.target == 'creer_acteur') {
		        		sltActeur={};
		        		showAddActeur(dataActeurVide); 	
	            }
	            if (event.target == 'ajout_contexte') {
	            		console.log(acteur);
	            }
	            if (event.target == 'find_acteur') {
	            		var q = "";
	            		if(c.length){
	            			if(c[0].prenom)q = c[0].prenom;
            				if(c[0].nom)q += c[0].nom;
		            		findActeurGoogle(q);
	            		}else
	            			findActeurGoogle(acteur.prenom+' '+acteur.nom);
	            }
	            if (event.target == 'lier_photo') {
	            		ajoutActeurLien(getSaisiActeur(acteur, c),tofSelect['pId'],tofSelect);
	            }	            
	            if (event.target == 'lier_visage') {
	            		ajoutActeurLien(getSaisiActeur(acteur, c),tofSelect['doc_id'],tofSelect);
	            }	            
	            if (event.target == 'modif_acteur') {
	            		modifierActeur(getSaisiActeur(acteur, c));
	            }	            
	        }
	    },	
	}; 

function getSaisiActeur(a, c){
	for (var v in c[0]){
		a[v]=c[0][v];
	}
	delete a['w2ui'];
	delete a['exi_id'];
	delete a['pertinence'];
	delete a['pertinenceParent'];
	delete a['pertinenceTof'];	        			
	delete a['confiancePhoto'];	        			
	delete a['nbVote'];	        			
	delete a['confianceVisage'];	        			

	return a;
}

var gridResultLiens = {
        name: 'grid_result_liens', 
		header: 'Liens de références',		
        show: { 
			header	: true,		
            	footer	: true,
        },		
        columns: [                
            { field: 'recid', hidden:true},
            { field: 'value', caption: 'Lien', size: '80%',editable: { type: 'text' } },
            { field: 'type', caption: 'Type', size: '20%',editable: { type: 'text' } },
        ],
        sort: [
            { field: "value", direction: "ASC" },
            { field: "type", direction: "ASC" }
        ],        
        onClick: function (event) {
        		var item = this.get(event.recid);
			$('#ifActeurLod').attr("src",item.value);            		
        }	    
	};

var gridResultGoogleKG = {
        name: 'grid_result_googlekg', 
        recordHeight : 200,
		header: 'Resultat de la recherche',		
        show: { 
			header	: true,		
            	footer	: true,
        },		
        columns: [                
            { field: 'id', caption: 'ID', size: '100px', hidden:true },
            { field: 'name', caption: 'Nom', size: '100px'},
            { field: 'type', caption: 'Type', size: '100px'},
            { field: 'desc', caption: 'Description', size: '100%',
                render: function (record) {
                    return '<div style="width: 190px; margin: auto; height:180px;white-space: normal;" >' + record.desc + '</div>';
                }},
        ],
        onClick: function (event) {
			//if(itemLodSelect && itemLodSelect.recid == event.recid) return;
			itemLodSelect = this.get(event.recid);
			selectActeurGoogle(itemLodSelect);
        }	    
	};

function showNommerActeur(dt){
	tofSelect = dt;
	w2popup.open({
        title   : 'Identifier les acteurs',
        showMax : true,
        /*
        buttons   : '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">'+
        '<img class="imgButton" alt="Ajouter un document" onclick="openPopupAjoutDoc()" src="'+prefUrl+'img/document107.png">'+
        '<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',
        */
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"><div id="content" style="width: 100%; height: 100%;"></div></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
                //on nomme qu'un seul acteur
            		initPopUpActeurs();
                	w2popup.max();
                	w2popup.lock("<p>Merci de patienter...</p>", true);
            };
        },
    });	
	
}

function initPopUpActeurs(){

	//récupère la liste des acteurs du contexte
    $.getJSON("../flux/an?q=getActeursContexte&idDoc="+tofSelect['gpId']+"&idTheme="+tofSelect['pId']+"&idVisage="+tofSelect['doc_id']+"&idBase="+idBase,
        function(data){
    			gridActeur.records=data;
    			sltActeur = false;
        		//réinitialise les composants
		    if(w2ui['layout_acteur'])w2ui['layout_acteur'].destroy();
		    if(w2ui['grid_acteur'])w2ui['grid_acteur'].destroy();            	 	
			//active les composants
	    		$('#w2ui-popup #content').w2layout(lyActeur);
		    	w2ui['layout_acteur'].content('top', $().w2grid(gridActeur));   
		    	w2ui['layout_acteur'].content('left', getPopUpTof(tofSelect,0));            		            		        					
		    	showDetailsActeur();
        
        		//supprime le message de chargement	
        	 	w2popup.unlock();
        		
        });
	
	/*
	gridActeur.records=datas["Acteurs"];
		
	*/

}

function showDetailsActeur(){

	//affiche le détail du lien		
	if(sltActeur){
		showAddActeur(sltActeur); 		
		if(sltActeur.url){
			getActeurGoogle(sltActeur);
		}	            					
	}else{
		showAddActeur(dataActeurVide); 	
		showPhotoOriginale();
	}
}

function showPhotoOriginale(){
	w2ui['layout_acteur_add'].set('left', { size: '0px' });
	w2ui['layout_acteur_add'].set('right', { size: '0px' });
	w2ui['layout_acteur_add'].set('main', { size: '100%' });
	w2ui['layout_acteur_add'].content('main', "<div><img src='"+tofSelect['original']+"' width='100%' /></div>");				
}


function showAddActeur(dt){
	
	if(w2ui['layout_acteur_add'])w2ui['layout_acteur_add'].destroy();
	if(w2ui['grid_acteur_add'])w2ui['grid_acteur_add'].destroy();
	//chargement des données
	gridActeurAdd.records=[dt];	
	//chargement des layouts
	w2ui['layout_acteur'].content('main', $().w2layout(lyActeurAdd)); 
	w2ui['layout_acteur_add'].content('top', $().w2grid(gridActeurAdd));	
	
}

function showFindActeur(){
	if(dtActeurFind.length==0)w2alert("Aucun acteur trouvé.");
	//création du tableau des résultats
	var dtGKG = [];
	dtActeurFind.forEach(function(d,i){
		var r = {type:d.result["@type"].join(', '),recid:i,id:d.result["@id"],name:d.result.name,url:d.result.url,data:d.result,img:"",detail:""};
		if(d.result.image)r.img=d.result.image.contentUrl;
		if(d.result.detailedDescription)r.desc=d.result.detailedDescription.articleBody;
		dtGKG.push(r);
	});
	
	//affiche les résultats
	gridResultGoogleKG.records = dtGKG;
 	if(w2ui['grid_result_googlekg'])w2ui['grid_result_googlekg'].destroy();	
 	
 	w2ui['layout_acteur_add'].content('left', $().w2grid(gridResultGoogleKG));		
 	w2ui['layout_acteur_add'].content('main', '');		
 	w2ui['layout_acteur_add'].content('right', htmlIframeVide.replace(/idIf/gi,'ifActeurLod'));		
		
}

function showSelectActeurGoogle(dt){
	
 	if(w2ui['grid_result_liens'])w2ui['grid_result_liens'].destroy();	
	gridResultLiens.records = dt.liens;
	w2ui['layout_acteur_add'].content('main', $().w2grid(gridResultLiens));		
 	w2ui['layout_acteur_add'].content('right', htmlIframeVide.replace(/idIf/gi,'ifActeurLod'));	
 	modifActeurGoogle(dt);
}

function showDetailsLOD(dt){

	//récupère l'image
	var img = "";
	if(dt.data.image)img='<img src="'+dt.data.image.contentUrl+'" />';
	else{
		dt.liens.forEach(function(d){
			if(d.type=='img'){
				img += '<img src="'+d.value+'" />';
			}
		})
	}
	if(!dt.nom)dt.nom=sltActeur.nom;
	if(!dt.prenom)dt.prenom=sltActeur.prenom;
	if(!dt.data['@type'])dt.data['@type']=[];
	
	
	//affiche les données knowledge graph		
	var html = '<div style="width: 100%;">'
		+'<h1>'+dt.prenom+' '+dt.nom+'</h1>'
		+'<div style="width: 100%;"><h2>'+dt.data['@id']+'</h2><h3>'+dt.data['@type'].join(', ')+'</h3>'
		+img+'<p>'+dt.abstract+'</p></div>';
		+'</div>';
	w2ui['layout_acteur_add'].content('left', html);		
	
}

function modifActeurGoogle(dt){
	
	//ajoute le lien vers wikipedia s'il existe
	var liens = dt.liens.filter(function(d){
		return d.type == 'wikipedia';
	});	
	//met à jour le formulaire d'acteur	
	if(sltActeur){
		if(!sltActeur.url)sltActeur.url = dt.data['@id'] ? dt.data['@id'].substring(3) : "";
		if(!sltActeur.data)sltActeur.data = liens.length ? liens[0].value : "";
		if(!sltActeur.nom)sltActeur.nom = dt.nom ? dt.nom : "";
		if(!sltActeur.prenom)sltActeur.prenom = dt.prenom ? dt.prenom : "";
		if(!sltActeur.nait)sltActeur.nait = dt.nait ? dt.nait : "";
		if(!sltActeur.mort)sltActeur.mort = dt.mort ? dt.mort : "";
		sltActeur.confiance = 0;
	}else{
		sltActeur.recid = 0;
		sltActeur.confiance = 0;
		sltActeur.url = dt.data['@id'] ? dt.data['@id'].substring(3) : "";
		sltActeur.data = liens.length ? liens[0].value : "";
		sltActeur.nom = dt.nom ? dt.nom : "";
		sltActeur.prenom = dt.prenom ? dt.prenom : "";
		sltActeur.nait = dt.nait ? dt.nait : "";
		sltActeur.mort = dt.mort ? dt.mort : "";
	}
	
	w2ui['grid_acteur_add'].records=[sltActeur];	
	w2ui['grid_acteur_add'].refresh();
	
}

