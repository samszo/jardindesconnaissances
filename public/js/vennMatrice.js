function vennMatrice() {
    var margin = {top: 20, right: 20, bottom: 20, left: 20},
        width = 400,
        height = 400,
        forme = 'rect',//'ellipse',
        cxValue = function(d) { 
          var v;
          if(d.type=='matrice')v=d.abs.length/2;
          if(d.type=='abs')v=d.abs;
          if(d.type=='ord')v=1;
          if(d.type=='item')v=d.abs;
          return v; 
        },
        cyValue = function(d) { 
          var v;
          if(d.type=='matrice')v=d.ord.length/2;
          if(d.type=='abs')v=1;
          if(d.type=='ord')v=d.ord;
          if(d.type=='item')v=d.ord;
          return v; 
        },
        rxValue = function(d) { 
          var v;
          if(d.type=='matrice')v=d.abs.length;
          if(d.type=='abs')v=1;
          if(d.type=='ord')v=1;
          if(d.type=='item')v=0.8;
          return v; 
        },
        ryValue = function(d) { 
          var v;
          if(d.type=='matrice')v=d.ord.length;
          if(d.type=='abs')v=1;
          if(d.type=='ord')v=1;
          if(d.type=='item')v=0.8;
          return v; 
        },
        cxScale = d3.scaleBand().paddingInner([0.3]).paddingOuter([1]),
        cyScale = d3.scaleBand().paddingInner([0.3]).paddingOuter([2]),
        colors = {'E':"rgb(0,0,0)",'U':"rgb(0,255,255)",'A':"rgb(255,0,0)",'S':"rgb(0,255,0)",'B':"rgb(0,0,255)",'T':"rgb(255,255,0)"},	
        tooltip = d3.select("body").append("div")
          .attr("style", "opacity:0;background-color:white;padding:6px;position:absolute;width:200px;height:35px;pointer-events:none;border:black;border-style:solid;");
  
    function chart(selection) {
      selection.each(function(data) {
  
        // Convert data to standard representation greedily;
        // this is needed for nondeterministic accessors.
        data = data.map(function(d, i) {
          return {
            'cx':cxValue.call(data, d, i)
            ,'cy': cyValue.call(data, d, i)
            ,'rx':rxValue.call(data, d, i)
            ,'ry':ryValue.call(data, d, i)
            ,'d':d
          };
        });
  
        // Update scales.
        cxScale
            .domain(data[0].d.abs)
            .range([0, width - margin.left - margin.right]);
        cyScale
            .domain(data[0].d.ord)
            .range([height - margin.top - margin.bottom, 0]);
  
        // Select the svg element, if it exists.
        var svg = d3.select(this).append('svg');

        //création du def pour les dégradés
        var dataLG=[], dataRG=[];
        data.forEach(function(d){
          var arrP = d.d.dico.primitives.split(':');
          var stops = [];
          arrP.forEach(function(s){stops.push({prim:s,pas:1/arrP.length});});
          var dt = {id:d.d.dico.INDEX,p:stops};
          if(d.d.type=='item' || d.d.type=='matrice')
            dataRG.push(dt);
          else      
            dataLG.push(dt);
        });
        var svgDefs = svg.append('defs');
        var linearGrad = svgDefs.selectAll("linearGradient").data(dataLG).enter().append("linearGradient")
          .attr('id', function(d){
            return 'lg'+d.id
          });
        var radialGrad = svgDefs.selectAll("radialGradient").data(dataRG).enter().append("radialGradient")
          .attr('id', function(d){
            return 'rg'+d.id
          })
          .attr('spreadMethod', "pad")
          .attr('gradientUnits',"objectBoundingBox")
          .attr('cx',"50%")
          .attr('cy',"50%")
          .attr('r',"50%")
          .attr('fx',"50%")
          .attr('fy',"50%");
  
        // Create the stops of the main gradient. Each stop will be assigned
        // a class to style the stop using CSS.
        linearGrad.selectAll("stop").data(function(d){
            return d.p;
          }).enter().append("stop")
              .attr('offset', function(d, i){
                return d.pas*i;
              })
              .attr('stop-color', function(d){
                return colors[d.prim];
              });
        radialGrad.selectAll("stop").data(function(d){
            return d.p;
          }).enter().append("stop")
              .attr('offset', function(d, i){
                return d.pas*i;
              })
              .attr('stop-color', function(d){
                return colors[d.prim];
              });
    
        // Otherwise, create the skeletal chart.
        var gEnter;
        if(forme == 'ellipse'){
          gEnter = svg.selectAll("ellipse").data(data).enter().append("ellipse")
            .attr("class", function(d){
              return d.d.type;
            })
            .attr("cx", function(d){
              var v;
              if(d.d.type=='matrice')v=(width - margin.left - margin.right)/2;
              if(d.d.type=='abs')v=cxScale(d.d.value)+cxScale.bandwidth()/2;
              if(d.d.type=='ord')v=(width - margin.left - margin.right)/2;
              if(d.d.type=='item')v=cxScale(d.d.abs)+cxScale.bandwidth()/2;
              return v;
            })
            .attr("cy", function(d){
              var v;
              if(d.d.type=='matrice')v=(height - margin.top - margin.bottom)/2;
              if(d.d.type=='abs')v=(height - margin.top - margin.bottom)/2;
              if(d.d.type=='ord')v=cyScale(d.d.value)+cyScale.bandwidth()/2;
              if(d.d.type=='item')v=cyScale(d.d.ord)+cyScale.bandwidth()/2;
              return v;
            })
            .attr("rx", function(d){ 
              var v;
              if(d.d.type=='matrice')v=(width - margin.left - margin.right)/2;
              if(d.d.type=='abs')v=cxScale.bandwidth()/2;
              if(d.d.type=='ord')v=(width - margin.left - margin.right)/2;
              if(d.d.type=='item')v=cxScale.bandwidth()/6;  
              return v;
            })
            .attr("ry", function(d){ 
              var v;
              if(d.d.type=='matrice')v=(height - margin.top - margin.bottom)/2;
              if(d.d.type=='abs')v=(height - margin.top - margin.bottom)/2;
              if(d.d.type=='ord')v=cyScale.bandwidth()/2;
              if(d.d.type=='item')v=cyScale.bandwidth()/6;  
              return v;
            });
        }
        if(forme == 'rect'){
          gEnter = svg.selectAll("rect").data(data).enter().append("rect")
            .attr("rx", 10)
            .attr("ry", 10)
            .style("fill",function(d){
              if(d.d.type=='item' || d.d.type=='matrice') return "url(#rg"+d.d.dico.INDEX+")"; 
              else return "url(#lg"+d.d.dico.INDEX+")";              
            })
            .attr("class", function(d){
              return d.d.type;
            })
            .attr("x", function(d){
              var v;
              if(d.d.type=='matrice')v=0;
              if(d.d.type=='abs')v=cxScale(d.d.value);
              if(d.d.type=='ord')v=margin.left;
              if(d.d.type=='item')v=cxScale(d.d.abs);//+(cxScale.bandwidth()/2)-cxScale.bandwidth()/4;
              return v;
            })
            .attr("y", function(d){
              var v;
              if(d.d.type=='matrice')v=0;
              if(d.d.type=='abs')v=margin.top;
              if(d.d.type=='ord')v=cyScale(d.d.value);
              if(d.d.type=='item')v=cyScale(d.d.ord);//+(cyScale.bandwidth()/2)-cyScale.bandwidth()/4;
              return v;
            })
            .attr("width", function(d){ 
              var v;
              if(d.d.type=='matrice')v=width;
              if(d.d.type=='abs')v=cxScale.bandwidth();
              if(d.d.type=='ord')v=width- margin.left - margin.right;
              if(d.d.type=='item')v=cxScale.bandwidth();  
              return v;
            })
            .attr("height", function(d){ 
              var v;
              if(d.d.type=='matrice')v=height;
              if(d.d.type=='abs')v=height - margin.top - margin.bottom;
              if(d.d.type=='ord')v=cyScale.bandwidth();
              if(d.d.type=='item')v=cyScale.bandwidth();  
              return v;
            })
            .on('mouseover', function(d){
              tooltip.transition()
                      .duration(700)
                      .style("opacity", 1);
              })
            .on('mousemove', function(d){      
              var html = d.d.value, 
              h = tooltip.style("height"),
              posi = Number(h.substring(0, (h.length-2)));
              if(d.d.dico){
                html = "<b>"+d.d.dico.FR+"</b><br/>"+html;
                posi += 20;
              }
              tooltip.html(html)
                .style("left", d3.event.pageX + "px")
                .style("top",  (d3.event.pageY-posi) + "px");
              })
            .on('mouseout', function(d){
              tooltip.transition()
                .duration(500)
                .style("opacity", 0);
              });        
        }

        // Update the outer dimensions.
        svg.attr("width", width)
            .attr("height", height);
  
        // Update the inner dimensions.
        var g = svg.select("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
  
      });
    }
  
    // The x-accessor for the path generator; xScale ∘ xValue.
    function X(d) {
      return cxScale(d[0]);
    }
  
    // The x-accessor for the path generator; yScale ∘ yValue.
    function Y(d) {
      return cyScale(d[1]);
    } 
  
    chart.margin = function(_) {
      if (!arguments.length) return margin;
      margin = _;
      return chart;
    };
  
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
  
    chart.cx = function(_) {
      if (!arguments.length) return cxValue;
      cxValue = _;
      return chart;
    };
  
    chart.cy = function(_) {
      if (!arguments.length) return cyValue;
      cyValue = _;
      return chart;
    };
  
    return chart;
  }