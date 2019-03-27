class cartoaxes {
    constructor(params) {
        var me = this;
        this.data = [];
        this.structure = params.structure ? params.structure : [{'lbl':'clair','posi':0},{'lbl':'obscur','posi':180},{'lbl':'pertinent','posi':90},{'lbl':'inadapté','posi':270}];
        this.urlData = params.urlData ? params.urlData : "../data/quatreaxes.json";
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
            me.g.append("rect")
            .attr("width", me.domainWidth)
            .attr("height", me.domainHeight)
            .attr("fill", "rgba(96, 177, 156, 0.34)")
            .on('mousemove',function(e){
                console.log(d3.mouse(this)[0]);
                console.log(me.x.invert(d3.mouse(this)[0]));
                console.log(me.y.invert(d3.mouse(this)[1]));
            });
            if (typeof patienter !== 'undefined') 
                patienter('Chargement des données');

            $.post(me.urlData, {}, function (data) {
                me.data = data;
                me.draw();
                if(me.fctCallBackInit)me.fctCallBackInit();
            }, "json")
                .fail(function (e) {
                    throw new Error("Donnée introuvable : "+e);
                })
                .always(function () {
                    if (typeof patienter !== 'undefined') 
                        patienter('', true);
                });
        };
    
        this.draw = function () {
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
              //
            me.g.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + me.y.range()[0] / 2 + ")")
                .call(d3.axisBottom(me.x).ticks(10));
          
            me.g.append("g")
                .attr("class", "y axis")
                .attr("transform", "translate(" + me.x.range()[1] / 2 + ", 0)")
                .call(d3.axisLeft(me.y).ticks(10));
          
            //ajoute les titre d'axes
            me.g.selectAll(".txtTitreAxe")
                .data(me.structure)
              .enter().append("text")
                .attr("class", '.txtTitreAxe')
                .attr("transform", function(d){
                  let t = "rotate(0)";
                  //if(d.posi=='0' || d.posi=='180' ) t = "rotate(-90)";        
                  return t;
                })
                .attr("y", function (d) {
                    if (d.posi == '0') return 0;
                    if (d.posi == '90') return (me.domainHeight / 2)+10;
                    if(d.posi == '270') return (me.domainHeight / 2)-30;
                    if (d.posi == '180') return me.domainHeight;
                })
                .attr("x", function (d) {
                    if (d.posi == '0') return (me.domainWidth / 2)+10;
                    if (d.posi == '180') return (me.domainWidth / 2)-10;
                    if (d.posi == '90') return me.domainWidth;
                    if (d.posi == '270') return 0;
                })
                .attr("text-anchor", function (d) {
                    if (d.posi == '0' || d.posi == '270') return 'start';
                    if (d.posi == '90') return 'end';
                    if (d.posi == '180') return 'end';
                })
                .attr("dy", function (d) {
                    if (d.posi == '0' || d.posi == '90' || d.posi == '270') return '1em';
                    if (d.posi == '180') return '-1em';
                })
                .text(function(d){
                    return d.lbl;
                });       
        };

      
        function padExtent (e, p) {
            if (p === undefined) p = 1;
            return ([e[0] - p, e[1] + p]);
        }

        this.init();
    }
}


  
