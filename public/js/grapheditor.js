// set up SVG for D3
var width  = 1280,
    height = 720,
    colors =  d3.scale.ordinal()
    		.domain(["REFERENCES","EVENEMENTS","ACTEURS","CONCEPTS","LIENS_ACTEURS_ACTEURS","LIENS_ACTEURS_CONCEPTS"])
		.range(["pink","green","red","yellow","rose","blue","gray"]);
	clusters =  d3.scale.ordinal()
		.domain(["REFERENCES","EVENEMENTS","ACTEURS","CONCEPTS"])
		.range([100,200,300,500]);

var datas, dataCat, dataPays, dataLieux=[];
var selectItem, selectPays, selectSpecia, selectProf;

function showMessage(mess) {
	document.getElementById('mess').innerHTML = mess; 			
	diagMess.showModal();
}

d3.csv("../../data/biolographes/notionsBiolographes.csv", function(error, data) {
	datas={"REFERENCES":[],"EVENEMENTS":[],"ACTEURS":[],"LIENS_ACTEURS_ACTEURS":[],"LIENS_ACTEURS_CONCEPTS":[],"LIEUX":[],"CONCEPTS":[]};
	data.forEach(function(d, i) {
		if(d.REFERENCES)datas["REFERENCES"].push({"id":i,"value":d.REFERENCES});
		if(d.EVENEMENTS)datas["EVENEMENTS"].push({"id":i,"value":d.EVENEMENTS});
		if(d.ACTEURS)datas["ACTEURS"].push({"id":i,"value":d.ACTEURS});
		if(d.LIENS_ACTEURS_ACTEURS)datas["LIENS_ACTEURS_ACTEURS"].push({"id":i,"value":d.LIENS_ACTEURS_ACTEURS});
		if(d.LIENS_ACTEURS_CONCEPTS)datas["LIENS_ACTEURS_CONCEPTS"].push({"id":i,"value":d.LIENS_ACTEURS_CONCEPTS});
		if(d.LIEUX)datas["LIEUX"].push({"id":i,"value":d.LIEUX});
		if(d.CONCEPTS)datas["CONCEPTS"].push({"id":i,"value":d.CONCEPTS});
	});
	
	//nodes = nodes.splice(0, 10);
	setAutocomplete("ACTEURS");
	
});
d3.csv("../../data/country.csv", function(error, data) {
	dataPays = data;
	//ajoute la propriété value pour l'autocomplete
	dataPays.forEach(function(d){
		d.value = d.name;
	});
	//		
	$("#dtPays").autocomplete({
		minLength: 0,
		source: dataPays,
		focus: function( event, ui ) {
			$( "#dtPays" ).val( ui.item.label );
			return false;
		},
		select: function( event, ui ) {
	        selectPays = ui.item.value;
			return false;
		}
	})
	.autocomplete("option", "appendTo", "#dlgEvent")
	.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
			.append( "<a>" + item.label + " (" + item.iso + ")</a>" )
			.appendTo( ul );
	};		
		//
});

d3.csv("../../data/biolographes/CategorisationRapports.csv", function(error, data) {
	dataCat={"ACTEURS":{}
		,"REFERENCES":{}
		,"LIEUX":{}
		,"RAPPORTS":{}
		,"CONCEPTS":{}
		};
	data.forEach(function(d, i) {
		if(d["Professions"])dataCat["ACTEURS"]["Professions"] = d["Professions"].split(",");
		if(d["Spécialités scientifiques"])dataCat["ACTEURS"]["Spécialité"] = d["Spécialités scientifiques"].split(",");
		
		if(d["Académies"])dataCat["LIEUX"]["Académies"] = d["Académies"].split(",");
		if(d["Universités françaises"])dataCat["LIEUX"]["Universités françaises"] = d["Universités françaises"].split(",");
		if(d["Sociétés savantes françaises"])dataCat["LIEUX"]["Sociétés savantes françaises"] = d["Sociétés savantes françaises"].split(",");
		if(d["Universités allemandes"])dataCat["LIEUX"]["Universités allemandes"] = d["Universités allemandes"].split(",");
		if(d["Sociétés savantes allemandes"])dataCat["LIEUX"]["Sociétés savantes allemandes"] = d["Sociétés savantes allemandes"].split(",");
		if(d["Espaces de sociabilité"])dataCat["LIEUX"]["Espaces de sociabilité"] = d["Espaces de sociabilité"].split(",");
		if(d["Autres lieux de savoirs"])dataCat["LIEUX"]["Autres lieux de savoirs"] = d["Autres lieux de savoirs"].split(",");
		if(d["Villégiatures"])dataCat["LIEUX"]["Villégiatures"] = d["Villégiatures"].split(",");		
		
		if(d["Rapports Acteur → Acteur"])dataCat["RAPPORTS"]["acteur_acteur"] = d["Rapports Acteur → Acteur"].split(",");
		if(d["Rapports Acteur → Lieu"])dataCat["RAPPORTS"]["acteur_lieu"] = d["Rapports Acteur → Lieu"].split(",");
		if(d["Rapport Acteur → Notions"])dataCat["RAPPORTS"]["acteur_concept"] = d["Rapport Acteur → Notions"].split(",");
		if(d["Rapport Notions → Acteur"])dataCat["RAPPORTS"]["concept_acteur"] = d["Rapport Notions → Acteur"].split(",");
		if(d["Notions de biologie"])dataCat["CONCEPTS"]["biologie"] = d["Notions de biologie"].split(",");
		if(d["Notions de Science de la vie"])dataCat["CONCEPTS"]["science de la vie"] = d["Notions de Science de la vie"].split(",");
		if(d["Notions de Science de la Terre"])dataCat["CONCEPTS"]["science de la Terre"] = d["Notions de Science de la Terre"].split(",");
		if(d["Notions d'Anatomie"])dataCat["CONCEPTS"]["anatomie"] = d["Notions d'Anatomie"].split(",");

		if(d["Références"])dataCat["REFERENCES"] = d["Références"].split(",");
				
	});
	//construction des tableaux pour l'autocomplétion
	setAutocompleteLieu("Académies");
	setAutocompleteActeur();
	/*
	var name;
    for (name in dataCat["LIEUX"]) {
    		dataCat["LIEUX"][name].forEach(function(d){
        		dataLieux.push({label:d, category: name});    			
    		})
    }	
	*/

});


// set up initial nodes and links
//  - nodes are known by 'id', not by index in array.
//  - reflexive edges are indicated on the node (as a bold black circle).
//  - links are always source < target; edge directions are set by 'left' and 'right'.
/*
var nodes = [
    {id: 0, reflexive: false},
    {id: 1, reflexive: true },
    {id: 2, reflexive: false}
  ],
  lastNodeId = 2,
  links = [
    {source: nodes[0], target: nodes[1], left: false, right: true },
    {source: nodes[1], target: nodes[2], left: false, right: true }
  ];
*/
var nodes = [],
lastNodeId = -1,
links = [],
svg, force,	drag_line, path, circle;

//mouse event vars
var selected_node = null,
    selected_link = null,
    mousedown_link = null,
    mousedown_node = null,
    mouseup_node = null;

// init D3 force layout
function initForceLayout(){
	svg = d3.select('#viz')
		.append('svg')
		.attr('width', width)
		.attr('height', height);
	force = d3.layout.force()
	    .nodes(nodes)
	    .links(links)
	    .size([width, height])
	    .linkDistance(400)
	    .charge(-500)
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
	circle = svg.append('svg:g').selectAll('g');
	
	svg.on('mousedown', createNode)
	  .on('mousemove', mousemove)
	  .on('mouseup', mouseup);
	d3.select("#viz")
	  .on('keydown', keydown)
	  .on('keyup', keyup);
	restart();	
}

function resetMouseVars() {
  mousedown_node = null;
  mouseup_node = null;
  mousedown_link = null;
}

var node_drag = d3.behavior.drag()
	.on("dragstart", dragstart)
	.on("drag", dragmove)
	.on("dragend", dragend);

function dragstart(d, i) {
	force.stop() // stops the force auto positioning before you start dragging
}

function dragmove(d, i) {
	d.px += d3.event.dx;
	d.py += d3.event.dy;
	d.x += d3.event.dx;
	d.y += d3.event.dy; 
	tick(); // this is the key to make it work together with updating both px,py,x,y on d !
}

function dragend(d, i) {
	d.fixed = true; // of course set the node to fixed so the force doesn't include the node in its auto positioning stuff
	tick();
	force.resume();
}


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
	  circle.attr('transform', function(d) {
		  if(!d.fixed)d.y = clusters(d.type);
		  return 'translate(' + d.x + ',' + d.y + ')';
	  });
}

// update graph (called when needed)
function restart() {
  // path (link) group
  path = path.data(links);

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
      if(d3.event.ctrlKey) return;
      // select link
      mousedown_link = d;
      if(mousedown_link === selected_link) selected_link = null;
      else selected_link = mousedown_link;
      selected_node = null;
      restart();
    	})
    .on("click",function(d){
    		showSpatioTempo(d);
     });

  // remove old links
  path.exit().remove();


  // circle (node) group
  // NB: the function arg is crucial here! nodes are known by id, not by index!
  circle = circle.data(nodes, function(d) { return d.id; });

  // update existing nodes (reflexive & selected visual states)
  circle.selectAll('node')
    .classed('reflexive', function(d) { return d.reflexive; });

  // add new nodes
  var g = circle.enter().append('svg:g')
  	.attr('class', 'node')
    .attr("id", function(d){
  		return "g"+d.id;
  		})
  	.call(node_drag);
  var r = 12;
  g.append('svg:circle')
    .attr('r', r)
    .style('fill', function(d) { 
    		return (d === selected_node) ? d3.rgb(colors(d.type)).brighter().toString() : colors(d.type); 
    		})
    .style('stroke', function(d) { return d3.rgb(colors(d.type)).darker().toString(); })
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

      restart();
    })
    .on('mouseup', function(d) {
      if(!mousedown_node) return;

      // needed by FF
      drag_line
        .classed('hidden', true)
        .style('marker-end', '');

      // check for drag-to-self
      mouseup_node = d;
      if(mouseup_node === mousedown_node) { resetMouseVars(); return; }

      // unenlarge target node
      d3.select(this).attr('transform', '');

      // add link to graph (update if exists)
      // NB: links are strictly source < target; arrows separately specified by booleans
      var source, target, direction;
      //samszo l'ordre du clic est important premier = source, deuxième = target
      source = mousedown_node;
      target = mouseup_node;
      direction = 'right';
      /*
      if(mousedown_node.id < mouseup_node.id) {
        source = mousedown_node;
        target = mouseup_node;
        direction = 'right';
      } else {
        source = mouseup_node;
        target = mousedown_node;
        direction = 'left';
      }
	  */
      var link;
      link = links.filter(function(l) {
        return (l.source === source && l.target === target);
      })[0];

      if(link) {
        link[direction] = true;
      } else {
        link = {source: source, target: target, left: false, right: false, id:source.id+"_"+target.id};
        link[direction] = true;
        links.push(link);
      }

      // select new link
      selected_link = link;
      selected_node = null;
      
      //choix du type de lien
      createLien(link, g);
      
      restart();
    })
    .on("click",function(d){
    		if(d.type=="ACTEURS"){
    			if(d.dt){
        			document.querySelector('#nomActeur').value = d.dt.nom;		
        			document.querySelector('#prenomActeur').value = d.dt.prenom;
        			document.querySelector('#professionActeur').value = d.dt.profession;
        			document.querySelector('#specialiteActeur').value = d.dt.specialite;
        			document.querySelector('#dtNait').value = d.dt.naissance;
        			document.querySelector('#dtMort').value = d.dt.mort;	
        			document.querySelector('#addActeur').innerHTML = "Modifier";	
    			}else{
        			document.querySelector('#nomActeur').value = d.desc;		
        			document.querySelector('#addActeur').innerHTML = "Modifier";	
    			}
    			
    			dialogActeur.showModal();
    		}
    	
    });

  // show node IDs
  var txt = g.append('svg:text')
      .attr('x', 0)
      .attr('y', r/2)
      .attr('id', function(d) { return "txt"+d.id; })
      .attr('class', 'id')
      .text(function(d) { return d.desc; });

  g.append('svg:rect')
  	.attr('x', function(d) {
		d.bb = d3.select("#txt"+d.id).node().getBBox();
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
  // remove old nodes
  circle.exit().remove();

  // set the graph in motion
  force.start();
}

function mousemove() {
  if(!mousedown_node) return;

  // update drag line
  drag_line.attr('d', 'M' + mousedown_node.x + ',' + mousedown_node.y + 'L' + d3.mouse(this)[0] + ',' + d3.mouse(this)[1]);

  restart();
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

function spliceLinksForNode(node) {
  var toSplice = links.filter(function(l) {
    return (l.source === node || l.target === node);
  });
  toSplice.map(function(l) {
    links.splice(links.indexOf(l), 1);
  });
}

// only respond once per keydown
var lastKeyDown = -1;

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
      restart();
      break;
    case 66: // B
      if(selected_link) {
        // set link direction to both left and right
        selected_link.left = true;
        selected_link.right = true;
      }
      restart();
      break;
    case 76: // L
      if(selected_link) {
        // set link direction to left only
        selected_link.left = true;
        selected_link.right = false;
      }
      restart();
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
      restart();
      break;
  }
}

function keyup() {
  lastKeyDown = -1;

  // ctrl
  if(d3.event.keyCode === 17) {
    circle
      .on('mousedown.drag', null)
      .on('touchstart.drag', null);
    svg.classed('ctrl', false);
  }
}

function createLien(l) {
	
	//vérifie le type de lien
	if(l.source.type=="ACTEURS" && l.target.type=="ACTEURS"){
		setTypeLien(datas["LIENS_ACTEURS_ACTEURS"]);
		dialogLien.showModal();		
	}
	if(l.source.type=="ACTEURS" && l.target.type=="CONCEPTS"){
		setTypeLien(datas["LIENS_ACTEURS_CONCEPTS"]);
		dialogLien.showModal();		
	}
	if(l.source.type=="ACTEURS" && (l.target.type=="EVENEMENTS" || l.target.type=="REFERENCES")){
		//document.getElementById('titreEvent').innerHTML = "Détail de l'événement";
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
			 });
			$( "#events-ajout tbody" ).append( "<tr id='eventST_"+l.spatiotempo.length+"'>" +
					"<td>" + $("#dtDeb").val() + "</td>" +
					"<td>" + $("#dtFin").val() + "</td>" +
					"<td>" + $("#dtLieu").val() + "</td>" +
					"<td>" + $("#dtPays").val() + "</td>" +
					"<td>" + $("#dtVille").val() + "</td>" +
					"<td>" + $("#dtAdresse").val() + "</td>" +
					"<td><span onclick='suppSpatioTempo("+(l.spatiotempo.length)+")' class='ui-icon ui-icon-trash'></span></td>" +					
				"</tr>" );			 
		};					
	}	 
	
	
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
		 
		 dialogLien.close();
		 restart();

		};	
}

function suppSpatioTempo(i){
	$( "#eventST_"+i).remove();
}

function showSpatioTempo(d){
	document.querySelector('#dtDeb').value="";
	document.querySelector('#dtFin').value="";
	document.querySelector('#dtLieu').value="";
	document.querySelector('#dtPays').value="";
	document.querySelector('#dtVille').value="";
	document.querySelector('#dtAdresse').value="";
	$( "#events-ajout tbody tr" ).remove();	
	//ajoute les event enregistré
	if(d.spatiotempo){
		var t = $( "#events-ajout tbody" );
		d.spatiotempo.forEach(function(e, i){
			t.append( "<tr id='eventST_"+i+"'>" +
					"<td>" + e.debut + "</td>" +
					"<td>" + e.fin + "</td>" +
					"<td>" + e.lieu + "</td>" +
					"<td>" + e.pays + "</td>" +
					"<td>" + e.ville + "</td>" +
					"<td>" + e.adresse + "</td>" +
					"<td><span onclick='suppSpatioTempo("+i+")' class='ui-icon ui-icon-trash'></span>" +					
				"</tr>" );			 			
		})		
	}

	dialogEvent.showModal();	
}

//ADD FES
//Création de Node
function createNode() {
	// prevent I-bar on drag
	var point = d3.mouse(this);
	if(d3.event.ctrlKey || mousedown_node || mousedown_link) return;
	
	dialogNoeud.showModal();
	
	document.querySelector('#addNode').onclick = function() {
		 // because :active only works in WebKit?
		 svg.classed('active', true);
		 // Get dialog
		 var dt, idNode = ++lastNodeId;
		 var type = document.querySelector('#typeNoeud').value;
		 if(!selectItem) {
			 selectItem=document.querySelector('#autocomplete').value;
			 if(type=="ACTEURS"){
				 //on affiche le formulaire
				 initFormAuteur();				 
				 document.querySelector('#nomActeur').value=selectItem;
				 document.querySelector('#addActeur').innerHTML = "Ajouter";	
				 dialogActeur.showModal();
				 document.querySelector('#addActeur').onclick = function() {
					 dt= {"nom":document.querySelector('#nomActeur').value
						, "prenom":document.querySelector('#prenomActeur').value 
						, "profession":document.querySelector('#professionActeur').value 
						, "specialite":document.querySelector('#specialiteActeur').value 
						, "naissance":document.querySelector('#dtNait').value 
						, "mort":document.querySelector('#dtMort').value 
					 };	
					nodes[idNode].dt = dt;
					dialogActeur.close();
					selectItem = "";
				 }
			 }
		 }
		 var  node = {id: idNode, reflexive: false, desc : selectItem, type : type, dt:dt};
		 node.x = point[0];
		 node.y = point[1];
		 nodes.push(node);
		 // Close dialog
		 restart();
		 dialogNoeud.close();
		 //réinitialise l'input
		 document.querySelector('#autocomplete').value = "";
	};
}

function initFormAuteur(){
	document.querySelector('#nomActeur').value = "";
	document.querySelector('#prenomActeur').value = "";
	document.querySelector('#professionActeur').value = "";
	document.querySelector('#specialiteActeur').value = "";
	document.querySelector('#dtNait').value = "";
	document.querySelector('#dtMort').value = "";						
}

function findActeurRef(){
	 //on recherche l'occurence de l'acteur dans la base de référence mondiale	
}


function setAutocomplete(val){
	
	//ajoute l'autocompletion
	$('#autocomplete').autocomplete({source: datas[val]
	, select: function( event, ui ) {
	        selectItem = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgNoeud");			
}

function setAutocompleteActeur(){
	
	//ajoute l'autocompletion
	$('#professionActeur').autocomplete({source: dataCat["ACTEURS"]["Professions"]
	, select: function( event, ui ) {
	        selectProf = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgActeur");			

	$('#specialiteActeur').autocomplete({source: dataCat["ACTEURS"]["Spécialité"]
	, select: function( event, ui ) {
	        selectSpecia = ui.item.value;
	      }
	})
	.autocomplete("option", "appendTo", "#dlgActeur");			
}

function setAutocompleteLieu(val){
	
	$("#dtLieu").value = "";
	
	//construction de l'autocomplétion avec catégories
    $("#dtLieu").autocomplete({
		source: dataCat["LIEUX"][val],
		select: function( event, ui ) {
	        selectLieux = ui.item.value;
			return false;
		}
	})
	.autocomplete("option", "appendTo", "#dlgEvent")
	.data( "ui-autocomplete" )
		._renderItem = function( ul, item ) {
			return $( "<li>" )
				.append( "<a>" + item.value + "</a>" )
				.appendTo( ul );
		};	
    
    
}

function setTypeLien(arr){
	//ajoute les type de lien
	var select = document.getElementById("typeLien"); 
	var opts = select.getElementsByTagName('option');
	for (var i = 0; i < opts.length; i++) {
		select.removeChild(opts[i]);
	}	
	arr.forEach(function(d){
	    var el = document.createElement("option");
	    el.textContent = d.value;
	    el.value = d.value;
	    select.appendChild(el);			
	});
}

function setListeGraph(){
	var select  = d3.select("#saveGraph")
	.on("change",function (){
			if(this.selectedIndex){
				selectIclnation = false;
			}					
		}					
	);
	var options = select.selectAll('option').data(arrIclnaison); // Data join
	//ajoute les options
	options.enter().append("option").text(function(d) { 
		//var dt = new Date(d.maj);
		//return d.titre+" - "+d.login+" - "+chaineDate(dt); 
		return d.titre+" ("+d.login+")"; 
		});
	//ajout de l'option de sélection
	select.append("option")
		.text("Choisissez un modèle")
		.attr("selected",true); 	
}

// app starts here
//initForceLayout();
