/**
 * 
 * 
 * 
 */
function branche(config) {
	this.id = config.id;  
	this.div = config.div;  
	this.x = config.x;  
	this.w = config.w;  
	this.h = config.h;  
	this.root = config.root;
	this.render;
	this.titre = config.titre;
	
	this.branche = function() {
		
		var self = this;
		
		var x = d3.scale.linear().range([0, self.w]),
	    y = d3.scale.linear().range([0, self.h]);

       	var vis = self.div.append("svg:svg")
        .attr("x", self.x)
        .attr("width", self.w)
        .attr("height", self.h)
        .attr("id", self.id)
        .append("svg:g");               	

		var tooltip = d3.select("body")
	    .append("div")
		    .attr("class", "term")
		    .style("position", "absolute")
		    .style("z-index", "10")
		    .style("visibility", "hidden")
		    .style("font","12px sans-serif")
		    .style("background-color","white")		    
		    .text("a simple tooltip");
       	
	var partition = d3.layout.partition()
	    .value(function(d) {
	    	if(d.visible)
	    		return d.nbDoc; 
	    	});

	  var kx, ky, g;
		
	self.render = function (donnees) {
	
		  g = vis.selectAll("g").remove();
		  
		  g = vis.selectAll("g")
		      .data(partition.nodes(donnees))
		    .enter().append("svg:g")
		      .attr("transform", function(d) { return "translate(" + x(d.y) + "," + y(d.x) + ")"; })
		      .on("click", click);
	
		  kx = self.w / donnees.dx;
		  ky = self.h / 1;
		  
			g.append("svg:rect")
		      .attr("width", donnees.dy * kx)
		      .attr("height", function(d) {return d.dx * ky; })
		      .attr("class", function(d) { return d.children ? "parent" : "child"; });
	
			/*
			g.append("svg:text")
		      .attr("transform", transform)
		      //.attr("dy", ".35em")
		      .style("opacity", function(d) { 
		    	  if(d.type == "book") return 0;
		    	  return d.dx * ky > 12 ? 1 : 0; 
		    	  })
		      .text(function(d) { 
		    	  return d.type == "note" ? d.note : d.titre; 
		    	  })
		    */	  
		    g.append("svg:foreignObject")
		      .attr("class", "fot")
		      .attr("width", donnees.dy * kx)
		      .attr("height", function(d) {return d.dx * ky; })
		      .append("xhtml:body")
		      .attr("class", "fotBody")
		      .html(function(d) { 
		    	  var txt = "";
		    	  if(d.dx * ky > 12 && d.type != "book")
		    		  d.type == "note" ? txt = d.note : txt = d.titre; 
		    	  return txt;
	    	  })
		      .style("font", "12px Arial");

		    	  
			g.append("svg:image")
		    .attr("xlink:href", function(d) { 
		    	if(d.type == "book"){
		    		return d.dTofUrl ? d.dTofUrl : "../img/question.jpg";
		    	}
		    	})
	        	.on("mouseover", function(d, i) { 
	        			return tooltip.style("visibility", "visible");		        		
	        		})
	        	.on("mouseout", function(d, i) { 
	        		return tooltip.style("visibility", "hidden");
	        		})
		        .on("mousemove", function(d, i){
		        	var txt = "";
	    			d.type == "book" ? txt=d.titre : txt=d.note;     	        	
		        	return tooltip
		        		.style("top", (event.pageY+10)+"px")
		        		.style("left",(event.pageX+10)+"px")
		        		.text(txt);
		        	})	        			      
		    .attr("width", function(d) { return d.dx * ky;})
		    .attr("height", function(d) { return d.dx * ky;});
			
	
		  
		  d3.select(window)
		      .on("click", function() { click(donnees); })
	  }
	  
	  function click(d) {
	    if (!d.children) return;

	    kx = (d.y ? self.w - 40 : self.w) / (1 - d.y);
	    ky = self.h / d.dx;
	    x.domain([d.y, 1]).range([d.y ? 40 : 0, self.w]);
	    y.domain([d.x, d.x + d.dx]);

	    var t = g.transition()
	        .duration(d3.event.altKey ? 7500 : 750)
	        .attr("transform", function(d) { return "translate(" + x(d.y) + "," + y(d.x) + ")"; });

	    t.select("rect")
	        .attr("width", d.dy * kx)
	        .attr("height", function(d) { return d.dx * ky; });

	    /*
	    t.select("text")
	        .attr("transform", transform)
	        .style("opacity", function(d) { 
		    	if(d.type == "book") return 0;
	        	return d.dx * ky > 12 ? 1 : 0; 
	        	});
		*/
	    var b = t.select(".fot")
	      .attr("width", d.dy * kx)
	      .attr("height", function(d) {return d.dx * ky; })
	      .select(".fotBody")
		      .text(function(d) { 
		    	  var txt = "";
		    	  if(d.dx * ky > 12 && d.type != "book")
		    		  d.type == "note" ? txt = d.note : txt = d.titre; 
		    	  return txt;
		      });

	    t.select("image")
	        .attr("width", d.dy * kx)
	        .attr("height", function(d) { return d.dx * ky; });	    	

	    d3.event.stopPropagation();
	  }

	  function transform(d) {
	    return "translate(8," + d.dx * ky / 2 + ")";
	  }

		self.render(self.root);	  
	  
	};

	this.filtreUti = function(idUti) {
    	var nbDoc = 0;
    	var idsUti = "";
		var donnees = this.root.children.filter(function(d) { 
    		var arrUti = d.idsUti.split(",");
    		var inArr = false;
    		arrUti.forEach(function(e){
    			if(e == idUti){
    				inArr = true;
    				nbDoc += d.nbDoc; 
    			}
    		});
    		return inArr;
    		});
    	this.render({titre:this.titre, nbDoc:nbDoc, children:donnees, idsUti:"", type:"racine"});
	}

	this.filtreTag = function(tag) {
    	var nbDoc = 0;
    	var idsUti = "";
		//il faut faire deux passes pour ne pas changer le tableau original
    	//on filtre les livres
    	var donnees =  this.root.children.filter(function(d) { 
    		//vérifie les tag du livre
    		d.visible = false;
    		d.tags.forEach(function(e){
    			if(e.code == tag.text)d.visible = true;
    		});
    		//filtre les notes si on n'a pas trouvé le tag dans le document
			if(!d.show){
				d.children.forEach(function(n) { 
		    		//vérifie les tags des notes
    				n.visible = false;
		    		n.tags.forEach(function(t){
		    			if(t.code == tag.text){
		    				d.visible = true;
		    				n.visible = true;
		    			}
		    		});
	    		});
			}
			return d.visible;
   		});    	
    	this.render({titre:this.titre, nbDoc:nbDoc, children:donnees, idsUti:"", type:"racine", visible:true});
    	
	}
	
  return this.branche();
}