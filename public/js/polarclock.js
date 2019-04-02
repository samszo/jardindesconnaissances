//merci à http://bl.ocks.org/mbostock/1096355
class polarclock {
    constructor(params) {
        //propriétés génériques
        var me = this;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.svg = d3.select("#"+params.idSvg);
        this.width = params.width ? params.width : this.svg.attr("width");
        this.height = params.height ? params.height : svg.attr("height");
        //propriétés spécifiques
        this.rayon = this.width < this.height ? this.width/2 : this.height/2,
        this.spacing = params.spacing ? params.spacing : .09;
        this.wArc = 10;
        this.rangeColors = params.rangeColors ? params.rangeColors : ["hsl(0,100%,8%)", "hsl(360,100%,64%)"];
        this.fctGetGrad = params.fctGetGrad ? params.fctGetGrad : false;

        var formatSecond = d3.timeFormat("%-S seconds"),
            formatMinute = d3.timeFormat("%-M minutes"),
            formatHour = d3.timeFormat("%-H hours"),
            formatDay = d3.timeFormat("%A"),
            formatDate = function(d) { d = d.getDate(); switch (10 <= d && d <= 19 ? 10 : d % 10) { case 1: d += "st"; break; case 2: d += "nd"; break; case 3: d += "rd"; break; default: d += "th"; break; } return d; },
            formatMonth = d3.timeFormat("%B");        
        var color = d3.scaleLinear()
            .range(this.rangeColors)
            .interpolate(function(a, b) { var i = d3.interpolateString(a, b); return function(t) { return d3.hsl(i(t)); }; });
        var field,arcBody,arcCenter;

        this.init = function () {        
            me.gGlobal = me.svg.append('g')
                .attr('class','global')
                .attr('transform','translate('+me.width/2+','+me.height/2+')');


            arcBody = d3.arc()
                .startAngle(0)
                .endAngle(function(d) { return d.value * 2 * Math.PI; })
                .innerRadius(function(d) { return d.index * me.rayon; })
                .outerRadius(function(d) { 
                    return (d.index + me.spacing) * me.rayon; })
                .cornerRadius(6);
            
            arcCenter = d3.arc()
                .startAngle(0)
                .endAngle(function(d) { return d.value * 2 * Math.PI; })
                .innerRadius(function(d) { return (d.index + me.spacing / 2) * me.rayon; })
                .outerRadius(function(d) { return (d.index + me.spacing / 2) * me.rayon; });
            
            field = me.gGlobal.selectAll("g")
                .data(me.fields)
                .enter().append("g");
            
            field.append("path")
                .attr("class", "arc-body");
            
            field.append("path")
                .attr("id", function(d, i) { return "arc-center-" + i; })
                .attr("class", "arc-center");
            
            field.append("text")
                .attr("dy", ".35em")
                .attr("dx", ".75em")
                .style("text-anchor", "start")
              .append("textPath")
                .attr("startOffset", "50%")
                .attr("class", "arc-text")
                .attr("xlink:href", function(d, i) { return "#arc-center-" + i; });
            
            me.tick();
        }
            
            
        this.tick = function() {
            if (!document.hidden) field
                .each(function(d) { this._value = d.value; })
                .data(me.fields)
                .each(function(d) { d.previousValue = this._value; })
            .transition()
                .ease(d3.easeElastic)
                .duration(500)
                .each(me.fieldTransition);
        
            setTimeout(me.tick, 1000 - Date.now() % 1000);
        }
            
        this.fieldTransition = function () {
              var field = d3.select(this).transition();            
              field.select(".arc-body")
                  .attrTween("d", me.arcTween(arcBody))
                  .style("fill", function(d) { return color(d.value); });
            
              field.select(".arc-center")
                  .attrTween("d", me.arcTween(arcCenter));
           
              field.select(".arc-text")
                  .text(function(d) { return d.text; });
            }
            
        this.arcTween = function(arc) {
            return function(d) {
            var i = d3.interpolateNumber(d.previousValue, d.value);
            return function(t) {
                d.value = i(t);
                return arc(d);
            };
            };
        }
            
        this.fields = function () {
            var now = new Date, startIndex = .9;
            return [
                {index: startIndex, text: formatSecond(now), value: now.getSeconds() / 60},
                {index: startIndex-me.spacing, text: formatMinute(now), value: now.getMinutes() / 60},
                {index: startIndex-(me.spacing*2), text: formatHour(now),   value: now.getHours() / 24},
                //{index: .3, text: formatDay(now),    value: now.getDay() / 7},
                //{index: .2, text: formatDate(now),   value: (now.getDate() - 1) / (32 - new Date(now.getYear(), now.getMonth(), 32).getDate())},
                //{index: .1, text: formatMonth(now),  value: now.getMonth() / 12}
            ];
        }

        this.getArcWidth = function(){
            //vérifier le rendu du svg avant de pouvoir calculer
            me.gGlobal.select(".arc-body").call(function(d){
                var bb = d.node().getBBox();
                console.log(d);
            });
        }

        this.getInstantColors = function(){
            let ic = {'nom':'InstantColors',colors:[]};
            me.gGlobal.selectAll(".arc-body").each(function(n){
                let v = d3.select(this);
                ic.colors.push(v.style('fill'));
                ic.nom += '_'+n.text.replace(' ','-');
            });
            return ic;
        }

        this.init();

    }
}
