		var tweet = "";		
		var urlFond = "" ;
		var grilleSvg;
		var nbX, nbY, nbZone;
		var _X, _Y;
		var sem=[];
		var xx;
		var dtInput;
		
		window.onload = function(){
			
			getTweet();
			getInput();
						
			//gestion des evenements
			document.getElementById("gen").onclick = function(){
				//xx.store.generateRandomDataSet(100);
				setTweet();				
				envoiTweet();
			};
			$(".infoEvent").change(function () {
				getTweet();	
		        }).change();
		};

		function multiSelect(item){
			//gère les informations liées
			for (i=0;i<dtInput.events.length;i++){
				var val = item.innerHTML;
				if(val==dtInput.events[i].titre){
					$("#url_event").val(dtInput.events[i].url);
					return;
				}
				if(val==dtInput.events[i].url){
					$("#tag_event").val(dtInput.events[i].titre);
					return;
				}
			}			
		}
		
		function getSemClic(x, y){
			if(urlFond=="")return;
			sem=[];
			sem.push({"x":x,"y":y,"urlFond":urlFond});  
			//récupère la valeur sémantique X
			getSemX(x);
			//récupère la valeur sémantique Y
			getSemY(y);
			//récupère la valeur sémantique de la zone
			getSemZone(x, y);
			_X = x;
			_Y = y;
			for (i=0;i<sem.length;i++){
				console.log(_X+" - "+_Y+" : "+sem[i].lib);				
			}
			ecriTweet();
		}
		function getSemX(x){
			for (i=0;i<nbX;i++){
				if(i+1 == nbX){
					sem.push(grilleSvg.repX[i]);
					return;
				}else{
					var semDeb = grilleSvg.repX[i];			
					var semFin = grilleSvg.repX[i+1];			
					if(i==0 && semDeb.x >= x){
						sem.push(semDeb);  
						return;
					}
					if(semDeb.x < x && semFin.x >= x){
						sem.push(semDeb);  
						sem.push(semFin);  
						return;
					}				  					
				}					
			}			
		}
		function getSemY(y){
			for (i=0;i<nbY;i++){
				if(i+1 == nbY){
					sem.push(grilleSvg.repY[i]);
					return;
				}else{
					var semDeb = grilleSvg.repY[i];			
					var semFin = grilleSvg.repY[i+1];			
					if(i==0 && semDeb.y <= y){
						sem.push(semDeb);  
						return;
					}
					if(semDeb.y > y && semFin.y <= y){
						sem.push(semDeb);  
						sem.push(semFin);  
						return;
					}				  					
				}					
			}
			
		}		
		function getSemZone(x, y){
			for (i=0;i<nbZone;i++){
				var zone = grilleSvg.repZone[i];
				if(zone.x <= x && zone.y <= y && zone.x1 >= x && zone.y1 >= y ){
					sem.push(zone);  
					return;
				}				  					
			}
			
		}		

		function envoiTweet() {
			//tweet = ecriTweet();
		   	window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(tweet),
		       '',
		       'status=no, scrollbars=no, menubar=no, width=550, height=300');
		}
		
		function ecriTweet() {
						
			if(document.getElementById('tag_event').value)
				tweet = "#"+document.getElementById('tag_event').value;
			else
				tweet = "";				
			if(document.getElementById('user_event').value){
				var a = document.getElementById('user_event').value;
				a = a.replace(" ","_"); 
				tweet += " @"+a;				
			}
			
			var degre;					
			var showIEML = false;//document.getElementById('showIeml').checked;
			for (i=0;i<sem.length;i++){
				if(sem[i].lib){
					//calcul le degré de puissance
					degre = "";
					if(!sem[i].x1){
						if(sem[i].x) degre = _X-sem[i].x;
						if(sem[i].y) degre = sem[i].y-_Y;					
					}
					//vérifie s'il faut afficher le code IEML
					if(showIEML){
						tweet += " "+degre+"*"+sem[i].ieml+"**";
					}else{
						tweet += " "+degre+"#"+sem[i].lib;					
					}					
				}
			}
			document.getElementById('tweet_text').value = tweet;
			document.getElementById('taille_tweet').innerHTML = tweet.length+"/140";
			
			console.log(tweet);				

			return tweet;
		}

		function getTweet() {
			if(urlFond=="")return;
			$.post("tweetpalette/lit"
					, getParams(),
					 function(data){
						if(data)showTweetClic(data);
					 });
		}
				
		function showTweetClic(data){
			var obj = eval('('+data+')');
			xx.store.setDataSet(obj);			
		}
		
		function setTweet() {
			var p = getParams();
			$.post("tweetpalette/ajout"
					, p,
					 function(data){
						getTweet();
					 }, "json");
			getInput();
		}

		function getInput() {
			$.post("tweetpalette/input"
					, {"idBase":idBase},
					 function(data){
						setInput(data);
					 }, "json");			
		}

		function getParams(){

			var urlE = "no";
			var tagE = "no";
			var exi = "no";			
			
			if(document.getElementById('url_court').value) urlE = document.getElementById('url_court').value;
			if(document.getElementById('url_event').value) urlE = document.getElementById('url_event').value;
			//if(!url || !document.getElementById('filtrerUrl').checked) url = "no";
			
			if(document.getElementById('tag_event').value) tagE = document.getElementById('tag_event').value;
			//if(!document.getElementById('filtrerTag').checked) event = "no";
			
			if(document.getElementById('user_event').value) exi = document.getElementById('user_event').value;
			//if(!document.getElementById('filtrerUti').checked) uti = "no";

			var filtrer = true;
			if(urlE == "no" && tagE == "no" && exi == "no")filtrer=false;
			
			return {"idBase":idBase, "event":tagE, "url":urlE, "exi":exi, "uti":idUti, "sem":sem, "urlFond":urlFond, "filtrer":filtrer};
		}
		
		function setInput(data){
			dtInput = data;
			//création des tableaux
			var dtE=[], e="";
			var dtUrl=[], url="";
			var dtU=[];
			var dtR=[];
			for (i=0;i<dtInput.events.length;i++){
				//vérifie les doublons
				if(e!=dtInput.events[i].titre)
					dtE.push(dtInput.events[i].titre);
				if(url!=dtInput.events[i].url)
					dtUrl.push(dtInput.events[i].url);
				e = dtInput.events[i].titre;
				url = dtInput.events[i].url;
			}
			for (i=0;i<dtInput.utis.length;i++){
				dtU.push(dtInput.utis[i].login);
			}
			
	        $("#user_event").smartAutoComplete({source: dtU});
	        $("#url_event").smartAutoComplete({source: dtUrl});
	        $("#tag_event").smartAutoComplete({source: dtE});

		}
		
		function changePalette(e){
						
			var o = e.selectedOptions[0];
			//initialise les éléments
			d3.select('#svg').remove();
			d3.select('#png').remove();
			var hma = document.getElementById('heatmapArea');
			while (hma.firstChild) {
				hma.removeChild(hma.firstChild);
			}
			if(e.selectedIndex==0) return;				
			
			//charge les valeurs
			grilleSvg = grilles[e.selectedIndex-1];
			nbX = grilleSvg.repX.length;
			nbY = grilleSvg.repY.length;
			nbZone = grilleSvg.repZone.length;
			urlFond = grilleSvg.url;
			
			//défini les style de la heatmap
			hma.style.width = grilleSvg.widthArea;	
			hma.style.height = grilleSvg.heightArea;
			hma.style.top = grilleSvg.topArea;
			hma.style.left = grilleSvg.leftArea;

			//création du heatmap
			xx = h337.create({"element":document.getElementById("heatmapArea"), "radius":25, "visible":true});			
			xx.get("canvas").onclick = function(ev){
				var pos = h337.util.mousePosition(ev);
				xx.store.addDataPoint(pos[0],pos[1]);
				getSemClic(pos[0], pos[1]);
				if(iframe){
					setTweet();				
				}
			};
			
			//récupère les éléments de la base
			getTweet();
			
			//ajoute la valeur aux éléments
			if(o.className=="svg"){
				d3.select('#svgArea')
					.append("div")
						.attr("id", "svg");
				d3.xml(e.value, "image/svg+xml", function(xml) {
					var svg = document.getElementById("svg");
					svg.appendChild(xml.documentElement);
					});
			}
			if(o.className=="png"){
				d3.select('#svgArea')
					.append("img")
						.attr("src", e.value)
						.attr("id", "png");
			}
		}
		