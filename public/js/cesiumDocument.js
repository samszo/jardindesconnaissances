class cesiumDocument {
    constructor(params) {
        var me = this;
        this.data = [];
        this.entities = params.entities;
        this.id = params.id ? params.id : '1';
        this.label = params.label ? params.label : 'Document';
        this.urlImg = params.urlImg ? params.urlImg : false;
        this.urlData = params.urlData ? params.urlData : false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        //géographie par défaut = P8
        this.lng = params.lng ? params.lng : 2.3613169;
        this.lat = params.lat ? params.lat : 48.9449133;    
        this.width = params.width ? params.width : 100;
        this.height = params.height ? params.height : 100;    
        this.altitude = params.altitude ? params.altitude : 0;
        
        var posiCenter = Cesium.Cartesian3.fromDegrees(this.lng,this.lat, this.altitude);
        
        this.init = function () {

            me.drawImage();
           
        };
        

        // Fonction pour créer les bulles
        this.drawImage = function() {
            
            //ajoute l'image du document'
            entities.add({
                //position : Cesium.Cartesian3.fromDegrees(cW, cS),
                position : me.posiCenter,
                billboard : {
                    image : me.urlImg,
                    //width : s.img.width,
                    //height : s.img.height
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


  
