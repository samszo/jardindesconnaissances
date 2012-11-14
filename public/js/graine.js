/**
 * 
 * 
 * 
 */
function graine(config) {
	this.id = config.id;  
	this.svg = config.svg;  
	this.r = config.r;  
	this.x = config.x;  
	this.y = config.y;  
	this.points = new Array();
	this.g;
	
	this.graine = function() {
		
		var self = this;
				
        var line = d3.svg.line()
	        .x(function(d){return d.x;})
	        .y(function(d){return d.y;})
	        .interpolate("linear"); 

    	var tooltip = d3.select("body")
		    .append("div")
		    .style("position", "absolute")
		    .style("z-index", "10")
		    .style("visibility", "hidden")
		    .text("a simple tooltip");
        
		var r = 100,
	    x = d3.scale.linear().range([0, r]),
	    y = d3.scale.linear().range([0, r]),
	    node,
	    root;

		var vis = self.svg.append("svg:g")
	    	.attr("id", self.id)
	    	.attr("class", "graine")
		    .attr("transform", "translate(" + 400 + "," + 100 + ")");		
		
		var pack = d3.layout.pack()
		    .size([r, r])
		    .value(function(d) { return d.size; });
		
		d3.json("../js/flare.json", function(data) {
		  node = root = data;
	
		  var nodes = pack.nodes(root);
	
		  vis.selectAll("path")
		      .data(nodes)
		    .enter().append("svg:path")
		    	.attr("class", function(d) { return d.children ? "parent" : "child"; })
	        	.attr("d", function(d) { 
	        		return line(getCirclePoint(d.x, d.y, d.r));
	        		})
	        	.on("click", function(d) { 
	        		return zoom(node == d ? root : d); 
	        		});
	
		  vis.selectAll("text")
		      .data(nodes)
		    .enter().append("svg:text")
				.attr("class", function(d) { return d.children ? "parent" : "child"; })
				.attr("x", function(d) { return d.x; })
				.attr("y", function(d) { return d.y; })
				.attr("dy", ".35em")
				.attr("text-anchor", "middle")
				.style("opacity", function(d) { return d.r > 20 ? 1 : 0; })
				.on("mouseover", function(){
						return tooltip.style("visibility", "visible");
						})
				.on("mousemove", function(d){
					var name = d.name;
					return tooltip
						.style("top", (event.pageY-20)+"px")
						.style("left",(event.pageX-20)+"px")
						.text(name);
					})
				.on("mouseout", function(){return tooltip.style("visibility", "hidden");})
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
	
		  t.selectAll("path")
		  		.attr("d", function(d) {
		  			return line(getCirclePoint(x(d.x), y(d.y), k * d.r));
		  			});
	
		  t.selectAll("text")
		      .attr("x", function(d) { 
		    	  return x(d.x); 
		    	  })
		      .attr("y", function(d) { return y(d.y); })
		      .style("opacity", function(d) { return k * d.r > 20 ? 1 : 0; });
	
		  var v = d.depth > 0 ? "hidden" : "visible";
		  tG.selectAll(".bulle")
		  	.attr("visibility", v);
		  tG.selectAll(".branche")
		  	.attr("visibility", v);
		  tG.selectAll(".racine")
		  	.attr("visibility", v);
		  
		  node = d;
		  
		  d3.event.stopPropagation();
		}
        
        
		function getCirclePoint(x, y, r){
			var _x;
			var _y;
			var nbPoint = 6;
			var pi = Math.PI;
			var i;
			var points=[];
			//calcule les points du polygone
			for(var i=0; i < nbPoint; i++){
				_x = (Math.cos(2 * i * pi / nbPoint)*r)+x;
				_y = (Math.sin(2 * i * pi / nbPoint)*r)+y;
				points.push({"x":_x,"y":_y});
			}
			//pour fermer le polygone, on ajoute le premier point
			points.push({"x":points[0].x,"y":points[0].y});
			
			return points;
			
		}

  };
	  
  return this.graine();
}