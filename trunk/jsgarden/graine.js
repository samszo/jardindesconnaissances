function graine(J, x, y, r){
	this.jardin = J;
	this.x = x;
	this.y = y;
	this.r = r;
	this.points;
	this.paths = new Array();
	this.tag = "";
	this.txt;
	this.fond;
	this.st = new Array();
	this.rhizomes = new Array();
	this.txtL;
	this.maxX;

	this.draw = function(){	
		var i;
		this.st = this.jardin.R.set();
		for (i=0; i < 6; i++){
			this.getPath(i);
		}
		this.getFond();
		this.st.toFront();
		this.fond.toFront();
		this.txt.toFront();
		this.matchTag();
	};
	
	this.getFond = function(){
		// calcul le fond de l'exagone pour pouvoir gérer des événements globaux
		var path = "M"+this.points[0][0]+" "+this.points[0][1];
		var i;
		for (i=1; i < 6; i++){
			path += " L"+this.points[i][0]+" "+this.points[i][1];
		}
		if(this.fond){
			this.fond.animate({path: path}, 500);		
		}else{
			this.fond = this.jardin.R.path(path);		
			this.fond.attr({fill:"red", opacity: 0.2});
			var g = this;
			this.fond.node.onclick = function () {
				g.setFiltre();
			};
		}
	};

	this.setFiltre = function(saisie){

		if(saisie=="")saisie = prompt("Saisissez votre tag :", "");
		if (saisie!=null) {
        	this.tag = saisie; 
			this.getPoints();
        	if(this.txt){
				this.txt.attr({text:this.tag});
			}else{
				this.txt = this.jardin.R.text(this.x, this.y, this.tag);
				this.txt.attr({fill:"black", font: this.r+'px Helvetica, Arial'});	
			}	
		}
		//recalcule l'exagone
		this.draw();
	};

	this.getPath = function(j){
		var path = "M"+this.points[j][0]+" "+this.points[j][1]+"L"+this.points[j+1][0]+" "+this.points[j+1][1];
		if(this.paths[j]){
			this.paths[j].animate({path: path}, 500);
		}else{
			var c = this.jardin.R.path(path);		
			c.attr({stroke:"green","stroke-width":10});
			c.node.onclick = function () {
				if(c.attr("stroke")=="red"){
					c.attr("stroke", "green");			
				}else{
					c.attr("stroke", "red");
				}
			};
			this.st.push(c);
			this.paths.push(c);			
		}
	};

	this.getPoints = function(){
		//recalcule le x en fonction de l'étendu du rhizome de la dernière graine
		var txt = this.jardin.R.text(10, 10, this.tag);
		this.txtL = txt[0].clientWidth;
		txt.remove();
		this.x = this.jardin.compost.attr("x")+this.txtL+this.r;
		if(this.jardin.graines.length>0){
			this.x = this.jardin.graines[this.jardin.graines.length-1].points[0][0]+(this.jardin.graines[this.jardin.graines.length-1].r*2);
			var rhi = this.jardin.graines[this.jardin.graines.length-1].rhizomes[0]; 
			if(rhi && this.x < rhi.maxX+(rhi.env*2))this.x = rhi.maxX+(rhi.env*2);
		}

		this.points = new Array();
		var _x,_y, nbPoint = 6, pi = Math.PI, i;
		for(i=0; i < nbPoint; i++){
			_x = (Math.cos(2 * i * pi / nbPoint)*this.r)+this.x;
			_y = (Math.sin(2 * i * pi / nbPoint)*this.r)+this.y;

			//gestion de la largeur de l'exagone suivant le texte
			if(i == 0 || i == 1) _x += this.txtL;
			if(i == 2 || i == 3 || i == 4) _x -= this.txtL;
			if(i == 5) _x = this.points[1][0];

			this.points.push(new Array(_x, _y));
		}
		this.points.push(new Array(this.points[0][0], this.points[0][1]));
	};

	this.matchTag = function(){
		var i, j, nbNuage = this.jardin.nuages.length;
		
		for(i=0; i < nbNuage; i++){
			var nuage = this.jardin.nuages[i];
			var nbBulle = nuage.bulles.length;
			for(j=0; j < nbBulle; j++){
				var bulle = nuage.bulles[j];
				if(bulle && this.tag == bulle.tag){
					//création du rhizome
					if(this.rhizomes.length==0){
						var rhi = new rhizome(this);
						rhi.draw();
						this.rhizomes.push(rhi);
						bulle.remove(j);
						j--;
						nbBulle--;
					}
				}
			}		
		}
	};


}