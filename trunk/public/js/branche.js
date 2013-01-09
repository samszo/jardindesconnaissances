/**
 * 
 * 
 * 
 */
function branche(config) {
	this.id = config.id;  
	this.svg = config.svg;  
	this.w = config.w;  
	this.h = config.h;  
	this.root = config.root;
	
	this.branche = function() {
		
		var self = this;
		
		var x = d3.scale.linear().range([0, self.w]),
	    y = d3.scale.linear().range([0, self.h]);

	var vis = self.svg;

	var partition = d3.layout.partition()
	    .value(function(d) { 
	    	return d.nbNote; 
	    	});

	  var g = vis.selectAll("g")
	      .data(partition.nodes(self.root))
	    .enter().append("svg:g")
	      .attr("transform", function(d) { return "translate(" + x(d.y) + "," + y(d.x) + ")"; })
	      .on("click", click);

	  var kx = self.w / root.dx,
	      ky = self.h / 1;

	  g.append("svg:rect")
	      .attr("width", root.dy * kx)
	      .attr("height", function(d) { return d.dx * ky; })
	      .attr("class", function(d) { return d.children ? "parent" : "child"; });

	  g.append("svg:text")
	      .attr("transform", transform)
	      .attr("dy", ".35em")
	      .style("opacity", function(d) { return d.dx * ky > 12 ? 1 : 0; })
	      .text(function(d) { return d.name; })

	  d3.select(window)
	      .on("click", function() { click(root); })

	  function click(d) {
	    if (!d.children) return;

	    kx = (d.y ? w - 40 : w) / (1 - d.y);
	    ky = h / d.dx;
	    x.domain([d.y, 1]).range([d.y ? 40 : 0, w]);
	    y.domain([d.x, d.x + d.dx]);

	    var t = g.transition()
	        .duration(d3.event.altKey ? 7500 : 750)
	        .attr("transform", function(d) { return "translate(" + x(d.y) + "," + y(d.x) + ")"; });

	    t.select("rect")
	        .attr("width", d.dy * kx)
	        .attr("height", function(d) { return d.dx * ky; });

	    t.select("text")
	        .attr("transform", transform)
	        .style("opacity", function(d) { return d.dx * ky > 12 ? 1 : 0; });

	    d3.event.stopPropagation();
	  }

	  function transform(d) {
	    return "translate(8," + d.dx * ky / 2 + ")";
	  }

	};
	  
  return this.branche();
}