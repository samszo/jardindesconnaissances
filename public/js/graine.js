/**
 * 
 * 
 * merci beaucoup à http://bl.ocks.org/1167173
 * http://www-cs-students.stanford.edu/~amitp/Articles/HexLOS.html
 */
function graine(config) {
	this.id = config.id;  
	this.div = config.div;  
	this.r = config.r;  
	this.x = config.x;  
	this.y = config.y;  
	this.h = config.h;  
	this.w = config.w;  
	this.points = new Array();
	this.g;
	this.urlJson = config.urlJson;
	this.branches = config.branches;
	
	this.graine = function() {
		
		var self = this;

       	var vis = self.div.append("svg:svg")
       		.attr("x", config.x)
	        .attr("width", self.w)
	        .attr("height", self.h)
	        .attr("id", self.id)
	        .append("svg:g");               	
		
        var tooltip;
        tooltip = d3.select('body')
        	.append('div')
        	.classed('tooltip', true);
                
		var data=[];
		
		var size = 100, // hexagon size
	    radius = 25, // map radius
	    tilted = true, // true is horizontal alignment
		padding = 1;
	
		var translate = [0, 0];
	    
	    // binding events over 'svg' only
	    // problème avec svg.mouse vis.on('mousedown', mouseDrag)
	        vis.on('mousewheel', mouseScroll) // webkit
	        .on('DOMMouseScroll', mouseScroll); // firefox
	    /*    
	    vis.append('svg:rect')
	        .classed('bounds', true)
	        .attr('x', padding)
	        .attr('y', padding)
	        .attr('width', self.w - padding * 2)
	        .attr('height', self.h - padding * 2);
		*/
	    /* crosshair
	    vis.append('svg:circle')
	        .classed('bounds', true)
	        .attr('cx', self.w / 2)
	        .attr('cy', self.h / 2)
	        .attr('r', 20);
        */
	    
		d3.json(self.urlJson, function(result) {		        		    
		    fillMap(result.children);
		    position();
		    render();
		});
		
		
		function fillMap(result) {
		    var id = 0,
		        limit1 = 0,
		        limit2 = radius
		        nbUti = result.length, uti = 0;
		    //création de la grille
		    for (var j = -radius; j <= radius; j++) {
		        var i = limit1;
		        while (i <= limit2) {
		            data.push({
		                id: id++,
		                coordinates: [i, j],
		                lastSelected: 0,
		                type: 'regular',
		                idUti: -1,
		                login: "",
		                nbDoc: -1,
		                role: "",
		                resource: false
		            });		        				        		
		            i++;
		        }
		        if (j < 0) {
		            limit1--;
		        } else {
		            limit2--;
		        }
		    }
		    //remplissage des données du centre à la périphérie
		    i = 0, ps = [];
		    for (var uti = 0; uti < nbUti; uti++) {
		    	//met à jour les données
			    for (var j = 0; j <= i+1; j++) {
			    	if(uti < nbUti && !ps[i+"_"+j]){
			    		updateData(i, j, result[uti]);
				    	ps[i+"_"+j]=true;
			            uti ++;
			    	}
		            if(uti < nbUti && !ps[i+"_"+(-j)]){
		            	updateData(i, -j, result[uti]);
				    	ps[i+"_"+-j]=true;
			            uti ++;
		            }
		            if(uti < nbUti && !ps[-i+"_"+j]){
		            	updateData(-i, j, result[uti]);
				    	ps[-i+"_"+j]=true;
			            uti ++;
		            }
		            if(uti < nbUti && -i != -1 && -j != -1  && !ps[-i+"_"+(-j)]){
		            	updateData(-i, -j, result[uti]);
				    	ps[-i+"_"+-j]=true;
			            uti ++;			    	
		            }
			    }
	            i ++;
        	}
		    
		}
		
		function updateData(i, j, uti) {
	    	//trouve le bon hexagone
	    	var hexa = data.filter(function(d) { return d.coordinates[0] == i && d.coordinates[1] == j; });
	    	hexa = hexa[0];
	    	//met à jour les données
	    	data[hexa["id"]].idUti = uti["uti_id"];
	    	data[hexa["id"]].login = uti["login"];
	    	data[hexa["id"]].nbDoc = uti["nbDoc"];
	    	data[hexa["id"]].role = uti["role"];
	    	data[hexa["id"]].resource = true;			
		}
		
		function position() {
		    // http://goo.gl/8djhT
		    var stepX = tilted ? size * 3 / 4 : Math.sqrt(3) * size / 2,
		        stepY = tilted ? Math.sqrt(3) * size / 2 : size * 3 / 4,
		        offset = size / Math.sqrt(3) * 3 / 4
		    data.map(function(d) {
		        var i = d.coordinates[0],
		            j = d.coordinates[1],
		            x = stepX * i + (!tilted ? offset * j : 0) + self.w / 2,
		            y = stepY * j + (tilted ? offset * i : 0) + self.h / 2;
		        d.centroid = [Math.round(x * 1e2) / 1e2, Math.round(y * 1e2) / 1e2];
		        d.visible = !outbounds(x, y);
		    });
		}

		function render() {
		    renderMap();
		}

		function renderMap() {
		    var grid = vis.selectAll('polygon.tile')
		        .data(getVisibleData(), function(d) { return d.id; });
		    vis.selectAll('text').remove();

		    grid.enter()
		        .sort(function(a, b) { return a.id - b.id; })
		        .append('svg:polygon')
		        .classed('tile', true)
		        .classed('selected', function(d) { return !~(d.type.search('r')); })
		        .classed('resource', function(d) { return d.resource; })
		        .attr('points', function(d) {
		            return hex(d.centroid, size, tilted).join(' ');
		        })
		        .on('mouseover', mouseOver)
		        .on('mousemove', mouseMove)
		        .on('mouseout', mouseOut)
		        .on('mousedown', mouseDown);
			  grid.enter()
		        .sort(function(a, b) { return a.id - b.id; })
		        .append("svg:text")
		        	.attr("text-anchor", "middle")
		        	.style("font-size", size/8)
				    .attr("x",function(d) { return d.centroid[0]; })
				    .attr("y",function(d) { return d.centroid[1]; })
				    .text(function(d) { return d.login; });
		    
		    grid.exit().remove();
		}

		// Custom drag behavior (replacing 'zoom')
		function mouseDrag() {
		    var m0 = d3.svg.mouse(this),
		        that = this,
		        previousMove = [0, 0];
		    
		    d3.select('body').on('mousemove', function() {
		        var m1 = d3.svg.mouse(that),
		            shift = d3.event.shiftKey,
		            ctrl = d3.event.ctrlKey,
		            alt = d3.event.altKey,
		            x = ctrl ? 0 : m1[0] - m0[0] - previousMove[0],
		            y = shift ? 0 : m1[1] - m0[1] - previousMove[1];
		        
		        move(x, y);
		        previousMove[0] += x;
		        previousMove[1] += y;
		    });
		    d3.select('body').on('mouseup', function() {
		        d3.select('body')
		            .on('mousemove', null)
		            .on('mouseup', null);
		    });
		    
		    d3.event.preventDefault();
		}
		//

		function move(x, y) {
		    translate[0] += x;
		    translate[1] += y;

		    moveMap();
		}

		function moveMap() {
		    var dx = translate[0],
		        dy = translate[1];

		    // Update data
		    data.filter(function(d) {
		        var x = d.centroid[0] + dx,
		            y = d.centroid[1] + dy;
		        return d.visible && outbounds(x, y);
		    }).map(function(d) {
		        d.visible = false;
		        return d;
		    });
		    
		    data.filter(function(d) {
		        var x = d.centroid[0] + dx,
		            y = d.centroid[1] + dy;
		        return !d.visible && !outbounds(x, y);
		    }).map(function(d) {
		        d.visible = true;
		        return d;
		    });
		    //
		    
		    renderMap();
		    
		    vis.selectAll('.tile')
		        .attr('transform', 'translate(' + [dx, dy] + ')');
		}

		function outbounds(x, y) {
		    return x < padding || x > self.w - padding || y < padding || y > self.h - padding;
		}

		function removeAll() {
		    vis.selectAll('.tile').remove();
		}

		function hex(centroid) {
		    var a = size / 2, 
		        b = (Math.sqrt(3) * a) / 2,
		        x = centroid[0],
		        y = centroid[1];
		    return tilted
		        ? [[x - a / 2, y - b], [x - a, y], [x - a / 2, y + b], [x + a / 2, y + b], [x + a, y], [x + a / 2, y - b]]
		        : [[x - b, y - a / 2], [x - b, y + a / 2], [x, y + a], [x + b, y + a / 2], [x + b, y - a / 2], [x, y - a]];
		}

		// d3 mouse events
		function mouseOver(d, i) {
		    d3.select(this).classed('over', true);
		    tooltip.text(d.id + ' (' + d.coordinates + ') / ' + d.lastSelected + ' : ' + d.login)
		        .classed('visible', true);
		}

		function mouseMove(d, i) {
		    tooltip.style('top', (d3.event.pageY - 20) + 'px')
		        .style('left', (d3.event.pageX + 20) + 'px');
		}

		function mouseOut(d, i) {
		    d3.select(this).classed('over', false);
		    tooltip.classed('visible', false);
		}

		function mouseDown(d, i) {
		    var element = d3.select(this),
		        selected = element.classed('selected');
		        
		        element.classed('selected', !selected);
		        d.type = selected ? 'regular' : 'selected';
		        d.lastSelected = +d3.event.timeStamp;
		        
		        tooltip.text(d.id + ' (' + d.coordinates + ') / ' + d.lastSelected);
		        
		        //on met à jour la branche
		        self.branches.filtreUti(d.idUti);
		        
		}

		function mouseScroll(d, i) {
		    var e = d3.event,
		        delta = e.wheelDelta ? e.wheelDelta : e.detail ? -e.detail : 0;
		    if (delta > 0) {
		        scrollUp();
		    } else {
		        scrollDown();
		    }
		    d3.event.preventDefault();
		}
		//

		function scrollDown() {
		    if (size > 20) {
		        zoom(-20); // zoom out
		    }
		}

		function scrollUp() {
		    if (size <= 80) {
		        zoom(20); // zoom in
		    }
		}

		function zoom(amount) {
		    var proportion = (size + amount) / size,
		        dx = translate[0] * proportion - translate[0],
		        dy = translate[1] * proportion - translate[1];
		    size += amount;
		    removeAll();
		    position();
		    move(dx, dy);
		}

		function getVisibleData() { 
		    return data.filter(function(d) { return d.visible; });
		}
				
  };
  return this.graine();
}