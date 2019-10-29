class cesiumRapport {
    constructor(params) {
        var me = this;
        this.data = params.data ? params.data : [];
        this.entities = params.entities;
        this.label = params.label ? params.label : 'Rapport';
        this.id = params.id ? params.id : '1';
        this.urlData = params.urlData ? params.urlData : false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.color = params.color ? params.color : 'red';
        this.doc = params.doc;
        this.act = params.act;
        this.crb = params.crb;
        //géographie par défaut = P8
        this.lng = params.lng ? params.lng : 2.3613169;
        this.lat = params.lat ? params.lat : 48.9449133;    
        this.altitude = params.altitude ? params.altitude : 0;
        this.radius = params.radius ? params.radius : 2;
        this.minCribleSrc = params.minCribleSrc ? params.minCribleSrc : -200;
        this.maxCribleSrc = params.maxCribleSrc ? params.maxCribleSrc : 200;
        this.radiusCribleDst = params.radiusCribleDst ? params.radiusCribleDst : 6;
        this.posiLine = [];
        var posiCenter = Cesium.Cartesian3.fromDegrees(this.lng,this.lat, this.altitude);
        var sclX, sclY;

        this.init = function () {

            /*création des échelles
            sclX = d3.scaleLinear()
                .domain([me.data.detail.ratingScaleMin,me.data.detail.ratingScaleMax])
                .range([0, me.crb.oCesium.radius]);
            sclY = d3.scaleLinear()
                .domain([me.data.detail.ratingScaleMin,me.data.detail.ratingScaleMax])
                .range(padExtent([0, me.crb.oCesium.radius]));
            */
            //création du rapport entre le document, l'acteur, le crible 
            // et la position du point de vue de l'acteur
            
            /*TODO:
            la taille de la ligne entre le centre et la position est proportionnelle 
            à la vitesse de traçage => récolter cette vitesse
            */  

            //calcul la position du pdv dans le crible à partir du crible d'expression
            let scaleCrible = d3.scaleLinear()
                .domain([me.minCribleSrc, me.maxCribleSrc])
                .range([0, me.radiusCribleDst]);
            let posiY = scaleCrible(parseFloat(me.data.details['jdc:y'][0]['@value']));
            let posiX = scaleCrible(parseFloat(me.data.details['jdc:x'][0]['@value']));
            let latPosiEval = me.crb.lat + posiY; 
            let lngPosiEval = me.crb.lng + posiX; 

            //calcul les position du rapports
            me.posiLine = [
                me.doc.lng, me.doc.lat, me.doc.altitude
                ,me.lng, me.lat, me.altitude
                ,me.crb.lng, me.crb.lat, me.crb.altitude
                ,lngPosiEval,latPosiEval, me.crb.altitude
            ];

            //création de la ligne
            me.drawLine();            

            //création de la position sémantique
            me.drawSemanticPosition();
           
        };

        // Fonction pour créer la ligne
        this.drawLine = function() {
                    
            /*création des lignes de rapport*/
            var polyline = {
                name : me.id+' '+me.label,
                polyline : {
                    positions : Cesium.Cartesian3.fromDegreesArrayHeights(me.posiLine),
                    //arcType : new Cesium.ConstantProperty(Cesium.ArcType.NONE),
                    width : 10.0,
                    material : new Cesium.PolylineGlowMaterialProperty({
                        color : me.color,
                        //glowPower : 0.25
                    })
                }
            }
            me.entities.add(polyline);
            
        }

        //fonction pour créer la position sémantique
        this.drawSemanticPosition = function() {
                let cPosiCpt = Cesium.Cartesian3.fromDegrees(me.posiLine[9],me.posiLine[10],me.posiLine[11]);
                //Create a random bright color.
                let color = Cesium.Color.fromRandom({
                    alpha : 0.6
                });
                let bulle = me.entities.add( {
                    name : 'Semantic Position_'+me.id ,
                    position: cPosiCpt,
                    ellipsoid : {
                        radii : new Cesium.Cartesian3(20, 20, 20),
                        outline : false,
                        outlineColor : Cesium.Color.YELLOW,
                        material : color
                    }
                });    
                var label = me.entities.add({
                    position : cPosiCpt,
                    label : {
                        text: me.data.date,
                        font : '4px sans-serif',
                    }
                });

        }
        
        this.init();
    }
}


  
