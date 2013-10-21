
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
                    }
                }
            });
        }
        return false;
    });
    

    $('#connexion').on('submit', function() {
    	 
        var login = $('#login').val();
        var mdp = $('#password').val();
 
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
                    }
                }
            });
        }
        return false;
    });
    
    
    
});