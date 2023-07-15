function nuage(jardin, x, y, wCiel, data, type){
	this.jardin = jardin;
	this.x = x;
	this.y = y;
	this.cx = x;
	this.cy = y;
	this.wCiel = wCiel;
	this.data = data;
	this.bulles = [];
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
	this.TagIntervals = [];
	this.nbLimit = 10000;
	this.links = [];
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
						//console.log(this.data[i]["poids"]+"="+p);
						//var e = new bulle(this, this.cx, this.y, this.data[i]["code"], p, this.data[i]);
						//e.draw();
						//this.bulles.push(e);
						this.bulles.push({tag: this.data[i]["code"], poids: this.data[i]["poids"], value: parseInt(this.data[i]["poids"])});
						//ajoute les liens pour gérer la force
						if(i>0)	this.links.push({source: this.bulles[i], target: this.bulles[i-1]});
					}
				}
				//this.flotte(this.wCiel, 30000, true);
				//this.force();
				this.fragmentation(this.bulles, this.data[0]["login"]);
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
					var e = new bulle(this, this.cx, this.y, item, this.getTailleTag(this.data[item]));
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
		this.cx = this.txt[0].clientWidth;
		this.cy = this.txt[0].clientHeight;
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
				//if(deb)this.bulles[i].bougeChaos(0);
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
			"100%": {x: fin, cx: fin, callback: function () {n.flotte(fin, duree, false);}}
		},duree);
	}
	,force: function(){
		var force = self.force = d3.layout.force()
        	.nodes(this.bulles)
        	.links(this.links)
        	//.gravity(.05)
        	.distance(100)
        	//.charge(-100)
        	//.size([this.cx, this.cy])
        	.start();

		var link = d3svg.selectAll("line.link")
        	.data(this.links)
        	.enter().append("svg:line")
        	.attr("class", "link")
        	.attr("x1", function(d) { return d.source.x; })
        	.attr("y1", function(d) { return d.source.y; })
        	.attr("x2", function(d) { return d.target.x; })
        	.attr("y2", function(d) { return d.target.y; });

	    var node = d3svg.selectAll("g.node")
	    	.data(this.bulles)
	    	.enter().append("svg:g")
	        .attr("class", "node")
	        .call(force.drag);

	    node.append("svg:ellipse")
		  .attr("rx", function(d) { return d.cx/2;})
		  .attr("ry", function(d) { return d.cy;})
		  .attr("fill","black")
		  .attr("opacity",0.2);		

	    node.append("svg:text")
	        //.attr("class", "nodetext")
	        //.attr("dx","30")
	        //.attr("dy", "5em")
	        .attr("x", function(d) { return -(d.cx/2);})
	        .attr("y", function(d) { return d.taille/3;})
	        .style("font", function(d) { return Math.round(d.taille)+"px sans-serif";})
	        .text(function(d) { return d.tag; });		

	    force.on("tick", function() {
	        link.attr("x1", function(d) { return d.source.x; })
	            .attr("y1", function(d) { return d.source.y; })
	            .attr("x2", function(d) { return d.target.x; })
	            .attr("y2", function(d) { return d.target.y; });

	        node.attr("transform", function(d) { return "translate(" + d.x + "," + (d.y) + ")"; });
	      });
	}
	,fragmentation: function(data, login){
		 var nbFrag = 64;
		 var nb = data.length/nbFrag;
		 var globalNua = this; j = 0, r = 300, format = d3.format(",d"), w = this.wCiel, duree = w*10, nappe = (this.jardin.h/2)+this.jardin.hCompost;
		 var bubble = d3.layout.pack()
		      .sort(null)
		      .size([r, r]);
		 var timer = setInterval(fragment, w);

		 
		 function fragment(){
			
			// for(j= 0; j < nb; j++){
				 if (j >= nb) return clearInterval(timer);
			 
				 var deb = j*nbFrag, fin = (j+1)*nbFrag, xnua, ynua;
				 var dtn = data.filter(function(d, i) {
					 return i>=deb && i<=fin && d.value>0; 
					 });
				 dtn = bubble.nodes({children: dtn});
	
				 var nua = d3svg.append("svg:g")
				 	.attr("class", "nua_"+deb+"_"+fin)
				    .attr("transform", "translate(0,0)");
				 
				 var bulles = nua.selectAll("g.bulles_"+deb+"_"+fin)
				 	.data(dtn)
				    .enter().append("svg:g")
				    .attr("class", "bulles_"+deb+"_"+fin)
				    .attr("transform", function(d) { return "translate(" + (d.x-r) + "," + (d.y) + ")"; });
				 bulles.transition()
				    .duration(200)
				    .style("opacity", Math.random())
				    .each("end", pluie);
				 
				 var bulle = bulles.append("svg:g")
				    .attr("class", "bulle");
				 bulle.append("svg:title")
			 		.text(function(d) {return d.tag;});
				 bulle.append("svg:circle")
				 	.attr("r", function(d) { return d.r; })
					.attr("opacity",0.2)
					.attr("fill", "black");				 
				 bulle.append("svg:text")
				 	.attr("text-anchor", "middle")
				 	//.style("font", function(d) { return Math.round(d.value)+"px sans-serif"})
				    .text(function(d) { return d.tag;});
	
				 //création de la légende du nuage
				 nua.append("svg:circle")
				 	.attr("r", r/2)
				 	.attr("cx", -r/2)
				 	.attr("cy", r/2)
					.attr("opacity",0.1)
					.attr("fill", "black");				 
				 nua.append("svg:text")
				 	.attr("x", -r/2)
				 	.attr("y", r)
				 	.style("font", "16px sans-serif")
				    .text(login);
				 nua.append("svg:text")
				 	.attr("x", -r/2)
				 	.attr("y", r+20)
				 	.style("font", "16px sans-serif")
				    .text(deb+" : "+fin+" / "+data.length);
				    //.text("tags de "+deb+" à "+fin+" sur "+data.length);
				 nua.append("svg:image")
					.attr("xlink:href","../public/img/delicious.20.gif")
					.attr("x",-r/2-20).attr("y",r)
					.attr("height",16).attr("width",16);
				 
				 nua.transition()
				    .duration(duree)
				    .ease("linear")
				    .attr("transform", "translate(" + (r+w) + "," + (0) + ")")
				    .each("end", ventRetour)
				    ;
			 //}
			j++; 
		}
		function ventRetour(d, i) {
		  d3.select(this)
		    .transition()
		    .ease("linear")
		    .attr("transform", function(d) {return "translate(" + (-r-w) + "," + (0) + ")";})
		    .each("end", ventContinu);
		}		 
		function ventContinu(d, i) {
			  var nua = d3.select(this);
			  var nb = nua[0][0].childElementCount;
			  //vérifie s'il reste des bulles dans le nuage
			  if(nb<=4){
				  nua.remove();
			  }else{
				  nua.transition()
				    .duration(duree)
				    .ease("linear")
				    .attr("transform", function(d) {return "translate(" + (r+w) + "," + (0) + ")";})
				    .each("end", ventRetour);				  
			  }
		}		 
		function pluie(d, i) {
			var rnd = Math.random()*1000;
			if((rnd)<10){ 
				var goutte = d3.select(this)
					.transition()
					.duration(3000)
					.ease("linear")
					.style("opacity", 1)
				    .attr("fill", "blue")
					.attr("transform",function(d) {return "translate(" + (d.x) + "," + nappe + ")";});
				//création d'une bulle
				var b = new bulle(globalNua, d.x, d.y, d.tag, d.poids, d);
				goutte.remove();
				b.matchTag();
			}else{
				d3.select(this)
					.transition()
				    .duration(200)
				    .style("opacity", Math.random())
				    .each("end", pluie);
			}			
		}
	}
	// Returns a flattened hierarchy containing all leaf nodes under the root.
	,classes:function(root) {
	  var classes = [];
	  
	  root.forEach(function(node){
		  classes.push({tag: node.tag, value: node.taille ,children:null});
	  });
	  return {children: classes};
	  //return classes;
	  
	}
};