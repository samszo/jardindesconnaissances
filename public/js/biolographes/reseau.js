function reseau(config) {
	this.idCont = config.idCont;  
	this.w = config.w;  
	this.h = config.h;
	this.charge = -500;
	this.lkDist = 400;
	this.nodes = config.nodes;
	this.links = config.links;
	this.colors = config.colors;
	this.clusters = config.clusters;
	this.dialogues = config.dialogues;
	this.lastNodeId = 0;
	this.selectNodeId;

	this.rs = function() {

		var svg, force, path, node, drag_line, drag, ajout=true, self=this;
		self.lastNodeId = self.nodes.length-1;

		var selected_node = null,
	    selected_link = null,
	    mousedown_link = null,
	    mousedown_node = null,
	    mouseup_node = null;

		var node_drag = d3.behavior.drag()
			.on("dragstart", dragstart)
			.on("drag", dragmove)
			.on("dragend", dragend);
		// only respond once per keydown
		var lastKeyDown = -1;
		
		svg = d3.select('#'+self.idCont)
			.append('svg')
			.attr('width', self.w)
			.attr('height', self.h);
		force = d3.layout.force()
		    .nodes(self.nodes)
		    .links(self.links)
		    .size([self.w, self.h])
		    .linkDistance(self.lkDist)
		    .charge(self.charge)
		    .on('tick', tick)

		// define arrow markers for graph links
		svg.append('svg:defs').append('svg:marker')
		    .attr('id', 'end-arrow')
		    .attr('viewBox', '0 -5 10 10')
		    .attr('refX', 6)
		    .attr('markerWidth', 3)
		    .attr('markerHeight', 3)
		    .attr('orient', 'auto')
		  .append('svg:path')
		    .attr('d', 'M0,-5L10,0L0,5')
		    .attr('fill', '#000');
		
		svg.append('svg:defs').append('svg:marker')
		    .attr('id', 'start-arrow')
		    .attr('viewBox', '0 -5 10 10')
		    .attr('refX', 4)
		    .attr('markerWidth', 3)
		    .attr('markerHeight', 3)
		    .attr('orient', 'auto')
		  .append('svg:path')
		    .attr('d', 'M10,-5L0,0L10,5')
		    .attr('fill', '#000');
		
		// line displayed when dragging new nodes
		drag_line = svg.append('svg:path')
		  .attr('class', 'link dragline hidden')
		  .attr('d', 'M0,0L0,0');
		// handles to link and node element groups
		path = svg.append('svg:g').selectAll('path'),
		node = svg.append('svg:g').selectAll('g');
		
		svg.on('mousedown', createNode)
		  .on('mousemove', mousemove)
		  .on('mouseup', mouseup);
		d3.select('#'+self.id)
		  .on('keydown', keydown)
		  .on('keyup', keyup);
		
		//ajoute les écouteur sur les dialogues
		document.querySelector('#addActeur').onclick = function() {
			 dt= {"nom":document.querySelector('#nomActeur').value
				, "prenom":document.querySelector('#prenomActeur').value 
				, "profession":document.querySelector('#professionActeur').value 
				, "specialite":document.querySelector('#specialiteActeur').value 
				, "fonction":document.querySelector('#fonctionActeur').value 
				, "nait":document.querySelector('#dtNait').value 
				, "mort":document.querySelector('#dtMort').value 
				, "isni":document.querySelector('#isniActeur').value 
				, "liens":[]
			 };	
			 //ajoute les liens
			 var tdLiens = document.querySelectorAll('#resultActeurLiens td');
			 for (var i = 0; i < tdLiens.length; i++) {
				 dt.liens.push(tdLiens[i].innerText);
			 }
			nodes[self.selectNodeId].dt = dt;
			draw();
			dialogues.acteur.close();
			selectItem = "";
		 }		
		document.querySelector('#addLieu').onclick = function() {
			 dt= {
				"nom":document.querySelector('#dtLieuAjout').value
				,"pays":document.querySelector('#dtPaysAjout').value
				,"ville":document.querySelector('#dtVilleAjout').value
				,"adresse":document.querySelector('#dtAdresseAjout').value
				,"lat":document.querySelector('#dtAdresseAjout').value
				,"lng":document.querySelector('#dtAdresseAjout').value
				,"zoom":document.querySelector('#dtZoomAjout').value
			 };			
			nodes[self.selectNodeId].dt = dt;
			draw();
			dialogues.lieu.close();
			selectItem = "";
		 }
		
		//visualisation du réseau
		draw();	

		// update force layout (called automatically each iteration)
		function tick(e) {
			  
		  // draw directed edges with proper padding from node centers
		  path.attr('d', function(d) {
		      var sourceX,sourceY,targetX,targetY;	  
			  if(d.fixed){
				  sourceX = d.source.x, sourceY = d.source.y;
				  targetX = d.target.x, targetY = d.target.y;
			  }else{
				    var deltaX = d.target.x - d.source.x,
			        deltaY = d.target.y - d.source.y,
			        dist = Math.sqrt(deltaX * deltaX + deltaY * deltaY),
			        normX = deltaX / dist,
			        normY = deltaY / dist,
			        sourcePadding = d.left ? 17 : 12,
			        targetPadding = d.right ? 17 : 12;
			        sourceX = d.source.x + (sourcePadding * normX);
			        sourceY = d.source.y + (sourcePadding * normY);
			        targetX = d.target.x - (targetPadding * normX);
			        targetY = d.target.y - (targetPadding * normY);
			    //calcule les coordonnées par rapport au rectangle
			    if(deltaX > 0){
			        sourceX = d.source.x + (d.source.bb.width/2);
			        targetX = d.target.x - (d.target.bb.width/2);    	    	
			    }else{
			        sourceX = d.source.x - (d.source.bb.width/2);
			        targetX = d.target.x + (d.target.bb.width/2);    	    	
			    }       
			    if(deltaY > 0){
			        sourceY = d.source.y + (d.source.bb.height/2);
			        targetY = d.target.y - (d.target.bb.height/2);    	
			    }else{
			        sourceY = d.source.y - (d.source.bb.height/2);
			        targetY = d.target.y + (d.target.bb.height/2);    	    	
			    }		  
			  }
			  return 'M' + sourceX + ',' + sourceY + 'L' + targetX + ',' + targetY;
		  });

			  //Push different nodes in different directions for clustering.
			  node.attr('transform', function(d) {
				  //if(!d.fixed)d.y = self.clusters(d.type);
				  return 'translate(' + d.x + ',' + d.y + ')';
			  });
		}		
		
		// update graph (called when needed)
		function draw() {
		  // path (link) group
		  path = path.data(self.links);

		  // update existing links
		  path.classed('selected', function(d) { return d === selected_link; })
		    .style('marker-start', function(d) { return d.left ? 'url(#start-arrow)' : ''; })
		    .style('marker-end', function(d) { return d.right ? 'url(#end-arrow)' : ''; });


		  // add new links
		  path.enter().append('svg:path')
		    .attr('class', 'link')
		    .attr('id', function(d) { return d.id; })    
		    .classed('selected', function(d) { return d === selected_link; })
		    .style('marker-start', function(d) { return d.left ? 'url(#start-arrow)' : ''; })
		    .style('marker-end', function(d) { return d.right ? 'url(#end-arrow)' : ''; })
		    .on('mousedown', function(d) {
		    		mousedownPath(d);
		    	})
		    .on("click",function(d){
		    		showSpatioTempo(d);
		     });

		  // remove old links
		  path.exit().remove();


		  // circle (node) group
		  // NB: the function arg is crucial here! nodes are known by id, not by index!
		  node = node.data(nodes, function(d) { return d.id; });

		  // update existing nodes (reflexive & selected visual states)
		  node.selectAll('node')
		    .classed('reflexive', function(d) { return d.reflexive; });

		  // add new nodes
		  var g = node.enter().append('svg:g')
		  	.attr('class', 'node')
		    .attr("id", function(d){
		  		return "g"+d.id;
		  		})
		  	.call(node_drag);
		  var r = 12;
		  g.append('svg:circle')
		    .attr('r', r)
		    .style('fill', function(d) { 
		    		return (d === selected_node) ? d3.rgb(self.colors(d.type)).brighter().toString() : self.colors(d.type); 
		    		})
		    .style('stroke', function(d) { return d3.rgb(self.colors(d.type)).darker().toString(); })
		    .classed('reflexive', function(d) { return d.reflexive; })
		    .on('mouseover', function(d) {
		      if(!mousedown_node || d === mousedown_node) return;
		      // enlarge target node
		      d3.select(this).attr('transform', 'scale(1.1)');
		    })
		    .on('mouseout', function(d) {
		      if(!mousedown_node || d === mousedown_node) return;
		      // unenlarge target node
		      d3.select(this).attr('transform', '');
		    })
		    .on('mousedown', function(d) {
		    		mousedownNode(d);
		    	})
		    .on('mouseup', function(d) {
		    		mouseupNode(this, d);
		    });

		  //ajoute le texte du noeud
		  var txt = g.append('svg:text')
		      .attr('x', 0)
		      .attr('y', r/2)
		      .attr('id', function(d) { return "txt"+d.id; })
		      .attr('class', 'id')
		      .text(function(d) { return d.desc; });
		  //ajoute le cadre autour du noeud
		  g.append('svg:rect')
		  	.attr('x', function(d) {
				var bb = d3.select("#txt"+d.id).node().getBBox();
				d.bb = bb;
				d.bb.x = d.bb.x-r;
				return d.bb.x; 
				})
			.attr('y', function(d) { 
				d.bb.y = d.bb.y-r;
				return d.bb.y;
				})
			.attr('width', function(d) { 
				d.bb.width = d.bb.width+r*2;
				return d.bb.width;
				})
			.attr('height', function(d) { 
				d.bb.height = d.bb.height+r*2;
				return d.bb.height;
				})
		    .style('stroke', function(d) { 
		    		return (d === selected_node) ? d3.rgb(colors(d.type)).brighter().toString() : colors(d.type); 
		    		})
		    	.style("fill","none");
		  //ajoute le bouton pour le drag & drop
		  g.append('svg:image')
		  	.attr('x', function(d){
		  		return d.bb.x;
		  	})
			.attr('y', function(d){
		  		return d.bb.y;
		  	})
			.attr('width', r)
			.attr('height', r)
			.style('cursor','move')
			.attr('xlink:href',"../img/expand.png")
			.on('mouseover', function(d) {
		    		ajout=false;
		    	})
			.on('mouseleave', function(d) {
		    		ajout=true;
		    	})
			.on('mousedown', function(d) {
		    		drag=true;
		    	})
		    .on('mouseup', function(d) {
		    		drag=false;
		    });	
		  //ajoute le bouton pour les infos
		  g.append('svg:image')
		  	.attr('x', function(d){
		  		return d.bb.x+d.bb.width-r;
		  	})
			.attr('y', function(d){
		  		return d.bb.y;
		  	})
			.attr('width', r)
			.attr('height', r)
			.style('cursor','context-menu')
			.attr('xlink:href',"../img/document107.png")
			.on('mouseover', function(d) {
		    		ajout=false;
		    	})
			.on('mouseleave', function(d) {
		    		ajout=true;
		    	})
		    .on("click",function(d){
		    		showInfos(d);
		    });
	
		  
		  // remove old nodes
		  node.exit().remove();

		  // set the graph in motion
		  force.start();
		}
		function createNode() {
			// prevent I-bar on drag
			var point = d3.mouse(this);
			if(!ajout || d3.event.ctrlKey || mousedown_node || mousedown_link) return;
			
			self.dialogues.noeud.showModal();
			
			document.querySelector('#addNode').onclick = function() {
				 // because :active only works in WebKit?
				 svg.classed('active', true);
				 // Get dialog
				 var dt;
				 self.lastNodeId ++;
				 var type = document.querySelector('#typeNoeud').value;
				 selectItem=document.querySelector('#autocomplete').value;
				 self.selectNodeId = self.lastNodeId;
				 if(type=="Acteurs"){
					 showDialogActeur(selectItem);
				 }
				 if(type=="Lieux"){
					 showDialogLieu(selectItem);
				 }
				 var  node = {id: self.lastNodeId, reflexive: false, desc : selectItem, type : type, dt:dt};
				 node.x = point[0];
				 node.y = point[1];
				 nodes.push(node);
				 // Close dialog
				 self.dialogues.noeud.close();
				 //calcule la représentation
				 draw();
				 //réinitialise l'input
				 document.querySelector('#autocomplete').value = "";
			}
			
		}
		function createLien(l, g){

			//vérifie le type de lien
			if(l.source.type=="Acteurs" && l.target.type=="Acteurs"){
				setTypeLien("Rapports Acteur → Acteur");
				showSpatioTempo(l);
				document.querySelector('#addEvent').onclick = function() {
					 //ajoute une précision spatio-temporelle au noeud
					if(!l.spatiotempo)l.spatiotempo=[];
					l.spatiotempo.push({
						 'debut':document.querySelector('#dtDeb').value
						 ,'fin':document.querySelector('#dtFin').value
						 ,'lieu':document.querySelector('#dtLieu').value
						 ,'pays':document.querySelector('#dtPays').value
						 ,'ville':document.querySelector('#dtVille').value
						 ,'adresse':document.querySelector('#dtAdresse').value
						 ,'rapport':document.querySelector('#dtRapport').value
					 });
					$( "#events-ajout tbody" ).append( "<tr id='eventST_"+l.spatiotempo.length+"'>" +
							"<td>" + $("#dtDeb").val() + "</td>" +
							"<td>" + $("#dtFin").val() + "</td>" +
							"<td>" + $("#dtLieu").val() + "</td>" +
							"<td>" + $("#dtPays").val() + "</td>" +
							"<td>" + $("#dtVille").val() + "</td>" +
							"<td>" + $("#dtAdresse").val() + "</td>" +
							"<td>" + $("#dtRapport").val() + "</td>" +
							"<td><span onclick='suppSpatioTempo("+(l.spatiotempo.length)+")' class='ui-icon ui-icon-trash'></span></td>" +					
						"</tr>" );			 
				};				
				
			}
			if(l.source.type=="Acteurs" && l.target.type=="Lieux"){
				setTypeLien("Rapports Acteur → Lieu");
				showSpatioTempo(l);
				document.querySelector('#addEvent').onclick = function() {
					 //ajoute une précision spatio-temporelle au noeud
					if(!l.spatiotempo)l.spatiotempo=[];
					l.spatiotempo.push({
						 'debut':document.querySelector('#dtDeb').value
						 ,'fin':document.querySelector('#dtFin').value
						 ,'lieu':document.querySelector('#dtLieu').value
						 ,'pays':document.querySelector('#dtPays').value
						 ,'ville':document.querySelector('#dtVille').value
						 ,'adresse':document.querySelector('#dtAdresse').value
						 ,'rapport':document.querySelector('#dtRapport').value
					 });
					$( "#events-ajout tbody" ).append( "<tr id='eventST_"+l.spatiotempo.length+"'>" +
							"<td>" + $("#dtDeb").val() + "</td>" +
							"<td>" + $("#dtFin").val() + "</td>" +
							"<td>" + $("#dtLieu").val() + "</td>" +
							"<td>" + $("#dtPays").val() + "</td>" +
							"<td>" + $("#dtVille").val() + "</td>" +
							"<td>" + $("#dtAdresse").val() + "</td>" +
							"<td>" + $("#dtRapport").val() + "</td>" +
							"<td><span onclick='suppSpatioTempo("+(l.spatiotempo.length)+")' class='ui-icon ui-icon-trash'></span></td>" +					
						"</tr>" );			 
				};				
				
			}			
			/*
			document.querySelector('#addLien').onclick = function() {
				 // because :active only works in WebKit?
				 svg.classed('active', true);
				 // Get dialog
				 var type = document.querySelector('#typeLien').value;

				 //ajoute le texte au lien
				// Add a text label.
				 var text = svg.append("text")
				     .attr("x", 5)
				     .attr("dy", 15);

				 text.append("textPath")
				     .attr("stroke","black")
				     .attr("xlink:href","#"+l.id)
				     .text(type);
				 
				 self.dialogues.lien.close();
				 draw();

				};			
			*/
		}
		
		//DEB objet event
		function mousedownPath(d){
			if(d3.event.ctrlKey) return;
		    // select link
		    mousedown_link = d;
		    if(mousedown_link === selected_link) selected_link = null;
		    else selected_link = mousedown_link;
		    selected_node = null;
		    draw();			
		}
		function mousedownNode(d){
		      if(d3.event.ctrlKey) return;

		      // select node
		      mousedown_node = d;
		      if(mousedown_node === selected_node) selected_node = null;
		      else selected_node = mousedown_node;
		      selected_link = null;
		 
		      // reposition drag line
		      drag_line
		        .style('marker-end', 'url(#end-arrow)')
		        .classed('hidden', false)
		        .attr('d', 'M' + mousedown_node.x + ',' + mousedown_node.y + 'L' + mousedown_node.x + ',' + mousedown_node.y);

		      draw();			
		}
		function mouseupNode(t, d){
		      if(!mousedown_node) return;

		      // needed by FF
		      drag_line
		        .classed('hidden', true)
		        .style('marker-end', '');

		      // check for drag-to-self
		      mouseup_node = d;
		      if(mouseup_node === mousedown_node) { resetMouseVars(); return; }

		      // unenlarge target node
		      d3.select(t).attr('transform', '');

		      // add link to graph (update if exists)
		      // NB: links are strictly source < target; arrows separately specified by booleans
		      var source, target, direction;
		      //samszo l'ordre du clic est important premier = source, deuxième = target
		      source = mousedown_node;
		      target = mouseup_node;
		      direction = 'right';
		      var link;
		      link = self.links.filter(function(l) {
		        return (l.source === source && l.target === target);
		      })[0];

		      if(link) {
		        link[direction] = true;
		      } else {
		        link = {source: source, target: target, left: false, right: false, id:source.id+"_"+target.id};
		        link[direction] = true;
		        self.links.push(link);
		      }

		      // select new link
		      selected_link = link;
		      selected_node = null;
		      
		      //choix du type de lien
		      createLien(link);
		      
		      draw();			
		}
		
		function showInfos(d){
	    		if(d.type=="Acteurs"){
	    			initFormAuteur();
	    			if(d.dt){
	    				setSelectAuteur(d.dt)
	        			document.querySelector('#addActeur').innerHTML = "Modifier";	
	    			}else{
	        			document.querySelector('#nomActeur').value = d.desc;		
	        			document.querySelector('#addActeur').innerHTML = "Modifier";	
	    			}
	    			
	    			self.dialogues.acteur.showModal();
	    		}			
		}
		//FIn objet event
		
		//DEB mouse event
		function resetMouseVars() {
		  mousedown_node = null;
		  mouseup_node = null;
		  mousedown_link = null;
		}
		
		function dragstart(d, i) {
			if(drag){
				force.stop() // stops the force auto positioning before you start dragging
			}
		}

		function dragmove(d, i) {
			if(drag){
				d.px += d3.event.dx;
				d.py += d3.event.dy;
				d.x += d3.event.dx;
				d.y += d3.event.dy; 
				tick(); // this is the key to make it work together with updating both px,py,x,y on d !
			}
		}

		function dragend(d, i) {
			if(drag){
				d.fixed = true; // of course set the node to fixed so the force doesn't include the node in its auto positioning stuff
				tick();
				force.resume();
			}
		}
		
		function mousemove() {
			  if(!mousedown_node) return;

			  // update drag line
			  drag_line.attr('d', 'M' + mousedown_node.x + ',' + mousedown_node.y + 'L' + d3.mouse(this)[0] + ',' + d3.mouse(this)[1]);

			  draw();
		}

		function mouseup() {
			if(mousedown_node) {
				// hide drag line
				drag_line
				.classed('hidden', true)
				.style('marker-end', '');
			}

			// because :active only works in WebKit?
			svg.classed('active', false);

			// clear mouse event vars
			resetMouseVars();
		}		
		//FIN mouse event

		//DEBUT gestion clavier
		function keydown() {
		  d3.event.preventDefault();

		  if(lastKeyDown !== -1) return;
		  lastKeyDown = d3.event.keyCode;

		  // ctrl
		  if(d3.event.keyCode === 17) {
		    circle.call(force.drag);
		    svg.classed('ctrl', true);
		  }

		  if(!selected_node && !selected_link) return;
		  switch(d3.event.keyCode) {
		    case 8: // backspace
		    case 46: // delete
		      if(selected_node) {
		        nodes.splice(nodes.indexOf(selected_node), 1);
		        spliceLinksForNode(selected_node);
		      } else if(selected_link) {
		        links.splice(links.indexOf(selected_link), 1);
		      }
		      selected_link = null;
		      selected_node = null;
		      draw();
		      break;
		    case 66: // B
		      if(selected_link) {
		        // set link direction to both left and right
		        selected_link.left = true;
		        selected_link.right = true;
		      }
		      draw();
		      break;
		    case 76: // L
		      if(selected_link) {
		        // set link direction to left only
		        selected_link.left = true;
		        selected_link.right = false;
		      }
		      draw();
		      break;
		    case 82: // R
		      if(selected_node) {
		        // toggle node reflexivity
		        selected_node.reflexive = !selected_node.reflexive;
		      } else if(selected_link) {
		        // set link direction to right only
		        selected_link.left = false;
		        selected_link.right = true;
		      }
		      draw();
		      break;
		  }
		}

		function keyup() {
		  lastKeyDown = -1;

		  // ctrl
		  if(d3.event.keyCode === 17) {
		    node
		      .on('mousedown.drag', null)
		      .on('touchstart.drag', null);
		    svg.classed('ctrl', false);
		  }
		}		
		//FIN gestion cl	avier
						
  };
  
  this.params = function() {			
		return {"deb":this.deb, "fin":this.fin, "id":this.id};
	  };
	  
  return this.rs();
}