class cartoaxes {
    constructor(params) {
        var me = this;
        this.data = [];
        this.structure = params.structure ? params.structure : [{'lbl':'clair','posi':0},{'lbl':'obscur','posi':180},{'lbl':'pertinent','posi':90},{'lbl':'inadapté','posi':270}];
        this.urlData = params.urlData ? params.urlData : false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.svg = d3.select("#"+params.idSvg),
        this.margin = params.margin ? params.margin : {top: 20, right: 20, bottom: 20, left: 20},
        this.width = params.width ? params.width : this.svg.attr("width"),
        this.height = params.height ? params.height : svg.attr("height"),
        this.domainWidth = this.width - this.margin.left - this.margin.right,
        this.domainHeight = this.height - this.margin.top - this.margin.bottom;
        this.x = params.x ? params.x : 0;          
        this.y = params.y ? params.y : 0;          
        this.xMin = params.xMin ? params.xMin : -100;          
        this.xMax = params.xMax ? params.xMax : 100;          
        this.yMin = params.yMin ? params.yMin : -100;          
        this.yMax = params.yMax ? params.yMax : 100;
        this.colorFond = params.colorFond ? params.colorFond : "transparent";
        this.tick = params.tick ? params.tick : 0;
        this.rayons = d3.range(params.nbRayon ? params.nbRayon : 5); // Création d'un tableau pour les rayons des cercles
        this.fctGetGrad = params.fctGetGrad ? params.fctGetGrad : false;
        this.fctSavePosi = params.fctSavePosi ? params.fctSavePosi : false;
        this.idDoc = params.idDoc ? params.idDoc : false;
        var scCircle = d3.scalePoint()
            .domain(this.rayons)
            .range([0, this.domainWidth < this.domainHeight ? this.domainWidth / 2 : this.domainHeight / 2]);
        var svgDefs;
        //drag variables
        var onDrag = true, svgDrag;
        // Timer variables

        
        this.transform = params.transform ? params.transform : "translate(" + (this.x+this.margin.left) + "," + (this.y+this.margin.top) + ")";          
        this.x = d3.scaleLinear()
            .domain(padExtent([this.xMin,this.xMax]))
            .range(padExtent([0, this.domainWidth]));
        this.y = d3.scaleLinear()
            .domain(padExtent([this.yMin,this.yMax]))
            .range(padExtent([this.domainHeight, 0]));
        this.g = svg.append("g")
                .attr("class", "cartoaxes")
                .attr("transform", this.transform);

        this.init = function () {

            //ajoute une balise def pour les dégradés
            svgDefs = me.g.append('defs');

            me.g.append("rect")
            .attr("width", me.domainWidth)
            .attr("height", me.domainHeight)
            .attr("fill", me.colorFond)
                .on('mousemove',function(e){
                    /*
                    console.log(d3.mouse(this)[0]);
                    console.log(me.x.invert(d3.mouse(this)[0]));
                    console.log(me.y.invert(d3.mouse(this)[1]));
                    */
                });                
            
            me.drawAxes();
            me.drawCible();

           
        };

        // Fonction pour créer la cible
        this.drawCible = function(d) {
            //ajoute les cercles concentriques
            //le cercle 1 sert de curseur d'intensité
            me.g.selectAll('.cFond').data(this.rayons).enter().append('circle') // Création des cercles + attributs
                .attr('class', 'cFond')
                .attr('id', function (d, i) {
                    return 'orbit' + i;
                })
                .attr('r', function (d) {
                    let r = d == 0 ? 0 : d == 1 ? scCircle(d)/2 : scCircle(d);
                    return r;
                })
                .attr('cx', me.domainWidth / 2)
                .attr('cy', me.domainHeight / 2)
                .call(d3.drag()
                    .container(me.g.node())
                    .on("start", me.dragstarted)
                    .on("drag", me.dragged)
                    .on("end", me.dragended));
        }

        // Fonction pour créer les axes
        this.drawAxes = function(d) {
            if(me.structure.length==4){
                me.g.append("g")
                    .attr("class", "y axis")
                    .attr("transform", "translate(" + me.x.range()[1] / 2 + ", 0)")
                    .call(d3.axisLeft(me.y).ticks(me.tick));
            }
            me.g.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + me.y.range()[0] / 2 + ")")
                .call(d3.axisBottom(me.x).ticks(me.tick));    
    
    
            //ajoute les titre d'axes
            me.g.selectAll(".txtTitreAxe")
                .data(me.structure)
                .enter().append("text")
                .attr("class", function(d){
                    return 'txtTitreAxe'+d.posi;
                })
                .attr("transform", function(d){
                    let t = "rotate(0)";
                    //if(d.posi=='0' || d.posi=='180' ) t = "rotate(-90)";        
                    return t;
                })
                .attr("y", function (d) {
                    if (d.posi == '0') return 0;
                    if (d.posi == '90' ) return (me.domainHeight / 2)+10;
                    if(d.posi == '270' ) return (me.domainHeight / 2)-30;
                    if (d.posi == '180') return me.domainHeight;
                    if (d.posi == 'haut') return (me.domainWidth / 2)-30;
                    if (d.posi == 'bas') return (me.domainWidth / 2)+30;
                })
                .attr("x", function (d) {
                    if (d.posi == '0' ) return (me.domainWidth / 2)+10;
                    if (d.posi == '180' ) return (me.domainWidth / 2)-10;
                    if (d.posi == '90') return me.domainWidth;
                    if (d.posi == '270') return 0;
                    if (d.posi == 'haut' || d.posi == 'bas') return (me.domainWidth / 2);
                })
                .attr("text-anchor", function (d) {
                    if (d.posi == '0') return 'start';
                    if (d.posi == '270') return 'start';
                    if (d.posi == '90') return 'end';
                    if (d.posi == '180') return 'end';
                    if (d.posi == 'haut' || d.posi == 'bas') return 'middle';

                })
                .attr("dy", function (d) {
                    if (d.posi == '0' || d.posi == '90' || d.posi == '270') return '1em';
                    if (d.posi == '180') return '-1em';
                })
                .text(function(d){
                    return d.lbl;
                });     
        }

        // Fonction pour l'event "drag" d3js
        this.dragstarted = function(d) {
            //on ne peut déplacer que le cercle 1
            if(d!=1)return;
            me.setSvgDrag([d3.event.x,d3.event.y]);
            me.onDrag = true;
        }

        this.dragged = function() {
            //console.log(me.domainWidth+','+me.domainHeight+' : '+d3.event.x+','+d3.event.y);
            if(d3.event.x < me.domainWidth && d3.event.x > 0 && d3.event.y < me.domainHeight && d3.event.y > 0)
                me.svgDrag.attr("cx", d3.event.x).attr("cy", d3.event.y);
        }

        this.dragended = function() {
            //récupère les données du points
            let posi = d3.mouse(this);
            let r = {'x':posi[0],'y':posi[1]
                ,'numX':me.x.invert(posi[0]),'numY':me.y.invert(posi[1])
                ,'structure':me.structure
                ,'id':me.idDoc
                };
            console.log(r);
            if(me.fctSavePosi)me.fctSavePosi(r);
        }

        this.setSvgDrag = function(p){
            //console.log(p);
            let c = me.getGradient();
            me.svgDrag = me.g.append("circle")
                .attr('class','sltDrag')
                .attr('r',scCircle.step()/3)
                .attr('cx',p[0])
                .attr('cy',p[1])
                .attr('fill',c)
                .attr('stroke','black')
                .attr("stroke-width",'1');
        }		
        
        this.drawData = function () {
            if (typeof patienter !== 'undefined') 
                patienter('Chargement des données');
            if(me.urlData){
                $.post(me.urlData, {}, function (data) {
                    me.data = data;
                    //me.drawData();
                    if(me.fctCallBackInit)me.fctCallBackInit();
                }, "json")
                    .fail(function (e) {
                        throw new Error("Donnée introuvable : "+e);
                    })
                    .always(function () {
                        if (typeof patienter !== 'undefined') 
                            patienter('', true);
                    });
        
                me.data.forEach(function(d) {
                    d.consequence = +d.consequence;
                    d.value = + d.value;
                });
                //    
                me.g.selectAll("circle")
                    .data(me.data)
                  .enter().append("circle")
                    .attr("class", "dot")
                    .attr("r", 7)
                    .attr("cx", function(d) { return me.x(d.consequence); })
                    .attr("cy", function(d) { return me.y(d.value); })
                      .style("fill", function(d) {        
                        if (d.value >= 3 && d.consequence <= 3) {return "#60B19C"} // Top Left
                        else if (d.value >= 3 && d.consequence >= 3) {return "#8EC9DC"} // Top Right
                        else if (d.value <= 3 && d.consequence >= 3) {return "#D06B47"} // Bottom Left
                        else { return "#A72D73" } //Bottom Right         
                    });  
            }

        };

      
        function padExtent (e, p) {
            if (p === undefined) p = 1;
            return ([e[0] - p, e[1] + p]);
        }

        this.getGradient = function(){
            if(!me.fctGetGrad)return 'white';
            let degrad = me.fctGetGrad();
            //pas besoin de vérifier que le dégrader existe puisqu'il est lié à l'instant

            var radialG = svgDefs.append('radialGradient')
                .attr('id', degrad.nom);
                                                
            // Create the stops of the main gradient. Each stop will be assigned
            radialG.selectAll('stop').data(degrad.colors).enter().append('stop')
                .attr('stop-color', function(d){
                    return d;
                })
                .attr('offset', function(d,i){
                    let pc = 100/degrad.colors.length*i;
                    return pc+'%';
                });
            return "url(#"+degrad.nom+")";            
        }

        this.init();
    }
}


  
