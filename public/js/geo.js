var geoPosi;
var optionsGeo = {
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 0
  };
  
  function successGeo(pos) {
    geoPosi = pos;
  
    console.log('Votre position actuelle est :');
    console.log(`Latitude : ${geoPosi.coords.latitude}`);
    console.log(`Longitude : ${geoPosi.coords.longitude}`);
    console.log(`La précision est de ${geoPosi.coords.accuracy} mètres.`);
  }
  
  function errorGeo(err) {
    console.warn(`ERREUR (${err.code}): ${err.message}`);
    geoPosi=false;
  }
  
function getGeoInfos(){
    if(geoPosi)
        return {'lat':geoPosi.coords.latitude,'lng':geoPosi.coords.longitude,'pre':geoPosi.coords.accuracy,'t':geoPosi.timestamp};
    else
        return {};
}

navigator.geolocation.getCurrentPosition(successGeo, errorGeo, optionsGeo);