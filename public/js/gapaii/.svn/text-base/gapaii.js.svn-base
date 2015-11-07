
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