var dialogues;
//var pstyle = 'border: 1px solid #dfdfdf; padding: 5px;';
var pstyle = 'padding:3px;';

function showReseau(data){
	//change les valeurs null en array vide pour éviter les plantage de D3
	data.nodes ? nodes = data.nodes : nodes = [];
	data.links ? links = data.links : links = [];
	data.posis ? posis = data.posis : posis = [];
	rs = new reseau({"idCont":"viz", "w":1280, "h":720,"nodes":nodes
		,"links":links,"posis":posis,"colors":colors,"clusters":clusters, "dialogues":dialogues});
}

var gridCrible = { 
    name: 'grid_crible', 
	header: 'Cribles disponibles',		
	show: {toolbar		: true,
		toolbarReload   : false,
		toolbarColumns  : false,
        	toolbarSearch   : false,
        	toolbarAdd      : true,
        	toolbarDelete   : true,
        	toolbarSave		: false,
        	header			: true, 
        	columnHeaders	: true},
    columns: [      		  		           
        { field: 'recid', caption: 'ID', size: '50px', hidden:true, sortable: true, resizable: true },
        { field: 'nom', caption: 'Nom', size: '100%', sortable: true, resizable: true},
    ],
    records: rsCribles,
    onClick: function (event) {
        sltCrible = this.get(event.recid);
        	chargeCrible(sltCrible);		        	
    },        				        
};    
var gridGraph = { 
    name: 'grid_graph', 
	header: 'Graphs enregistrés',		
	show: {toolbar		: true,
		toolbarReload   : false,
		toolbarColumns  : false,
        	toolbarSearch   : false,
        	toolbarAdd      : true,
        	toolbarDelete   : true,
        	toolbarSave		: false,
        	header			: true, 
        	columnHeaders	: true},
    columns: [      		  		           
        { field: 'recid', caption: 'ID', size: '50px', hidden:true, sortable: true, resizable: true },
        { field: 'titre', caption: 'Titre', editable: { type: 'text' }, size: '10%', sortable: true, resizable: true},
        //{ field: 'auteur', caption: 'Process', hidden:true, size: '100px', sortable: true, resizable: true},
        //{ field: 'crible', caption: 'Process', hidden:true, size: '100px', sortable: true, resizable: true},
    ],
    records: rsGraphs,
    onAdd: function(event) {
    		var data = {"titre":"nouveau graph","tronc":"graphInfluence","obj":"graph"};
    		$.get(prefUrl+"biolographes/ajout",
			data,
        		function(js){
    				finsession(js);
    				rsGraphs.push(js.rs);
    				w2ui.grid_graph.add(js.rs);
        			w2alert(js.message);
       		},"json");		
	},    
	onDelete: function(event) {      
		//vérifie que l'utilisateur est propriétaire du graph
		if(sltGraph.uti_id!=uti.uti_id){
			w2alert("Vous ne pouvez pas supprimer un graph qui ne vous appartient pas.");
			return;
		}
		var p = {id:sltGraph.recid, obj:'graph'};
		if(event.force){						
			var g = w2ui[event.target];
            	$.get(prefUrl+'biolographes/delete',
				p,
            		function(js){
            			finsession(js);
            			w2alert(js.message);
           		},"json");
		}
    },
    toolbar: {
        items: [
            { id: 'edit_graph', type: 'button', caption: 'Sauver', icon: 'fa-file' },
        ],
        onClick: function (event) {
        		if(!w2ui.grid_graph.getSelection())	w2alert("Veuillez sélectionner un graph");
            if (event.target == 'edit_graph') {
            		var changes = w2ui.grid_graph.getChanges();
            		var data={};
            		if(changes.length > 0) data = changes[0];
            		else data.recid = sltGraph.recid;
            		//sauvegarde les positions            		
            		rs.force.stop();
            		data.posis={"n":[],"l":[]};
            		d3.selectAll(".node")[0].map(function (d) {
            		      if (d) {
            		    	  	var dt = d.__data__;
            		    	  	data.posis.n[dt.id] = [dt.x,dt.y];
            		    	  	}
            		}); 
            		//sauvegarde les datas liées aux objets
            		nodes ? data.nodes = nodes : data.nodes=[];
            		links ? data.links = links : data.links=[];
            		data.obj='graph';
        	        	$.post(prefUrl+'biolographes/edit',
	        			data,
	        			function(js){
	            			finsession(js);
	        				w2alert(js.message);
	        			},"json");                        
            }
        }
    },	
    onClick: function (event) {
        sltGraph = this.get(event.recid);
        d3.select("#titreGraph").text(sltGraph.titre);
		$.get(prefUrl+"biolographes/get",
				{"obj":"graph","id":sltGraph.recid},
	        		function(js){
	    				finsession(js);
	    				var data = JSON.parse(js.rs["data"]);
	    		        showReseau(data);	        	
	       		},"json");		
        
    },        				        
}; 
var gridActeur = {
    name: 'grid_acteur', 
	header: 'Acteurs',		
	show: {toolbar		: true,
		toolbarReload   : false,
		toolbarColumns  : false,
        	toolbarSearch   : true,
        	toolbarAdd      : true,
        	toolbarDelete   : true,
        	toolbarSave		: true,
        	header			: true, 
        	columnHeaders	: true},
    columns: [      		  		           
        { field: 'recid', caption: 'ID', size: '50px', hidden:true, sortable: true, resizable: true },
        { field: 'prenom', caption: 'Prénom', size: '100px', sortable: true, resizable: true},
        { field: 'nom', caption: 'Nom', size: '100px', sortable: true, resizable: true},
        { field: 'isni', caption: 'ISNI', size: '100px', sortable: true, resizable: true},
        { field: 'nait', caption: 'nait', size: '100px', sortable: true, resizable: true},
        { field: 'mort', caption: 'mort', size: '100px', sortable: true, resizable: true},
    ],
    onClick: function (event) {
        var sltGraph = this.get(event.recid);
    },
    onAdd: function(event) {
		isUpdate = false;
    		showRefActeur();
	},
    toolbar: {
        items: [
            { id: 'ajout_reseau', type: 'button', caption: 'Ajouter au réseau', icon: 'fa-file' },
            { id: 'find_bnf', type: 'button', caption: 'Trouver dans DataBNF', icon: 'fa-file-excel-o' }
        ],
        onClick: function (event) {
        		if(!w2ui.grid_acteur.getSelection())	w2alert("Veuillez sélectionner un acteur");
        		var acteur = w2ui.grid_acteur.get(w2ui.grid_acteur.getSelection());
            if (event.target == 'ajout_reseau') {
            		idUpdate = false;
            		rs.creaNode("Acteurs",acteur.nom,acteur);
            }
            if (event.target == 'find_bnf') {
            		idUpdate = acteur.recid;
            		showRefActeur(acteur.prenom+' '+acteur.nom);
            }
        }
    },	
}; 
var formActeur = { 
    header: 'Information sur l\'acteur',
    msgSaving  : 'Merci de patienter...',
    name: 'form_acteur',
    focus  : -1,
    fields: [
        { name: 'nom', type: 'text', html: { caption: 'Nom' } },
        { name: 'prenom', type: 'text', html: { caption: 'Prénom' } },
        { name: 'nait', type: 'date', options:{format: 'yyyy-mm-dd'}, html: { caption: 'Date de naissance' } },
        { name: 'mort', type: 'date', options:{format: 'yyyy-mm-dd'}, html: { caption: 'Date de mort' } },
        { name: 'isni', type: 'text', html: { caption: 'ISNI' } },
        //{ name: 'profession', type: 'text', html: { caption: 'Profession' } },
        //{ name: 'specialite', type: 'text', html: { caption: 'Spécialité' } },
        //{ name: 'fonction', type: 'text', html: { caption: 'Fonction' } },
    ],
    actions: {
        Reset: function () {
            w2popup.close();
        },
	    Save: function () {
	        var errors = this.validate();
	        if (errors.length > 0) return;
	        var data = this.record;
	        data.obj = "acteur";
	        var url = 'biolographes/ajout'
	        if(idUpdate){
	        		url = 'biolographes/edit';
	        		data.recid = idUpdate;
	        }	        
	        	$.get(prefUrl+url,
				data,
	        		function(js){
	    				finsession(js);
	    				if(idUpdate){
	    					//mise à jour de la data
	    					rsActeurs.forEach(function(d, i){
	    						if(d.recid==js.rs.recid)rsActeurs[i]=js.rs;
	    					});
	    				}else{
	    					rsActeurs.push(js.rs);
	    				}
	        			w2alert(js.message);
	        			openPopupAjoutActeur();
	       		},"json");
	    }
    }
};
var tbActeur = {
    name: 'tb_acteur',
    items: [
        { type: 'html',  id: 'ipNom',
            html: '<div style="padding: 3px 10px;">'+
                  ' Nom de l\'acteur:'+
                  '    <input id="tb_acteur_ip" size="100" style="padding: 3px; border-radius: 2px; border: 1px solid silver"/>'+
                  '</div>' 
        },
        { type: 'button',  id: 'btnGetRef',  caption: 'DataBNF', icon: 'fa-search' },
    ],
    onClick: function (event) {
		if(event.target=="btnGetRef"){
			findAuteur($('#tb_acteur_ip')[0].value);			
		}
		if(event.target=="btnGetBio"){
			findActeur($('#tb_acteur_ip')[0].value);			
		}
    },
	onRender: function(event) {
		event.onComplete = function () {
		    document.getElementById("tb_acteur_ip").focus();		            			        			
	    }		        
	},

};
var lyActeur = {
        name: 'layout_acteur',
        panels: [
            { type: 'top', size: 50, style: pstyle, content: '',resizable: true },
            { type: 'left', size:"400px", style: pstyle, content: '',resizable: true },
            { type: 'main', size:"60%", style: pstyle, content: '',resizable: true },
            { type: 'bottom', size: "40%", style: pstyle, content: '',resizable: true }
        ],
    };
var lyActeurBottom = {
        name: 'layout_acteur_bottom',
        panels: [
            { type: 'left', size:"400px", style: pstyle, content: '',resizable: true },
            { type: 'main', size:"40%", style: pstyle, content: '<iframe id="ifActeur" src="'+prefUrl+'vide.html" height="100%" width="100%" />',resizable: true },
        ]
    };

var formRef = { 
    header: 'Information sur la référence',
    msgSaving  : 'Merci de patienter...',
    name: 'form_ref',
    fields: [
        { name: 'titre', type: 'text', html: { caption: 'Titre' } },
        { name: 'url', type: 'text', html: { caption: 'URL' } },
        { name: 'pubDate', type: 'date', options:{format: 'yyyy-mm-dd'}, html: { caption: 'Date de publication' } },
        { name: 'type', type: 'text', html: { caption: 'Type' } },
        { name: 'data', type: 'textarea'
            , html: { caption: 'Données :', attr: 'style="width: 100%; height: 100px; resize: none"'} 
       	}
    ],
    actions: {
        Reset: function () {
            w2popup.close();
        },
	    Save: function () {
	        var errors = this.validate();
	        if (errors.length > 0) return;
	        var data = this.record;
	        data.obj = "ref";
	        	$.get(prefUrl+'biolographes/ajout',
				data,
	        		function(js){
	    				finsession(js);
	        			w2alert(js.message);
	       		},"json");
	    }
    }
};
var tbRef = {
    name: 'tb_ref',
    items: [
        { type: 'html',  id: 'ipTitre',
            html: '<div style="padding: 3px 10px;">'+
                  ' Titre de la référence :'+
                  '    <input size="100" style="padding: 3px; border-radius: 2px; border: 1px solid silver"/>'+
                  '</div>' 
        },
        { type: 'button',  id: 'btnGetRef',  caption: 'DataBNF', icon: 'fa-search' }
    ]
};
var lyRef = {
        name: 'layout_ref',
        panels: [
            { type: 'top', size: 50, style: pstyle, content: 'top',resizable: true },
            { type: 'left', size: "400px", style: pstyle, content: 'left',resizable: true },
            { type: 'main', size: "40%", style: pstyle, content: 'main',resizable: true }
        ]
    };


var gridTag = {
	    name: 'grid_tag', 
		header: 'Notions',		
		show: {toolbar		: true,
			toolbarReload   : false,
			toolbarColumns  : false,
	        	toolbarSearch   : true,
	        	toolbarAdd      : true,
	        	toolbarDelete   : true,
	        	toolbarSave		: true,
	        	header			: true, 
	        	columnHeaders	: true},
	    columns: [      		  		           
	        { field: 'recid', caption: 'ID', size: '50px', hidden:true, sortable: true, resizable: true },
	        { field: 'code', caption: 'Libellé', size: '10%', sortable: true, resizable: true},
	        { field: 'parent1', caption: 'Parent', size: '10%', sortable: true, resizable: true},
	        { field: 'uri', caption: 'lien de référence', size: '10%', sortable: true, resizable: true},
	    ],
	    onClick: function (event) {
	        var sltTag = this.get(event.recid);
	    },
	    onAdd: function(event) {
	    		showRefTag();
		},
	    toolbar: {
	        items: [
	            { id: 'ajout_reseau', type: 'button', caption: 'Ajouter au réseau', icon: 'fa-file' },
	            { id: 'find_bnf', type: 'button', caption: 'Trouver dans DataBNF', icon: 'fa-file-excel-o' }
	        ],
	        onClick: function (event) {
	        		if(!w2ui.grid_tag.getSelection())	w2alert("Veuillez sélectionner une notion");
	        		var tag = w2ui.grid_tag.get(w2ui.grid_tag.getSelection());
	            if (event.target == 'ajout_reseau') {
	            		idUpdate = false;
	            		rs.creaNode("Notions",tag.code,tag);
	            }
	            if (event.target == 'find_bnf') {
	            		idUpdate = tag.recid;
	            		showRefTag(tag.code);
	            }
	        }
	    },	
	}; 
var formTag = { 
    header: 'Information sur la notion',
    msgSaving  : 'Merci de patienter...',
    name: 'form_tag',
    fields: [
        { name: 'code', type: 'text', html: { caption: 'Libellé' } },
        { name: 'desc', type: 'text', html: { caption: 'Description' } },
        { name: 'uri', type: 'text', html: { caption: 'URI' } },
        { name: 'parent', type: 'list', options: { items: rsTags }
			, html: { caption: 'Parent', attr: 'size="20"' } 
		},
    ],
    actions: {
        Reset: function () {
            w2popup.close();
        },
	    Save: function () {
	        var errors = this.validate();
	        if (errors.length > 0) return;
	        var data = this.record;
	        data.obj = "tag";
	        	$.get(prefUrl+'biolographes/ajout',
				data,
	        		function(js){
    				if(idUpdate){
    					//mise à jour de la data
    					rsTags.forEach(function(d, i){
    						if(d.recid==js.rs.recid)rsTags[i]=js.rs;
    					});
    				}else{
    					rsTags.push(js.rs);
    				}
        			w2alert(js.message);
        			openPopupAjoutTag();
	       		},"json");
	    }
    }
};
var tbTag = {
    name: 'tb_tag',
    items: [
        { type: 'html',  id: 'ipCode',
            html: '<div style="padding: 3px 10px;">'+
                  ' Libellé de la notion :'+
                  '    <input id="tb_tag_ip" size="100" style="padding: 3px; border-radius: 2px; border: 1px solid silver"/>'+
                  '</div>' 
        },
        { type: 'button',  id: 'btnGetRef',  caption: 'Rechercher sur DataBNF', icon: 'fa-search' }
    ],
	onClick: function (event) {
		if(event.target=="btnGetRef"){
			findTag($('#tb_tag_ip')[0].value);			
		}
	},
	onRender: function(event) {
		event.onComplete = function () {
		    document.getElementById("tb_tag_ip").focus();		            			        			
	    }		        
	},
};

var lyTag = {
        name: 'layout_tag',
        panels: [
            { type: 'top', size: 50, style: pstyle, content: '',resizable: true },
            { type: 'left', size:"30%", style: pstyle, content: '',resizable: true },
            { type: 'main', size:"220px", style: pstyle, content: '',resizable: true },
            { type: 'bottom', size:"40%", style: pstyle, content: '<iframe id="ifTag" src="'+prefUrl+'vide.html" height="100%" width="100%" />',resizable: true }
        ],
    };

var gridResultBNF = {
        name: 'grid_result_bnf', 
		header: 'Resultat de la recherche',		
        show: { 
			header	: true,		
            	footer	: true,
        },		
        columns: [                
            { field: 'label', caption: 'Nom', size: '60%' },
            { field: 'value', caption: 'Lien', size: '200px',editable: { type: 'text' } },
        ],
        onClick: function (event) {
			if(itemSelect && itemSelect.recid == event.recid) return;
			itemSelect = this.get(event.recid);
			if(itemSelect.raw_category=="Rameau"){
				itemSelect.parent = idTagRameau;
				selectTag(event.recid);				
				setSelectTag(itemSelect);
			}
			if(itemSelect.raw_category=="Person")selectAuteur(event.recid);
        }	    
	};
var gridResultBNFliens = {
        name: 'grid_result_bnf_liens', 
		header: 'Liens de références',		
        show: { 
			header	: true,		
            	footer	: true,
        },		
        columns: [                
            { field: 'recid', hidden:true},
            { field: 'value', caption: 'Lien', size: '100%',editable: { type: 'text' } },
        ],
        onClick: function (event) {
        		var item = this.get(event.recid);
			$('#ifActeur').attr("src",item.value);            		
        }	    
	};

function openPopupAjoutActeur(){
	
	w2popup.open({
        title   : 'Ajouter un acteur',
        showMax : true,
        buttons   : '<img class="imgButton" alt="Ajouter une référence" onclick="openPopupAjoutRef()" src="'+prefUrl+'img/document107.png">'+
  		'<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',        
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
            		gridActeur.records=rsActeurs;
            	 	if(w2ui['grid_acteur'])w2ui['grid_acteur'].destroy();
            		$('#w2ui-popup #main').w2grid(gridActeur);
                	w2popup.max();
            		w2popup.resize(1000, 500);
            };
        },
    });
}	

function showRefActeur(cherche){
	
 	if(w2ui['layout_acteur'])w2ui['layout_acteur'].destroy();
 	if(w2ui['form_acteur'])w2ui['form_acteur'].destroy();
 	if(w2ui['tb_acteur'])w2ui['tb_acteur'].destroy();
    	$('#w2ui-popup #main').w2layout(lyActeur);            		
    	w2ui['layout_acteur'].content('left', $().w2form(formActeur));            		            		
    	w2ui['layout_acteur'].content('top', $().w2toolbar(tbActeur));
    	w2popup.max();
	if(cherche){
		findAuteur(cherche);
	}
}

function openPopupAjoutRef(){
	
	w2popup.open({
        title   : 'Ajouter une référence',
        buttons   : '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">'+
  		'<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',        
        width   : 1000,
        height  : 500,
        body    : '<div id="main" style="height:400px;width:988px;position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        showMax : true,
        onOpen  : function (event) {
            event.onComplete = function () {
            		w2popup.resize(1000, 500);
            	 	if(w2ui['layout_ref'])w2ui['layout_ref'].destroy();
            	 	if(w2ui['form_ref'])w2ui['form_ref'].destroy();
	        	 	if(w2ui['tb_ref'])w2ui['tb_ref'].destroy();
            		$('#w2ui-popup #main').w2layout(lyRef);            		
            		w2ui['layout_ref'].content('top', $().w2toolbar(tbRef));            		
            		w2ui['layout_ref'].content('left', $().w2form(formRef));            		
            		
            };
        },
    });
}	

function openPopupAjoutTag(){
	
	w2popup.open({
        title   : 'Ajouter une notion',
        buttons   : '<img class="imgButton" alt="Ajouter une référence" onclick="openPopupAjoutRef()" src="'+prefUrl+'img/document107.png">'+
        '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">',
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        showMax : true,
        onOpen  : function (event) {
            event.onComplete = function () {
	        		gridTag.records=rsTags;
	        	 	if(w2ui['grid_tag'])w2ui['grid_tag'].destroy();
	        		$('#w2ui-popup #main').w2grid(gridTag);
	            	w2popup.max();
	        		//w2popup.resize(1000, 500);
            };
        },
    });
}	

function showRefTag(cherche){
	
 	if(w2ui['layout_tag'])w2ui['layout_tag'].destroy();
 	if(w2ui['form_tag'])w2ui['form_tag'].destroy();
 	if(w2ui['tb_tag'])w2ui['tb_tag'].destroy();
    	$('#w2ui-popup #main').w2layout(lyTag);            		
    	w2ui['layout_tag'].content('left', $().w2form(formTag));            		            		
    	w2ui['layout_tag'].content('top', $().w2toolbar(tbTag));
    	w2popup.max();
	if(cherche){
		findTag(cherche.trim());
	}
}
function setFindAuteur(){
	if(dtAuteurFind.length==0)w2alert("Aucun acteur trouvé.");
	//ajoute un recid
	dtAuteurFind.forEach(function(d,i){
		d.recid = i;
	});
	//affiche les résultats
	gridResultBNF.records = dtAuteurFind;
 	if(w2ui['grid_result_bnf'])w2ui['grid_result_bnf'].destroy();	
	w2ui['layout_acteur'].content('main', $().w2grid(gridResultBNF));
 	if(w2ui['layout_acteur_bottom'])w2ui['layout_acteur_bottom'].destroy();	
	w2ui['layout_acteur'].content('bottom', $().w2layout(lyActeurBottom));            		
	
}


function setSelectAuteur(dt){
	if(dt.length==0 || dt.idArk==null)
		w2alert("Aucunne référence dans data.bnf.fr");
	else{
        var g = w2ui['form_acteur'];
        g.clear();
		g.record = dt;
		g.refresh();
		$('#ifActeur').attr("src",dt.idArk);            		
		
		gridResultBNFliens.records = dt.liens;
	 	if(w2ui['grid_result_bnf_liens'])w2ui['grid_result_bnf_liens'].destroy();	
		w2ui['layout_acteur_bottom'].content('left', $().w2grid(gridResultBNFliens));
		
	}
}

function setFindTag(){
	if(dtTagFind.length==0)w2alert("Aucune notion trouvée.");
	//ajoute un recid
	dtTagFind.forEach(function(d,i){
		d.recid = i;
	});
	//affiche les résultats
	gridResultBNF.records = dtTagFind;
 	if(w2ui['grid_result_bnf'])w2ui['grid_result_bnf'].destroy();	
	w2ui['layout_tag'].content('main', $().w2grid(gridResultBNF));
 	//if(w2ui['layout_acteur_bottom'])w2ui['layout_acteur_bottom'].destroy();	
	//w2ui['layout_acteur'].content('bottom', $().w2layout(lyActeurBottom));            		
	
}


function setSelectTag(dt){
    var g = w2ui['form_tag'];
    g.clear();
	g.record = {"code":dt.label,"desc":"","uri":dt.value,"parent":dt.parent};
	g.refresh();
}