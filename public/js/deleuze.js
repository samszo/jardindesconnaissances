		var arrSst = [];

		(function(Popcorn) {
		    
			  Popcorn.plugin( "funzo" , function( options ) {

			    return {

			      start: function( event, options ) {

			        // Inside of these plugin method definitions, you can
			        // always trust that the context will be the Popcorn
			        // instance that will call the plugin method...

			        this.pause();

			        // ...would pause the current video when the plugin
			        // is executed.

			      },
			      end: function( event, options ) {

			        // still the same context in here!

			      }
			    };
			  });
			    
			})(Popcorn);		

		
		function showSst(idElem){			
			var chk = document.getElementById("chk_"+idElem);
			var bs = d3.select('#son_'+idElem);
			bs.attr("visibility", function() { if(chk.checked) return "visible"; else return "hidden"; });	  		
			var bt = d3.select('#txt_'+idElem);
			bt.attr("visibility", function() { if(chk.checked) return "visible"; else return "hidden"; });
			var show = document.getElementById("showSelect_"+idElem);
			if(chk.checked){
				show.style.display="inline"; 
			}else{
				show.style.display="none"; 
			}	  		
		}
		function updSst(idDoc, num, idDocPosi){
			var p = getParams(idDoc, num);
			p.idDocPosi = idDocPosi;
			$.post("modif", p,
					 function(data){
						alert("Modification effectuée.");
					 }, "json");						
		}
		function saveSst(idDoc, num){
			var p = getParams(idDoc, num);
			$.post("ajout", p,
					 function(data){
						addSb(data);
					 }, "json");
		}
		function delSst(idDoc, idDocPar, idElem){
			$.post("supp"
					,  {"idDoc":idDoc},
					 function(data){
						var toto = data;
					 }, "json");
			/*
			var dPar = document.getElementById("table_"+idDocPar);
			var d = document.getElementById("Select_exi_"+idDoc);
			dPar.removeChild(d); 
			d = document.getElementById("showSelect_"+idElem);
			dPar.removeChild(d);
			*/
			d3.select('#Select_exi_'+idDoc).remove();
			d3.select('#showSelect_'+idDoc).remove();


		}
		function saveTag(tag, poids, idDoc){
			var arr = idDoc.split("_");
			var p = {"tag":tag, "idDoc":arr[3], "poids":poids, "db":db};
			$.post("../flux/ajoututitag", p,
					 function(data){
						updTc(data, arr, idDoc);
					 }, "json");
		}
		function updTc(data, arrId, idElem){
			//modifie les data de la position
			var sst = arrSst[arrId[1]];
			sst.data['posis'][arrId[5]]['tags'] = data;
			//récupère la barre de sélection
			var sb = sst.arrSbTxt[arrId[2]];
			//recalcule la sélection
			sb.show();			
			activeTag();
		}	
		function getParams(idDoc, num){

			var sst = arrSst[idDoc];
			var p = sst.sbparams(num);
			p.idDoc = idDoc;
			p.idExi = idExi;
			p.mailExi = mailExi;
			return p;
		}
		function addSb(p){
 			var oP = eval('(' + p['note'] + ')');
			var sst = arrSst[oP['idDoc']];
			var nbSb = sst.arrSbSon.length;
			var nbTc = 0;
			if(sst.data['posis'])
				nbTc = sst.data['posis'].length-1;
 			var idElem = oP['idDoc']+"_"+nbSb+"_"+p['idDoc']+"_"+oP['idExi']+"_"+nbTc;
			var bs = d3.select('#table_'+oP['idDoc']);
			var dL = bs.append("tr")
				.attr("id", "Select_exi_"+p['idDoc']);
			var d1 = dL.append("td")
				.attr("colspan", "2");
			var d2 = d1.append("div")
				.attr("class","Select")
				.attr("id","Select_"+idElem)
				.html(oP['mailExi']);
			d2.append("input")
				.attr("id","chk_"+idElem)
				.attr("type","checkbox")
				.attr("checked","checked")
				.attr("onclick","showSst('"+idElem+"',-1)");
			d2.append("img")
				.attr("id", "Select_del_"+p['idDoc'])
				.attr("onclick","delSst("+p['idDoc']+","+oP['idDoc']+",'"+idElem+"')")
				.attr("src", "../img/DeleteRecord.png")
				.attr("title", "Supprimer la sélection");
			d2.append("img")
				.attr("id", "Select_upd_"+p['idDoc'])
				.attr("onclick","updSst("+oP['idDoc']+","+nbSb+","+p['idDoc']+")")
				.attr("src", "../img/UpdateRecord.png")
				.attr("title", "Modifier la sélection");
			d2.append("span")
				.attr("id", "Select_son_"+idElem)
				.attr("class","sonSelect");
			d2.append("span")
				.attr("id", "status_"+idElem)
				.attr("class","status");
			var dL1 = bs.append("tr").attr("id", "showSelect_"+idElem);
			var cl1 = dL1.append("td");
			cl1.append("div")
				.attr("id", "Select_txt_"+idElem)
				.attr("class","txtSelect");
			var cl2 = dL1.append("td");
			cl2.append("div")
				.attr("id", "vis_"+idElem)
				.attr("class","tgcld");
			sst.addNewPosiSb(oP, idElem);
			activeTag();
		}

		function activeTag(){
			d3.selectAll(".tag")
			.on("click", function(){
					var d = event.target;
					console.log(d.id+" "+d.innerText+" "+d.getAttribute('v'));
					saveTag(d.innerText, d.getAttribute('v'), d.id);
				});			
		}
		
		function chgcrible(c) {
			if (c.selectedIndex != 0){
				d3.select('#gPosi').html("");
				d3.select('#vis_gTC').html("");
				var p = {"idUti":c.options[c.selectedIndex].value, "db":db};
				$.post("../flux/gettutitags", p,
						 function(data){
							//console.log(data);
							tcGlobal = new tagcloud({idDoc:"gTC", data:data, w:1000, h:300, global:true}); 							
						 }, "json");
			}	 
		}

		function chargeTag(tag){
			d3.select('#gPosi').html("");
			var p = {"term":tag, "ajax":true};
			$.post("../deleuze/position", p,
					 function(data){
				 		//affiche les nouvelles positions
						//var gPosi = document.getElementById("gPosi");
						//gPosi.innerHTML = data;
						d3.select('#gPosi').html(data);
						d3.selectAll(".sst")
							.html(function() {
								eval(this.innerText); 
								});
					 });
		}
