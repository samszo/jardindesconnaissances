function iemlForce() {
    var margin = {top: 3, right: 20, bottom: 3, left: 20},
        width = 1200,
        height = 600,
        color = d3.scaleSequential().interpolator(d3['interpolateWarm']),
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
                .append('g').attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")

                ;
            var nodes = d3.nest()
                .key(function(d){
                    return d.iemlR;
                })
                .entries(data.reponses),
                arrValide = data.reponses.filter(function(r){
                    return r.isValide;}),
                links = [];
                arrValide.forEach(function(d){
                    links = links.concat(d.liens);
                });
                var maxLayers = d3.max(data.reponses.map(function(d)  {
                    return d.cpt.dico.LAYER;
                    })),
                extentTaille = d3.extent(data.reponses.map(function(d)  {
                        return d.cpt.dico.TAILLE;
                        })),
                    layers = d3.range(0, maxLayers+1, 1),               
                cyScale = d3.scaleBand()
                    .domain(layers)
                    .range([0, (height/2) - (margin.top) - (margin.bottom)]);
            color.domain(extentTaille);

            //ajoute les liens
            var link = svg.append("g")
                .attr("class", "links")
                //.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
            .selectAll("line")
            .data(links)
            .enter().append("line")
                .attr("stroke-width", function(d) { 
                    return 1; });//d.value

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
            
            //ejoute les noeuds
            var node = svg.append("g")
                .attr("class", "nodes")
                //.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
                .selectAll("g")
                .data(nodes)
                .enter().append("g");

            
            var circles = node.append("circle")
                .attr("r", function(d) { 
                    return 10; })//d.cpt.dico.TAILLE/2;
                .attr("fill-opacity",0.4)
                .attr("fill", function(d) { 
                    return color(d.values[0].cpt.dico.TAILLE); })
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
                        console.log(d.values[0].cpt);
                        return cyScale(d.values[0].cpt.dico.LAYER); 
                        }))
                    .on("tick", ticked);
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
                    .attr("x2", function(d) { return d.target.x; })
                    .attr("y2", function(d) { return d.target.y; });

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

    return chart;
}
