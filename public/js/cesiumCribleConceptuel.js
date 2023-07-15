class cesiumCribleConceptuel {
    constructor(params) {
        var me = this;
        this.data = [];
        this.entities = params.entities;
        this.id = params.id ? params.id : '1';
        this.label = params.label ? params.label : 'Crible polygon';
        this.crible = params.crible ? params.crible : [
            {'label':'clair','id':'0','idP':'0'}
            ,{'label':'obscur','id':'1','idP':'0'}
            ,{'label':'pertinent','id':'2','idP':'0'}
            ,{'label':'inadapté','id':'3','idP':'0'}];
        this.urlData = params.urlData ? params.urlData : false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.inScheme = params.inScheme ? params.inScheme : false;
        //géographie par défaut = P8
        this.lng = params.lng ? params.lng : 2.3613169;
        this.lat = params.lat ? params.lat : 48.9449133;    
        this.altitude = params.altitude ? params.altitude : 0;
        this.radius = params.radius ? params.radius : 6;
        
        var posiCenter = Cesium.Cartesian3.fromDegrees(this.lng,this.lat, this.altitude);
        var nbConcept = this.crible.length;
        var angleSlice=Math.PI * 2 / nbConcept;
        var posiPoly = [], posiAxes=[];
        
        this.init = function () {

            //calcul les positions des points
            for (let index = 0; index < nbConcept; index++) {
                let ln = me.lng + (me.radius * Math.cos(angleSlice*index - Math.PI/2)); 
                //let ln = (me.radius * Math.cos(angleSlice*index - Math.PI/2)); 
                posiPoly.push(ln);
                let la = me.lat + (me.radius * Math.sin(angleSlice*index - Math.PI/2)); 
                //let la = (me.radius * Math.sin(angleSlice*index - Math.PI/2)); 
                posiPoly.push(la);        
                posiPoly.push(me.altitude);     
                posiAxes.push([me.lng, me.lat, me.altitude, ln, la, me.altitude]);   
            }
                  
            
            me.drawPolygone();
            me.drawAxes();
            me.drawBulles();
           
        };

        // Fonction pour créer le polygone
        this.drawPolygone = function() {
            //ajoute le polygone du crible
            var cribleP = me.entities.add({
                name : me.label,
                polygon : {
                    hierarchy : Cesium.Cartesian3.fromDegreesArrayHeights(posiPoly),
                    //extrudedHeight: 0,
                    perPositionHeight : true,
                    material : Cesium.Color.ORANGE.withAlpha(0.5),
                    outline : true,
                    outlineColor : Cesium.Color.BLACK,
                    arcType : Cesium.ArcType.GEODESIC            
                }
            });

        }

        // Fonction pour créer les axes
        this.drawAxes = function() {
            //construction des lignes d'axes
            for (let i = 0; i < nbConcept; i++) {                
                let ligneAxe = me.entities.add({
                    name : 'Axe '+i,
                    polyline : {
                        positions : Cesium.Cartesian3.fromDegreesArrayHeights(posiAxes[i]),
                        width : 12,
                        /*
                        material : new Cesium.PolylineOutlineMaterialProperty({
                            color : Cesium.Color.ORANGE,
                            outlineWidth : 3,
                            outlineColor : Cesium.Color.BLACK
                        })
                        */
                        arcType : Cesium.ArcType.GEODESIC,
                        material : new Cesium.PolylineArrowMaterialProperty(Cesium.Color.PURPLE)
                    }
                });
            }  

        }

        // Fonction pour créer les bulles
        this.drawBulles = function() {

            me.crible.forEach(function(cbl, i){
            
                //ajoute la bulle conceptuelle
                let cPosiCpt = Cesium.Cartesian3.fromDegrees(posiAxes[i][3],posiAxes[i][4],posiAxes[i][5]);
                let r = Cesium.Cartesian3.distance(posiCenter,cPosiCpt);
                //Create a random bright color.
                let color = Cesium.Color.fromRandom({
                    alpha : 0.3
                });
                let bulle = me.entities.add( {
                    name : cbl.id + ' ' + cbl.label,
                    position: cPosiCpt,
                    ellipsoid : {
                        radii : new Cesium.Cartesian3(r, r, r),
                        outline : false,
                        outlineColor : Cesium.Color.YELLOW,
                        material : color
                    }
                });    
                var label = me.entities.add({
                    position : cPosiCpt,
                    label : {
                        // This callback updates the length to print each frame.
                        text: cbl.label,
                        font : '20px sans-serif',
                        //pixelOffset : new Cesium.Cartesian2(0.0, 20)
                    }
                });

            })
            
        }

        
        this.init();
    }
}


  
