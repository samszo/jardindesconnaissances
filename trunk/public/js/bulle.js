/**
 * 
 * 
 * 
 */
function bulle(config) {
	this.id = config.id;  
	this.div = config.div;  
	this.svg;  
	this.urlJson = config.urlJson;
	this.urlBookDetail = config.urlBookDetail;
	this.urlUtiDetail = config.urlUtiDetail;
	this.r = config.r;
	this.h = config.h;  
	this.w = config.w;  
	//this.x = config.x;  
	//this.y = config.y;  
	//this.points = config.points;
	
	this.bulle = function() {
		
		var self = this;

		self.svg = self.div.append("svg:svg")
	        .attr("width", self.w)
	        .attr("height", self.h)
	        .attr("id", self.id)
	        .append("svg:g");
		
	    var x = d3.scale.linear().range([0, self.r]),
	    y = d3.scale.linear().range([0, self.r]),
	    node,
	    root;

		var vis = self.svg.append("svg:g")
			.attr("class", "bulle")
		    .attr("transform", "translate(" + 0 + "," + 0 + ")");		
		
		var pack = d3.layout.pack()
		    .size([self.r, self.r])
		    .value(function(d) { 
		    	return d.nb;
		    	});

		var tooltip = d3.select("body")
	    .append("div")
		    .attr("class", "term")
		    .style("position", "absolute")
		    .style("z-index", "10")
		    .style("visibility", "hidden")
		    .style("font","32px sans-serif")
		    .style("background-color","white")		    
		    .text("a simple tooltip");
		
		d3.json(self.urlJson, function(data) {
		  node = root = data;
		  var nodes = pack.nodes(root);
		  
		  vis.selectAll("circle")
		      .data(nodes)
		    .enter().append("svg:circle")
		      .attr("class", function(d) { 
		    	  return d.children.length > 0 ? "parent" : "child"; 
		    	  })
		      .attr("cx", function(d) { return d.x; })
		      .attr("cy", function(d) { return d.y })
		      .attr("r", function(d) { return d.r; })
		      .on("mouseover", function(d, i) { 
	        			return tooltip.style("visibility", "visible");		        		
	        		})
	        	.on("mouseout", function(d, i) { 
	        		return tooltip.style("visibility", "hidden");
	        		})
    	        .on("mousemove", function(d, i){
    	        	return tooltip
		        		.style("top", (event.pageY+10)+"px")
		        		.style("left",(event.pageX+10)+"px")
    	        		.text(d.desc);
    	        	})	        		
    	        .on("click", function(d) { return zoom(node == d ? root : d); });		  

	
 		  vis.selectAll("text")
		      .data(nodes)
		    .enter().append("svg:text")
		      .attr("class", function(d) { 
		    	  return d.children.length > 0 ? "parent" : "child"; 
		    	  })
		      .attr("x", function(d) { return d.x; })
		      .attr("y", function(d) { return d.children.length > 0 ? d.y-2 : d.y; })
		      //.attr("dy", ".35em")
		      .attr("text-anchor", "middle")
		      .style("font-size", function(d) { 
		    	  //return d.niveau == 0 ? (r/20)+"px" : (r/20/d.niveau)+"px"; 
		    	  return d.r/6+"px"; 
		    	  })
		      .style("opacity", function(d) { return d.niveau < 2 && d.niveau >= 0  ? 1 : 0; })
		      .text(function(d) { return d.desc; });
		  
	
		  d3.select(window).on("click", function() { zoom(root); });
		});
	
		function zoom(d, i) {
			getDetail(d);
		  var k = self.r / d.r / 2;
		  var n = d.niveau;
		  x.domain([d.x - d.r, d.x + d.r]);
		  y.domain([d.y - d.r, d.y + d.r]);
	
		  var tG = self.svg.transition()
		      .duration(d3.event.altKey ? 7500 : 750);
		  var t = vis.transition()
	      	.duration(d3.event.altKey ? 7500 : 750);
	
		  t.selectAll("circle")
		      .attr("cx", function(d) { 
		    	  return x(d.x); 
		    	  })
		      .attr("cy", function(d) { return y(d.y); })
		      .attr("r", function(d) { return k * d.r; });
	
		  t.selectAll("text")
		      .attr("x", function(d) { return x(d.x); })
		      .attr("y", function(d) { return y(d.children.length > 0 ? d.y-2 : d.y); })
		      .style("font-size", function(d) { 
		    	  //return d.niveau == 0 ? (r/20*k)+"px" : (r/20/d.niveau*k)+"px"; 
		    	  return k * d.r/6+"px"; 
		    	  })
		      .style("opacity", function(d) { 
		    	  return d.niveau < parseInt(n+1) && d.niveau >= n ? 1 : 0; 
		    	  });
		  
		  var v = d.depth > 0 ? "hidden" : "visible";
		 /*
		  tG.selectAll(".graine")
		  	.attr("visibility", v);
		  tG.selectAll(".branche")
		  	.attr("visibility", v);
		  tG.selectAll(".racine")
		  	.attr("visibility", v);
		  */
		  node = d;
		  
		  d3.event.stopPropagation();
		}

		function getDetail(d) {
			d3.select("#grn").remove();
			d3.select("#brc").remove();
			if(d.idsDoc){
				d3.json(self.urlBookDetail+d.idsDoc, function(data) {
					tc = new tagcloud({idDoc:"gTC", div:divTagCloud, data:data.tags, w:300, h:300, global:true});						
					var nbUti = data.children.length;
					if(nbUti && data.idsUti){
						grn = new graine({id:"grn", div:divUtiExa, x:0, y:0, w:300, h:600, urlJson:"../biblio/utidetail?db=flux_zotero&idsUti="+data.idsUti, r:10});
						brc = new branche({id:"brc", titre:"Bibliographie", div:divDocBranches, x:0, w:680, h:600, root:data});
						grn.branches=brc;
					}

				});

			}
		}
		
  };
	  
  return this.bulle();
}