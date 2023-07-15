function rhizome(graine){
	this.graine = graine;
	this.data;
	this.st;
	this.env = 10;
	this.txts = new Array();
	this.cirs = new Array();
	this.tags = new Array();
	this.paths = new Array();
	this.tronc = 30;
	this.maxX = 0;
		
	this.draw = function(){	
		this.getHistoDeliciousTag();
	};

	this.getHistoDeliciousTag = function(){
		//récupération de l'historique des tags delicious
		var url = "http://feeds.delicious.com/v2/json/"+this.graine.jardin.exis+"/"+this.graine.tag+"?count=100";
		var dT = "jsonp"; //jsonp = valide en cross domain
		var url = "http://localhost/jardindesconnaissances/public/flux/docs?uti="+this.graine.jardin.exis+"&tag="+this.graine.tag;
		var dT = "json";
		var rh = this;
		$.ajax({ type: "GET", dataType: dT, url: url,
			success: function(data){
				rh.data = data;
				rh.pousse();
			}
		});
	};

	this.pousse = function(){
		var i, item, x, y, dt, hDate, nbItem = this.data.length
			, txt, cir, rhi = this;
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
			y = this.graine.jardin.getTempoY(dt);	
			hDate = (this.graine.jardin.now-dt.getTime())/1000;
			
			//création des graphiques
			txt = this.graine.jardin.R.text(x+this.env, y, item["dt"]);
			txt.attr({fill:"white", font: this.env+'px Helvetica, Arial'});
			txt.hide();
			cir = this.graine.jardin.R.circle(x, y, this.env);
			cir.attr({fill:"white", opacity: 0.3});
			cir.id = rhi.graine.jardin.graines.length+"-"+i;
			
			this.txts.push(txt);
			this.cirs.push(cir);
			//affichage d'un popup 
			cir.hover(function(event){
					var id = this.id.split("-")[1];
					var idDate = new Date(rhi.data[id]["dt"]);
					rhi.graine.jardin.showPopup(this.attr("cx"), this.attr("cy"), [rhi.data[id]["d"],"date : "+idDate.getDate()+'-'+idDate.getMonth()+'-'+idDate.getFullYear()]);
					rhi.paths[id].attr({stroke:"red"}).toFront();
				}
				,function (event) {
					rhi.graine.jardin.hidePopup();
					var id = this.id.split("-")[1];
					rhi.paths[id].attr({stroke:"green"});
	            }
			);
			
			var tags = item["t"], j, xTag, path="", courbe, cirT;
			for(j= 0; j < tags.length; j++){
				if(tags[j]!=this.graine.tag){
					xTag = x+(this.env*3*(j+1));
					//enregistre le max pour le placement de la prochaine graine
					if(this.maxX < xTag)this.maxX = xTag;
					//création du graphique pour le tag lié
					cirT = this.graine.jardin.R.circle(xTag, y, this.env);
					cirT.attr({fill:"orange", opacity: 0.3});
					cirT.id = cir.id+"-"+j;				
					this.tags.push(cirT);
					
					//affichage d'un popup 
					cirT.hover(function(event){						
							var ids = this.id.split("-");
							rhi.graine.jardin.showPopup(this.attr("cx"), this.attr("cy"), [rhi.data[ids[1]]["d"],"tag : "+rhi.data[ids[1]]["t"][ids[2]]]);
							rhi.paths[ids[1]].attr({stroke:"red"}).toFront();
						}
						,function (event) {
							rhi.graine.jardin.hidePopup();
							var id = this.id.split("-")[1];
							rhi.paths[id].attr({stroke:"green"});
			            }
					);
					this.st.push(cir);
					//calcul le lien
					if(path=="")path = "M"+xTag+" "+y; else	path += " L"+xTag+" "+y;
				}
			}

			//lien du noeud 
			if(path=="") path = "M"+x+" "+y; else path += " L"+x+" "+y;
			//à la graine
			path += " L"+x+" "+this.graine.y;
			//lien de la graine à la branche
			y = this.graine.jardin.compost.attr("y")-(this.graine.jardin.hSec*hDate/2)-this.tronc;
			//M 231.42857,298.07647 C 365.10362,113.8281 441.48286,128.04512 454.28571,212.36218
			if((i-1)%2)
				courbe=" C"+(x-13.4)+","+(y-18.5)+" "+(x-21)+","+(y-17)+" "+(x-22)+","+(y-8);
			else
				courbe=" C"+(x+13.4)+","+(y-18.5)+" "+(x+21)+","+(y-17)+" "+(x+22)+","+(y-8);
			path += " L"+x+" "+y+courbe;
				
			path = this.graine.jardin.R.path(path).attr({stroke: "green"});				
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

	};
	
	this.redraw = function(){
		//recalcul la position des noeuds
		var i, z=0, courbe, hdate, dt, x = this.graine.x, nbItem = this.txts.length, duree = 2000;
		for(i= 0; i < nbItem; i++){
			
			//calcul la position verticale par rapport à la date
			dt = new Date(this.data[i]["dt"]);
			hDate = (this.graine.jardin.now-dt.getTime())/1000;
			y = this.graine.jardin.getTempoY(dt);	

			//mise à jour du texte
			this.txts[i].stop().animate({y:y, easing: "elastic"}, duree);

			//mise à jour du noeud
			this.cirs[i].stop().animate({cy: y, easing: "elastic"}, duree);
			
			//mise à jour du lien
			var tags = this.data[i]["t"], j, path="", xTag;
			for(j= 0; j < tags.length; j++){
				if(tags[j]!=this.graine.tag){
					xTag = x+(this.env*3*(j+1));
					//calcul le lien
					if(path=="")path = "M"+xTag+" "+y; else	path += " L"+xTag+" "+y;
					//mise à jour des tags liés
					this.tags[z].stop().animate({cy: y, easing: "elastic"}, duree);
					z++;										
				}
			}
			//lien du noeud 
			if(path=="") path = "M"+x+" "+y; else path += " L"+x+" "+y;
			//à la graine
			path += " L"+x+" "+this.graine.y;
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
		
	};

}