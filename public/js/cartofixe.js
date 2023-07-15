function cartofixe(config) {

	this.idCont = config.idCont;  
	this.user = config.user; 
	this.zoom = config.zoom; 
	this.size = config.size; 
	this.style = config.style; 
	this.h = config.h; 
	this.posi=""; 
	this.geoname=""; 

	this.cartofixe = function() {
		
		var ts = this;
		//gestion cartographique
		var userGeoname = ts.user;

		if (navigator.geolocation) {
	        navigator.geolocation.getCurrentPosition(showPosition, showError);
	    } else {
		    	showMessage("La géolocation n'est pas supportée par votre navigateur.");
	    }
		
		function showPosition(position) {
			ts.posi = position;
			//récupère le geoname
			var p = {"lat":position.coords.latitude, "lng":position.coords.longitude, "username":userGeoname};
			$.post("http://api.geonames.org/findNearbyJSON", p,
					function(data){
						ts.geoname = data.geonames[0];
					 }, "json");	
			//récupère l'image de la carte		
		    var latlon = position.coords.latitude + "," + position.coords.longitude;
		    //pour faire les styles = http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html
		    var img_url = "http://maps.googleapis.com/maps/api/staticmap?center="
		    +latlon+"&zoom="+ts.zoom+"&size="+ts.size+"&markers=color:black%7Clabel:Vous+%c3%aates+ici%7C"+latlon+"&sensor=false&format=png&maptype=roadmap&style="+ts.style;
		    document.getElementById(ts.idCont).setAttribute("xlink:href",img_url);
		}		

		function showError(error) {
		    switch(error.code) {
		        case error.PERMISSION_DENIED:
		        		showMessage("User denied the request for Geolocation.");
		            break;
		        case error.POSITION_UNAVAILABLE:
		        		showMessage("Location information is unavailable.");
		            break;
		        case error.TIMEOUT:
		        	showMessage("The request to get user location timed out.");
		            break;
		        case error.UNKNOWN_ERROR:
		        		showMessage("An unknown error occurred.");
		            break;
		    }
		}		
		//fin cartographie
  };
  return this.cartofixe();
}
