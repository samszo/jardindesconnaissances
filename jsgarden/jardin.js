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
	this.label;
	this.frame;
	this.is_label_visible=false;
	this.leave_timer;
	
}
jardin.prototype = { 

	draw: function(){
	
		//popup
		var i
			,txt = {font: '18px Helvetica, Arial', fill: "black"}
        	,txt1 = {font: '14px Helvetica, Arial', fill: "black"}
  			;
		this.label = this.R.set();
	    this.label.push(this.R.text(60, 10, "---").attr(txt));
	    this.label.push(this.R.text(60, 30, "---").attr(txt1));
	    this.label.hide();
	    this.frame = this.R.popup(100, 100, this.label, "top").attr({fill: "white", stroke: "green", "stroke-width": 2, "fill-opacity": .7}).hide();
			
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
	,showPopup: function(x, y, infos){
		clearTimeout(this.leave_timer);
		this.label[0].attr({text: infos[0]}).stop().hide();
        this.label[1].attr({text: infos[1]}).stop().hide();
		var ppp = this.R.popup(x, y, this.label, "top", 1);
		this.frame.show().stop().toFront().animate({path: ppp.path}, 200);
		this.label[0].show().toFront().animateWith(this.frame, {translation: [ppp.dx, ppp.dy]}, 200 * this.is_label_visible);
        this.label[1].show().toFront().animateWith(this.frame, {translation: [ppp.dx, ppp.dy]}, 200 * this.is_label_visible);
        this.is_label_visible = true;
	}
	,hidePopup: function(x, y, infos){
        var j = this;
        this.leave_timer = setTimeout(function () {
            j.frame.hide();
            j.label[0].hide();
            j.label[1].hide();
            j.is_label_visible = false;
        }, 1);
	}
	,planteGraine: function(x,y,tag){
		var e = new graine(this, x,this.compost.attr("y")+(this.compost.attr("height")/2),this.compost.attr("height")/2);
		e.setFiltre(tag);
		this.graines.push(e);
	}
	,setNuage: function(type){	
		var n = new nuage(this, 0, 100, this.w, this.data, type);
		n.draw();
		this.nuages.push(n);
	}
	,vent: function(x){
		var b = Math.floor(Math.random()*this.forceVent);
		if((b-1)%2)
			return x+b;
		else
			return x-b;
	}
	,drawCouchesTempo: function () {
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
		var nbLigne, font = 12; maxInt = 40;//this.terre.attr("height")/font;
		
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
		var i, x = this.x+10, y, d, path, txt; 
		for (i=0; i <= nbLigne; i++){
			//on affiche les heures
			if(nbHeure < maxInt){
				d = new Date(dN.getFullYear(), dN.getMonth(), dN.getDate(), dN.getHours(), 0, 0, 0);
				d = new Date(d.getTime()-(i*60*60*1000));
				strDate = this.jours[d.getDay()] + ' ' +d.getHours() + ' H';
			}else if(nbJour < maxInt){
				//on affiche les jours
				d = new Date(dN.getFullYear(), dN.getMonth(), dN.getDate(), 0, 0, 0, 0);
				d = new Date(d.getTime()-(i*60*60*1000*24));
				strDate = this.jours[d.getDay()] + ' ' + d.getDate() + ' ' + this.mois[d.getMonth()];
			}else if(nbMois < maxInt){
				//on affiche les mois
				var nbY = parseInt((dN.getMonth()+i)/12);
				var numM = dN.getMonth()-i+(12*nbY);
				d = new Date(dN.getFullYear()-nbY, numM, 1, 0, 0, 0, 0);
				strDate = this.mois[d.getMonth()] + ' ' + d.getFullYear();
			}else if(nbMois > maxInt){
				//on affiche les années
				d = new Date(dN.getFullYear()-i, 0, 1, 0, 0, 0, 0);
				strDate = d.getFullYear();
			}
			y = this.getTempoY(d);		
			path = "M"+this.x+" "+y+" L"+this.w+", "+y;
			path = this.R.path(path).attr({stroke: "white"});
			txt = this.R.text(x,y-10,strDate).attr({fill: "white", font: font+'px Helvetica, Arial', 'text-anchor':'start'});
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
	,getTempoY: function (d) {
		//y = this.terre.attr("y")+(this.hInt*i);
		var nbSec = (this.now-d.getTime())/1000;
		return this.terre.attr("y")+(this.hSec*nbSec)+20;
	}

}