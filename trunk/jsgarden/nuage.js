function nuage(jardin, x, y, wCiel, data, type){
	this.jardin = jardin;
	this.x = x;
	this.y = y;
	this.cx = x;
	this.cy = y;
	this.wCiel = wCiel;
	this.data = data;
	this.bulles = new Array();
	this.eli;
	this.txt;
	this.st;
	this.type = type;
	this.env = 50;
	this.label;
	this.frame;
	this.is_label_visible=false;
	this.leave_timer;
	this.TagNbMax=0;
	this.TagNbMin=0;
	this.TagIntervals = new Array();
	this.nbLimit = 100;
}
nuage.prototype = { 

	draw: function(type, w){

		var i;

		//dessine le nuage suivant le type de data
		switch (this.type) {
			case "deliciousBookUser":				
				for(i= 0; i < this.data.length; i++){
					if(i==0){
						//création des éléments graphiques
						this.setForme(this.data[i]["a"]);
					}				
					//gestion des dates
					var dt = new Date(this.data[i]["dt"]).getTime(); 
					if(this.jardin.oldest > dt){
						this.jardin.oldest = dt;
					}
					var tags = this.data[i]["t"];
					var j;
					for(j= 0; j < tags.length; j++){
						var e = new bulle(this, 0-this.cx, this.y, tags[j], 1);
						e.draw();
						this.bulles.push(e);
					}
				}
				this.flotte(this.wCiel, 30000, true);
				this.jardin.drawCouchesTempo();				
		 		break;
			case "fluxTagsExis":
				//transforme les poids en tableau lineaire
			    var arr = this.data.map(function(d){return d.poids;});
				var poids = d3.scale.log().domain([d3.min(arr),d3.max(arr)]).range([10, 64]); 
				for(i= 0; i < this.data.length; i++){
					if(i==0){
						//création des éléments graphiques
						this.setForme(this.data[i]["login"]);
					}
					if(i<this.nbLimit){
						var p = poids(this.data[i]["poids"]);
						console.log(this.data[i]["poids"]+"="+p);
						var e = new bulle(this, 0-this.cx, this.y, this.data[i]["code"], p, this.data[i]);
						e.draw();
						this.bulles.push(e);						
					}

				}
				this.flotte(this.wCiel, 30000, true);
				this.jardin.drawCouchesTempo();				
		 		break;
			case "deliciousTagsUser":
				var item;
				i=0;
				this.calculTags();
				for(item in this.data){				
					if(i==0){
						//création des éléments graphiques
						this.setForme(this.jardin.exis);
						i++;
					}				
					var e = new bulle(this, 0-this.cx, this.y, item, this.getTailleTag(this.data[item]));
					e.draw();
					this.bulles.push(e);
				}
				this.flotte(this.wCiel, 30000, true);
		 		break;
			case "jsonTags":
				this.t = this.jardin.R.text(this.x, this.y, "luckysemiosis");
				this.t.attr({fill:"black", font: '50px Helvetica, Arial'});
				
				this.nbBulle = this.data.nodes.length;
				if (this.nbBulle > 0) {
					for (i=0; i < this.nbBulle; i++){ i;
						//var rnd = Math.floor(Math.random()*this.w);
						var e = new bulle(this, 100,100,this.r,this.data.nodes[i]);
						e.draw();
						this.bulles.push(e);
					}
				}	
				break;
		}

	}
	,calculTags: function(){
		
		//calcul les intervales
		var nb;
		for(item in this.data){		
			//enregistre les intervalles d'occurence
			nb = this.data[item];
	    	if(this.TagNbMax < nb)
				this.TagNbMax = nb;
			if(this.TagNbMin > nb)
				this.TagNbMin=nb;				
		}
		this.TagIntervals.push((this.TagNbMax-this.TagNbMin)/3);
		this.TagIntervals.push((this.TagNbMax-this.TagNbMin)/1.5);		
	}
	,getTailleTag: function(nb) {
		//calcul un interval par rapport à une taille cf. protovis pour le faire plus proprement
		var taille;
		if (nb <= this.TagNbMin) {
			taille = 0.5;
		} else if (nb > this.TagNbMin && nb <= this.TagIntervals[0]) {
			taille = 1;
		} else if (nb > this.TagIntervals[0] && nb <= this.TagIntervals[1]) {
			taille = 3/2;
		} else if (nb > this.TagIntervals[1] && nb < this.TagNbMax) {
			taille = 4/2;
		} else if (nb >= this.TagNbMax) {
			taille = 5/2;
		}
		return taille*2;
	}
	,setForme: function(cpt){
		//création des éléments graphiques
		this.txt = this.jardin.R.text(this.x, this.y, cpt);
		this.txt.attr({fill:"black", font: this.env+'px Helvetica, Arial'});
		this.cx = this.txt.attr("text").length*this.env/3;
		this.cy = this.env;
		this.eli = this.jardin.R.ellipse(this.x, this.y, this.cx, this.cy);
		this.eli.attr({fill:"black", opacity: 0.2});
		this.st = this.jardin.R.set();
		this.st.push(
		    this.eli,
		    this.txt
		);
	}
	,flotte: function(fin, duree, deb){
		var n = this, i, j=0, nb = this.bulles.length;
		fin = fin + this.cx;
		//vérifie s'il reste des bulles
		for(i= 0; i < nb; i++){
			if(this.bulles[i].txt.attr("fill")=="black")j++;
			//s'il y a trop de bulle on ne les fait pas bouger
			if(nb<=this.nbLimit){			
				if(deb)this.bulles[i].bougeChaos(0);
			}
			/*
			else{
				//choisi si la bulle tombe 	
				if((Math.random()*nb)>i){
					var b = this.bulles[i];
					//b.eli.attr("cx") = this.txt.attr("x");
					b.tombe(); 
				}
			}
			*/
		}
		if(j==0)return;		
		this.st.attr({x: 0-this.cx, cx: 0-this.cx});
		this.st.stop().animate({
			"0%": {x: 0-this.cx, cx: 0-this.cx, easing: "linear"},
			"25%": {x: fin/4, cx: fin/4, easing: "linear"},
			"50%": {x: fin/2, cx: fin/2, easing: "linear"},
			"100%": {x: fin, cx: fin, callback: function () {n.flotte(fin, duree, false)}}
		},duree);
	}

};