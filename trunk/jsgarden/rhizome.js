function rhizome(graine){
	this.graine = graine;
	this.data;
	this.st;
	this.env = 10;
	this.txts = new Array();
	this.cirs = new Array();
	this.paths = new Array();
	this.tronc = 30;
	
	this.draw = function(){	
		this.getHistoDeliciousTag();
	}

	this.getHistoDeliciousTag = function(){
		//récupération de l'historique des tags delicious
		var url = "http://feeds.delicious.com/v2/json/"+this.graine.jardin.exis+"/"+this.graine.tag+"?count=100";
		var rh = this;
		$.ajax({ type: "GET", dataType: "jsonp", url: url,
			success: function(data){
				rh.data = data;
				rh.pousse();
			}
		});
	}

	this.pousse = function(){
		var i, item, x, y, dt, hDate, nbItem = this.data.length
			, txt, cir, path, courbe;
		this.st = this.graine.jardin.R.set();
		//calcul la nouvelle grille
		for(i= 0; i < nbItem; i++){
			item = this.data[i];
			dt = new Date(item["dt"]).getTime();
			if(this.graine.jardin.oldest > dt){
				this.graine.jardin.oldest = dt;
			}			
		}
		this.graine.jardin.drawCouchesTempo();
		
		//positionne les noeuds
		x = this.graine.x;
		for(i= 0; i < nbItem; i++){
			item = this.data[i];
			//calcul la position verticale par rapport à la date
			dt = new Date(item["dt"]);
			hDate = (this.graine.jardin.now-dt.getTime())/1000;
			y = this.graine.jardin.terre.attr("y")+(this.graine.jardin.hSec*hDate);
			//création des graphiques
			txt = this.graine.jardin.R.text(x+this.env, y, item["dt"]);
			txt.attr({fill:"white", font: this.env+'px Helvetica, Arial'});
			txt.hide();
			cir = this.graine.jardin.R.circle(x, y, this.env);
			cir.attr({fill:"white", opacity: 0.1});
			//lien du noeud à la graine
			path = "M"+x+" "+y+" L"+x+" "+this.graine.y;
			//lien de la graine à la branche
			y = this.graine.jardin.compost.attr("y")-(this.graine.jardin.hSec*hDate/2)-this.tronc;
			//M 231.42857,298.07647 C 365.10362,113.8281 441.48286,128.04512 454.28571,212.36218
			if((i-1)%2)
				courbe=" C"+(x-13.4)+","+(y-18.5)+" "+(x-21)+","+(y-17)+" "+(x-22)+","+(y-8);
			else
				courbe=" C"+(x+13.4)+","+(y-18.5)+" "+(x+21)+","+(y-17)+" "+(x+22)+","+(y-8);
			path += " L"+x+" "+y+courbe;
			
			
			path = this.graine.jardin.R.path(path).attr({stroke: "green"});				
			this.txts.push(txt);
			this.cirs.push(cir);
			this.paths.push(path);
			this.st.push(
			    txt,
			    cir,
			    path
			);
		}
		this.graine.st.toFront();
		this.graine.fond.toFront();
		this.graine.txt.toFront();

	}
	this.redraw = function(){
		//recalcul la position des noeuds
		var i, courbe, path, hdate, dt, x = this.graine.x, nbItem = this.txts.length, duree = 2000;
		for(i= 0; i < nbItem; i++){
			
			//calcul la position verticale par rapport à la date
			dt = new Date(this.data[i]["dt"]);
			hDate = (this.graine.jardin.now-dt.getTime())/1000;
			y = this.graine.jardin.terre.attr("y")+(this.graine.jardin.hSec*hDate);

			//mise à jour du texte
			this.txts[i].stop().animate({y:y, easing: "elastic"}, duree);

			//mise à jour du noeud
			this.cirs[i].stop().animate({cy: y, easing: "elastic"}, duree);

			//lien lien
			path = "M"+x+" "+y+" L"+x+" "+this.graine.y;
			//lien de la graine à la branche
			y = this.graine.jardin.compost.attr("y")-(this.graine.jardin.hSec*hDate/2)-this.tronc;
			//M 231.42857,298.07647 C 365.10362,113.8281 441.48286,128.04512 454.28571,212.36218
			if((i-1)%2)
				courbe=" C"+(x-13.4)+","+(y-18.5)+" "+(x-21)+","+(y-17)+" "+(x-22)+","+(y-8);
			else
				courbe=" C"+(x+13.4)+","+(y-18.5)+" "+(x+21)+","+(y-17)+" "+(x+22)+","+(y-8);
			path += " L"+x+" "+y+courbe;
			//mise à jour du noeud
			this.paths[i].stop().animate({path: path, easing: "elastic"}, duree);
		}
		
	}

}