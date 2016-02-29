var spans, sTxt = [], dtGen;

$(document).ready(function() {
		
    $('#inscription').on('submit', function() {
 
        var login = $('#login').val();
        var mdp = $('#mdp').val();
        var mail = $('#email').val();
 
        if(login == '' || mdp == '') {
            alert('Les champs doivent êtres remplis');
        } else {
            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serialize(),
                success: function(result) {
                    if(isNaN(result)) {
                    	alert('Erreur : '+ result);
                    }else{
                    	idUti=result;
                    	$(document.body).animate({
                    	    'scrollTop':   $('#generation').offset().top-180
                    	}, 1000);
                    }
                }
            });
        }
        return false;
    });
    

    $('#connexion').on('submit', function() {
    	 
        var login = $('#conLogin').val();
        var mdp = $('#conPass').val();
 
        if(login == '' || mdp == '') {
            alert('Les champs doivent êtres remplis');
        } else {
        	var dt = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serialize(),
                success: function(result) {
                    if(isNaN(result)) {
                    	alert('Erreur : '+ result);
                    }else{
                    	idUti=result;
                    	$(document.body).animate({
                    	    'scrollTop':   $('#generation').offset().top-180
                    	}, 1000);
                    }
                }
            });
        }
        return false;
    });
    
});

function load(idDiv, idCpt) {
	cursor_wait();
    d3.text(urlGen+"&cpt="+idCpt, function(fragment) {
	    	cursor_clear();
    		d3.select("#"+idDiv).html(fragment);
	    paroleGen(idDiv, fragment);
        saveGen(idDiv, fragment, idCpt);
    });
}
function loadEval(idDiv, idCpt, callBack) {
	cursor_wait();
	$.ajax({
		dataType: "json",
		url: urlGen+"&cpt="+idCpt,
	    	error: function(error){
		    	cursor_clear();
        		try {
        			var js = JSON.parse(error.responseText);
        		}catch (e){
        			console.log(error.responseText)            		  	
        			w2alert("Erreur : "+e);
        		}
	    	},            	
	    	success: function(data) {
		    	cursor_clear();
		    	dtGen = data;
			//affiche le texte
		    	var div = d3.select("#"+idDiv).html(dtGen.txt);
		    	//enregistre la génération
		    	var p = {"idBase":idBase, "idUti":idUti
		    			, "data":{"titre":dtGen.txt, "idOeu":idOeu, "idCpt":idCpt,"txt":dtGen.txt
		    				,"data":JSON.stringify(dtGen)}};
		    	$.ajax({
	        		url: "../gapaii/savegen",
	        		data: p,
	            	type: 'post',
	            	error: function(error){
	          		w2alert("Erreur : "+error);
	            	},            	
	            	success: function(rep) {
	    				dtGen.idDoc = rep;
	    				//execute la fonction callBack		    				
	    				callBack();
	            }
			});		    	
		}
	});		    	
	
			/*
			caracts = d3.selectAll("span")
				.on("mouseover",function(d){
					var s = d3.select("#"+this.id);
					s.style("color","red");
					showOverlay(this);					
				})
				.on("mouseout",function(d){
					var s = d3.select("#"+this.id);
					s.style("color","black");
				});
			
		    	sTxt = [];
			arrMot = dtGen.txt.split(" ");
			arrMot.forEach(function(m){
				sTxt.push({"m":m,"c":m.split('')}); 
			});
			div.selectAll('span').remove();
			spans = div.selectAll('span').data(sTxt);
			spans.enter()
				.append("span")
				.attr("id", function(d,i){ return "sMot_"+i;})
			.html(function(d){
				return d.m;
			})
			.on("mouseover",function(d){
				var s = d3.select("#"+this.id);
				s.style("color","red");
			})
			.on("mouseout",function(d){
				var s = d3.select("#"+this.id);
				s.style("color","black");
			});
			var caracts = spans.selectAll('span').data(function(row, i) {
			       // evaluate column objects against the current row
			       return sTxt.map(function(c) {
			           var cell = {};
			           d3.keys(c).forEach(function(k) {
			               cell[k] = typeof c[k] == 'function' ? c[k](row,i) : c[k];
			           });
			           return cell;
			       });
			   });
			  */
		    //	paroleGen(idDiv, fragment);
		    //saveGen(idDiv, fragment, idCpt);
	
}

function saveGen(titre, txt, idCpt) {
	var p = {"idBase":idBase, "idUti":idUti, "data":{"titre":"gapaii_"+titre, "idOeu":idOeu, "idCpt":idCpt,"txt":txt}};
    
	$.post("gapaii/savegen"
		, p,
		 function(data){
			console.log(data);
			idDoc = data;
		 });
}



function paroleGen(idDiv, txt) {
	var p = {"txt":txt};
	$.post("flux/parole"
		, p,
		 function(fragment){
	    	d3.select("#"+idDiv+"Audio").html(fragment);
		 });
}

function evaluer() {
	document.getElementById('ifEval').src = "eval?idDoc="+idDoc+"&idUti="+idUti;
	$(document.body).animate({
	    'scrollTop':   $('#evaluer').offset().top
	}, 1000);	
}

function naviguer() {
	document.getElementById('ifNavig').src = "eval/navigation";
	$(document.body).animate({
	    'scrollTop': $('#naviguer').offset().top
	}, 1000);	
}

//Changes the cursor to an hourglass
function cursor_wait() {
	document.body.style.cursor = 'wait';
}

// Returns the cursor to the default pointer
function cursor_clear() {
	document.body.style.cursor = 'default';
}