class streamgraph {
    //merci beaucoup à https://observablehq.com/@d3/streamgraph
    /*utilise :
    d3.js pour data driving document
    moment.js pour gérer les dates
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
        this.data = []; 

        var svg, container, series, margin, color, x, y, area, xAxis, yAxis, keys, extreme, formatTemps;

        this.init = function () {
            
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
                    //format le temps en date
                    dt.forEach(d => d.date = moment(d.temps, me.tempsFormat));
                    keys = Array.from(new Set(dt.map(d => d.key)));       
                    extreme = d3.extent(dt, d => d.date);
                    //construction des regroupement
                    let nested_data = d3.nest()
                        .key(d => d.temps)
                        .entries(dt);
                    let mqpdata = nested_data.map(function(d){
                        let obj = {
                          date: d.values[0].date      
                        }                        
                        d.values.forEach(function(v){
                          obj[v.key] = v.value;
                          obj.k = v.key+'-'+d.key.valueOf();		  	      
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

            color = d3.scaleOrdinal()
                .domain(keys)
                .range(d3.schemeCategory10);

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
                                            
            xAxis = g => g
                .attr("transform", `translate(0,${me.height - margin.bottom})`)
                .call(d3.axisBottom(x).ticks(me.width / 80).tickSizeOuter(0))
                .call(g => g.select(".domain").remove());
            yAxis= g => g
                .attr("transform", `translate(${30 + margin.left},0)`)
                .call(d3.axisLeft(y).ticks(me.height / 80))
                .call(g => g.select(".domain").remove());

            svg = me.cont.append("svg")
                .attr("width", me.width+'px').attr("height", me.height+'px')
                .attr("viewBox", [0, 0, me.width, me.height]);
            
            container = svg.append("g")
                .selectAll("path")
                .data(series)
                .join("path")
                .attr("fill", ({key}) => color(key))
                .attr("d", area)
                .append("title")
                .text(({key}) => key);
            
            if(me.orientation=='vertical')
                svg.append("g").call(yAxis);
            else
                svg.append("g").call(xAxis);
        }

        this.hide = function(){
          svg.attr('visibility',"hidden");
        }

        this.show = function(){
          svg.attr('visibility',"visible");
        }
        

        function tempsToDate(temps) {
            var dfy, dRef, arrTemps = temps.split('-')
            formatTemps = arrTemps.length;
            if(arrTemps.length==3){
                //vérifie la présence d'heure
                var arrTemps2 = arrTemps[2].split(' ');
                if(arrTemps2.length==1) dRef = [arrTemps[0], parseInt(arrTemps[1])-1, arrTemps[2], 0, 0, 0];
                else{
                    var arrTemps3 = arrTemps2[1].split(':');
                    formatTemps += arrTemps3.length;				
                    if(arrTemps3.length==1) dRef = [arrTemps[0], parseInt(arrTemps[1])-1, arrTemps2[0], arrTemps2[1], 0, 0];
                    if(arrTemps3.length==2) dRef = [arrTemps[0], parseInt(arrTemps[1])-1, arrTemps2[0], arrTemps3[0], arrTemps3[1], 0];		  	 
                    if(arrTemps3.length==3) dRef = [arrTemps[0], parseInt(arrTemps[1])-1, arrTemps2[0], arrTemps3[0], arrTemps3[1], arrTemps3[2]];		  	 
                }
            }
            if(arrTemps.length==2) dRef = [arrTemps[0], parseInt(arrTemps[1])-1, 1, 0, 0, 0];
            if(arrTemps.length==1) dRef = [arrTemps[0], 0, 1, 0, 0, 0];
            dfy = new Date(dRef[0], dRef[1], dRef[2], dRef[3], dRef[4], dRef[5]);
            dfy.setFullYear(dRef[0]);
              
            return dfy 
        }
        function dateToTemps(dt) {
            if(formatTemps==6) return Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDay(), dt.getHours(), dt.getMinutes(), dt.getSeconds());
            if(formatTemps==5) return Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDay(), dt.getHours(), dt.getMinutes(),0);
            if(formatTemps==4) return Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDay(), dt.getHours(),0, 0);
            if(formatTemps==3) return Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDay(),0,0,0);
            if(formatTemps==2) return Date.UTC(dt.getFullYear(), dt.getMonth(), 1,0,0,0);
            if(formatTemps==1) return Date.UTC(dt.getFullYear(), 0, 1,0,0,0);	    			
        }
    
        me.init();

    }
}

  


