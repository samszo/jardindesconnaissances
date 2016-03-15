function reseau(config) {
	this.idCont = config.idCont;  
	this.w = config.w;  
	this.h = config.h;
	this.charge = -500;
	this.lkDist = 400;
	this.nodes = config.nodes;
	this.links = config.links;
	this.posis = config.posis;
	this.colors = config.colors;
	this.clusters = config.clusters;
	this.dialogues = config.dialogues;
	this.lastNodeId = 0;
	this.selectNodeId;
	this.svg;
	this.force;
	var point, path, selected_link= null,node,node_drag,force,selected_node = null;
	var drag_line, drag, ajout=true;
	var mousedown_link = null,
	    mousedown_node = null,
	    mouseup_node = null;
	// only respond once per keydown
	var lastKeyDown = -1;
	var self;

	this.rs = function() {

		self=this;
		self.lastNodeId = self.nodes.length-1;

		node_drag = d3.behavior.drag()
			.on("dragstart", dragstart)
			.on("drag", dragmove)
			.on("dragend", self.dragend);
		
		//supprime le svg		
		d3.select('#svgResauGlobal').remove();
		self.svg = d3.select('#'+self.idCont)
			.append('svg')
			.attr('id',"svgResauGlobal")
			.attr('width', self.w)
			.attr('height', self.h);
		self.force = d3.layout.force()
		    .nodes(self.nodes)
		    .links(self.links)
		    .size([self.w, self.h])
		    .linkDistance(self.lkDist)
		    .charge(self.charge)
		    .on('tick', self.tick);
		//self.force.stop();
		    
		    
		// define arrow markers for graph links
		self.svg.append('svg:defs').append('svg:marker')
		    .attr('id', 'end-arrow')
		    .attr('viewBox', '0 -5 10 10')
		    .attr('refX', 6)
		    .attr('markerWidth', 3)
		    .attr('markerHeight', 3)
		    .attr('orient', 'auto')
		  .append('svg:path')
		    .attr('d', 'M0,-5L10,0L0,5')
		    .attr('fill', '#000');
		
		self.svg.append('svg:defs').append('svg:marker')
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
		drag_line = self.svg.append('svg:path')
		  .attr('class', 'link dragline hidden')
		  .attr('d', 'M0,0L0,0');
		// handles to link and node element groups
		path = self.svg.append('svg:g').selectAll('path'),
		node = self.svg.append('svg:g').selectAll('g');
		
		self.svg.on('mousedown', ajoutNode)
		  .on('mousemove', mousemove)
		  .on('mouseup',function(d) {
			  		mouseup();
			    	});
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
			self.draw();
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
		self.draw();	
		
		//positionne l'historique
		if(self.posis.n && self.posis.n.length)self.setSavePosi();
		

		function ajoutNode() {
			// prevent I-bar on drag
			point = d3.mouse(this);
			if(!ajout || d3.event.ctrlKey || mousedown_node || mousedown_link) return;
			
			$('#popupAjoutNoeud').w2popup();
			openPopupAjoutActeur();

		}
		
		//DEB mouse event		
		function dragstart(d, i) {
			if(drag){
				path.attr('d', function(p) {
				      p.fixed=false
				})
				self.force.stop() // stops the force auto positioning before you start dragging
			}
		}

		function dragmove(d, i) {
			if(self.drag){
				d.px += d3.event.dx;
				d.py += d3.event.dy;
				d.x += d3.event.dx;
				d.y += d3.event.dy; 
				self.tick(); // this is the key to make it work together with updating both px,py,x,y on d !
			}
		}
		
		function mousemove() {
			  if(!mousedown_node) return;

			  // update drag line
			  drag_line.attr('d', 'M' + mousedown_node.x + ',' + mousedown_node.y + 'L' + d3.mouse(this)[0] + ',' + d3.mouse(this)[1]);

			  self.draw();
		}

		function mouseup() {
			if(mousedown_node) {
				// hide drag line
				drag_line
				.classed('hidden', true)
				.style('marker-end', '');
			}

			// because :active only works in WebKit?
			self.svg.classed('active', false);

			// clear mouse event vars
			self.resetMouseVars();
		}		
		//FIN mouse event

		//DEBUT gestion clavier
		function keydown() {
		  d3.event.preventDefault();

		  if(lastKeyDown !== -1) return;
		  lastKeyDown = d3.event.keyCode;

		  // ctrl
		  if(d3.event.keyCode === 17) {
		    circle.call(self.force.drag);
		    self.svg.classed('ctrl', true);
		  }

		  if(!selected_node && !selected_link) return;
		  switch(d3.event.keyCode) {
		    case 8: // backspace
		    case 46: // delete
		      if(selected_node) {
		        nodes.splice(nodes.indexOf(selected_node), 1);
		        spliceLinksForNode(selected_node);
		      } else if(selected_link) {
		        self.links.splice(self.links.indexOf(selected_link), 1);
		      }
		      selected_link = null;
		      selected_node = null;
		      self.draw();
		      break;
		    case 66: // B
		      if(selected_link) {
		        // set link direction to both left and right
		        selected_link.left = true;
		        selected_link.right = true;
		      }
		      self.draw();
		      break;
		    case 76: // L
		      if(selected_link) {
		        // set link direction to left only
		        selected_link.left = true;
		        selected_link.right = false;
		      }
		      self.draw();
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
		      self.draw();
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
		    self.svg.classed('ctrl', false);
		  }
		}		
		//FIN gestion cl	avier
						
  };
  
  this.showInfos=function (d){
		if(d.type=="Acteur"){
			initFormAuteur();
			if(d.dt){
				setSelectAuteur(d.dt)
    			document.querySelector('#addActeur').innerHTML = "Modifier";	
			}else{
    			document.querySelector('#nomActeur').value = d.desc;		
    			document.querySelector('#addActeur').innerHTML = "Modifier";	
			}
			
			this.dialogues.acteur.showModal();
		}			
  }

  
  this.resetMouseVars = function () {
	  mousedown_node = null;
	  mouseup_node = null;
	  mousedown_link = null;
	}

  
  this.creaNode = function(type,desc,dt) { 
		// prevent I-bar on drag
				
		 // because :active only works in WebKit?
		 this.svg.classed('active', true);
		 // Get dialog
		 this.lastNodeId ++;
		 this.selectNodeId = this.lastNodeId;
		 var  node = {id: this.lastNodeId, reflexive: false, desc : desc, type : type, dt:dt};
		 node.x = point[0];
		 node.y = point[1];
		 nodes.push(node);
		 this.draw();
	} 
  
	// update graph (called when needed)
  this.draw = function() {
	  // path (link) group
	  path = path.data(this.links);

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
	    .style('stroke-width', function(d) { 
	    		return d.spatiotempo ? (d.spatiotempo.length+1)*3 : 3; 
	    		})
	    .on('mousedown', function(d) {
	    		self.mousedownPath(d);
	    	})
	    .on('mouseover', function(d) {
	    		if(d.spatiotempo.length){
	    			openOverlaySpatioTempo(d);
	    		}	    	
	    })	    	
	    .on('mouseout', function(d) {
	    		w2popup.close();
	    })
	    .on("click",function(d){
	    		//showSpatioTempo(d);
			openPopupSpatioTempo(d);
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
	  //pour éviter un conflit dans les événements
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
	      d3.select(this).attr('transform', 'scale(3)');
	    })
	    .on('mouseout', function(d) {
	      if(!mousedown_node || d === mousedown_node) return;
	      // unenlarge target node
	      d3.select(this).attr('transform', '');
	    })
	    .on('mousedown', function(d) {
	    		self.mousedownNode(d);
	    	})
	    .on('mouseup', function(d) {
	    		self.mouseupNode(this, d);
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
		.attr('xlink:href',prefUrl+"img/expand.png")
		.on('mouseover', function(d) {
	    		ajout=false;
	    	})
		.on('mouseleave', function(d) {
	    		ajout=true;
	    	})
		.on('mousedown', function(d) {
	    		self.drag=true;
	    	})
	    .on('mouseup', function(d) {
	    		self.drag=false;
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
		.attr('xlink:href',prefUrl+"img/document107.png")
		.on('mouseover', function(d) {
	    		ajout=false;
	    	})
		.on('mouseleave', function(d) {
	    		ajout=true;
	    	})
	    .on("click",function(d){
	    		self.showInfos(d);
	    });

	  
	  // remove old nodes
	  node.exit().remove();

	  // set the graph in motion
	  this.force.start();
	}  
  
	//DEB objet event
	this.mousedownPath = function(d){
		if(d3.event.ctrlKey) return;
	    // select link
	    mousedown_link = d;
	    if(mousedown_link === selected_link) selected_link = null;
	    else selected_link = mousedown_link;
	    selected_node = null;
	    this.draw();			
	}		  
  
	this.mousedownNode = function (d){
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
	
	      this.draw();			
	}    
	this.mouseupNode = function(t, d){
	      if(!mousedown_node) return;

	      // needed by FF
	      drag_line
	        .classed('hidden', true)
	        .style('marker-end', '');

	      // check for drag-to-self
	      mouseup_node = d;
	      if(mouseup_node === mousedown_node) { this.resetMouseVars(); return; }

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
	      link = this.links.filter(function(l) {
	        return (l.source === source && l.target === target);
	      })[0];

	      if(link) {
	        link[direction] = true;
	      } else {
	        link = {source: source, target: target, left: false, right: false, id:source.id+"_"+target.id};
	        link[direction] = true;
	        this.links.push(link);
	      }

	      // select new link
	      selected_link = link;
	      selected_node = null;
	      
	      //choix du type de lien
	      this.createLien(link);
	      
	      this.draw();			
	}
	//FIn objet event
	
	//DEB Drag event
	this.dragend = function(d, i) {
		if(drag){
			d.fixed = true; // of course set the node to fixed so the force doesn't include the node in its auto positioning stuff
			self.tick();
			self.force.resume();
		}
	}
	
	// update force layout (called automatically each iteration)
	this.tick = function (e) {
		  
	  // draw directed edges with proper padding from node centers
	  path.attr('d', function(d) {
	      var sourceX,sourceY,targetX,targetY;	  
		  if(d.fixed){
			  sourceX = d.source.x, sourceY = d.source.y;
			  targetX = d.target.x, targetY = d.target.y;
		  }else{
			var arrPosis = self.getPath(d);  
			sourceX =arrPosis[0], sourceY =arrPosis[1], targetX =arrPosis[2], targetY =arrPosis[3];
			//			  
		  }
		  return 'M' + sourceX + ',' + sourceY + 'L' + targetX + ',' + targetY;			  
		  
	  });

	  //Push different nodes in different directions for clustering.
	  node.attr('transform', function(d) {
		  //if(!d.fixed)d.y = self.clusters(d.type);
		  return 'translate(' + d.x + ',' + d.y + ')';
	  });
	}		
	
	//FIN Drag event

	//charge les anciennes positions
	this.setSavePosi = function (){
		
		
		drag=true;
		//charge les positions enregistrée
		node[0].forEach(function(d){
			var dt = d.__data__;			
			if (this.posis.n[dt.id]) {
		         oldX = this.posis.n[dt.id][0];
		         oldY = this.posis.n[dt.id][1];
			} else {
		         // If no previous coordinate... Start from off screen for a fun zoom-in effect .
		         oldX = -100;
		         oldY = -100;
			}
	       d.x = oldX;
	       d.y = oldY;
	       d.px = oldX;
	       d.py = oldY;
	       //dt.fixed = true;
			//récréer la relation entre les noeuds et leurs source et target pour rendre opérant le drag and drop
			path[0].forEach(function(l){
				var dtL = l.__data__;			
				//met à jour la source
				if (dtL.source.id==dt.id)dtL.source = dt;
				//met à jour la target
				if (dtL.target.id==dt.id)dtL.target = dt;
	  		});
	       
  		});

		this.dragend(node[0][0].__data__);    						
		//drag=false;
				
	}
	
	this.getPath = function(d){
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
	    return [sourceX,sourceY,targetX,targetY];
		
	} 
	
	//


	this.createLien = function(l, g){
		openPopupSpatioTempo(l);
	}	
	this.params = function() {			
		return {"deb":this.deb, "fin":this.fin, "id":this.id};
	  };
	  
  return this.rs();
}