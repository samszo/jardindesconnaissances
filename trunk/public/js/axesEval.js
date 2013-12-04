//Objet HeatMap
var xx;
var xOri=160, yOri=160;
var xClic, yClic;
//objet sémantique
var sem=[];

window.onload = function(){
  
	//initialise la heatmap
	setHeatMap();

};


function setHeatMap(){

    var hma = document.getElementById('heatmapArea');
  	while (hma.firstChild) {
    	hma.removeChild(hma.firstChild);
  	}
  
    /*charge les valeurs
  	grilleSvg = grilles[e.selectedIndex-1];
  	nbX = grilleSvg.repX.length;
  	nbY = grilleSvg.repY.length;
  	nbZone = grilleSvg.repZone.length;
  	urlFond = grilleSvg.url;
	*/ 
 
    //défini les style de la heatmap
    hma.style.width = 319;	
    hma.style.height = 319;
    hma.style.top = 199;
    hma.style.left = 57;

	//création du heatmap
  	xx = h337.create({"element":hma, "radius":25, "visible":true});			
  	xx.get("canvas").onclick = function(ev){
    	var pos = h337.util.mousePosition(ev);
    	xx.store.addDataPoint(pos[0],pos[1]);
    	//getSemClic(pos[0], pos[1]);
    	xClic = pos[0];
    	yClic = pos[1];
    	setEval();
	};
}

function setEval() {
	var p = getParams();
	$.post(urlSaveEvalSem
			, p,
			 function(data){
				console.log(data);
			 }, "json");
	//getInput();
}

function getEvals() {
	$.post("eval/input"
			, {"idBase":idBase},
			 function(data){
				setInput(data);
			 }, "json");			
}

function getParams(){


	sem=[];
	sem.push(getSem('catN', 'N'));
	sem.push(getSem('catE', 'E'));
	sem.push(getSem('catS', 'S'));
	sem.push(getSem('catW', 'W'));
			
	return {"idBase":idBase, "idDoc":idDoc, "idUti":idUti, "sem":{"titre":"axesEval", "x":xClic, "y":yClic,"sems":sem}};
}

function getSem(idE, axe){
	
	var e = document.getElementById(idE);
	var lib = "vide", vide="E:.-',",ieml=vide, s, degre=0;
	if(e.selectedIndex!=0){
	    s = e.options[e.selectedIndex];		
		lib = s.label;
		ieml = s.getAttribute("ieml");
	}
	//calcule le degré suivant l'axe N, S, E ou W
	/* sémantique des axes
	 * E:A:.M:M:.- = axe horizontal
	 * E:U:.M:M:.- = axe vertical
	 * E:U:.d.- = en dessous / vers le bas
	 * E:A:.d.- = vers l'arrière
	 * E:U:.s.- = en haut / vers le haut
	 * E:A:.s.- = en avant / vers l'avant
	 * M:M:.-S:.U:.-' = Nord abstrait
	 * M:M:.-S:.A:.-' = Sud abstrait
	 * M:M:.-B:.U:.-' = Est abstrait
	 * M:M:.-B:.A:.-' = Ouest abstrait
	 */
	if(axe=="N"){
		degre = yOri - yClic;
		//ieml = "E:U:.M:M:.-E:U:.s.-',"+ieml+vide+"_";
	}
	if(axe=="E"){
		degre = xClic - xOri;
		//ieml = "E:A:.M:M:.-E:U:.s.-',"+ieml+vide+"_";
	}
	if(axe=="S"){
		degre = yClic - yOri;
		//ieml = "E:U:.M:M:.-E:U:.s.-',"+ieml+vide+"_";
	}
	if(axe=="E"){
		degre = xOri - xClic;
		//ieml = "E:A:.M:M:.-E:U:.s.-',"+ieml+vide+"_";
	}
	
	return {"lib":lib, "ieml":ieml, "idUti":idUti, "degre":degre};
}
