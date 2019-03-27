//merci à http://hassansin.github.io/chronograph-stopwatch-with-d3js
class audiobalayage {
    constructor(params) {
        var me = this;
        this.data = [];
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.svg = d3.select("#"+params.idSvg);
        this.margin = params.margin ? params.margin : {top: 20, right: 20, bottom: 20, left: 20};
        this.width = params.width ? params.width : this.svg.attr("width");
        this.height = params.height ? params.height : svg.attr("height");
        this.rayon = this.width < this.height ? this.width/2 : this.height/2,
        // Scales
        this.scale = d3.scaleLinear()
            .range([0, 360]),
        this.scaleMarkersDomains = params.scaleDomains ? params.scaleDomains : [[0,100],[0, 10],[0,5]];
        this.scaleLabelDomain = params.scaleLabelDomain ? params.scaleLabelDomain : [0,60,5];
        //marker settings
        this.markers = params.markers ? params.markers : [{width: 1,height: 10,color: 'green'},{width: 10,height: 10,color: 'yellow'},{width: 5,height: 5,color: 'red'}];
        //marker labels
        this.labels = {color: '#666',font: 14},
        //needles
        this.needles = {width: 2,length: this.rayon,wheelRadius: 8,wheelStroke: 2,color: '#607D8B'};    
        gGlobal = svg.append('g')
            .attr('class','total')
            .attr('transform','translate('+margin.left+','+margin.top+')');
        gGlobal.append('circle').attr('fill',"none")
            .attr('cx',width/2)
            .attr('cy',height/2)
            .attr('r',rayon)
            .attr('stroke',"black")
            .attr('stroke-width',1);        
        var gMarkers = gGlobal.append('g')
            .attr('class','markers')
            .attr('transform','translate('+ (width/2) +','+ (height/2) +')');
        gMarkers.selectAll('.cent-marker').data(d3.range(0,nbSecond))
            .enter().append('rect')
                .attr('x',-markers.cent.width/2)
                .attr('y', - rayon + 2 - markers.cent.height)
                .attr('width', markers.cent.width)
                .attr('height',markers.cent.height)
                .attr('fill', markers.cent.color)
                .attr('transform', function(d){
                    return 'rotate('+ centMarkerScale(d) +')';
                    })
                .attr('class','cent-marker');
        gMarkers.selectAll('.dix-marker').data(d3.range(0,10))
            .enter().append('rect')
            .attr('x',-markers.dix.width/2)
                .attr('y', - rayon + 2 - markers.dix.height)
                .attr('width', markers.dix.width)
                .attr('height',markers.dix.height)
                .attr('fill', markers.dix.color)
                .attr('transform', function(d){
                    return 'rotate('+ dixMarkerScale(d) +')';
                    })
                .attr('class','dix-marker');
        gMarkers.selectAll('.cinq-marker').data(d3.range(0,5))
            .enter().append('rect')
            .attr('x',-markers.cinq.width/2)
                .attr('y', - rayon + 2 - markers.cinq.height)
                .attr('width', markers.cinq.width)
                .attr('height',markers.cinq.height)
                .attr('fill', markers.cinq.color)
                .attr('transform', function(d){
                    return 'rotate('+ cinqMarkerScale(d) +')';
                    })
                .attr('class','cinq-marker');
        var lineFun = d3.line()
            .x(function(d){
                return d[0];})
            .y(function(d){return d[1];})
            .curve(d3.curveLinear);
        var gTrot = gGlobal.append('g')
            .attr('class','primary')
            .attr('transform','translate('+ (width/2) +','+ (height/2) +')');
    
    
    //draw needle
    function drawNeedle(g, data){
    
        g.needle = g.append('g');
        // Needle Shape
        let dt = needleData(data);
        g.needle.append('path')
            .attr('d',lineFun(dt))
            .attr('class','needle')
            .attr('fill', data.color)
            .attr('stroke-width', 0);
    
        // Needle Wheel
        g.needle.append('circle')
            .attr('r',data.wheelRadius)
            .attr('fill',"#fff")
            .attr('stroke',data.color)
            .attr("stroke-width",data.wheelStroke);
    
        // cercle de sélection
        g.needle.append('circle')
            .attr('r',data.wheelRadius*2)
            .attr('cx',0)
            .attr('cy',-rayon)
            .attr('fill',"red")
            .attr('stroke',data.color)
            .attr("stroke-width",data.wheelStroke)
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended));
        
    }
    
    // Fonction pour l'event "drag" d3js
    var onDrag = true, svgDrag;
    function dragstarted() {
        setSvgDrag(d3.mouse(this)[0]);
        onDrag = true;
    }
    
    function dragged() {
        var posi = d3.mouse(gGlobal.node());
        svgDrag.attr("cx", posi[0]).attr("cy", posi[1]);
    }
    
    function dragended() {
        console.log(d3.mouse(this)[0]);
    }
    
    function setSvgDrag(p){
        svgDrag = gGlobal.append("circle")
            .attr('r',10)
            .attr('cx',p[0])
            .attr('cy',p[0])
            .attr('fill',"green")
            .attr('stroke','black')
            .attr("stroke-width",'3');
    }		
    
    //data for needle shape
    function needleData(data){
      var wa = data.width,
          wb = data.width*3,
          lb = data.wheelRadius + 5,
          la = data.length - lb;
    
      return [
            [wb/2, lb],
            [wb/2, -lb],
            [wa/2,-lb],
            [wa/2,-la-lb],
            [-wa/2,-la-lb],
            [-wa/2,-lb],
            [-wb/2,-lb],
            [-wb/2,lb]
          ];
    }
    
    // update needle angle
    function updateNeedle(needle, angle, transition){
        transition = transition || 0;
        needle.transition()
            .duration(transition)
            .ease(d3.easeQuadOut)
            .attr('transform',"rotate("+ angle +")");
    }
    // Timer variables
    var timeoutHandle, now, startTime, isStarted = false, elapsedTime = 0;
    var clock = typeof performance === "object" ? performance: Date;
    
    // Toggle timer state
    function toggleTimer(){
      isStarted = !isStarted;
      if(isStarted){
        startTime = clock.now();
        tick();
      }else {
        clearTimeout(timeoutHandle);
      }
    }
    
    function tick(){
      now = clock.now();
      elapsedTime = elapsedTime + now - startTime;
      startTime = now;
    
      var ms = elapsedTime/10,
          seconds = ms/100,
          minutes = seconds/60;
    
      updateNeedle(gTrot.needle, centMarkerScale(seconds));
      timeoutHandle = setTimeout(tick,0);
    }
    
    drawNeedle(gTrot, needles);
    toggleTimer();

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
