/**
 * 
 * 
 * 
 */
function bulle(config) {
	this.id = config.id;  
	this.svg = config.svg;  
	//this.x = config.x;  
	//this.y = config.y;  
	//this.points = config.points;
	
	this.bulle = function() {
		
		var self = this;
		
		var r = 300,
	    x = d3.scale.linear().range([0, r]),
	    y = d3.scale.linear().range([0, r]),
	    node,
	    root;

		var vis = self.svg.append("svg:g")
			.attr("class", "bulle")
		    .attr("transform", "translate(" + 100 + "," + 100 + ")");		
		
		var pack = d3.layout.pack()
		    .size([r, r])
		    .value(function(d) { return d.size; });
		
		d3.json("../js/flare.json", function(data) {
		  node = root = data;
	
		  var nodes = pack.nodes(root);
	
		  vis.selectAll("circle")
		      .data(nodes)
		    .enter().append("svg:circle")
		      .attr("class", function(d) { return d.children ? "parent" : "child"; })
		      .attr("cx", function(d) { return d.x; })
		      .attr("cy", function(d) { return d.y; })
		      .attr("r", function(d) { return d.r; })
		      .on("click", function(d) { return zoom(node == d ? root : d); });
	
		  vis.selectAll("text")
		      .data(nodes)
		    .enter().append("svg:text")
		      .attr("class", function(d) { return d.children ? "parent" : "child"; })
		      .attr("x", function(d) { return d.x; })
		      .attr("y", function(d) { return d.y; })
		      .attr("dy", ".35em")
		      .attr("text-anchor", "middle")
		      .style("opacity", function(d) { return d.r > 20 ? 1 : 0; })
		      .text(function(d) { return d.name; });
	
		  d3.select(window).on("click", function() { zoom(root); });
		});
	
		function zoom(d, i) {
		  var k = r / d.r / 2;
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
		      .attr("y", function(d) { return y(d.y); })
		      .style("opacity", function(d) { return k * d.r > 20 ? 1 : 0; });
	
		  var v = d.depth > 0 ? "hidden" : "visible";
		  tG.selectAll(".graine")
		  	.attr("visibility", v);
		  tG.selectAll(".branche")
		  	.attr("visibility", v);
		  tG.selectAll(".racine")
		  	.attr("visibility", v);
		  
		  node = d;
		  
		  d3.event.stopPropagation();
		}

  };
	  
  return this.bulle();
}