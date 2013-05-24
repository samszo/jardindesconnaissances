
	var dataCloud;
		
	function initCarte(){
		//réinitialise la carte
		//document.getElementById("map_canvas").innerHTML = "";
		dataCloud = "";
		d3.select('#map_canvas').remove();
		document.getElementById("scene_1.1").style.display='none';
		document.getElementById("scene_1.2").style.display='none';
		document.getElementById("scene_1.3").style.display='none';
		document.getElementById("scene_2").style.display='none';

	}
	
	function setCarte(sb){
		initCarte();
		//récupère la carte sélectionnée
		var ind = sb.options.selectedIndex;
		//sort si aucune carte n'est sélectionnée
		if(ind==0) return;
		//création du conteneur de carte
		var td = d3.select('#tdCarto');
		var divMap = td.append("div").attr("id", "map_canvas");
		//affiche la carte sélectionnée
		if(ind==1)getGeo(document.getElementById("map_canvas"));
		if(ind==2)getTagcloud(divMap);
		if(ind==3)getTaxoIdee(divMap, "Tweetpalette?idPalette=2&iframe=true&tag=frontières&url=/public/frontieres?id="+arrTof[iTof]['doc_id']+"&showIeml=true&idBase="+db);
		//affiche l'instruction suivante
		document.getElementById("scene_1."+ind).style.display='block';
	}

	function chargeTof() {
		creaDegrade();
		var dT = d3.select('#tof');
		d3.select('#selectTof').remove();
		var url = arrTof[iTof]['photo_file_url'];
		dT.append("img")
			.attr("id", "selectTof")
			.attr("src", url);
		choix = false;
		document.getElementById("numTof").innerHTML = iTof+1;
		document.getElementById("nbTof").innerHTML = arrTof.length;
	}	
	
	function creaDegrade() {

		var colors = ["#ccdef0", "#1e4164"];
		var c = d3.scale.linear().domain([0, 100]).range(colors);
		
		var arr = [];
		for(var i=0; i < 100; i++){
			arr.push({"id":i, "valeur":Math.floor((Math.random()*100)+1)});
		}
		
		d3.select('#svgDeg').remove();
		var chart = d3.select('#degrad').append("svg")
		  .attr('width', 200)
		  .attr('height', 400)
		  .attr("id", "svgDeg");
		var g = chart.append("g").attr("id", "gDeg");
		g.selectAll("rect").data(arr).enter().append("rect")
		  .attr('y', function(d) { return 10*d["id"]; })
		  .attr('height', 10)
		  .attr('width', function(d) { return d["valeur"]; })
		  .attr('stroke', 'white')
		  .attr('fill', function(d) {  
			  var color = c(d["valeur"]); 
			  return color;
		  });

		
		
	}

	function nextTof() {
		iTof++;
		if(iTof == arrTof.length) iTof = 0;
		//removeRuler();
		//document.getElementById("choix_carte").options.selectedIndex = 0;
		//initCarte();
		chargeTof();
		//map.setOptions({zoom: 1,center:new google.maps.LatLng(0,0)});
	}

	function prevTof() {
		iTof--;
		if(iTof == -1) iTof = 0;
		//removeRuler();
		//document.getElementById("choix_carte").options.selectedIndex = 0;
		//initCarte();
		chargeTof();
		//map.setOptions({zoom: 1,center:new google.maps.LatLng(0,0)});
	}
	
	function savePosition() {
		var p = {"idDoc":arrTof[iTof]['doc_id'], "lat":posiClic.lat(), "lng":posiClic.lng(), "zoom":map.getZoom(), "note":posiNote};
		$.post("frontieres/ajout", p,
				 function(data){
					dataCloud = data;
				 }, "json");
	}