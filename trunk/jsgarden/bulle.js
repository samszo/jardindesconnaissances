function bulle(nuage, x, y, tag, taille, data){
	this.nuage = nuage;
	this.x = x;
	this.cx;
	this.y = y;
	this.cy;
	this.tag = tag;
	this.taille = taille;
	this.eli;
	this.txt;
	this.st;
	this.env = 4;
	this.data = data;
	
	this.draw = function(){
		this.cx = this.tag.length*this.taille;
		this.cy = (this.taille);
		
		this.eli = this.nuage.jardin.R.ellipse(this.x, this.y, this.cx, this.cy);
		this.eli.attr({fill:"black", opacity: 0.2});		
		
		this.txt = this.nuage.jardin.R.text(this.x, this.y, this.tag);
		this.txt.attr({fill:"black", font: (this.taille)+'px Helvetica, Arial'});

		this.st = this.nuage.jardin.R.set();
		this.st.push(
		    this.eli,
		    this.txt
		);

		var b = this;
		//affichage d'un popup 
		this.st.hover(function(event){
				b.nuage.jardin.showPopup(this.attr("cx"), this.attr("cy")-10, ["tag : "+b.tag, "nombre d'occurence : "+b.data['poids']]);
			}
			,function () {
				b.nuage.jardin.hidePopup();
            }
		);
		
		this.st.click(function(event){
			b.tombe();
			b.nuage.jardin.planteGraine(this.attr("cx"), this.attr("cy"), b.tag);
		});
		
	}

	this.bougeChaos = function(niv){
		//bouge en suivant le nuage	
		var cxN = this.nuage.eli.attr("cx");
		var rxN = this.nuage.eli.attr("rx");

		//choisi si la bulle tombe
		if((Math.random()*100)<niv && this.txt.attr("x") > rxN && this.txt.attr("x") < this.nuage.wCiel-rxN){
			this.tombe(); 
		}else{
			//revient à gauche si le nuage le fait
			if(cxN<0)this.st.attr({x: 0-this.cx, cx: 0-this.cx});
			
			var rndX = Math.floor(Math.random()*rxN)+cxN;
			var rndY = Math.floor(Math.random()*this.nuage.eli.attr("ry"))+this.nuage.eli.attr("cy");
			var rndV = Math.floor(Math.random()*3000);
			var b = this;
			
			this.st.stop().animate({x: rndX, cx: rndX, y: rndY, cy: rndY
				, easing: "elastic"}, rndV, function () {b.bougeChaos(niv+1)});
		}
	}
	
	this.tombe = function(){
		var b = this;
		var fin = b.nuage.jardin.terre.attr("height")-this.cy, pas = fin/4;
		if(b.eli.attr("cy")<fin){
			this.st.stop().animate({
				"0%": {y: b.eli.attr("cy"), cy: b.eli.attr("cy"), easing: "linear"},
				"25%": {y: pas, cy: pas, easing: "linear"},
				"50%": {y: pas*2, cy: pas*2, easing: "linear"},
				"75%": {y: pas*3, cy: pas*3, easing: "linear"},
				"100%": {y: fin, cy: fin, easing: "bounce", callback: function () {b.coule()}}
			},5000);
		}
	}
	
	this.coule = function(){
		this.txt.attr({fill:"white"});
		this.eli.attr({"stroke-width":this.env, stroke:"green", "stroke-dasharray":"-"});
		this.matchTag();
	}

	this.matchTag = function(){
		var i, nbGraine = this.nuage.jardin.graines.length;
		
		for(i=0; i < nbGraine; i++){
			var graine = this.nuage.jardin.graines[i];
			if(this.tag == graine.tag){
				if(graine.rhizomes.length==0){
					//création du rhizome
					var rhi = new rhizome(graine);
					rhi.draw();
					graine.rhizomes.push(rhi);
					this.remove(i);
				}
			}
		}
	}	
	
	this.remove = function(id){
		//supprime toute les bulles identiques
		var i, nb = this.nuage.bulles.length;
		
		for(i=0; i < nb; i++){
			var b = this.nuage.bulles[i];
			if(b && this.tag == b.tag){
				b.st.stop()
				b.st.remove();
				this.nuage.bulles.splice(i, 1);
			}
		}
	}

}