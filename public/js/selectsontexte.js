/**
 * @author : samuel szoniecky
 * @version  
 */
function selectsontexte(config) {

	this.arrSbSon = [], this.arrSbTxt = [], this.arrCar = [], this.sbs, this.sbt, this.term = config.term;
	this.xCntxSon, this.xCntxSonInv, this.xCntxTxt, this.xCntxTxtInv;
	this.formatDate, this.audioW, this.allTexte, this.svg, this.width;
	this.mrgCntxSon, this.mrgCntxText, this.hCntxSon, this.hCntxText;
  	this.nbSecDeb, this.nbSecFin;
  	this.arrTc = [], this.idDoc = config.idDoc;
	this.data = config.data;
	this.audioElm = "#audioW_"+this.idDoc;
	this.idExi = config.idExi;

	this.sst = function() {
	  
	  var sbparams;
	  this.allTexte = this.data['text'];
	  var nbCarDeb = 0, nbCarFin = 0, nbCarTot = this.allTexte.length, arrMots = this.allTexte.split(" ")
	  , phrases = this.data['phrases'], posis = this.data['posis'], arrSon = []
	  , audioSource = this.data['urlSonLocal']
	  , term = config.term, idDoc = config.idDoc
	  , txtSelect
	  , txtAuto = document.getElementById("txtAuto_"+idDoc)
	  , divSVG = "#divSVG_"+idDoc
	  , self = this;	  
	  
	  //création du tagcloud général
	  //var tcg	= new tagcloud({idDoc:"divSVG_"+idDoc, txt:this.allTexte, data:false});

	  //chargement de l'audio
	  this.audioW = Popcorn(this.audioElm);
	  	  	  
	  var margin = {top: 10, right: 10, bottom: 80, left: 40};
	  this.width = 1000 - margin.left - margin.right;
	  this.mrgCntxSon = {top: 10, right: 10, bottom: 20, left: 40};
	  var height = 10;
	  this.hCntxSon = 20;
	  this.hCntxText = 20;
	  this.mrgCntxText = {top: 60, right: 10, bottom: 10, left: 40};

	  //construction du tableau des mots
	  for(var i=0; i < arrMots.length; i++){
	  	nbCarFin += arrMots[i].length+1;
	  	this.arrCar.push({n:i,mot:arrMots[i],carDeb:nbCarDeb,carFin:nbCarFin});
	  	nbCarDeb = nbCarFin;
	  }

	  //construction du tableau des secondes	  
	 var nbSecTot = this.data['mp3Infos']['Length'];
	 for(var i=1; i < nbSecTot; i++){
		 var d = new Date();
		 d = new Date(i*1000);
		 arrSon.push(d);
	 }
		   	  	
	  this.xCntxSon = d3.time.scale().range([0, this.width]).domain(d3.extent(arrSon.map(function(d) {return d; })));
	  this.xCntxSonInv = d3.time.scale().range(d3.extent(arrSon.map(function(d) { return d; }))).domain( [0, this.width]);
	  this.xCntxTxt = d3.scale.linear().range([0, this.width]).domain([0, this.arrCar.length-1]);
	  this.xCntxTxtInv = d3.scale.linear().range([0, this.arrCar.length-1]).domain([0, this.width]);

	  var xAxisCntxSon = d3.svg.axis().scale(this.xCntxSon).orient("top").tickFormat(this.formatDate).ticks(d3.time.minutes, 5);
	  var xAxisCntxTxt = d3.svg.axis().scale(this.xCntxTxt).orient("bottom");

	  this.svg = d3.select(divSVG).append("svg")
	  	.attr("width", this.width + margin.left + margin.right)
	  	.attr("height", height + margin.top + margin.bottom);

	  var gCntxText = this.svg.append("g")
	  	.attr("transform", "translate(" + this.mrgCntxText.left + "," + this.mrgCntxText.top + ")");
	  gCntxText.append("g")
	  		.attr("class", "x axis")
	  		.attr("transform", "translate(0," + this.hCntxText + ")")
	  		.call(xAxisCntxTxt)
	        .on("mouseover", function(){return tooltip.style("visibility", "visible");})
	        .on("mousemove", function(){
	        	return tooltip
	        		.style("top", (event.pageY-20)+"px")
	        		.style("left",(event.pageX-20)+"px")
	        		.text(getMotByX(event.offsetX-margin.left));
	        	})
	        .on("mouseout", function(){return tooltip.style("visibility", "hidden");});
	  gCntxText.append("text")
	  	   	.attr("class", "x axis")
	  		.attr("transform", "translate(-"+this.mrgCntxText.left+"," + this.hCntxText + ")")
	  		.text("texte");

	  var gCntxSon = this.svg.append("g")
	      .attr("transform", "translate(" + this.mrgCntxSon.left + "," + this.mrgCntxSon.top + ")");
	  	

	var tooltip = d3.select("body")
	    .append("div")
	    .style("position", "absolute")
	    .style("z-index", "10")
	    .style("visibility", "hidden")
	    .text("a simple tooltip");
	  
    gCntxSon.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + this.hCntxSon + ")")
        .call(xAxisCntxSon)
	  	.attr("cursor", "pointer")
        .on("click", bougeSon)
        .on("mouseover", function(){return tooltip.style("visibility", "visible");})
        .on("mousemove", function(){
        	return tooltip
        		.style("top", (event.pageY+10)+"px")
        		.style("left",(event.pageX+10)+"px")
        		.text(getSonTimeString(event.offsetX-margin.left));
        	})
        .on("mouseout", function(){return tooltip.style("visibility", "hidden");});

    gCntxSon.append("text")
  		.attr("class", "x axis")
  		.attr("transform", "translate(-"+this.mrgCntxSon.left+",30)")
  		.text("audio");
    
    //ajoute les phrases calculées automatiquement
    var i=0;
    if(phrases){    	
	    for(i=0; i < phrases.length; i++){
	    	this.arrSbSon.push(new selectbar({id:'son_'+idDoc+'_'+i, wBar:10, wSel:10, d:[{x: getSecByCar(phrases[i]['deb']), y: 0}], width:this.width
	    		, hSel:this.hCntxSon, left:this.mrgCntxSon.left, top:this.mrgCntxSon.top+this.hCntxSon
		  		, xCntx:this.xCntxSon ,xCntxInv:this.xCntxSonInv, svg:this.svg, fncDragEnd:this.playSonSelect, xUnit:1000, sst:this}));
	    	var sb =new selectbar({id:'txt_'+idDoc+'_'+i, wBar:10, wSel:10, d:[{x: getMotByTerm(phrases[i]['deb']), y: 0}], width:this.width
	    		, hSel:this.hCntxText, left:this.mrgCntxText.left, top:this.mrgCntxText.top
		  		, xCntx:this.xCntxTxt ,xCntxInv:this.xCntxTxtInv , svg:this.svg, fncDragEnd:this.showTextSelect, xUnit:1, sst:this});
		  	this.arrSbTxt.push(sb);
	
		  	sb.show();
	    }
	}

    //ajoute les phrases positionnées par des existences
    if(posis){
	    for(var j=0; j < posis.length; j++){
	    	var p = eval('(' + posis[j]['note'] + ')');
	    	var idElem = idDoc+"_"+(i+j)+"_"+posis[j]['idDoc']+"_"+p['idExi']+"_"+j;
	    	this.addNewPosiSb(p, idElem);
		  }
    }

	  function bougeSon(d, i){
		  var t = d3.event;
	      var x = t.offsetX-margin.left;
	      //getSonTimeString(x);
		  var nbSec = self.xCntxSonInv(x) / 1000;
	      self.nbSecFin = nbSecTot;
		  self.audioW.play(nbSec);
	  }
	  
	  function getSonTimeString(x){
		  var nbSec = self.xCntxSonInv(x) / 1000;
		  var d1 = new Date(nbSec*1000);
		  var s = self.formatDate(d1);
		  //console.log(s);
		  return s;
	  }

	  function getMotByX(x){
		  	var xDeb = self.xCntxTxtInv(x);
			var motDeb = Math.round(xDeb)-3;
			var motFin = motDeb+3;
		  	var arrMotDeb = self.arrCar[motDeb];
		  	var arrMotFin = self.arrCar[motFin];
		  	var txt = self.allTexte.substring(arrMotDeb["carDeb"],arrMotFin["carFin"]);
		    return txt;
	  }
	  
	  function showTextAuto(){
	  	//calcule l'interval en texte
	  	nbCarDeb = Math.round(nbCarTot/nbSecTot*nbSecDeb);
	  	nbCarFin = Math.round(nbCarTot/nbSecTot*nbSecFin);
	  	var queryTexte = nbCarDeb+" - "+nbCarFin;
	  	//console.log(queryTexte);
	  	var txt = allTexte.substring(nbCarDeb,nbCarFin);
	  	console.log("texte="+txt);
	  	txtAuto.innerHTML = txt;	
	  }
	  
	  function getMotByTerm(txt){
		  //calcule le mot par rapport à un term
		  var posi = self.allTexte.indexOf(txt);
		  return self.getMotByCar(posi);
	  }
	  
	  function getSecByCar(term){
		  var posi = self.allTexte.indexOf(term);		  
		  var numSec = Math.round(nbSecTot/nbCarTot*posi);
		  var d = new Date(numSec*1000);
		  return self.xCntxSon(d);		  
	  }
	  
  };

  //utc important pour le calcul des secondes
  this.formatDate = d3.time.format.utc("%X");


  this.showTextSelect = function(arrExt, id, sst){
  	//Récupère le nombre de caractère
	var motDeb = Math.round(arrExt[0]);
	var motFin = Math.round(arrExt[1]);
  	var arrMotDeb = sst.arrCar[motDeb];
  	if(motFin >= sst.arrCar.length)motFin = sst.arrCar.length-1;
  	var arrMotFin = sst.arrCar[motFin];
  	var txt = sst.allTexte.substring(arrMotDeb["carDeb"],arrMotFin["carFin"]);
  	//console.log(arrMotDeb["carDeb"]+" - "+arrMotFin["carFin"]+" : "+txt);
  	//hypertextualise le texte
	document.getElementById("Select_"+id).innerHTML = txt;
	var arrId = id.split("_");
	var strId = id.substring(4);
	var sbs=sst.arrSbSon[arrId[2]];
	if(!sst.sbs){
		sst.sbs=sbs;
		sst.sbs.show();
	}else if(sst.sbs.id != sbs.id){
		sst.sbs=sbs;
		sst.sbs.show();
	}
	var tc = sst.arrTc[arrId[2]], utiWords=false;
	//récupère les mots de l'utilisateur uniquement pour les posis
	if(arrId.length > 3 && sst.data['posis']) utiWords = sst.data['posis'][arrId[5]]['tags'];
	if(!tc){
		sst.arrTc.push(new tagcloud({idDoc:strId, idExi:sst.idExi, txt:txt, data:false, utiWords:utiWords, term:sst.term}));	
	}else if(tc.utiWords != utiWords){
		var dPar = document.getElementById("vis_"+strId);
		var d = document.getElementById("svg_"+strId);
		if(d){
			dPar.removeChild(d); 
			sst.arrTc[arrId[2]]=new tagcloud({idDoc:strId, idExi:sst.idExi, txt:txt, data:false, utiWords:utiWords, term:sst.term}); 
		}
	}
		
	//document.getElementById("Select_"+arrId[1]+"_"+arrId[2]).style.display='inline';		
	return {"deb":arrMotDeb["carDeb"], "fin":arrMotFin["carFin"]};
  };
    
  this.playSonSelect = function(arrExt, id, sst) {
	  	//calcule l'intervale en seconde
	  	var d0 = new Date(arrExt[0]);
	  	var d1 = new Date(arrExt[1]);
	  	sst.nbSecDeb = (d0.getMinutes()*60) + d0.getSeconds();//arrExt[0] / 1000;
	  	sst.nbSecFin = (d1.getMinutes()*60) + d1.getSeconds();//arrExt[1] / 1000;
	  	var queryTime = sst.formatDate(d0)+" - "+sst.formatDate(d1);//+" = "+nbSecDeb+" - "+nbSecFin;
	  	//console.log(queryTime);

	  	// "funzo" is an instance method!
	  	if(sst.audioW.readyState() > 0) sst.audioW.funzo({
		      start: sst.nbSecDeb,
		      end: sst.nbSecFin
		  }).play(sst.nbSecDeb);
	  	
		document.getElementById("Select_"+id).innerHTML = queryTime;
		var arrId = id.split("_");
		var sbt=sst.arrSbTxt[arrId[2]];
		if(!sst.sbt){
			sst.sbt=sbt;
			sst.sbt.show();
		}else if(sst.sbt.id != sbt.id){
			sst.sbt=sbt;
			sst.sbt.show();
		}
		return {"deb":sst.nbSecDeb, "fin":sst.nbSecFin};
		
	  	//showTextAuto();
	  };
  
  
  this.sbparams = function (num) {
	var sbs = this.arrSbSon[num];  
	var sbt = this.arrSbTxt[num];  
	var ps = sbs.params();	
	var pt = sbt.params();	
	return {"sbs":ps, "sbt":pt, "num":num, "term":this.term};
	  
  };

  this.getMotByCar = function (numCar){
	  //calcule le mot par rapport à un caractère
	  for(var i=0; i < this.arrCar.length; i++){
		  if(this.arrCar[i]["carDeb"] <= numCar && this.arrCar[i]["carFin"] > numCar ){
			  var m = this.arrCar[i]['mot'];
			  return this.xCntxTxt(i);
		  }
	  }
  };
  
  this.addNewPosiSb = function(p, idElem) {
		var sonDeb = this.xCntxSon(new Date(p['sbs']['deb']*1000));
		var sonFin = this.xCntxSon(new Date(p['sbs']['fin']*1000));
		var sonW = sonFin-sonDeb;
		var txtDeb = this.getMotByCar(p['sbt']['deb']);
		var txtFin = this.getMotByCar(p['sbt']['fin']);
		var txtW = txtFin-txtDeb;
		this.arrSbSon.push(new selectbar({id:'son_'+idElem, wBar:10, wSel:10, d:[{x: sonDeb, y: 0}], wSel:sonW, width:this.width
			, hSel:this.hCntxSon, left:this.mrgCntxSon.left, top:this.mrgCntxSon.top+this.hCntxSon
	  		, xCntx:this.xCntxSon ,xCntxInv:this.xCntxSonInv, svg:this.svg, fncDragEnd:this.playSonSelect, xUnit:1000, sst:this}));
		var sb = new selectbar({id:'txt_'+idElem, wBar:10, wSel:10, d:[{x: txtDeb, y: 0}], wSel:txtW, width:this.width
			, hSel:this.hCntxText, left:this.mrgCntxText.left, top:this.mrgCntxText.top
	  		, xCntx:this.xCntxTxt ,xCntxInv:this.xCntxTxtInv , svg:this.svg, fncDragEnd:this.showTextSelect, xUnit:1, sst:this});	
	  	this.arrSbTxt.push(sb);

	  	sb.show();
  
  };
  
  this.setAudio = function() {
	  document.addEventListener('DOMContentLoaded', function () {
		  //variable pour la gestion de l'audio
			this.audioW = Popcorn(this.audioElm);
			this.audioW.listen("timeupdate", function (evt) {
		  		var t = evt.currentTarget.currentTime;
		  		if(t>=this.nbSecFin)	this.audioW.pause();
		  	});
	  }, false);
  };
  
  return this.sst();
}