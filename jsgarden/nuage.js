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
}
nuage.prototype = { 

	draw: function(type, w){

		var i
			,txt = {font: '12px Helvetica, Arial', fill: "#fff"}
        	,txt1 = {font: '10px Helvetica, Arial', fill: "#fff"}
        	,colorhue = .6 || Math.random()
        	,color = "hsb(" + [colorhue, .5, 1] + ")"
        	;
		this.label = this.jardin.R.set();
	    this.label.push(this.jardin.R.text(60, 12, "---").attr(txt));
	    this.label.push(this.jardin.R.text(60, 27, "---").attr(txt1).attr({fill: color}));
	    this.label.hide();
	    this.frame = this.jardin.R.popup(100, 100, this.label, "top").attr({fill: "#000", stroke: "#666", "stroke-width": 2, "fill-opacity": .7}).hide();
	    
		//dessine le nuage suivant le type de data
		switch (this.type) {
			case "deliciousBookUser":				
				for(i= 0; i < this.data.length; i++){
					if(i==0){
						//création des éléments graphiques
						this.txt = this.jardin.R.text(this.x, this.y, this.data[i]["a"]);
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
	,
	flotte: function(fin, duree, deb){
		var n = this, i, j=0, nb = this.bulles.length;
		fin = fin + this.cx;
		//vérifie s'il reste des bulles
		for(i= 0; i < nb; i++){
			if(this.bulles[i].txt.attr("fill")=="black")j++;
			if(deb)this.bulles[i].bougeChaos(0);
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

}