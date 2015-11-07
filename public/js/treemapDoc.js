/**
 * fontionalité pour géréer des treemaps de document
 * 
 */
	
		//pour les doc
		var chartWidthDoc = 920;
	    var chartHeightDoc = 140;
	    var xscaleDoc = d3.scale.linear().range([0, chartWidthDoc]);
	    var yscaleDoc = d3.scale.linear().range([0, chartHeightDoc]);
	    var colorDoc = d3.scale.category10();//todo: une couleur par type de documents : html, texte, image, son...
	    var headerHeightDoc = 30;
	    var headerColorDoc = "#555555";
	    var transitionDurationDoc = 500;
	    var rootDoc;
	    var nodeDoc;
	    var svgDoc;		
	    var treemapDoc;
	    
		function setSvgDoc(dataDocs){
			//construction des documents avec les données conservées dans la base
			//merci beaucoup à http://bl.ocks.org/billdwhite/4325246
		   
			if(svgDoc)svgDoc.remove();
			
			svgDoc = svg
				.append("svg:g")
			    .attr("id", "svgDoc")		
		        .attr("width", chartWidthDoc)
		        .attr("height", chartHeightDoc)
       		    .attr("transform", "translate("+tableauXDoc+","+tableauYDoc+")");			

			treemapDoc = d3.layout.treemap()
		    	.round(false)
		    	.size([chartWidthDoc, chartHeightDoc])
		    	.sticky(true)
		    	.value(function(d) { 
			    	return d.poids; 
			    });

		    rootDoc = nodeDoc = dataDocs;		
				
			var nodes = treemapDoc.nodes(dataDocs);

	        var children = nodes.filter(function(d) {
	            return !d.children;
	        });
	        var parents = nodes.filter(function(d) {
	            return d.children;
	        });

			//instancie les data svg conservés dans la base
			children.forEach(function(svg){
				svg.dt = JSON.parse(svg.svgData);
			});
			//

			
			// create parent cells
	        var parentCells = svgDoc.selectAll("g.cellDoc.parent")
	            .data(parents, function(d) {
	                return "p-" + d.doc_id;
	            });
	        var parentEnterTransition = parentCells.enter()
	            .append("g")
	            .attr("class", "cellDoc parent")
	            .on("click", function(d) {
	                zoomDoc(d);
	            })
	            .append("svg")
	            .attr("class", "clip")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", headerHeightDoc);
	        parentEnterTransition.append("rect")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", headerHeightDoc)
	            .style("fill", headerColorDoc);
	        parentEnterTransition.append('text')
	            .attr("class", "labelDoc")
	            .attr("transform", "translate(3, 13)")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", headerHeightDoc)
	            .text(function(d) {
	                return d.titre;
	            });			

	     // update transition
	        var parentUpdateTransition = parentCells.transition().duration(transitionDurationDoc);
	        parentUpdateTransition.select(".cellDoc")
	            .attr("transform", function(d) {
	                return "translate(" + d.dx + "," + d.y + ")";
	            });
	        parentUpdateTransition.select("rect")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", headerHeightDoc)
	            .style("fill", headerColorDoc);
	        parentUpdateTransition.select(".labelDoc")
	            .attr("transform", "translate(10, 20)")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", headerHeightDoc)
	            .text(function(d) {
	                return d.titre;
	            });
	        // remove transition
	        parentCells.exit()
	            .remove();


	     // create children cells
	        var childrenCells = svgDoc.selectAll("g.cellDoc.child")
	            .data(children, function(d) {
	                return "c-" + d.doc_id;
	            });
	        // enter transition
	        var childEnterTransition = childrenCells.enter()
	            .append("g")
	            .attr("class", "cellDoc child")
	            .on("click", function(d) {
	                zoomDoc(nodeDoc === d.parent ? rootDoc : d.parent);
	            })
	            .append("svg")
	            .attr("class", "clip");
	        childEnterTransition.append("rect")
	            .classed("background", true)
	            .style("fill", function(d) {
	                return colorDoc(d.parent.titre);
	            })
	            .style("stroke", "white")
	        	.style("stroke-width", "2");
	        
	        childEnterTransition.append('text')
	            .attr("class", "labelDoc")
	            .attr('x', function(d) {
	                return d.dx / 2;
	            })
	            .attr('y', function(d) {
	                return d.dy / 2;
	            })
	            .attr("dy", ".35em")
	            .attr("text-anchor", "middle")
	            .style("display", function(d) {
	                return "";
	            })
	            .text(function(d) {
	                return d.titre;
	            });
	        // update transition
	        var childUpdateTransition = childrenCells.transition().duration(transitionDurationDoc);
	        childUpdateTransition.select(".cellDoc")
	            .attr("transform", function(d) {
	                return "translate(" + d.x + "," + d.y + ")";
	            });
	        childUpdateTransition.select("rect")
	            .attr("width", function(d) {
	                return Math.max(0.01, d.dx);
	            })
	            .attr("height", function(d) {
	                return d.dy;
	            })
	            .style("fill", function(d) {
	                return colorDoc(d.parent.titre);
	            });
	        childUpdateTransition.select(".labelDoc")
	            .attr('x', function(d) {
	                return d.dx / 2;
	            })
	            .attr('y', function(d) {
	                return d.dy / 2;
	            })
	            .attr("dy", ".35em")
	            .attr("text-anchor", "middle")
	            .style("display", "")
	            .text(function(d) {
	                return d.titre;
	            });

	        // exit transition
	        childrenCells.exit()
	            .remove();

	        zoomDoc(nodeDoc);

		}	
		function zoomDoc(d) {
	        treemapDoc
	            .padding([headerHeightDoc / (chartHeightDoc / d.dy), 0, 0, 0])
	            .nodes(d);

	        // moving the next two lines above treemap layout messes up padding of zoom result
	        var kx = chartWidthDoc / d.dx;
	        var ky = chartHeightDoc / d.dy;
	        var level = d;

	        xscaleDoc.domain([d.x, d.x + d.dx]);
	        yscaleDoc.domain([d.y, d.y + d.dy]);

	        if (nodeDoc != level) {
	        	svgDoc.selectAll(".cellDoc.child .labelDoc")
	                .style("display", "");
	        }

	        var zoomTransition = svgDoc.selectAll("g.cellDoc").transition().duration(transitionDurationDoc)
	            .attr("transform", function(d) {
	                return "translate(" + xscaleDoc(d.x) + "," + yscaleDoc(d.y) + ")";
	            })
	            .each("start", function() {
	                d3.select(this).select("labelDoc")
	                    .style("display", "");
	            })
	            .each("end", function(d, i) {
	                if (!i && (level !== self.root)) {
	                	svgDoc.selectAll(".cellDoc.child")
	                        .filter(function(d) {
	                            return d.parent === self.node; // only get the children for selected group
	                        })
	                        .select(".labelDoc")
	                        .style("display", "")
	                        .style("fill", function(d) {
	                            return idealTextColor(colorDoc(d.parent.titre));
	                        });
	                }
	            });

	        zoomTransition.select(".clip")
	            .attr("width", function(d) {
	                return Math.max(0.01, (kx * d.dx));
	            })
	            .attr("height", function(d) {
	                return d.children ? headerHeightDoc : Math.max(0.01, (ky * d.dy));
	            });

	        zoomTransition.select(".label")
	            .attr("width", function(d) {
	                return Math.max(0.01, (kx * d.dx));
	            })
	            .attr("height", function(d) {
	                return d.children ? headerHeightDoc : Math.max(0.01, (ky * d.dy));
	            })
	            .text(function(d) {
	                return d.titre;
	            });

	        zoomTransition.select(".child .labelDoc")
	            .attr("x", function(d) {
	                return kx * d.dx / 2;
	            })
	            .attr("y", function(d) {
	                return ky * d.dy / 2;
	            });

	        zoomTransition.select("rect")
	            .attr("width", function(d) {
	                return Math.max(0.01, (kx * d.dx));
	            })
	            .attr("height", function(d) {
	                return d.children ? headerHeightDoc : Math.max(0.01, (ky * d.dy));
	            })
	            .style("stroke", "white")
	        	.style("stroke-width", "2")	            
	            .style("fill", function(d) {
	                return d.children ? headerColorDoc : colorDoc(d.parent.titre);
	            });

	        nodeDoc = d;

	        if (d3.event) {
	            d3.event.stopPropagation();
	        }
	    }

	    function textHeight(d) {
	        var ky = chartHeightDoc / d.dy;
	        yscaleDoc.domain([d.y, d.y + d.dy]);
	        return (ky * d.dy) / headerHeightDoc;
	    }

	    function getRGBComponents(color) {
	        var r = color.substring(1, 3);
	        var g = color.substring(3, 5);
	        var b = color.substring(5, 7);
	        return {
	            R: parseInt(r, 16),
	            G: parseInt(g, 16),
	            B: parseInt(b, 16)
	        };
	    }


	    function idealTextColor(bgColor) {
	        var nThreshold = 105;
	        var components = getRGBComponents(bgColor);
	        var bgDelta = (components.R * 0.299) + (components.G * 0.587) + (components.B * 0.114);
	        return ((255 - bgDelta) < nThreshold) ? "#000000" : "#ffffff";
	    }