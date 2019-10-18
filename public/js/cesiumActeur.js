class cesiumActeur {
    constructor(params) {
        var me = this;
        this.data = [];
        this.entities = params.entities;
        this.id = params.id ? params.id : '1';
        this.label = params.label ? params.label : 'Acteur';
        this.color = params.color ? params.color : Cesium.Color.fromRandom({
            alpha : 0.3
        });
        this.urlData = params.urlData ? params.urlData : false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        //géographie par défaut = P8
        this.lng = params.lng ? params.lng : 2.3613169;
        this.lat = params.lat ? params.lat : 48.9449133;    
        this.altitude = params.altitude ? params.altitude : 0;
        this.radius = params.radius ? params.radius : 1;
        
        var posiCenter = Cesium.Cartesian3.fromDegrees(this.lng,this.lat, this.altitude);
        
        this.init = function () {

            me.drawBulle();
           
        };
        

        // Fonction pour créer les bulles
        this.drawBulle = function() {
            
            //ajoute la bulle acteur
            //TODO:créer une membrane fullerénique cf. https://onlinelibrary.wiley.com/doi/pdf/10.1002/wcms.1207

            let bulle = me.entities.add( {
                name : me.id + ' ' + me.label,
                position: posiCenter,
                ellipsoid : {
                    radii : new Cesium.Cartesian3(me.radius, me.radius, me.radius),
                    outline : true,
                    material : me.color,
                    slicePartitions : 6,
                    stackPartitions : 2
            
                }
            });    
            var label = me.entities.add({
                position : posiCenter,
                label : {
                    // This callback updates the length to print each frame.
                    text: me.label,
                    font : '20px sans-serif',
                    //pixelOffset : new Cesium.Cartesian2(0.0, 20)
                }
            });

            
        }

        
        this.init();
    }
}


  
