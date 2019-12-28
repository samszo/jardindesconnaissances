class menuSunburst {
    constructor(params) {
        var me = this;
        this.cont = d3.select("#"+params.idCont);
        this.width = params.width ? params.width : 400;
        this.data = params.data ? params.data : {
            name: 'menu',
            color: 'magenta',
            children: [{
              name: 'supprimer',
              color: 'yellow',
              size: 1
            },{
              name: 'b',
              color: 'red',
              children: [{
                name: 'ba',
                color: 'orange',
                size: 1
              }, {
                name: 'bb',
                color: 'blue',
                children: [{
                  name: 'bba',
                  color: 'green',
                  size: 1
                }, {
                  name: 'bbb',
                  color: 'pink',
                  size: 1
                }]
              }]
            }]
          }; 

        var svgMenu, objEW, parent, root, color, g, path, label
        ,format = d3.format(",d")
        ,radius = this.width / 6
        ,arc = d3.arc()
                .startAngle(d => d.x0)
                .endAngle(d => d.x1)
                .padAngle(d => Math.min((d.x1 - d.x0) / 2, 0.005))
                .padRadius(radius * 1.5)
                .innerRadius(d => d.y0 * radius)
                .outerRadius(d => Math.max(d.y0 * radius, d.y1 * radius - 1))
        , partition = data => {
            root = d3.hierarchy(data)
                    .sum(d => d.size)
                    .sort((a, b) => b.value - a.value);
            return d3.partition()
                    .size([2 * Math.PI, root.height + 1])
                    (root);
        };              

        this.init = function () {

            console.log(me.data);
            root = partition(me.data);
            color = d3.scaleOrdinal().range(d3.quantize(d3.interpolateRainbow, me.data.children.length + 1));
    
            root.each(d => d.current = d);

            //ajoute le svg du menu
            svgMenu = me.cont.append("svg")
                    .style("width", "100%")
                    .style("height", "auto")
                    .style("position","absolute")
                    .attr('viewBox',"0 0 "+me.width+" "+me.width)
                    .style("font", "10px sans-serif");
    
            g = svgMenu.append("g")
                    .attr("transform", `translate(${me.width / 2},${me.width / 2})`);
    
            path = g.append("g")
                    .selectAll("path")
                    .data(root.descendants().slice(1))
                    .join("path")
                    .attr("fill", d => {
                        while (d.depth > 1)
                            { d = d.parent; }
                        return color(d.data.name);
                    })
                    .attr("fill-opacity", d => arcVisible(d.current) ? (d.children ? 1 : 0.8) : 0)
                    .attr("d", d => arc(d.current));
    
            /*le click uniqument sur cases avec des enfants
            path.filter(d => d.children)
                    .style("cursor", "pointer")
                    .on("click", clicked);
            */
           path.style("cursor", "pointer")
            .on("click", clicked);

    
            path.append("title")
                    .text(d => `${d.ancestors().map(d => d.data.name).reverse().join("/")}\n${format(d.value)}`);
    
            label = g.append("g")
                    .attr("pointer-events", "none")
                    .attr("text-anchor", "middle")
                    .style("user-select", "none")
                    .selectAll("text")
                    .data(root.descendants().slice(1))
                    .join("text")
                    .attr("dy", "0.35em")
                    .attr("fill-opacity", d => +labelVisible(d.current))
                    .attr("transform", d => labelTransform(d.current))
                    .text(d => d.data.name);
    
            parent = g.append("circle")
                    .datum(root)
                    .attr("r", radius)
                    .attr("fill", "none")
                    .attr("pointer-events", "all")
                    .on("click", clicked);
           
        };

        this.hide = function(){
          svgMenu.attr('visibility',"hidden");
        }
        this.show = function(){
          svgMenu.attr('visibility',"visible");
          if(objEW)objEW.hide();
        }

        function fctExecute(p) {
            switch (p.data.fct) {
                case 'showRoueEmotions':
                  me.hide();
                  if(!objEW)
                    objEW = new emotionswheel({'idCont':me.cont.attr('id'),'width':me.width,'height':me.width});
                  else
                    objEW.show();
                  break;
                default:
                  console.log(p);
            }            
        }

        function clicked(p) {

            //vérifie si une fonction est définie
            if(p.data.fct)fctExecute(p);

            //si pas d'enfant on sort
            if(!p.children)return;

            parent.datum(p.parent || root);

            root.each(d => d.target = {
                    x0: Math.max(0, Math.min(1, (d.x0 - p.x0) / (p.x1 - p.x0))) * 2 * Math.PI,
                    x1: Math.max(0, Math.min(1, (d.x1 - p.x0) / (p.x1 - p.x0))) * 2 * Math.PI,
                    y0: Math.max(0, d.y0 - p.depth),
                    y1: Math.max(0, d.y1 - p.depth)
                });

            const t = g.transition().duration(750);

            // Transition the data on all arcs, even the ones that aren’t visible,
            // so that if this transition is interrupted, entering arcs will start
            // the next transition from the desired position.
            path.transition(t)
                    .tween("data", d => {
                        const i = d3.interpolate(d.current, d.target);
                        return t => d.current = i(t);
                    })
                    .filter(function (d) {
                        return +this.getAttribute("fill-opacity") || arcVisible(d.target);
                    })
                    .attr("fill-opacity", d => arcVisible(d.target) ? (d.children ? 1 : 0.8) : 0)
                    .attrTween("d", d => () => arc(d.current));

            label.filter(function (d) {
                    return +this.getAttribute("fill-opacity") || labelVisible(d.target);
                }).transition(t)
                        .attr("fill-opacity", d => +labelVisible(d.target))
                        .attrTween("transform", d => () => labelTransform(d.current));
        }

        function arcVisible(d) {
            return d.y1 <= 3 && d.y0 >= 1 && d.x1 > d.x0;
        }

        function labelVisible(d) {
            return d.y1 <= 3 && d.y0 >= 1 && (d.y1 - d.y0) * (d.x1 - d.x0) > 0.03;
        }

        function labelTransform(d) {
            const x = (d.x0 + d.x1) / 2 * 180 / Math.PI;
            const y = (d.y0 + d.y1) / 2 * radius;
            return `rotate(${x - 90}) translate(${y},0) rotate(${x < 180 ? 0 : 180})`;
        }

        this.init();
    }
}

  
