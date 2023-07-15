//merci à http://hassansin.github.io/chronograph-stopwatch-with-d3js
class balayage {
    constructor(params) {
        var me = this;
        this.nbSecond = 100;
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
        this.needleParam = {width: 2,length: this.rayon,wheelRadius: 8,wheelStroke: 2,color: '#607D8B'};
        this.needle;    
        this.gGlobal, this.gMarkers, this.lineFun, this.gTrot;
        //drag variables
        var onDrag = true, svgDrag;
        // Timer variables
        var timeoutHandle, now, startTime, isStarted = false, elapsedTime = 0;
        var clock = typeof performance === "object" ? performance: Date;


        this.init = function () {        
            me.gGlobal = me.svg.append('g')
                .attr('class','total')
                .attr('transform','translate('+me.margin.left+','+me.margin.top+')');
            me.gGlobal.append('circle').attr('fill',"none")
                .attr('cx',me.width/2)
                .attr('cy',me.height/2)
                .attr('r',me.rayon)
                .attr('stroke',"black")
                .attr('stroke-width',1);        
            me.gMarkers = me.gGlobal.append('g')
                .attr('class','markers')
                .attr('transform','translate('+ (me.width/2) +','+ (me.height/2) +')');
            me.markers.forEach(function(m,i){
                m.scale = me.scale.copy().domain(me.scaleMarkersDomains[i]);    
                me.gMarkers.selectAll('.marker'+i).data(d3.range(0,me.scaleMarkersDomains[i][1]))
                .enter().append('rect')
                    .attr('x',-m.width/2)
                    .attr('y', - me.rayon + 2 - m.height)
                    .attr('width', m.width)
                    .attr('height',m.height)
                    .attr('fill', m.color)
                    .attr('transform', function(d){
                        return 'rotate('+ m.scale(d) +')';
                        })
                    .attr('class','.marker'+i);                
            });

            me.lineFun = d3.line()
                .x(function(d){
                    return d[0];})
                .y(function(d){return d[1];})
                .curve(d3.curveLinear);
            me.gTrot = me.gGlobal.append('g')
                .attr('class','primary')
                .attr('transform','translate('+ (me.width/2) +','+ (me.height/2) +')');

        }

        //draw needle
        this.drawNeedle = function (g, data){

            me.needle = g.append('g');
            // Needle Shape
            let dt = me.needleData(data);
            me.needle.append('path')
                .attr('d',me.lineFun(dt))
                .attr('class','needle')
                .attr('fill', data.color)
                .attr('stroke-width', 0);

            // Needle Wheel
            me.needle.append('circle')
                .attr('r',data.wheelRadius)
                .attr('fill',"#fff")
                .attr('stroke',data.color)
                .attr("stroke-width",data.wheelStroke);

            // cercle de sélection
            me.needle.append('circle')
                .attr('r',data.wheelRadius*2)
                .attr('cx',0)
                .attr('cy',-me.rayon)
                .attr('fill',"red")
                .attr('stroke',data.color)
                .attr("stroke-width",data.wheelStroke)
                .call(d3.drag()
                    .container(me.gGlobal.node())
                    .on("start", me.dragstarted)
                    .on("drag", me.dragged)
                    .on("end", me.dragended));
            
        }

        // Fonction pour l'event "drag" d3js
        this.dragstarted = function() {
            //me.setSvgDrag(d3.mouse(me.gGlobal.node()));
            me.setSvgDrag([d3.event.x,d3.event.y]);
            me.onDrag = true;
        }

        this.dragged = function() {
            //var posi = d3.mouse(me.gGlobal.node());
            var posi = [d3.event.x,d3.event.y];
            console.log(posi);
            me.svgDrag.attr("cx", posi[0]).attr("cy", posi[1]);
        }

        this.dragended = function() {
            console.log(d3.mouse(this));
        }

        this.setSvgDrag = function(p){
            console.log(p);
            me.svgDrag = me.gGlobal.append("circle")
                .attr('class','sltTime')
                .attr('r',10)
                .attr('cx',p[0])
                .attr('cy',p[1])
                .attr('fill',"green")
                .attr('stroke','black')
                .attr("stroke-width",'3');
        }		

        //data for needle shape
        this.needleData = function(data){
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
        this.updateNeedle = function (needle, angle, transition){
            transition = transition || 0;
            me.needle.transition()
                .duration(transition)
                .ease(d3.easeQuadOut)
                .attr('transform',"rotate("+ angle +")");
        }

        // Toggle timer state
        this.toggleTimer = function(){
            isStarted = ! isStarted;
            if(isStarted){
                startTime = clock.now();
                me.tick();
            }else {
                clearTimeout(timeoutHandle);
            }
        }

        this.tick = function (){
            now = clock.now();
            elapsedTime = elapsedTime + now - startTime;
            startTime = now;

            var ms = elapsedTime/10,
                seconds = ms/100,
                minutes = seconds/60;

            me.updateNeedle(me.needle, me.markers[0].scale(seconds));
            timeoutHandle = setTimeout(me.tick,0);
        }

        this.init();
        this.drawNeedle(this.gTrot, this.needleParam);
        this.toggleTimer();
    }
}
