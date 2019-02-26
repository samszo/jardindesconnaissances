function iemlForce() {
    var margin = {top: 3, right: 20, bottom: 3, left: 20},
        width = 1200,
        height = 600,
        maxRep = 100,
        color = d3.scaleSequential().interpolator(d3['interpolateWarm']),
        rayon = d3.scaleLinear().domain([0, maxRep]).range([1, 100]),
        cyScale,
        simulation,
        svg,
        tooltip = d3.select("body").append("div")
          .attr("style", "opacity:0;background-color:white;padding:6px;position:absolute;width:200px;height:35px;pointer-events:none;border:black;border-style:solid;"),
        me = this;

    function chart(selection) {


        //création de la matrice à partir des data
        selection.each(function(data) {

            // Select the svg element, if it exists.
            svg = d3.select(this).append('svg')
                .attr("width", width)
                .attr("height", height)
                .append('g').attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

            /*ajoute la définition des flèches*/
            var def = svg.append('defs')
            def.append('marker')
                .attr('id','ArrowEnd')
                .attr('refX',0.0)
                .attr('refY',0.0)
                .attr('orient','auto')
                .style('overflow','visible')
                .append('svg:path')
                .attr('d', 'M 0.0,0.0 L 5.0,-5.0 L -12.5,0.0 L 5.0,5.0 L 0.0,0.0 z ')
                .attr('fill', 'black')
                .attr('transform', 'scale(0.8) rotate(180) translate(10,0)') 
                .style('stroke','none');
            def.append('marker')
                  .attr('id', 'BulleDeb')
                  .attr('markerHeight', 10)
                  .attr('markerWidth', 10)
                  .attr('markerUnits', 'strokeWidth')
                  .attr('orient', 'auto')
                  .attr('refX', 0)
                  .attr('refY', 0)
                  .attr('viewBox', '-6 -6 12 12')
                  .append('svg:path')
                    .attr('d', 'M 0, 0  m -5, 0  a 5,5 0 1,0 10,0  a 5,5 0 1,0 -10,0')
                    .attr('fill', 'red');                

            //récupère les noeuds ieml qui ne sont pas cachés
            var nodes = d3.nest()
                .key(function(d){
                    return d.iemlR;
                })
                .entries(data.propositions.filter(function(r){
                    return !r.isMasque;})),
            //récupère les parents
            arrValide = data.propositions.filter(function(r){
                    return r.isValide;}),
            links = data.liens;
            var maxLayers = d3.max(data.propositions.map(function(d)  {
                d.layer = parseInt(d.layer);
                return d.layer;
                })),
            extentTaille = d3.extent(data.propositions.map(function(d)  {
                d.taille = parseInt(d.taille);
                return d.taille;
                })),
            layers = d3.range(0, maxLayers+1, 1);               
            color.domain(extentTaille);
            cyScale = d3.scaleBand()
                    .domain(layers)
                    .range([0, (height/2) - (margin.top) - (margin.bottom)]);


            //ajoute les liens
            var link = svg.append("g")
                .attr("class", "links")
                .selectAll("line")
                .data(links)
                .enter().append("line")
                    .attr('marker-end','url(#ArrowEnd)')
                    .attr('marker-start','url(#BulleDeb)')
                    .attr("stroke-width", function(d) { 
                        return 1; });//d.value

            //ajoute le type de lien
            var edge = svg.append("g")
                .attr("class", "edges")
            var edgepaths = edge.selectAll(".edgepath")
                .data(links)
                .enter()
                .append('path')
                .attr('class','edgepath')
                .attr('d','M 0,0 m -1,-5 L 1,-5 L 1,5 L -1,5 Z')
                .attr('fill-opacity',0)
                .attr('stroke-opacity',0)
                .attr('id',function (d, i) {
                    return 'edgepath' + d.idEdge})
                .style("pointer-events", "none");
            var edgelabels = edge.selectAll(".edgelabel")
                .data(links)
                .enter()
                .append('text')
                .style("pointer-events", "none")
                .attr('class','edgelabel')
                .attr('id',function (d, i) {return 'edgelabel' + i})
                .attr('font-size',10)
                .attr('fill','#aaa');    
            edgelabels.append('textPath')
                .attr('xlink:href', function (d, i) {
                    return '#edgepath' + d.idEdge})
                .style("text-anchor", "middle")
                .style("pointer-events", "none")
                .attr("startOffset", "50%")
                .text(function (d) {
                    return d.reltype});        

            //ajoute les couches
            var layer = svg.append("g")
                .attr("class", "layer")
                .selectAll("circle")
                .data(layers)
                .enter().append("circle")
                    .attr('r',function(d) { 
                        return cyScale(d); 
                        })
                    .attr('stroke',"black")
                    .attr('stroke-opacity',"0.5")
                    .attr('fill',"none");

            //ajoute les noeuds
            var node = svg.append("g")
                .attr("class", "nodes")
                .selectAll("g")
                .data(nodes)
                .enter().append("g");
            
            var circles = node.append("circle")
                .attr("id", function(d) { 
                    return 'c_'+d.values[0].recidQuest+'_'+d.values[0].idDico; })
                .attr("r", function(d) { 
                    return 1; })
                .attr("fill-opacity",0.4)
                .attr("stroke",'none')
                .attr("fill", function(d) { 
                    return color(d.values[0].taille); })
                .call(d3.drag()
                    .on("start", dragstarted)
                    .on("drag", dragged)
                    .on("end", dragended));
        
            var labels = node.append("text")
                .text(function(d) {
                    return d.values[0].txtR;
                })
                .attr('fill',function(d) {
                    var valide = d.values.filter(function(f){
                        return f.isValide}); 
                    return valide.length ? 'red' : 'black';
                })
                .attr('text-anchor',"middle")
                .attr('x', 0)
                .attr('y', 0)
                .call(wrap, cyScale.bandwidth());
        
            node.append("title")
                .text(function(d) { 
                    return d.values[0].iemlR;});
                                 

            //calcule le positionnement par force
            simulation = d3.forceSimulation(nodes)
                .force("charge", d3.forceCollide().radius(20))
                .force("link", d3.forceLink(links).id(function(d) { 
                    return d.key; }).strength(0))        
                .force("r", d3.forceRadial(function(d) { 
                        //console.log(d.values[0]);
                        return cyScale(d.values[0].layer); 
                        }))
                    .on("tick", ticked);

            //mise en forme continu du diagramme        
            function ticked() {
                node
                    .attr("transform", function(d) {
                        return "translate(" + d.x + "," + d.y + ")";
                    });
                link
                    .attr("x1", function(d) { 
                        return d.source.x; })
                    .attr("y1", function(d) { 
                        return d.source.y; })
                    .attr("x2", function(d) { 
                        return d.target.x; })
                    .attr("y2", function(d) { 
                        return d.target.y; });

                edgepaths.attr('d', function (d) {
                    return 'M ' + d.source.x + ' ' + d.source.y + ' L ' + d.target.x + ' ' + d.target.y;
                });
        
                edgelabels.attr('transform', function (d) {
                    //console.log(d);
                    if (d.target.x < d.source.x) {
                        var bbox = this.getBBox();
                        //console.log(bbox);
                        rx = bbox.x + bbox.width / 2;
                        ry = bbox.y + bbox.height / 2;
                        return 'rotate(180 ' + rx + ' ' + ry + ')';
                    }
                    else {
                        return 'rotate(0)';
                    }
                });

            }
            
        });


    }

    //merci beaucoup à https://bl.ocks.org/guypursey/f47d8cd11a8ff24854305505dbbd8c07
    function wrap(text, width) {
        text.each(function() {
            var text = d3.select(this);
            var words = text.text().split(/\s+/).reverse(),
            word,
            line = [],
            lineNumber = 0,
            lineHeight = 1.1, // ems
            y = text.attr("y"),
            x = text.attr("x"),
            dy = 0;//parseFloat(text.attr("dy")),
            if(words.length>1)y -= lineHeight*4;//cyScale.bandwidth()/3;
            var tspan = text.text(null).append("tspan").attr("x", x).attr("y", y).attr("dy", dy + "em");
            while (word = words.pop()) {
                line.push(word)
                tspan.text(line.join(" "))
                if (tspan.node().getComputedTextLength() > width) {
                line.pop()
                tspan.text(line.join(" "))
                line = [word]
                tspan = text.append("tspan").attr("x", x).attr("y", y).attr("dy", `${++lineNumber * lineHeight + dy}em`).text(word)
                }
            }        
        })
    }

    function dragstarted(d) {
        if (!d3.event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }
    
    function dragged(d) {
        d.fx = d3.event.x;
        d.fy = d3.event.y;
    }
    
    function dragended(d) {
        if (!d3.event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

    chart.width = function(_) {
        if (!arguments.length) return width;
        width = _;
        return chart;
      };
    
    chart.height = function(_) {
        if (!arguments.length) return height;
        height = _;
        return chart;
      };

    chart.maxRep = function(_) {
        if (!arguments.length) return maxRep;
        maxRep = _;
        let maxRange = cyScale ? cyScale.bandwidth() : 100;
        rayon = d3.scaleLinear().domain([0, maxRep]).range([1, maxRange]);
        return chart;
    };
      
    
    chart.changeRayonNoeud = function(id,v) {

        d3.select('#c_'+id)
            .attr('r',rayon(v));

    } 

    return chart;
}
