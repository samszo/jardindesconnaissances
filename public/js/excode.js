$(document).ready(function(){
	//alert("hello");
	
	//var margin = {top: 20, right: 20, bottom: 30, left: 40};
	var infoTitre = document.getElementById("titre");
	var infoColl = document.getElementById("collection");
	var infoCote = document.getElementById("cote");
	var infoCouv = document.getElementById("infos-couverture");
		
	var svgLignes = [{url:"ligne-1.svg",svg:false,d:"M53.715-7.12V6.976c-15.673-0.001-57.306,0-86.59-50.039l12.165-7.121C4.492-7.121,38.987-7.12,53.715-7.12z"},
		{url:"ligne-2.svg",svg:false,d:"M9.2-17.944c0-10.459,5.854-22.275,11.575-32.186l12.208,7.047c-10.579,18.321-10.64,25.907-8.829,29.043 c1.809,3.132,8.405,6.867,29.562,6.867V6.922c-15.707,0-34.648-1.581-41.768-13.914C10.013-10.34,9.2-14.049,9.2-17.944z"},
		{url:"ligne-3.svg",svg:false,d:"M-53.63-7.172h5.887l0.067,0.001c0.293,0.005,29.468,0.549,47.504,0.001 C17.96-7.72,46.437-7.21,48.396-7.172h5.32V6.923l-5.518-0.001C47.901,6.916,18.294,6.372,0.256,6.92 c-18.127,0.551-46.187,0.04-48.132,0.003h-5.753L-53.63-7.172z"}];
	
	var grille = [], listes = [], liste, livres = [];
	var dtSlct;
	
	var ligneNum = 0, rot = 0, equipeJoueur = 2;
	var mode = "explore";
	
			var url = window.location.href.split("?");
			if(url[1]) /* provisoire */
			{
				url = url[1];
				url = url.split("=");
				if(url[1]){ equipeJoueur = url[1]; }
			}
	
	var equipes = [
		{collection:"Arts plastiques - SA", theme:"Arts Plastiques", couleur:"#a5e034", bgcolor:"#8caf2c"},
		{collection:"Cinéma - SC", theme:"Cinéma", couleur:"#4242cc", bgcolor:"#2c2c84"},
		{collection:"Photographie - SP", theme:"Photographie", couleur:"#00c9ff", bgcolor:"#9b212a"},
		{collection:"Musique - SM", theme:"Musique", couleur:"#dd2f3c", bgcolor:"#43aed4"},
		{collection:"Arts du spectacle - ST", theme:"Art du spectacle", couleur:"#f7e608",bgcolor:"#d0177d"},
		{collection:"b", theme:"beta-testeur", couleur:"#00ff00"},
		{collection:"Urbanisme - T", theme:"Urbanisme", couleur:"#a39518", bgcolor:"#d3bf09"},
		{collection:"Géographie - X", theme:"Géographie", couleur:"#27b247", bgcolor:"#277038"},
		{collection:"Bandes dessinées", theme:"Bandes dessinées", couleur:"#33758c", bgcolor:"#43aed4"},
		{collection:"Sciences - L", theme:"Sciences", couleur:"#ba2394", bgcolor:"#6b1756"},
		{collection:"Informatique Tech - N", theme:"Informatique Tech", couleur:"#2c2cb7", bgcolor:"#2c2c84"},
		{collection:"Philosophie - B", theme:"Philosophie", couleur:"#a06f4d", bgcolor:"#674b37"},
		{collection:"Psychologie - C", theme:"Psychologie", couleur:"#db8623", bgcolor:"#ca8330"},
		{collection:"Sociologie - E", theme:"Sociologie", couleur:"#af3838", bgcolor:"#fd0000"},
		{collection:"Linguistique - W", theme:"Linguistique", couleur:"#af3838", bgcolor:"#fd0000"},
		{collection:"Ethnologie - M", theme:"Ethnologie", couleur:"#af3838", bgcolor:"#fd0000"},
		{collection:"Info Doc Médias - A", theme:"Info Doc Médias", couleur:"#af3838", bgcolor:"#fd0000"},
		{collection:"Sciences éducation - K", theme:"Sciences éducation", couleur:"#af3838", bgcolor:"#fd0000"},
		{collection:"Usuels Généralités - U", theme:"Usuels Généralités", couleur:"#fea", bgcolor:"#e0dc23"},
		{collection:"Histoire - Y", theme:"Histoire", couleur:"#7d39c7", bgcolor:"#804db8"},
		{collection:"Sciences politiques - F", theme:"Sciences politiques", couleur:"#7d39c7", bgcolor:"#804db8"},
	];
	function getEquipeColor(eq, cType){
		var c = "#000000";
		var equi = equipes.filter(function(e){
			return e.collection == eq;
		})
		if(!equi.length){
			console.log(eq + " " + cType);		
		}else
			c = equi[0][cType];
		return c;
	}
	
	var width = window.innerWidth - 0;
	var height = window.innerHeight - 0;
	var radius = 64;
	var etendu = 6;
	var padding = 1;
	var nbrHexa = 0, maxHexa = 2000;
	          
	var color = d3.scale.linear()
	    .domain([0, 20])
	    .range(["white", "steelblue"])
	    .interpolate(d3.interpolateLab);
	
	var hexbin = d3.hexbin()
	    .size([width, height])
	    .radius(radius);
		
	//-------------------------------------------
	
	var zoom = d3.behavior.zoom()
	    .scaleExtent([0.5, 1])
	    .on("zoom", zoomed);
	
	var svg = d3.select("#content").append("svg")
	    .attr("width", width )
	    .attr("height", height )
		.append("g")
		.call(zoom).on("dblclick.zoom", null).append("g");
	
	svg.append("clipPath")
	    .attr("id", "clip")
	  .append("rect")
	    .attr("class", "mesh")
	    .attr("width", width)
	    .attr("height", height);
	
	var selectTui, selectRot, selectColor;
	
	$(".icone-equipe-joueur").css("background-color", equipes[equipeJoueur].couleur);
	$("#info-equipe-joueur").html("Équipe : <br>" + equipes[equipeJoueur].theme);

	//chargement de la liste des cartes
	d3.json("../flux/bup8?obj=getListe&idBase=flux_excode",function (dt) {
	    	var lstC = d3.select("#lstCarte").selectAll("li")
	        .data(dt)
	        .enter()
	        .append("li")
	        .append("a")
	        .attr("href","#")
	        .text(function(d){
	        		return d.titre + " ("+ d.NbLivre+" liv.)";
	        })
	        .on("click",function(d){
		        console.log(d);
		        chargeCarte(d.tronc);
	        });
	});
			
	//chargement d'une carte
	function chargeCarte(idCarte){
		$.getJSON( "../flux/bup8?obj=getListeLivre&idBase=flux_excode&idListe="+idCarte, function( data ) {		
			/*vérification des couvertures 
			impossible car image amazon changeante		
			$.each( data.livres, function( key, val ) {
				livres.push(val.data);
				console.log(val.data.couverture);
				if(val.data.couverture=="http://images.amazon.com/images/P/.01.MZZZZZZZ.jpg")
					val.data.couverture="../img/excode/logo-mini.png";
			});
			*/
			liste = data;
			livres = data.livres;
			})
			.fail(function( jqxhr, textStatus, error ) {
			    var err = textStatus + ", " + error;
			    console.log( "Request Failed: " + err );
			})
			.done(function() {		
				
			//alert( livres[1].Titre );
			//création de la grille
			creaGrille();
			
			var exas = svg.append("g")
				.selectAll(".hexagon")
				.data(grille)
				.enter().append("g")
				.attr("id", function(d, i){
					return "g-" + i;
				})
				.attr("class", function(d) { return "hexagon x" + d.coordinates[0] + " y" + d.coordinates[1]; })
				.on("click",clickTuile);		
			
			exas.append("defs").append("pattern")
				.attr("id", function(d, i) {
					return "img-" + i; 
				})
				.attr("width", 1)
				.attr("height", 1)
				.attr("x", 0)
				.attr("y", 0)
				.append("image")
				.attr("width", 112)
				.attr("height", 152)
				.attr("xlink:href", function(d) {
					return d.couverture; 
				});
			
			exas.append("path")
				.attr("d", hexbin.hexagon())
				.attr("transform", function(d) {
					return "translate(" + d.x + "," + d.y + ")"; 
				})
				.style("fill", function(d, i) {
					return d.bgcolor; 
					//return "#000";
				});
			
			exas.append("path")
				.attr("d", hexbin.hexagon())
				.attr("id", function(d) {
					return "couv-" + d.num; 
				})
				.attr("transform", function(d) {
					return "translate(" + d.x + "," + d.y + ")"; 
				})
				.style("stroke-width",2) 
				.style("stroke", function(d) {
					return d.bgcolor; 
				})
				.style("fill", function(d) {
					//return d.bgcolor; 
					return "url(#img-" + d.num + ")";
				});
			
			
	
			exas.append("path")
				.attr("transform", function(d){
					return "transform", " translate(" + (d.x-(radius)+64) + "," + (d.y-(radius)+64) + ") rotate("+60+")"; 
				})
				.style("fill",equipes[0].couleur)
				.attr("d", function(d, i){
					if(i < 0){
						return svgLignes[1].d; 
					}
				});
							
			svg.append("path")
				.attr("d", hexbin.hexagon())
				.attr("id", "select-color")
				.attr("transform", function(d) {
					return "translate(" + 0 + "," + 0 + ")"; 
				})
				.style("fill", "#f00");
				
			svg.append("image")
				.attr("width",(radius*2.38)+"px")
				.attr("height",(radius*2.38)+"px")
				.attr("id", "select")
				.attr("xlink:href", "../img/excode/select.png");
				
			svg.append("image")
				.attr("width",(radius*3.3)+"px")
				.attr("height",(radius*3.3)+"px")
				.attr("id", "selectRot")
				.attr("xlink:href", "../img/excode/select-rot.png");
				
			selectTui = document.getElementById("select");
			selectTui.setAttribute("display", "none");
			selectRot = document.getElementById("selectRot");
			selectRot.setAttribute("display", "none");
			selectColor = document.getElementById("select-color");
			selectColor.setAttribute("display", "none");
	
			
			var btnP1 = document.getElementById("btn-p1");
			btnP1.addEventListener("click", function(){
				if(mode == "pose"){
					ligneNum = 1;
					d3.select("#pose-en-cours").attr("d", svgLignes[ligneNum].d);
				}
			}, true);
			var btnP0 = document.getElementById("btn-p0");
			btnP0.addEventListener("click", function(){
				if(mode == "pose"){
					ligneNum = 0;
					d3.select("#pose-en-cours").attr("d", svgLignes[ligneNum].d);
				}
			}, true);
			var btnP2 = document.getElementById("btn-p2");
			btnP2.addEventListener("click", function(){
				if(mode == "pose"){
					ligneNum = 2;
					d3.select("#pose-en-cours").attr("d", svgLignes[ligneNum].d);
				}
			}, true);
			
			
			var btnValider = document.getElementById("btn-valider");
			btnValider.addEventListener("click", function(){
				if(mode == "pose")
				{
					selectRot.setAttribute("display", "none");
					d3.select("#pose-en-cours").attr("id", null);
					dtSlct.ligneNum = ligneNum;
					dtSlct.rot = rot; // on enregistre les infos sur la grille
					dtSlct.equipe = equipeJoueur;
					$("#interface-pose").slideUp();
					mode = "explore";
				}
			}, true);
	
			
	
			
			
			selectTui.addEventListener("click", function(){			
				if(mode == "explore")
				{
					if(dtSlct.ligneNum == -1)
					{
						/* appareil photo !					
						cordova.plugins.barcodeScanner.scan(function(result){
							if(!result.cancelled)
							{
								//alert(result.text);
								var isbnJoueur = traiteIsbn(result.text);
								var isbnMap = traiteIsbn(grille[s].isbn);
								
								isbnJoueur = isbnJoueur.substring(3,isbnJoueur.length); // du 3em jusqu'au dernier
								
								isbnJoueur = isbnJoueur.substring(0,isbnJoueur.length-3);// de la premiere lettre jusqu'a la derniere -3
								isbnMap = isbnMap.substring(0,isbnMap.length-3);
								
								
								//alert("Joueur : " + result.text + "  map : " + grille[s].isbn);
								alert("Joueur : " + isbnJoueur + "\n   Map : " + isbnMap);
								
								
								if(isbnJoueur == isbnMap)
								{
								*/
									document.getElementById("couv-" + dtSlct.num).style.display = "none";
									
									selectTui.setAttribute("display", "none");
									selectColor.setAttribute("display", "none");
									selectRot.setAttribute("transform", "translate(" + (dtSlct.x - 62) + "," + (dtSlct.y - 112) + ")");
									selectRot.setAttribute("display", "block");
																	
									//var ligneNum = prompt("Ligne numero  0, 1, 2", "0");
									//var rot = prompt("rotation : 0, 60 , 120 etc.", "0");
									
									d3.select("#g-" + dtSlct.num).append("path")
										.attr("transform", " translate(" + (dtSlct.x-(radius)+64) + "," + (dtSlct.y-(radius)+64) + ") rotate("+rot+")" )
										.attr("id", "pose-en-cours")
										.style("fill",equipes[equipeJoueur].couleur)
										.attr("d",svgLignes[ligneNum].d);
	
									$("#interface-pose").slideDown();
									$("#interface-infos").slideUp();
									mode = "pose";
								/*
								}
								else
								{
									alert("Ce n'est pas le bon livre !");
								}
							}
						}, function(error){
							alert("Erreur : " + error);
						});
						*/
					}
					else{alert("L'équipe "+ equipes[dtSlct.equipe].theme +" à déjà posé une ligne ici.");}
				}
			
			}, true);
			
			
			
			
			
			
			selectRot.addEventListener("click", function(){
				if(mode == "pose")// condition pas vraiment obligatoire, car cet élément est masqué en mode "explore"
				{
					//alert("tourne");
					//d3.select("#pose-en-cours").attr("d",svgLignes[ligneNum].d);
					navigator.vibrate(30);
					rot += 60;
					if(rot >= 360){rot = 0;}
					
					d3.select("#pose-en-cours")
					.attr("transform", " translate(" + (dtSlct.x-(radius)+64) + "," + (dtSlct.y-(radius)+64) + ") rotate("+rot+")" );
					
					
				}
			}, true);
			
		});
	}
	
	function clickTuile(){
	    var t = d3.select(this);
	    dtSlct = t[0][0]["__data__"];
		if(mode == "explore")
		{
			
			infoColl.innerHTML = dtSlct.collection;
			infoColl.style.backgroundColor = dtSlct.bgcolor;
			infoCote.innerHTML = dtSlct.cote;
			infoTitre.innerHTML = dtSlct.titre;
			infoCouv.src = dtSlct.couverture;
					
			selectTui.setAttribute("transform", "translate(" + (dtSlct.x - 74) + "," + (dtSlct.y - 80) + ")");
			selectTui.setAttribute("display", "block");
			selectColor.setAttribute("transform", "translate(" + (dtSlct.x - 0) + "," + (dtSlct.y - 0) + ")");
			selectColor.setAttribute("display", "block");
			$("#select-color").css("fill", dtSlct.bgcolor);
			
			$("#interface-infos").slideDown();
			
			/*document.getElementById("couv-" + id).style.display = "none";*/
		}	        
	}

	
function creaGrille(){
	
	//création de la grille
	var limit1 = 0,
		limit2 = etendu; //nombre d'hexagone autour du centre
		
	//alert(livres[1].Titre);
	
	var bgcolor = "#eee";
	var k = 0;
    for (var j = -etendu; j <= etendu; j++) {
        var i = limit1;
        while (i <= limit2 && livres[k]) {
			var dt = livres[k].data;
			bgcolor = getEquipeColor(dt.dispos[0].Collection, 'bgcolor');
				
	        	grille.push({
	                id: livres[k].idDoc,
	                num:k,
	                idBU: livres[k].idBU,
	                idLst: liste.idDoc,
	                idBULst: liste.idBU,
	                coordinates: [i, j],
	                lastSelected: 0,
	                type: 'regular',
	                idUti: -1,
	                login: "",
	                nbDoc: -1,
	                role: "",
	                resource: false,
	                length:i*j,
	                nom:"exa "+i+","+j,
					titre:"" +dt.Titre,
					isbn: "" + dt.isbn,
					bgcolor: "" + bgcolor,
					collection:dt.dispos[0].Collection,
					cote:dt.dispos[0].Cote,
					couverture: dt.couverture,
					ligneNum: -1,
					rot:0,
					equipe:-1,
					idLigne:0	                
	            });		        				        		
            i++;
			k++;
			nbrHexa++;
        }
        if (j < 0) {
            limit1--;
        } else {
            limit2--;
        }
		
    }
    // http://goo.gl/8djhT
	var tilted = false, // true is horizontal alignment
		size = radius*2; // hexagon size
	
    var stepX = tilted ? size * 3 / 4 : Math.sqrt(3) * size / 2,
        stepY = tilted ? Math.sqrt(3) * size / 2 : size * 3 / 4,
        offset = size / Math.sqrt(3) * 3 / 4;
        
   grille.map(function(d, i) {
        var i = d.coordinates[0],
            j = d.coordinates[1],
            x = stepX * i + (!tilted ? offset * j : 0) + width / 2,
            y = stepY * j + (tilted ? offset * i : 0) + height / 2;
        d.centroid = [Math.round(x * 1e2) / 1e2, Math.round(y * 1e2) / 1e2];
        d.x = Math.round(x * 1e2) / 1e2;
        d.y = Math.round(y * 1e2) / 1e2;
        d.visible = !outbounds(x, y);
    });
}

function outbounds(x, y) {
    return x < padding || x > width - padding || y < padding || y > height - padding;
}
function zoomed() {
  svg.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}
function dragstarted(d) {
  d3.event.sourceEvent.stopPropagation();
  d3.select(this).classed("dragging", true);
}
function dragged(d) {
  d3.select(this).attr("cx", d.x = d3.event.x).attr("cy", d.y = d3.event.y);
}
function dragended(d) {
  d3.select(this).classed("dragging", false);
}
function traiteIsbn(isbn){
	isbn = isbn.split("-");
	isbn = isbn.join("");
	isbn = isbn.split(" ");
	isbn = isbn.join("");
	isbn = isbn.split(".");
	isbn = isbn.join("");
	return isbn;
}

/* fonction retourne l'id d'un hexagone à partir de X et Y */
function getHexaId( x, y){
	var classX = "x" + x;
	var classY = "y" + y;
	var hexa = document.querySelector("." + classX + "." + classY);
	var id = -1; /* retourne ce chiffre si l'hexagone n'existe pas (si on dépasse la map par exemple) */
	
	if(hexa){id = Number( hexa.id.substr(2));}
	
	return id;
}

/* fonction retourne un tableau de 2 cases [x,x] contenant l'id de l'hexa suivant. en fonction de la ligne  (-1 si aucun) */
function getNextHexa(id){
	var result = [-1, -1];
	var x = grille[id].coordinates[0];
	var y = grille[id].coordinates[1];
	// rot = 0, 60, 120, 180, 240, 300, 0 etc.
	
	if(grille[id].ligneNum == 0) // petit virage
	{
		     if(grille[id].rot == 0){   result[0] = getHexaId( x, y-1 );	result[1] = getHexaId( x+1, y ); }
		else if(grille[id].rot == 60){  result[0] = getHexaId( x+1, y-1 );	result[1] = getHexaId( x,   y+1 ); }
		else if(grille[id].rot == 120){ result[0] = getHexaId( x+1, y );	result[1] = getHexaId( x-1, y+1 ); }
		else if(grille[id].rot == 180){ result[0] = getHexaId( x, y+1 );	result[1] = getHexaId( x-1, y ); }
		else if(grille[id].rot == 240){ result[0] = getHexaId( x-1, y+1 );	result[1] = getHexaId( x,   y-1 ); }
		else if(grille[id].rot == 300){ result[0] = getHexaId( x-1, y );	result[1] = getHexaId( x+1, y-1 ); }
	}
	else if(grille[id].ligneNum == 1) // épingle à cheveux
	{
		     if(grille[id].rot == 0){   result[0] = getHexaId( x+1, y-1 );  result[1] = getHexaId( x+1, y ); }
		else if(grille[id].rot == 60){  result[0] = getHexaId( x+1, y );  result[1] = getHexaId( x, y+1 ); }
		else if(grille[id].rot == 120){ result[0] = getHexaId( x, y+1 );  result[1] = getHexaId( x-1, y+1 ); }
		else if(grille[id].rot == 180){ result[0] = getHexaId( x-1, y+1 );  result[1] = getHexaId( x-1, y ); }
		else if(grille[id].rot == 240){ result[0] = getHexaId( x-1, y );  result[1] = getHexaId( x, y-1 ); }
		else if(grille[id].rot == 300){ result[0] = getHexaId( x, y-1 );  result[1] = getHexaId( x+1, y-1 ); }
	}
	else if(grille[id].ligneNum == 2) // tout droit
	{
		     if(grille[id].rot == 0){   result[0] = getHexaId( x-1, y );  result[1] = getHexaId( x+1, y ); }
		else if(grille[id].rot == 60){  result[0] = getHexaId( x, y-1 );  result[1] = getHexaId( x, y+1 ); }
		else if(grille[id].rot == 120){ result[0] = getHexaId( x+1, y-1 );  result[1] = getHexaId( x-1, y+1 ); }
		else if(grille[id].rot == 180){ result[0] = getHexaId( x+1, y );  result[1] = getHexaId( x-1, y ); }
		else if(grille[id].rot == 240){ result[0] = getHexaId( x, y+1 );  result[1] = getHexaId( x, y-1 ); }
		else if(grille[id].rot == 300){ result[0] = getHexaId( x-1, y+1 );  result[1] = getHexaId( x+1, y-1 ); }
		
		/*   if(grille[id].rot == 0){   result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }
		else if(grille[id].rot == 60){  result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }
		else if(grille[id].rot == 120){ result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }
		else if(grille[id].rot == 180){ result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }
		else if(grille[id].rot == 240){ result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }
		else if(grille[id].rot == 300){ result[0] = getHexaId( x, y );  result[1] = getHexaId( x, y ); }*/
	}

	
	return result;
}

// fonction vérifie si le voisin V de la selection est bien connecté à lui meme
function checkLink(v, select){

	if(v != -1 && grille[v].equipe != -1)
	{
		if(grille[select].equipe == grille[v].equipe)
		{
			var connexion = getNextHexa(v);
			if(connexion[0] == select || connexion[1] == select)
			{
				return true;
			}
		}
	}
	
	return false;
}

function calculLignes(){

		for(i = 0 ; i < nbrHexa ; i++)// RESET
		{
			document.getElementById("g-" + i).classList.remove("id-ligne-" + grille[i].idLigne);
			document.getElementById("g-" + i).classList.remove("ligne-gras")
			grille[i].idLigne = 0;
		}

		// ajout d'une piece invisible, pour simplifier les tests. 
		// représente le test d'une pièce vide, ou un obstacle, exemple: if(grille[x].idLigne == 0)
		grille.push({
			id: nbrHexa,
			coordinates: [10, 10],
			lastSelected: 0,
			type: 'regular',
			idUti: -1,
			login: "",
			nbDoc: -1,
			role: "",
			resource: false,
			length:10*10,
			nom:"exa "+10+","+10,
			titre:"test",
			isbn: "99998888123",
			bgcolor: "#fff",
			collection:"none",
			couverture: "../img/excode/logo-mini.png",
			ligneNum: -1,
			rot:11,
			equipe:-1,
			idLigne:0
		});	
		
		//alert(nbrHexa);
	
		var voisins = getNextHexa(dtSlct.num);
		var j = 0;
		var idLigne = 1;
		var a = 0;
		var b = 0;
		
		for(i = 0 ; i < nbrHexa ; i++)
		{
			if(grille[i].equipe != -1)
			{
				idLigne++;
				j = i;
				voisins = getNextHexa(j);
				a = voisins[0];
				b = voisins[1];
				if(a == -1){a = nbrHexa;}
				if(b == -1){b = nbrHexa;}
				if(checkLink(a, j) == false){a = nbrHexa;}
				if(checkLink(b, j) == false){b = nbrHexa;}
				
				
				if( grille[a].idLigne == 0 && grille[b].idLigne == 0 )
				{
					grille[j].idLigne = idLigne;
				}
				else
				{
					// a ou b ?
					if(grille[a].idLigne > 0)
					{
						grille[j].idLigne = grille[a].idLigne;
					}
					else
					{
						grille[j].idLigne = grille[b].idLigne;
					}
				}
				//document.getElementById("g-" + j).classList.add("id-ligne-" + grille[j].idLigne);
				//document.getElementById("g-" + j).classList.add("ligne-gras");
				
				if(grille[a].idLigne > 0 && grille[b].idLigne > 0)
				{
					if(grille[a].idLigne != grille[b].idLigne)
					{
						//alert("aaa");
						idLigne++;
						for(k = 0 ; k < nbrHexa ; k++)
						{
							if(grille[k].idLigne == grille[a].idLigne || grille[k].idLigne == grille[b].idLigne)
							{
								//document.getElementById("g-" + k).classList.remove("id-ligne-" + grille[k].idLigne);
								grille[k].idLigne = idLigne;
								//document.getElementById("g-" + k).classList.add("id-ligne-" + grille[k].idLigne);
								//document.getElementById("g-" + k).classList.add("ligne-gras")
							}
						}
					}
				}
				
			}
		}
		
		
		// correction des erreurs (1 hexa de mauvaise couleur au milieu d'une ligne )
		var dd = 0, ddd = 0, dddd = 0;
		for(i = 0 ; i < nbrHexa ; i++)
		{
			if(grille[i].idLigne > 0)
			{
				voisins = getNextHexa(i);
				a = voisins[0];
				b = voisins[1];
				if(a == -1){a = nbrHexa;}
				if(b == -1){b = nbrHexa;}
				if(checkLink(a, i) == false){a = nbrHexa;}
				if(checkLink(b, i) == false){b = nbrHexa;}
				
				if(grille[i].idLigne != grille[a].idLigne && a != 0)// erreur trouvé
				{
					//alert(grille[i].idLigne + "\n" + grille[a].idLigne + "\n" + grille[b].idLigne);
					dd = grille[i].idLigne;
					ddd = grille[a].idLigne;
					dddd = grille[b].idLigne;// on prend les trois ID des lignes, et on en garde qu'un seul
					for(k = 0 ; k < nbrHexa ; k++)
					{
						if(grille[k].idLigne > 0)
						{
							if(grille[k].idLigne == dd || grille[k].idLigne == ddd || grille[k].idLigne == dddd )
							{
								grille[k].idLigne = dd;
							}
						}
					}
				}
			}
		}
		

}
	

function calculPoints()
{
	var t = [];
	var k = 0;
	var point = 1;
	
	for(i = 0; i < nbrHexa ; i++)
	{
		k = 0;
		for(j = 0; j < t.length ; j++)
		{
			if(t[j].ligne == grille[i].idLigne){k = 1; break;}
		}
		// t[j]  = une ligne
		
		if(grille[i].idLigne > 0)
		{
			if(equipes[grille[i].equipe].collection != grille[i].collection)
			{
				point = 2;
			}
			else{point = 1;}
			
			if( k == 0 )// et qu'il n'y a pas la ligne dans le T
			{
				t.push({
					ligne:grille[i].idLigne,
					pts:point,
					hexa:1,
					equipe:grille[i].equipe
				});
			}
			else if( k == 1 ) // sinon la ligne existe deja dans T
			{
				t[j].pts += point;
				t[j].hexa++;
			}
		}
	}
	
	t.sort(function (b, a) {
		if (a.pts > b.pts)
		  return 1;
		if (a.pts < b.pts)
		  return -1;
		return 0;
	});

	var score = "";
	for (i = 0 ; i < t.length ; i++){
		score += t[i].pts + "pts       Longueur : " + t[i].hexa + "       Equipe : " + equipes[t[i].equipe].theme + "\n";
		
	}
	alert(score);
	//alert(t[0].ligne);
	
	return t;

}
	
	$('#btncodebarre').click(function(event){
		/*
		cordova.plugins.barcodeScanner.scan(function(result){
			if(!result.cancelled)
			{
				alert(result.text);
			}
		}, function(error){
			alert("Erreur : " + error);
		});
		*/
	});
	
	$('#btn-dev').click(function(event){
	
		var devTest = prompt("Changer équipe \n 0, 1, 2, 3, 4, 5", 0);
		
		if(devTest >= 0 && devTest <= 6 && devTest)
		{
			equipeJoueur = devTest;
			$(".icone-equipe-joueur").css("background-color", equipes[equipeJoueur].couleur);
			$("#info-equipe-joueur").html("Équipe : <br>" + equipes[equipeJoueur].theme);
		}else{alert("erreur.");}
	
	});
	
	
	$('#btn-calcul').click(function(event){
	
		calculLignes();
		/*
		for(i = 0 ; i < nbrHexa ; i++)
		{
			if(grille[i].idLigne > 0)
			{
				document.getElementById("g-" + i).classList.add("id-ligne-" + grille[i].idLigne);
				document.getElementById("g-" + i).classList.add("ligne-gras")
			}
		}*/
		
		var scores = calculPoints();
		
		for(i = 0 ; i < nbrHexa ; i++)
		{
			document.getElementById("g-" + i).classList.remove("test");
			if(grille[i].idLigne == scores[0].ligne)
			{
				document.getElementById("g-" + i).classList.add("test");
			}
		}
	
	});
	
	$('#btn2').click(function(event){
		navigator.vibrate([50, 50, 200, 300, 500, 600, 50, 50, 50, 50, 50, 50]);
		navigator.notification.beep(1);
	});
	
	$('#btn-reset').click(function(event){	
		alert("je fait rien");	
	});
		
});