function jardin(R, x, y, w, h, exis){
	this.R = R;
	this.x = x;
	this.y = y;
	this.w = w;
	this.h = h;
	this.exis = exis;
	this.ciel;
	this.terre;
	this.compost;
	this.data;
	this.nuages = new Array();
	this.graines = new Array();
	this.forceVent = 80;
	this.hCompost = 32;
	this.now;
	this.oldest=new Date().getTime();
	this.diffDate;
	this.hInt;
	this.hSec;
	this.setCouchesTempo;
	this.jours = ['Dimanche', 'Lundi','Mardi','Mercredi','Jeudi','Vendredi', 'Samedi'];
	this.mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
}
jardin.prototype = { 

	draw: function(){
		//création du cadre pour le ciel
		this.ciel = this.R.rect(this.x, this.y, this.w, this.h/2);		
		this.ciel.attr({fill:"white"});
		//création du cadre pour le compost
		this.compost = this.R.rect(this.x, this.h/2, this.w, this.hCompost);		
		this.compost.attr({fill:"green", opacity: 0.5});
		//création du cadre pour la terre
		this.terre = this.R.rect(this.x, (this.h/2)+this.hCompost, this.w, this.h/2);		
		this.terre.attr({fill:"black"});
	}
	,
	planteGraine: function(x,y,tag){
		var e = new graine(this, x,this.compost.attr("y")+(this.compost.attr("height")/2),this.compost.attr("height")/2);
		e.setFiltre(tag);
		this.graines.push(e);
	}
	,
	setNuage: function(type){	
		var n = new nuage(this, 0, 100, this.w, this.data, type);
		n.draw();
		this.nuages.push(n);
	}
	,
	vent: function(x){
		var b = Math.floor(Math.random()*this.forceVent);
		if((b-1)%2)
			return x+b;
		else
			return x-b;
	}
	,
	drawCouchesTempo: function () {
		if(this.setCouchesTempo){
			this.setCouchesTempo.remove();
		}else{
			this.setCouchesTempo = this.R.set();
		}
			
	    //calcul l'interval de temps en seconde
		this.now=new Date().getTime();
	    var dN = new Date(this.now);
	    this.diffDate = (this.now-this.oldest)/1000;
			    
	    //calcul le nombre d'unité
	    var nbMinute = this.diffDate/60;
	    var nbHeure = nbMinute/60;
	    var nbJour = nbHeure/24;
	    var nbMois = nbJour/30;
	    var nbAn = nbJour/365;
		var nbLigne, font = 12; maxInt = this.terre.attr("height")/font;
		
		if(nbHeure < maxInt){
			//on affiche les heures
			nbLigne = nbHeure;
		}else if(nbJour < maxInt){
			//on affiche les jours
			nbLigne = nbJour;
		}else if(nbMois < maxInt){
			//on affiche les mois
			nbLigne = nbMois;
		}else if(nbMois > maxInt){
			//on affiche les années
			nbLigne = nbMois/12;
		}
		
	   	//calcul la hauteur de l'interval
		this.hInt = (this.terre.attr("height")-font)/nbLigne;
		this.hSec = (this.terre.attr("height")-100)/this.diffDate;

		//trace les repères
		var i, y, d, nbSec, path, txt; 
		for (i=0; i <= nbLigne; i++){
			//on affiche les heures
			if(nbHeure < maxInt){
				d = new Date(dN.getFullYear(), dN.getMonth(), dN.getDate(), dN.getHours(), 0, 0, 0);
				d = new Date(d.getTime()-(i*60*60*1000));
				strDate = d.toLocaleTimeString();
			}else if(nbJour < maxInt){
				//on affiche les jours
				d = new Date(dN.getFullYear(), dN.getMonth(), dN.getDate(), 0, 0, 0, 0);
				d = new Date(d.getTime()-(i*60*60*1000*24));
				strDate = this.jours[d.getDay()] + ' ' + d.getDate() + ' ' + this.mois[d.getMonth()];
			}else if(nbMois < maxInt){
				//on affiche les mois
				var nbY = parseInt((dN.getMonth()+i)/12);
				var numM = dN.getMonth()-i-(12*nbY);
				d = new Date(dN.getFullYear()-nbY, numM, 1, 0, 0, 0, 0);
				strDate = this.mois[d.getMonth()] + ' ' + d.getFullYear();
			}else if(nbMois > maxInt){
				//on affiche les années
				d = new Date(dN.getFullYear()-i, 0, 1, 0, 0, 0, 0);
				strDate = d.getFullYear();
			}
			//y = this.terre.attr("y")+(this.hInt*i);
			nbSec = (this.now-d.getTime())/1000;
			y = this.terre.attr("y")+(this.hSec*nbSec);
			
			path = "M"+this.x+" "+y+" L"+this.w+", "+y;
			path = this.R.path(path).attr({stroke: "white"});
			txt = this.R.text(this.x+100,y-10,strDate).attr({fill: "white", font: font+'px Helvetica, Arial'});
			this.setCouchesTempo.push(
			    txt,
			    path
			);
		}
		
		//recalcule les rhizomes
		var j, graine;
		for (i=0; i < this.graines.length; i++){
			graine = this.graines[i];
			for (j=0; j < graine.rhizomes.length; j++){
				graine.rhizomes[j].redraw();
			}	
		}
	}
}