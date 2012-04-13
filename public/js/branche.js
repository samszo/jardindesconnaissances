/**
 * 
 * 
 * 
 */
function branche(config) {
	this.id = config.id;  
	this.svg = config.svg;  
	this.x = config.x;  
	this.y = config.y;  
	this.points = config.points;
	
	this.branche = function() {
		
		var self = this;
		
		var g = this.svg.append("g")
		  	.attr("id", this.id)
		  	.attr("class", "branche")
		  	.attr("transform", "translate(" + this.x + "," + this.y + ")");
		
        var line = d3.svg.line()
	        .x(function(d){return d.x;})
	        .y(function(d){return d.y;})
	        .interpolate("cardinal"); 
        
        g.append("svg:path")
        	.attr("d", line(this.points))
        	.style("stroke-width", 2)
        	.style("stroke", "steelblue")
        	.style("fill", "none");

  };
	  
  return this.branche();
}