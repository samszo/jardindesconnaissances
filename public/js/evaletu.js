		//ajoute un cours
		function ajoutCours(etu) {
			var obj=document.getElementById("cours_"+etu); //liste
			option = document.createElement('option'); //Création d'un élément option à ajouter au select.
			option.innerHTML = prompt("Valeur a rajouter"); //On lui attribue un contenu visible dans la liste avec le nom du pays de l'utilisateur.
			obj.appendChild(option); //Et enfin on l'ajoute.
		} 

		//enregistrement la note d'un etudiant
		function ajoutNote(etu) {
			//récupère les informations de l'étudiant
			var nom = $("#etu_nom_"+etu).text();
			var exe = document.getElementById("exe_"+etu).value;
			var note = document.getElementById("note_"+etu).value;
			var selectCours = document.getElementById("cours_"+etu);
			var cours = selectCours.options[selectCours.selectedIndex].innerHTML;
			if(cours==""){
				alert("Veuillez sélectionner un cours");
				return;
			}
			var p = {"exe":exe, "nom":nom, "note":note, "cours":cours};
			$.get("ecrire.php", p,
					 function(data){
						alert(data);
						getNote(etu);
					 });
		}

		//modifier la note d'un etudiant
		function modifNote(idNote, etu) {
			//récupère la nouvelle note
			var note = prompt("Nouvelle note", "");
			var p = {"idNote":idNote, "note":note};
			$.get("modif.php", p,
					 function(data){
						getNote(etu);
					 });
		}
		
		//récupération des raison d'un étudiant
		function getNote(etu) {
			//récupère les informations de l'étudiant
			var nom = $("#etu_nom_"+etu).text();
			var selectCours = document.getElementById("cours_"+etu);
			var cours = selectCours.options[selectCours.selectedIndex].innerHTML;
			if(cours==""){
				alert("Veuillez sélectionner un cours");
				return;
			}
			var p = {"nom":nom, "cours":cours};
			$.get("lire.php", p,
					 function(data){                
						showMoyenne(data, etu);
						showNoteSur(data, etu);
						voirstat(data, etu);
					 }, "json");
		}

		//affiche la moyenne de l'étudiant
		function showMoyenne(data, etu){
			//calcule la somme des notes
			var total = 0;
		    data.forEach(function(d) {
		    	total += parseInt(d.note);
		      });
		    var moyenne = total/data.length
		    var c = 'red';
			if(moyenne >= 3) c = 'green'; 
			d3.select("#moyenne_"+etu)
		    	.text(moyenne.toFixed(2))
		    	.attr("style", "color:"+c);

		}	

		//affiche la note sur X de l'étudiant
		function showNoteSur(data, etu){
			//calcule la somme des notes
			var total = 0;
		    data.forEach(function(d) {
		    	if(d.exercice=="bug")
			    	total -= parseInt(d.note);
		    	else
		    		total += parseInt(d.note);
		      });
		    var sur = parseInt(document.getElementById("sur_"+etu).value);
		    var coef = parseInt(document.getElementById("max_"+etu).value)/sur;
		    var note = total/coef;
			c = 'red'; 
			if(note >= (sur/2)) c = 'green'; 
			d3.select("#note_sur_"+etu)
		    	.text(note.toFixed(2))
		    	.attr("style", "color:"+c);
			d3.select("#note_total_"+etu)
	    	.text(total.toFixed(2));
		}	
		
		//dessine le graphe svg
		function voirstat(data, etu){

			//var data = d3.csv.parse(d3.select('#csv').text());
			d3.select('#svg_barre_'+etu).remove();
			
			var valueLabelWidth = 40; // space reserved for value labels (right)
			var barHeight = 20; // height of one bar
			var barLabelWidth = 200; // space reserved for bar labels
			var barLabelPadding = 5; // padding between bar and bar labels (left)
			var gridLabelHeight = 18; // space reserved for gridline labels
			var gridChartOffset = 3; // space between start of grid and first bar
			var maxBarWidth = 420; // width of the bar with the max value

			// data aggregation
			var aggregatedData = d3.nest()
			  .key(function(d) { return d['exercice']; })
			  .rollup(function(d) {
			    return {
			      'value': d3.sum(d, function(e) { return parseFloat(e['note'])})
			    };
			  })
			  .entries(data);
			 
			// accessor functions 
			var barLabel = function(d) { return d.key; };
			var barValue = function(d) { return d.values.value; };
			 
			// scales
			var yScale = d3.scale.ordinal().domain(d3.range(0, aggregatedData.length)).rangeBands([0, aggregatedData.length * barHeight]);
			var y = function(d, i) { return yScale(i); };
			var yText = function(d, i) { return y(d, i) + yScale.rangeBand() / 2; };
			var x = d3.scale.linear().domain([0, d3.max(aggregatedData, barValue)]).range([0, maxBarWidth]);
			// svg container element
			var chart = d3.select('#etu_svg_'+etu).append("svg")
			  .attr('id', 'svg_barre_'+etu)
			  .attr('width', maxBarWidth + barLabelWidth + valueLabelWidth)
			  .attr('height', gridLabelHeight + gridChartOffset + aggregatedData.length * barHeight);
			// grid line labels
			var gridContainer = chart.append('g')
			  .attr('transform', 'translate(' + barLabelWidth + ',' + gridLabelHeight + ')'); 
			gridContainer.selectAll("text").data(x.ticks(10)).enter().append("text")
			  .attr("x", x)
			  .attr("dy", -3)
			  .attr("text-anchor", "middle")
			  .text(String);
			// vertical grid lines
			gridContainer.selectAll("line").data(x.ticks(10)).enter().append("line")
			  .attr("x1", x)
			  .attr("x2", x)
			  .attr("y1", 0)
			  .attr("y2", yScale.rangeExtent()[1] + gridChartOffset)
			  .style("stroke", "#ccc");
			// bar labels
			var labelsContainer = chart.append('g')
			  .attr('transform', 'translate(' + (barLabelWidth - barLabelPadding) + ',' + (gridLabelHeight + gridChartOffset) + ')'); 
			labelsContainer.selectAll('text').data(aggregatedData).enter().append('text')
			  .attr('y', yText)
			  .attr('stroke', 'none')
			  .attr('fill', 'black')
			  .attr("dy", ".35em") // vertical-align: middle
			  .attr('text-anchor', 'end')
			  .text(barLabel);
			// bars
			var barsContainer = chart.append('g')
			  .attr('transform', 'translate(' + barLabelWidth + ',' + (gridLabelHeight + gridChartOffset) + ')'); 
			barsContainer.selectAll("rect").data(aggregatedData).enter().append("rect")
			  .attr('y', y)
			  .attr('height', yScale.rangeBand())
			  .attr('width', function(d) { return x(barValue(d)); })
			  .attr('stroke', 'white')
			  .attr('fill', 'steelblue')
			  .on("click", function(d) { 
				  modifNote(idNote, etu); 
				  });
			// bar value labels
			barsContainer.selectAll("text").data(aggregatedData).enter().append("text")
			  .attr("x", function(d) { return x(barValue(d)); })
			  .attr("y", yText)
			  .attr("dx", 3) // padding-left
			  .attr("dy", ".35em") // vertical-align: middle
			  .attr("text-anchor", "start") // text-align: right
			  .attr("fill", "black")
			  .attr("stroke", "none")
			  .text(function(d) { return d3.round(barValue(d), 2); });
			// start line
			barsContainer.append("line")
			  .attr("y1", -gridChartOffset)
			  .attr("y2", yScale.rangeExtent()[1] + gridChartOffset)
			  .style("stroke", "#000");   
		}