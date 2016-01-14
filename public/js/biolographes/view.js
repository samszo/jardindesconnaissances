var dialogues;
//var pstyle = 'border: 1px solid #dfdfdf; padding: 5px;';
var pstyle = 'padding:3px;';
var popMax = false; //pour gérer l'affichage plein écran de la carte
var map, geocoder, markers = [];

var colors =  d3.scale.ordinal()
.domain(["Acteur","Notion","Document"])
.range(["green","red","yellow"]);
var clusters =  d3.scale.ordinal()
.domain(["REFERENCES","EVENEMENTS","ACTEURS","CONCEPTS"])
.range([100,200,300,500]);


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
            		editGraph();
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
            		rs.creaNode("Acteur",acteur.nom,acteur);
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
	    					datas["Acteurs"].forEach(function(d, i){
	    						if(d.recid==js.rs.recid)datas["Acteurs"][i]=js.rs;
	    					});
	    				}else{
	    					datas["Acteurs"].push(js.rs);
	    				}
	    			    editGraph();
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
        { type: 'button',  id: 'btnGetBio',  caption: 'DataBNF', icon: 'fa-search' },
    ],
    onClick: function (event) {
		if(event.target=="btnGetBio"){
			findAuteur($('#tb_acteur_ip')[0].value);			
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

var gridDoc = {
	    name: 'grid_doc', 
		header: 'Documents',		
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
	        { field: 'titre', caption: 'Titre', size: '10%', sortable: true, resizable: true},
	        { field: 'url', caption: 'URL', size: '10%', sortable: true, resizable: true},
	        { field: 'pudDate', caption: 'Date de publication', size: '10%', sortable: true, resizable: true},
	        { field: 'parent', caption: 'Parent', size: '10%', sortable: true, resizable: true},
	    ],
	    onClick: function (event) {
	    		docSelect = this.get(event.recid);
	    		if(docSelect.url)$('#ifDocList').attr("src",docSelect.url);            		
	    		//sélectionne les fragment du document
	        	$.get(prefUrl+'biolographes/get',
	    				{"obj":"fragment","idParent":docSelect.recid},
	    	        		function(js){
	    	    				finsession(js);
	    	    				if(js.rs.length)w2ui.grid_doc_frag.add(js.rs);
	    	    				else w2ui.grid_doc_frag.records = [];
	    	    				w2ui.grid_doc_frag.refresh();
	    	        			//w2alert(js.message);
	    	       		},"json");	    		
	    		
	    },
	    onAdd: function(event) {
	    		showRefDoc();
		},
	    toolbar: {
	        items: [
	            { id: 'ajout_reseau', type: 'button', caption: 'Ajouter au réseau', icon: 'fa-file' },
	            { id: 'find_bnf', type: 'button', caption: 'Trouver dans DataBNF', icon: 'fa-file-excel-o' }
	        ],
	        onClick: function (event) {
	        		if(!w2ui.grid_doc.getSelection())	w2alert("Veuillez sélectionner un document");
	        		var doc = w2ui.grid_doc.get(w2ui.grid_doc.getSelection());
	            if (event.target == 'ajout_reseau') {
	            		idUpdate = false;
	            		rs.creaNode("Document",doc.titre,doc);
	            }
	            if (event.target == 'find_bnf') {
	            		idUpdate = tag.recid;
	            		showRefDoc(tag.titre);
	            }
	        }
	    },	
	}; 
var formDoc = { 
    header: 'Information sur le document',
    msgSaving  : 'Merci de patienter...',
    name: 'form_doc',
    fields: [
        { name: 'titre', type: 'text', html: { caption: 'Titre' } },
        { name: 'url', type: 'text', html: { caption: 'URL' }, },
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
	        data.obj = "doc";
	        data.tronc = "ajoutDoc"
	        	$.get(prefUrl+'biolographes/ajout',
				data,
	        		function(js){
	    				finsession(js);
	    				datas["Docs"].push(js.rs);
	        			w2alert(js.message);
	       		},"json");
	    }
    }
};

var gridDocFrag = {
	    name: 'grid_doc_frag', 
		header: 'Fragments du document',		
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
	        { field: 'titre', caption: 'Titre', size: '10%', sortable: true, resizable: true,editable: { type: 'text' }},
	        { field: 'url', caption: 'URL', size: '30%', sortable: true, resizable: true,editable: { type: 'text' }},
	        { field: 'data', caption: 'Données', size: '60%', sortable: true, resizable: true,editable: { type: 'text' }},
	    ],
	    onClick: function (event) {
	    		docFragSelect = this.get(event.recid);
	    		if(docFragSelect.url)$('#ifDocFragList').attr("src",docFragSelect.url);            		
	    		if(docFragSelect.data)w2ui.layout_doc_bottom.content("right",docFragSelect.data);            		
	    },
	    onAdd: function(event) {
		    	if(!docSelect)	w2alert("Veuillez sélectionner un document");
	        var data = {"obj":"doc","tronc":"ajoutDocFrag","parent":docSelect.recid,"titre":"nouveau fragment"};
	        	$.get(prefUrl+'biolographes/ajout',
				data,
	        		function(js){
	    				finsession(js);
	    				w2ui.grid_doc_frag.add(js.rs);
	        			w2alert(js.message);
	       		},"json");
		},
		onSave: function(event) {
            var changes = w2ui.grid_doc_frag.getChanges();
            changes.forEach(function(c, i){
	            c.obj = 'doc';
	            $.post(prefUrl+'biolographes/edit',
	        			c,
	        			function(js){
	            			finsession(js);
	        				if(changes.length==i+1)w2alert(js.message);
	        			},"json");                        
            });
        },		
		onDelete: function(event) {
			var p = {id:docFragSelect.recid, obj:'doc'};
			if(event.force){						
	            	$.get(prefUrl+'biolographes/delete',
					p,
	            		function(js){
	            			finsession(js);
	            			w2alert(js.message);
	           		},"json");
			}
        },		
	}; 
var tbDoc = {
    name: 'tb_doc',
    items: [
        { type: 'html',  id: 'ipTitre',
            html: '<div style="padding: 3px 10px;">'+
                  ' Titre du document :'+
                  '    <input id="tb_doc_ip" size="100"  style="padding: 3px; border-radius: 2px; border: 1px solid silver"/>'+
                  '</div>' 
        },
        { type: 'button',  id: 'btnGetDoc',  caption: 'DataBNF', icon: 'fa-search' }
    ],
	onClick: function (event) {
		if(event.target=="btnGetDoc"){
			findDoc($('#tb_doc_ip')[0].value);			
		}
	},
	onRender: function(event) {
		event.onComplete = function () {
		    document.getElementById("tb_doc_ip").focus();		            			        			
	    }		        
	},

};
var lyDoc = {
        name: 'layout_doc',
        panels: [
     		{ type: 'left', size:"60%", style: pstyle, content: '',resizable: true },
		    { type: 'main', size:"40%", style: pstyle, content: '',resizable: true },
		    { type: 'bottom', size:"40%", style: pstyle, content: '',resizable: true }
		],
    };
var lyDocAjout = {
        name: 'layout_doc_ajout',
        panels: [
		    { type: 'top', size: 50, style: pstyle, content: '',resizable: true },
		    { type: 'left', size:"30%", style: pstyle, content: '',resizable: true },
		    { type: 'main', size:"60%", style: pstyle, content: '',resizable: true },
		    { type: 'bottom', size:"40%", style: pstyle, content: '<iframe id="ifDoc" src="'+prefUrl+'vide.html" height="100%" width="100%" />',resizable: true }
		],
    };
var lyDocBottom = {
        name: 'layout_doc_bottom',
        panels: [
		    { type: 'left', size:"60%", style: pstyle, content: '<iframe id="ifDocList" onLoad="loadDoc(this);" src="'+prefUrl+'vide.html" height="100%" width="100%" />',resizable: true },
		    { type: 'main', size:"20%", style: pstyle, content: '<iframe id="ifDocFragList" onLoad="loadDoc(this);" src="'+prefUrl+'vide.html" height="100%" width="100%" />',resizable: true },
		    { type: 'right', size:"20%", style: pstyle, content: '',resizable: true }
		],
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
	            		rs.creaNode("Notion",tag.code,tag);
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
        { name: 'parent', type: 'list', options: { items: datas["Tags"] }
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
    					datas["Tags"].forEach(function(d, i){
    						if(d.recid==js.rs.recid)datas["Tags"][i]=js.rs;
    					});
    				}else{
    					datas["Tags"].push(js.rs);
    				}
    			    editGraph();
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
			if(itemSelect.raw_category=="Work" || itemSelect.raw_category=="Periodic"){
				setSelectDoc(itemSelect);
			}			
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

var gridSpatioTempo = {
	    name: 'grid_spatiotempo', 
		header: "Influence spatio-temporelle",		
		show: {toolbar		: true,
			toolbarReload   : false,
			toolbarColumns  : false,
	        	toolbarSearch   : true,
	        	toolbarAdd      : true,
	        	toolbarDelete   : true,
	        	toolbarSave		: true,
	        	header			: true, 
	        	columnHeaders	: true},
        	columnGroups: [
        	               { caption: 'ID', master: true },
        	               { caption: 'Quand ?', span: 2 },
        	               { caption: 'Où ?', span: 6 },
        	               { caption: 'Quoi ?', master: true }
        	           ],
	   columns: [      		  		           
	        { field: 'recid', caption: 'ID', size: '50px', hidden:false, sortable: true, resizable: true },
	        { field: 'debut', caption: 'Début', editable: { type: 'date'}, size: '80px', sortable: true, resizable: true},
	        { field: 'fin', caption: 'Fin', editable: { type: 'date'}, size: '80px', sortable: true, resizable: true},
	        { field: 'lieu', caption: 'Lieu', size: '10%', sortable: true, resizable: true,
	        		editable: { type: 'combo', items: datas["Lieux"]}, 
                render: function (record, index, col_index) {
                    var html = this.getCellValue(index, col_index);
                    if(html.code)html=html.code;
                    return html || '';
                }},
	        { field: 'adresse', caption: 'Adresse', size: '10%', editable: { type: 'text'}, sortable: true, resizable: true},
	        { field: 'ville', caption: 'Ville', size: '100px', editable: { type: 'text'}, sortable: true, resizable: true},
	        { field: 'pays', caption: 'Pays', size: '100px', editable: { type: 'text'}, sortable: true, resizable: true},
	        { field: 'lat', caption: 'Lat.', size: '80px', render: 'float:8', sortable: true, resizable: true},
	        { field: 'lng', caption: 'Lng.', size: '80px', render: 'float:8', sortable: true, resizable: true},
	        { field: 'rapport', caption: 'Rapport', size: '10%', sortable: true, resizable: true,
	        		editable: { type: 'select', items: datas["Rapports"]}, 
	            render: function (record, index, col_index) {
	                var html = '';
	                for (var p in datas["Rapports"]) {
	                    if (datas["Rapports"][p].recid == this.getCellValue(index, col_index)) html = datas["Rapports"][p].code;
	                }
	                return html;
	            }},
            
	    ],
	    onClick: function (event) {
	    		stSelect = this.get(event.recid);
	        //vérifie s'il faut afficher la position
	        if(stSelect.lat && stSelect.lng && !markers[stSelect.recid]){
        			var redMarker = L.AwesomeMarkers.icon({
		        	    icon: 'university', //book,
		        	    markerColor: 'red',
		        	    	prefix: 'fa'
		        	  });	        	
	        		var m = L.marker([stSelect.lat, stSelect.lng]
	        			, {icon: redMarker
	        				,draggable:true
	        				}
	        			).addTo(map);
	        		markers[stSelect.recid] = m;
	        		m.on('dragend', function(event){
	                    var marker = event.target;
	                    var position = marker.getLatLng();
	                    var result = reverceGeocoding(position);
	                    // Create an element to hold all your text and markup
	                    var container = $('<div />');
	                    // Delegate all event handling for the container itself and its contents to the container
	                    container.on('click', '.smallPolygonLink', function() {
	                    		setCartoInfo(result);
	                    		markers[stSelect.recid] = marker;
	                    	});
	                    // Insert whatever you want into the container, using whichever approach you prefer
	                    container.html("<p>Voici la nouvelle adresse :"+result.display_name+"</p><a href='#' class='smallPolygonLink'>Mettre à jour</a>.");
	                    container.append($('<span class="bold">').text(" :)"))	                    
	                    marker.setLatLng(position).bindPopup(container[0]).openPopup();
	            });	        		
	        		var z = stSelect.zoom ? stSelect.zoom : 10;
	        		map.setView([stSelect.lat, stSelect.lng], z);
	        }
	    },
	    onAdd: function(event) {
	        var g = w2ui['grid_spatiotempo'].records.length;
	        w2ui['grid_spatiotempo'].add( { recid: g + 1} );
		},
		onSave: function(event) {
			//on enregistre le tableau dans les datas du lien
			linkSelect.spatiotempo = w2ui['grid_spatiotempo'].records;
		    editGraph();			
        },		
		onDelete: function(event) {
			//on enregistre le tableau dans les datas du lien
			if(event.force){						
				linkSelect.spatiotempo = w2ui['grid_spatiotempo'].records;
			    editGraph();			
			}
        },		
	    onResize: function(event) {
	    		event.onComplete = function () {
	    			if(popMax && !map)initCarte("carte");		            			        			
	    	    }		        
	    	},        		
	    toolbar: {
	        items: [
	            { id: 'find_geo', type: 'button', caption: "Valider l'adresse", icon: 'fa-map-marker' }
	        ],
	        onClick: function (event) {
	        		stSelect = w2ui.grid_spatiotempo.get(w2ui.grid_spatiotempo.getSelection());
	        		if(!stSelect){
	        			w2alert("Veuillez sélectionner une ligne");
	        			return;
	        		}
	            if (event.target == 'find_geo') {
	            		var place = [];
	            		if(stSelect.lieu)place.push(stSelect.lieu);	            		
	            		if(stSelect.adresse)place.push(stSelect.adresse);
	            		if(stSelect.ville)place.push(stSelect.ville);
	            		if(stSelect.pays)place.push(stSelect.pays);
	            		if(!place){
	            			w2alert("Veuillez renseigner les informations de localisation");
	            			return;
	            		}
	            		place = place.join(", ");
	            		findPlace(place);
	            }
	        }
	    },	    
	};

var gridSpatioTempoResume = {
	    name: 'grid_spatiotempo_resume', 
		header: "Influence spatio-temporelle",		
		show: {toolbar		: true,
			toolbarReload   : false,
			toolbarAdd      : true,
			toolbarColumns  : false,
	        	header			: true, 
	        	columnHeaders	: true},
        	columnGroups: [
        	               { caption: 'ID', master: true },
        	               { caption: 'Quand ?', span: 2 },
        	               { caption: 'Où ?', span: 4 },
        	               { caption: 'Quoi ?', master: true }
        	           ],
	   columns: [      		  		           
	        { field: 'recid', caption: 'ID', size: '50px', hidden:false, sortable: true, resizable: true },
	        { field: 'debut', caption: 'Début', size: '80px', sortable: true, resizable: true},
	        { field: 'fin', caption: 'Fin', size: '80px', sortable: true, resizable: true},
	        { field: 'lieu', caption: 'Lieu', size: '10%', sortable: true, resizable: true,
                render: function (record, index, col_index) {
                    var html = this.getCellValue(index, col_index);
                    if(html.code)html=html.code;
                    return html || '';
                }},
	        { field: 'adresse', caption: 'Adresse', size: '10%', sortable: true, resizable: true},
	        { field: 'ville', caption: 'Ville', size: '100px', sortable: true, resizable: true},
	        { field: 'pays', caption: 'Pays', size: '100px', sortable: true, resizable: true},
	        { field: 'rapport', caption: 'Rapport', size: '10%', sortable: true, resizable: true,
	            render: function (record, index, col_index) {
	                var html = '';
	                for (var p in datas["Rapports"]) {
	                    if (datas["Rapports"][p].recid == this.getCellValue(index, col_index)) html = datas["Rapports"][p].code;
	                }
	                return html;
	            }},
            
	    ],
	    onAdd: function(event) {
	    		openPopupSpatioTempo(linkSelect);	
	    	},
	};

var lySpatioTempo = {
        name: 'layout_spatiotempo',
        panels: [
            { type: 'left', size:"70%", style: pstyle, content: '',resizable: true },        	
            { type: 'main', size:"30%", style: pstyle, content: "<div id='carte' style='min-height: 100%;' ></div>",resizable: true },
            //{ type: 'main', size:"30%", style: pstyle, content: '<iframe id="ifGeo" src="carto" height="100%" width="100%" />',resizable: true },
        ],
    };



function openPopupAjoutActeur(){
	
	w2popup.open({
        title   : 'Ajouter un acteur',
        showMax : true,
        buttons   : '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">'+
        '<img class="imgButton" alt="Ajouter un document" onclick="openPopupAjoutDoc()" src="'+prefUrl+'img/document107.png">'+
        '<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
            		gridActeur.records=datas["Acteurs"];
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

function openPopupAjoutDoc(){
	
	w2popup.open({
        title   : 'Ajouter un document',
        buttons   : '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">'+
        '<img class="imgButton" alt="Ajouter un document" onclick="openPopupAjoutDoc()" src="'+prefUrl+'img/document107.png">'+
        '<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        showMax : true,
        onOpen  : function (event) {
            event.onComplete = function () {
            		docSelect = false;
	        	 	if(w2ui['layout_doc'])w2ui['layout_doc'].destroy();
	        	 	if(w2ui['grid_doc'])w2ui['grid_doc'].destroy();
	        	 	if(w2ui['grid_doc_frag'])w2ui['grid_doc_frag'].destroy();
	        	 	if(w2ui['layout_doc_bottom'])w2ui['layout_doc_bottom'].destroy();
	        	    	$('#w2ui-popup #main').w2layout(lyDoc);

		        gridDoc.records=datas["Docs"];
	        	    	
	        	    	w2ui['layout_doc'].content('left', $().w2grid(gridDoc));            		            		
	        	    	w2ui['layout_doc'].content('main', $().w2grid(gridDocFrag));            		            		
	        	    	w2ui['layout_doc'].content('bottom', $().w2layout(lyDocBottom));            		            		
	        	    		        	    	
	            	w2popup.max();
            		
            };
        },
    });
}	

function openPopupAjoutTag(){
	
	w2popup.open({
        title   : 'Ajouter une notion',
        buttons   : '<img class="imgButton" alt="Ajouter un acteur" onclick="openPopupAjoutActeur()" src="'+prefUrl+'img/document96.png">'+
        '<img class="imgButton" alt="Ajouter un document" onclick="openPopupAjoutDoc()" src="'+prefUrl+'img/document107.png">'+
        '<img class="imgButton" alt="Ajouter une notion" onclick="openPopupAjoutTag()" src="'+prefUrl+'img/document108.png">',
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        showMax : true,
        onOpen  : function (event) {
            event.onComplete = function () {
	        		gridTag.records=datas["Tags"];
	        	 	if(w2ui['grid_tag'])w2ui['grid_tag'].destroy();
	        		$('#w2ui-popup #main').w2grid(gridTag);
	            	w2popup.max();
	        		//w2popup.resize(1000, 500);
            };
        },
    });
}	

function openPopupSpatioTempo(d){
	
	w2popup.open({
        title   : "Influence(s) entre "+d.source.desc+" ("+d.source.type+") et "+d.target.desc+" ("+d.target.type+")",
        body    : '<div id="main" style="position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;"></div>',
        showMax : true,
        onOpen  : function (event) {
            event.onComplete = function () {
            		linkSelect = d;
	        	 	if(w2ui['layout_spatiotempo'])w2ui['layout_spatiotempo'].destroy();
	        	 	if(w2ui['grid_spatiotempo'])w2ui['grid_spatiotempo'].destroy();

	        	 	gridSpatioTempo.records = d.spatiotempo ? d.spatiotempo : [];
	        	 	var rapport = "Rapports "+d.source.type+" → "+d.target.type;
            		var rsRapportST = datas["Rapports"].filter(function(p){return p.parent1==rapport});
	        		gridSpatioTempo.columns[9].editable.items = rsRapportST;

	        		gridSpatioTempo.columns[3].editable.items = datas["Lieux"];
	        		
	        		$('#w2ui-popup #main').w2layout(lySpatioTempo);            		
	        		//            		
	        		w2ui['layout_spatiotempo'].content('left', $().w2grid(gridSpatioTempo));            		
	        		popMax = false;
                w2popup.max();
            }
        },
        onClose : function (event) {
            //supprime les objet pour éviter un bug leaflet
        		map.remove();
        		map = false;
        },
	    onMax: function(event) {
	    		event.onComplete = function () {
	    			popMax=true;		            			        			
	    	    }		        
	    	},                
    });
}

function openOverlaySpatioTempo(d){
	
	linkSelect = d;
	var body = '<div id="main" style="width:800px;height:300px;position: absolute; left: 5px; top: 5px; right: 5px; bottom: 5px;">';
	/*
	d.spatiotempo.forEach(function(st){
		body += datas["Rapports"][st.rapport].code+'<br/>';
	});
	*/
	body += '</div>';
	$(document.getElementById(d.id)).w2overlay({
		html: body,
		onShow  : function (event) {
        	 	if(w2ui['grid_spatiotempo_resume'])w2ui['grid_spatiotempo_resume'].destroy();
        	 	if(w2ui['grid_spatiotempo_resume_toolbar'])w2ui['grid_spatiotempo_resume_toolbar'].destroy();
        	 	
        	 	gridSpatioTempoResume.header = "Influence(s) entre "+d.source.desc+" ("+d.source.type+") et "+d.target.desc+" ("+d.target.type+")";

        	 	gridSpatioTempoResume.records = d.spatiotempo ? d.spatiotempo : [];
        		$('#w2ui-overlay #main').w2grid(gridSpatioTempoResume);            		
        },
        onHide : function (event) {
            //supprime les objet pour éviter un bug leaflet
        		if(map){
        			map.remove();
            		map = false;
        		}
        },
    });
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

function showRefTag(cherche){
	
 	if(w2ui['layout_tag'])w2ui['layout_tag'].destroy();
 	if(w2ui['form_tag'])w2ui['form_tag'].destroy();
 	if(w2ui['tb_tag'])w2ui['tb_tag'].destroy();
    	$('#w2ui-popup #main').w2layout(lyTag);
	formTag.fields[3].options.items = datas["Tags"];
    	
    	w2ui['layout_tag'].content('left', $().w2form(formTag));            		            		
    	w2ui['layout_tag'].content('top', $().w2toolbar(tbTag));
    	w2popup.max();
	if(cherche){
		findTag(cherche.trim());
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


function showRefDoc(cherche){
	
 	if(w2ui['layout_doc_ajout'])w2ui['layout_doc_ajout'].destroy();
 	if(w2ui['form_doc'])w2ui['form_doc'].destroy();
 	if(w2ui['tb_doc'])w2ui['tb_doc'].destroy();
    	$('#w2ui-popup #main').w2layout(lyDocAjout);            		
    	w2ui['layout_doc_ajout'].content('left', $().w2form(formDoc));            		            		
    	w2ui['layout_doc_ajout'].content('top', $().w2toolbar(tbDoc));
    	w2popup.max();
	if(cherche){
		findDoc(cherche.trim());
	}
}

function setFindDoc(){
	if(dtDocFind.length==0)w2alert("Aucun document trouvé.");
	//ajoute un recid
	dtDocFind.forEach(function(d,i){
		d.recid = i;
	});
	//affiche les résultats
	gridResultBNF.records = dtDocFind;
 	if(w2ui['grid_result_bnf'])w2ui['grid_result_bnf'].destroy();	
	w2ui['layout_doc_ajout'].content('main', $().w2grid(gridResultBNF));
	
}


function setSelectDoc(dt){
    var g = w2ui['form_doc'];
    g.clear();
	g.record = {"titre":dt.label,"url":dt.value};
	g.refresh();
	$('#ifDoc').attr("src",dt.value);            		
	
}

function loadDoc(t){
	//vérifie que le document existe
	var doc = datas["Docs"].filter(function(d){return d.url == t.src;});
	if(!doc){
		//demande s'il faut conserver le document
		var options = {
			    msg          : 'Voulez-vous conserver ce document ?',
			    title        : w2utils.lang('Confirmation'),
			    width        : 450,       // width of the dialog
			    height       : 220,       // height of the dialog
			    yes_text     : 'Oui',     // text for yes button
			    no_text      : 'Non',      // text for no button
			};
		w2confirm(options)
		    .yes(function () { 
		        console.log('user clicked YES'); 
		    })
		    .no(function () { 
		        console.log("user clicked NO")
		    });		
	}
	
}

function setSelectTag(dt){
    var g = w2ui['form_tag'];
    g.clear();
	g.record = {"code":dt.label,"desc":"","uri":dt.value,"parent":dt.parent};
	g.refresh();
}




function setCartoInfo(geo){
	var st = w2ui.grid_spatiotempo;
	var recid = st.getSelection();
	if(!recid)return;
    var arrAdd = [];
    if(geo.address.house_number)arrAdd.push(geo.address.house_number);
    if(geo.address.road)arrAdd.push(geo.address.road);
	/*
    stSelect.adresse = arrAdd.join(", ");
    stSelect.lat = geo.properties.lat;
    stSelect.lng = geo.properties.lon;
    linkSelect.spatiotempo = w2ui['grid_spatiotempo'].records;  
	st.editField(recid, 5, geo.properties.address.city);
	st.editField(recid, 6, geo.properties.address.country);
    */	
    stSelect.geo = geo;    
    stSelect.ville = geo.address.city;
    stSelect.pays = geo.address.country;
    stSelect.lat = geo.lat;
    stSelect.lng = geo.lon;
    stSelect.zoom = map.getZoom();
    w2ui.grid_spatiotempo.set(w2ui.grid_spatiotempo.getSelection(), stSelect);
    //w2ui.grid_spatiotempo.refresh();
	st.editField(recid, 4, arrAdd.join(", "));	
}
function editGraph(){
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
function initCarte(idElem){
    var max, scale,
    classes = 9,
    container = L.DomUtil.get(idElem);
    map = L.map(container).setView([0, 0], 2);

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    var optGeocoder = {position:"topleft"};
    geocoder = L.Control.geocoder(optGeocoder).addTo(map);
	
    markers = [];
}
function findPlace(place){
    geocoder._input.value = place;
    geocoder._geocode(this.Event);          
}

function reverceGeocoding(posi){
	var params = {format:"json",lat:posi.lat,lon:posi.lng,addressdetails:1,zoom:18};
	var result;
	jQuery.ajax({
        url: 'http://nominatim.openstreetmap.org/reverse',
        data: params,dataType:"json",
        async: false,
        success: function (js) {
            result=js;
        },
		error: function (js) {
	        console.log(js);
	    }
    });	
	return result;
}

