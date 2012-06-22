
	var dataCloud;
		
	function initCarte(){
		//réinitialise la carte
		//document.getElementById("map_canvas").innerHTML = "";
		dataCloud = "";
		d3.select('#map_canvas').remove();
		document.getElementById("scene_1.1").style.display='none';
		document.getElementById("scene_1.2").style.display='none';
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
		if(ind==1)getGeo();
		if(ind==2)getTagcloud(divMap);
		if(ind==3)getTaxoIdee(divMap);
		//affiche l'instruction suivante
		document.getElementById("scene_1."+ind).style.display='block';
	}

	function getTaxoIdee(ele){
		
		var ifr = ele.append("div")
			.attr("id","outerdiv")
				.append("iframe")
				.attr("src", "Tweetpalette?tag=frontières&url=http://www.jardindesconnaissances.com/public/frontieres?id="+arrTof[iTof]['doc_id']+"&showIeml=true&idBase="+db)
				.attr("id","inneriframe")
				.attr("scrolling", "no");
	}
	
	function getGeo() {
		//pour gérer la non connexion
		if(google){				
			var myOptions = {
			  zoom: 1,
			  center:  new google.maps.LatLng(0,0) ,
			  mapTypeId: google.maps.MapTypeId.ROADMAP		
			};
			
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			google.maps.event.addListener(map, 'click', function(mouseEvent) {
				if(!choix){					
					var posiTof = new google.maps.LatLng(arrTof[iTof]['lat'], arrTof[iTof]['lng']);
					removeRuler();
					posiClic = mouseEvent.latLng;
					addruler(posiClic, posiTof);
					choix = true;
					savePosition();
				}
			});
		}
	}

	function removeRuler() {
		//supprime les règle sur la carte
		if(ruler1) ruler1.setMap(null);
		if(ruler2) ruler2.setMap(null);
		if(rulerpoly) rulerpoly.setMap(null);
		if(ruler1label) ruler1label.setMap(null);
		//supprime le tag cloud
		var dV = d3.select('#visTof');
		d3.select('#vis_tcIma').remove();
		dV.append("div").attr("id", "vis_tcIma");			
	}

	function chargeTof() {
		var dT = d3.select('#tof');
		d3.select('#selectTof').remove();
		var url = arrTof[iTof]['url'];
		dT.append("img")
			.attr("id", "selectTof")
			.attr("src", url);
		choix = false;
		
	}	
	
	function nextTof() {
		iTof++;
		if(iTof == arrTof.length) iTof = 0;
		//removeRuler();
		initCarte();
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

	function saveTag(tag, poids){
		var p = {"tag":tag, "idDoc":arrTof[iTof]['doc_id'], "poids":poids, "db":db};
		$.post("../flux/ajoututitag", p,
				 function(data){
			 		var toto = "toto";
				 }, "json");
	}

	function getTagcloud(ele){
		//affiche le tag cloud
		ele.append("div").attr("id", "vis_tcIma");
		var tcg;	
		if(dataCloud){
	 		//affiche le tag cloud
			tcg	= new tagcloud({idDoc:"tcIma", data:dataCloud, w:600, h:300, colorTag:'white', verif:true});			
		}else{
			//récupère les données du tagcloud
			var p = {"idDoc":arrTof[iTof]['doc_id'], "db":db};
			$.post("frontieres/tagcloud", p,
					 function(data){
						dataCloud = data;
						tcg	= new tagcloud({idDoc:"tcIma", data:dataCloud, w:600, h:300, colorTag:'white', verif:true});			
					 }, "json");
			
			
		}
		
	}