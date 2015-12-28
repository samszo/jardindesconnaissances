var dtOrigine, datas, dtDoublons;
var dtCrible = [];
var dtGraph = [];
var dtAuteurFind, dtTagFind;
var catLieux= {"Académies":1,"Sociétés savantes":1,"Universités françaises":1
               ,"Sociétés savantes françaises":1,"Universités allemandes":1,"Sociétés savantes allemandes":1
               ,"Espaces de sociabilité: cercles et salons mondains ou littéraires":1
               ,"Autres lieux de savoirs":1,"Villégiatures":1};
var dtTypeNoeud = ["Références","Acteurs","Concepts","Lieux"];
var idTagRameau, idTagNotion;
var rsActeurs, rsTags;
$.get(prefUrl+"ice/getacteurs",{'db':idBase},function(js){
	rsActeurs = js;
},"json");                        				
/*
$.get(prefUrl+"flux/tags",{'idBase':idBase,'parent':20},function(js){
	rsTags = js;
},"json");                        				
*/
var itemSelect, idUpdate;

initDataCat();

function initDataCat(){
	datas={
			"Acteurs":[],
			"Professions":[],
			"Fonctions":[],
			"Spécialités scientifiques":[],
			"Académies":[],
			"Sociétés savantes":[],
			"Universités françaises":[],
			"Sociétés savantes françaises":[],
			"Universités allemandes":[],
			"Sociétés savantes allemandes":[],
			"Espaces de sociabilité: cercles et salons mondains ou littéraires":[],
			"Autres lieux de savoirs":[],
			"Rapports Acteur → Acteur":[],
			"Rapports Acteur → Lieu":[],
			"Rapport Acteur → Notions":[],
			"Rapport Notions → Acteur":[],
			"Notions de biologie":[],
			"Notions de Science de la vie":[],
			"Notions de Science de la Terre":[],
			"Notions d'Anatomie":[],
			"Villégiatures":[],
			"Lieux":[]
		};
	dtDoublons={
			"Acteurs":[],
			"Professions":[],
			"Fonctions":[],
			"Spécialités scientifiques":[],
			"Académies":[],
			"Sociétés savantes":[],
			"Universités françaises":[],
			"Sociétés savantes françaises":[],
			"Universités allemandes":[],
			"Sociétés savantes allemandes":[],
			"Espaces de sociabilité: cercles et salons mondains ou littéraires":[],
			"Autres lieux de savoirs":[],
			"Rapports Acteur → Acteur":[],
			"Rapports Acteur → Lieu":[],
			"Rapport Acteur → Notions":[],
			"Rapport Notions → Acteur":[],
			"Notions de biologie":[],
			"Notions de Science de la vie":[],
			"Notions de Science de la Terre":[],
			"Notions d'Anatomie":[],
			"Villégiatures":[]
		};	
}

function csvGetCat(setCrible){
	var crible;	
	//construction des données
	dtOrigine.forEach(function(d, i) {
		if(setCrible=="oui"){
			dtCrible.push(d["Votre nom"]);
			crible = d["Votre nom"];
		}else crible = setCrible;
		for (cat in datas) {
			if(d[cat]){
				dt = d[cat].split(",");
				dt.forEach(function(p,i){
					var pt = p.trim();
					//enregistre les auteurs pour chaque propriété
					if(!dtDoublons[cat][pt]){
						dtDoublons[cat][pt] = dtDoublons[cat].length+1;
						if(d["Votre nom"]==crible){
							datas[cat].push({"value":pt});
							//création de la catégorie lieu
							if(catLieux[cat]){
								datas["Lieux"].push({"value":pt,"cat":cat});
							}
						}
					}
					//datas[cat][dtDoublons[cat][pt]].utis.push(d["Votre nom"]);
					dtGraph.push({"Auteur":d["Votre nom"],"Catégorie":cat,"Propriété":pt});					
				});				
			}				
		};				
	});
}
var colors =  d3.scale.ordinal()
	.domain(["REFERENCES","Lieux","Acteurs","CONCEPTS","LIENS_ACTEURS_ACTEURS","LIENS_ACTEURS_CONCEPTS"])
	.range(["pink","green","red","yellow","rose","blue","gray"]);
var clusters =  d3.scale.ordinal()
	.domain(["REFERENCES","EVENEMENTS","ACTEURS","CONCEPTS"])
	.range([100,200,300,500]);

//chargement des csv
d3.csv(prefUrl+"../data/country.csv", function(error, data) {
	var dataPays = data;
	//ajoute la propriété value pour l'autocomplete
	dataPays.forEach(function(d){
		d.value = d.name;
	});
	if(setAutoComplet)setAutocompletePays(dataPays);
});
d3.csv(prefUrl+"../data/biolographes/appartenances_savants.csv", function(error, data) {
	var dataAuteurs = data;
	dataAuteurs.forEach(function(d){
		//ajoute la propriété value pour l'autocomplete
		d.value = d["Nom"]+" "+d["prénom"];
	});
	dataAuteurs.sort(compareValue);
	datas["Acteurs"] = dataAuteurs;
	//initialise l'autocompletion
	if(setAutoComplet)setAutocompleteNoeud("Acteurs");
});

function compareValue(a, b) { 
	if (a.value < b.value) {
		return -1;
	} else if (a.value > b.value) {
		return 1;
	} else { 
		return 0;
	} 
}
function setDataByCrible(crible){
	csvGetCat(crible);
	setAutocompleteActeur();
}
