class streamgraph {
    //merci beaucoup à https://observablehq.com/@d3/streamgraph
    /*utilise :
    - d3.js v5 pour data driving document
    - moment.js pour gérer les dates
    */

    constructor(params) {
        var me = this;
        this.cont = d3.select("#"+params.idCont);
        this.width = params.width ? params.width : 400;
        this.height = params.height ? params.height : 400;
        this.dataUrl = params.dataUrl ? params.dataUrl : "../data/unemployement.csv";
        this.dataType = params.dataType ? params.dataType : "csv";
        this.tempsFormat = params.tempsFormat ? params.tempsFormat : "YYYY";
        this.orientation = params.orientation ? params.orientation : "horizon";
        this.data = [], this.dataGroup, this.dataOri; 
        this.svg = false;
        this.fctLoad = params.fctLoad ? params.fctLoad : false;
        this.container = false;

        var series, margin, color, colorInit, x, y, area, tempsAxis, axe, keys, extreme, tooltip
            , totalTemps = [], totalKey = []
            , colorFond = 'black', colorText = 'white';

        this.init = function () {
            
            //construction du svg principal
            me.svg = this.cont.append("svg")
                .attr('id', 'streamgraph')
                .style('position','absolute')
                .attr("width", me.width+'px').attr("height", me.height+'px');


            if(me.dataType=='csv'){
                d3.csv(me.dataUrl,d3.autoType)
                .then(function(dt) {
                    me.data = Object.assign(dt, {y: "data"}); 
                    keys = me.data.columns.slice(1);           
                    extreme = d3.extent(me.data, d => d.date);    
                    me.draw();  
                })
                .catch(function(error){
                    console.log(error);
                })            
            }            
            if(me.dataType=='json'){
                d3.json(me.dataUrl)
                .then(function(dt) {
                    //consruction des clefs
                    keys = Array.from(new Set(dt.map(d => d.key)));       
                    dt.forEach(d => {
                        //format le temps en date
                        d.date = moment(d.temps, me.tempsFormat);
                        //format le temps en date
                        d.value = parseInt(d.value);
                        //cumul la valeur pour le temps
                        totalTemps[d.temps] ? totalTemps[d.temps] = totalTemps[d.temps]+d.value : totalTemps[d.temps] = d.value;
                        //cumul la valeur pour la ref
                        totalKey[d.key] ? totalKey[d.key] = totalKey[d.key]+d.value : totalKey[d.key] = d.value;
                        //cumul les valeurs total
                        totalTemps['total'] ? totalTemps['total'] = totalTemps['total']+d.value : totalTemps['total'] = d.value;
                        totalKey['total'] ? totalKey['total'] = totalKey['total']+d.value : totalKey['total'] = d.value;
                    });
                    extreme = d3.extent(dt, d => d.date);
                    me.dataOri = dt;
                    //construction des regroupements
                    me.dataGroup = d3.nest()
                        .key(d => d.temps)
                        .entries(me.dataOri);
                    let mqpdata = me.dataGroup.map(function(d){
                        let obj = {
                          date: d.values[0].date      
                        }                        
                        d.values.forEach(function(v){
                          obj[v.key] = v.value;
                        })                        
                        return obj;
                    })
                    me.data = Object.assign(mqpdata, {y: "data"});                     
                    me.draw();  
                })
                .catch(function(error){
                    console.log(error);
                })            
            }            


        }

        this.draw = function(){

            margin = ({top: 0, right: 20, bottom: 30, left: 20});

            //color = d3.scaleOrdinal().domain(keys).range(d3.schemeCategory20);
            color = d3.scaleSequential().domain([0, keys.length]).interpolator(d3.interpolateSinebow);

            series = d3.stack()
                .keys(keys)
                .offset(d3.stackOffsetWiggle)
                .order(d3.stackOrderInsideOut)
                (me.data);


            if(me.orientation=='horizon'){
                x = d3.scaleUtc()
                    .domain(extreme)
                    .range([margin.left, me.width - margin.right]);
                y = d3.scaleLinear()
                    .domain([d3.min(series, d => d3.min(d, d => d[0])), d3.max(series, d => d3.max(d, d => d[1]))])
                    .range([me.height - margin.bottom, margin.top]);
                area = d3.area()
                    .x(d =>x(d.data.date))
                    .y0(d =>y(d[0]))
                    .y1(d => y(d[1]))
                    .curve(d3.curveBasis);

            }    
            if(me.orientation=='vertical'){
                y = d3.scaleUtc()
                    .domain([extreme[1],extreme[0]])
                    .range([margin.top, me.height - margin.bottom]);
                
                x = d3.scaleLinear()
                    .domain([d3.min(series, d => d3.min(d, d => d[0])), d3.max(series, d => d3.max(d, d => d[1]))])
                    .range([me.width - margin.right, margin.left]);    
                area = d3.area()
                    .y(d => {
                        let v = y(d.data.date);
                        return v;
                    })
                    .x0(d =>x(d[0]))
                    .x1(d =>x(d[1]))
                    .curve(d3.curveBasis);
            }
                                            
            //ajoute les fonctionnalités de zoom
            me.svg.call(
                d3.zoom()
                    //.scaleExtent([0, 4])
                    .on("zoom", zoomed)
            );

            //ajoute les couche du graph
            me.container = me.svg.append("g")
            //ajoute un fond noir
            me.container.append('rect').attr("width", me.width+'px').attr("height", me.height+'px').attr('fill',colorFond);
            //ajoute les couche du stream
            me.container.selectAll("path")
                .data(series)
                .join("path")
                .attr("fill", function(d){
                    return color(d.index);
                })
                .attr("d", area)
                .on('mouseover', function(d){
                    let p = d3.select(this);
                    colorInit = p.style("fill");      
                    p.style('fill',d3.rgb(colorInit).brighter());
                    tooltip.transition()
                       .duration(700)
                       .style("opacity", 1);
                })
                .on('mousemove', function(d){      
                    //console.log(d);
                    //récupère les datas liés à la position de la souris
                    var dt = getRef(d3.mouse(this),y,d);
                    getTooltip(d, dt);
                })
                .on('mouseout', function(d){      
                    d3.select(this).style('fill',d3.rgb(colorInit));
                    tooltip.transition()
                           .duration(500)
                           .style("opacity", 0);
                });
            
            /*construction de l'axe temporel suivant l'orientation*/
            if(me.orientation!='vertical'){
                tempsAxis = d3.axisBottom(x);
                axe = me.svg.append("g").style('stroke',colorText).style('fill',colorText).call(tempsAxis);
            }else{
                tempsAxis = d3.axisRight(y);
                axe = me.svg.append("g").style('stroke',colorText).style('fill',colorText).call(tempsAxis);
            }

            //ajout du tooltip
            tooltip = d3.select("body").append("div")
                .attr("class", "tooltip")
                .style('position','absolute')
                //.style('width','300px')
                //.style('height','70px')
                .style('padding','4px')
                .style('background-color',colorFond)
                .style('color',colorText)
                .style('pointer-events','none');
    
            //execute la fonction de fin de construction
            if(me.fctLoad)me.fctLoad();            

        }

        this.hide = function(){
          me.svg.attr('visibility',"hidden");
        }

        this.show = function(){
          me.svg.attr('visibility',"visible");
        }
        

        function zoomed() {
            me.container.attr("transform", d3.event.transform);
            //svg.selectAll("path").attr("transform", d3.event.transform);
            if(me.orientation=='vertical')
                axe.call(tempsAxis.scale(d3.event.transform.rescaleY(y)));
            else
                axe.call(tempsAxis.scale(d3.event.transform.rescaleX(x)));
        }

        function getRef(mouse,y,d){
			//récupère les datas liés à la position de la souris
            let dRef = y.invert(mouse[1]);
            let t = moment(dRef).format(me.tempsFormat);
            let dt = me.dataOri.filter(o => o.key ==  d.key && o.temps == t);	    
			return dt[0];
	    }

        function getTooltip(d, dt){
            //calcule les élément du tooltip
            //if(totalTemps[dt.temps]==0)totalTemps[dt.temps] = 0.1;			
            var pcTemps = totalTemps[dt.temps]/totalTemps['total']*100;	    	
            var pcKey = totalKey[d.key]/totalKey['total']*100;	    	
            var pcKeyTemps = Math.trunc(dt.value)/totalTemps[dt.temps]*100;	    	
            tooltip.html("<h3>"+dt.type+"</h3>"
                    //+"Nb document :<br/>"
                    +"Total couche = "+totalKey[d.key]+" = "+pcKey.toFixed(2)+" %<br/>"
                    +"Total "+dt.temps+" = "+totalTemps[dt.temps]+" = "+pcTemps.toFixed(2)+" %<br/>"
                    +"Total couche "+dt.temps+" = "+Math.trunc(dt.value)+" = "+pcKeyTemps.toFixed(2)+" %<br/>"
                    )
                .style("left", (d3.event.pageX + 12) + "px")
                .style("top", (d3.event.pageY - 28) + "px");
	    }
        
        me.init();

    }
}

  


