/*code pour la montre 
merci beaucoup à http://bl.ocks.org/mbostock/1096355
*/
function horloge(config) {
	this.id = config.id;  
	this.idCont = config.idCont;  
	this.x = config.x; 
	this.y = config.y; 
	this.w = config.w; 
	this.h = config.h; 

	this.horloge = function() {
		
		var ts = this;

		var radius = Math.min(ts.w, ts.h) / 1.9;
		var spacing = .09;

		var formatSecond = d3.time.format("%S s"),
		    formatMinute = d3.time.format("%M m"),
		    formatHour = d3.time.format("%H h"),
		    formatDay = d3.time.format("%a"),
		    formatDate = d3.time.format("%d d"),
		    formatMonth = d3.time.format("%b");

		var color = d3.scale.linear()
		    .range(["hsl(0,0%,24%)", "hsl(0,0%,100%)"])
		    .interpolate(interpolateHsl);

		var arc = d3.svg.arc()
		    .startAngle(0)
		    .endAngle(function(d) { return d.value * 2 * Math.PI; })
		    .innerRadius(function(d) { return d.index * radius; })
		    .outerRadius(function(d) { return (d.index + spacing) * radius; });
		var svgClock, field;
		
		svgClock = d3.select(ts.idCont).append("svg")
		    .attr("id", ts.id)
		    .attr("width", ts.w)
		    .attr("height", ts.h)
		    .attr("x", ts.x)
		    .attr("y", ts.y)
			.append("g")
				.attr("transform", "translate(" + ts.w / 2 + "," + ts.h / 2 + ")");

		field = svgClock.selectAll("g")
		    .data(fields)
		  .enter().append("g");

		field.append("path");

		field.append("text");

		d3.transition().duration(0).each(tick);

		d3.select(self.frameElement).style("height", ts.h + "px");
		
		function tick() {
		  field = field
		      .each(function(d) { this._value = d.value; })
		      .data(fields)
		      .each(function(d) { d.previousValue = this._value; });

		  field.select("path")
		    .transition()
		      .ease("elastic")
		      .attrTween("d", arcTween)
		      .style("fill", function(d) { return color(d.value); });

		/*on n'affiche pas le texte
		  field.select("text")
		      .attr("dy", function(d) { return d.value < .5 ? "-.5em" : "1em"; })
		      .style("fill", "white")
		      .text(function(d) { return d.text; })
		    .transition()
		      .ease("elastic")
		      .attr("transform", function(d) {
		        return "rotate(" + 360 * d.value + ")"
		            + "translate(0," + -(d.index + spacing / 2) * radius + ")"
		            + "rotate(" + (d.value < .5 ? -90 : 90) + ")"
		      });
		*/
		  setTimeout(tick, 1000 - Date.now() % 1000);
		}

		function arcTween(d) {
		  var i = d3.interpolateNumber(d.previousValue, d.value);
		  return function(t) { d.value = i(t); return arc(d); };
		}

		function fields() {
		  var now = new Date;
		  return [
		    {index: .7, text: formatSecond(now), value: now.getSeconds() / 60},
		    {index: .6, text: formatMinute(now), value: now.getMinutes() / 60},
		    {index: .5, text: formatHour(now),   value: now.getHours() / 24},
		    {index: .3, text: formatDay(now),    value: now.getDay() / 7},
		    {index: .2, text: formatDate(now),   value: (now.getDate() - 1) / (32 - new Date(now.getYear(), now.getMonth(), 32).getDate())},
		    {index: .1, text: formatMonth(now),  value: now.getMonth() / 12}
		  ];
		}

		// Avoid shortest-path interpolation.
		function interpolateHsl(a, b) {
		  var i = d3.interpolateString(a, b);
		  return function(t) {
		    return d3.hsl(i(t));
		  };
		}
		//fin horloge	
				
  };
  return this.horloge();
}