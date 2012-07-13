/**
 * merci à http://bl.ocks.org/1341281
 */
function legende(config) {
	this.id = config.id;  
	this.data = config.data;
	
	this.legende = function() {

		var fctFiltre = config.fctFiltre;

		var m = [30, 3, 3, 3],
	    w = 800 - m[1] - m[3],
	    h = 120 - m[0] - m[2];

		var x = d3.scale.ordinal().rangePoints([0, w], 1),
		    y = {},
		    dragging = {};
	
		var line = d3.svg.line(),
		    axis = d3.svg.axis().orient("left"),
		    background,
		    foreground;
	
		var svg = d3.select("#"+this.id).append("svg:svg")
		    .attr("width", w + m[1] + m[3])
		    .attr("height", h + m[0] + m[2])
		  .append("svg:g")
		    .attr("transform", "translate(" + m[3] + "," + m[0] + ")");
	
		  var valeurs = this.data.valeurs;
		  var dimensions = this.data.dimensions;
		  x.domain(dimensions);
		  for(var i=0; i < dimensions.length; i++){
			  y[dimensions[i]] = d3.scale.linear().domain(this.data.y[i]).range([h, 0]);
		  }		
		
		  // Add grey background lines for context.
		  background = svg.append("svg:g")
		      .attr("class", "background")
		    .selectAll("path")
		      .data(valeurs)
		    .enter().append("svg:path")
		      .attr("d", path);
	
		  // Add blue foreground lines for focus.
		  foreground = svg.append("svg:g")
		      .attr("class", "foreground")
		    .selectAll("path")
		      .data(valeurs)
		    .enter().append("svg:path")
		      .attr("d", path);
	
		  // Add a group element for each dimension.
		  var g = svg.selectAll(".dimension")
		      .data(dimensions)
		    .enter().append("svg:g")
		      .attr("class", "dimension")
		      .attr("transform", function(d) { 
		    	  return "translate(" + x(d) + ")"; 
		    	  })
		      .call(d3.behavior.drag()
		        .on("dragstart", function(d) {
		          dragging[d] = this.__origin__ = x(d);
		          background.attr("visibility", "hidden");
		        })
		        .on("drag", function(d) {
		          dragging[d] = Math.min(w, Math.max(0, this.__origin__ += d3.event.dx));
		          foreground.attr("d", path);
		          dimensions.sort(function(a, b) { return position(a) - position(b); });
		          x.domain(dimensions);
		          g.attr("transform", function(d) { return "translate(" + position(d) + ")"; })
		        })
		        .on("dragend", function(d) {
		          delete this.__origin__;
		          delete dragging[d];
		          transition(d3.select(this)).attr("transform", "translate(" + x(d) + ")");
		          transition(foreground)
		              .attr("d", path);
		          background
		              .attr("d", path)
		              .transition()
		              .delay(500)
		              .duration(0)
		              .attr("visibility", null);
		        }));
	
		  // Add an axis and title.
		  g.append("svg:g")
		      .attr("class", "axis")
		      .each(function(d) { 
		    	  d3.select(this).call(axis.scale(y[d])); 
		    	  })
		    .append("svg:text")
		      .attr("text-anchor", "middle")
		      .attr("y", -9)
		      .text(String);
	
		  // Add and store a brush for each axis.
		  g.append("svg:g")
		      .attr("class", "brush")
		      .each(function(d) { d3.select(this).call(y[d].brush = d3.svg.brush().y(y[d]).on("brush", brush).on("brushend", brushend)); })
		    .selectAll("rect")
		      .attr("x", -8)
		      .attr("width", 16);
		//});
	
		function position(d) {
		  var v = dragging[d];
		  return v == null ? x(d) : v;
		}
	
		function transition(g) {
		  return g.transition().duration(500);
		}
	
		// Returns the path for a given data point.
		function path(d) {
		  return line(dimensions.map(function(p) { return [position(p), y[p](d[p])]; }));
		}
	
		// Handles a brush event, toggling the display of foreground lines.
		function brush() {
		  var actives = dimensions.filter(function(p) { return !y[p].brush.empty(); }),
		      extents = actives.map(function(p) { return y[p].brush.extent(); });
		  //met à jour les liens des échelles
		  foreground.style("display", function(d) {
		    return actives.every(function(p, i) {
		      return extents[i][0] <= d[p] && d[p] <= extents[i][1];
		    }) ? null : "none";
		  });
		}
		function brushend() {
			  var actives = dimensions.filter(function(p) { return !y[p].brush.empty(); }),
			      extents = actives.map(function(p) { return y[p].brush.extent(); });
			  //met à jour le graphique général
			  fctFiltre(actives, extents);
		}
		
  };
  	  
  return this.legende();
}
