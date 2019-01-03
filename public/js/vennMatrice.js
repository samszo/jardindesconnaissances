function vennMatrice() {
    var margin = {top: 20, right: 20, bottom: 20, left: 20},
        width = 1200,
        height = 600,
        forme = 'rect',//'ellipse',
        svg,
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
        cxScale = d3.scaleBand().paddingInner([0.1]).paddingOuter(0.6),
        cyScale = d3.scaleBand().paddingInner([0.1]).paddingOuter(0.6),
        colors = {'E':"rgb(0,0,0)",'U':"rgb(0,255,255)",'A':"rgb(255,0,0)",'S':"rgb(0,255,0)",'B':"rgb(0,0,255)",'T':"rgb(255,255,0)"},	
        tooltip = d3.select("body").append("div")
          .attr("style", "opacity:0;background-color:white;padding:6px;position:absolute;width:200px;height:35px;pointer-events:none;border:black;border-style:solid;"),
        toolBar = d3.select("body").append("div")
          .attr("id","toolBar")
          .attr("style", "top:10;left:10;background-color:white;padding:6px;position:absolute;width:200px;height:35px;border:black;border-style:solid;"),
        arrFctToolBar = [{fct:'showHideText', lib:"Affiche / Masque les textes", icon:"fas fa-font"}
          ,{fct:'expand', lib:"Expansion", icon:"fas fa-expand"}
          ,{fct:'showHideLimite', lib:"Affiche / Masque les limites", icon:"fas fa-route"}
        ]
        //zoom = d3.zoom().on("zoom", zoomFunction)
        , me = this;

        ;

    function chart(selection) {

      //création de la barre d'outil
      toolBar.selectAll("i").data(arrFctToolBar).enter().append("button")
        .on("click",function(d){
            if(d.fct=='showHideText')chart.showHideText();  
            if(d.fct=='showHideLimite')chart.showHideLimite();  
            if(d.fct=='expand')svg.transition().duration(delay).attr("viewBox","0 0 "+(width+margin.right)+" "+(height+margin.bottom));  
          }).append("i")
          .attr("class",function(d){
            return d.icon+" fa-2x"
          })
          .style("margin","3px");


      //création de la matrice à partir des data
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
            ,'select':false
          };
        });
  
        // Update scales.
        var marge = 2;
        cxScale
            .domain(data[0].d.abs)
            .range([0, width - (margin.left*marge) - (margin.right*marge)]);
        cyScale
            .domain(data[0].d.ord)
            .range([height - (margin.top*marge) - (margin.bottom*marge), margin.top*marge]);
  
        // Select the svg element, if it exists.
        svg = d3.select(this).append('svg');

        //création du def pour les dégradés
        var dataLG=[], dataRG=[];
        data.forEach(function(d){
          var arrP = d.d.dico.primitives.split(':');
          var stops = [];
          arrP.forEach(function(s){stops.push({prim:s,pas:1/arrP.length});});
          var dt = {id:d.d.dico.INDEX,p:stops};
          if(d.d.type=='item' || d.d.type=='matrice' || d.d.type=='abs' || d.d.type=='ord')
            dataRG.push(dt);
          else      
            dataLG.push(dt);
        });
        var svgDefs = svg.append('defs');
        var linearGrad = svgDefs.selectAll("linearGradient").data(dataLG).enter().append("linearGradient")
          .attr('id', function(d){
            return 'grad'+d.id
          })
          .attr('x1',"0")
          .attr('x2',"0")
          .attr('y1',"0")
          .attr('y2',"1");
        var radialGrad = svgDefs.selectAll("radialGradient").data(dataRG).enter().append("radialGradient")
          .attr('id', function(d){
            return 'grad'+d.id
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
              })
              .attr('stop-opacity',0.6);
    
        // Otherwise, create the skeletal chart.
        var gEnter, colorNeutre = "none", delay= 3000;
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
          var gConcept = svg.selectAll(".concept").data(data).enter().append("g")
            .attr('class','concept')
            .attr('id',function(d){
                return d.d.dico.INDEX;
              })
            .on('click', function(d){
              var cpt = d3.select(this);
              //masque ou affiche le degradé
              d.select = d.select ? false : true; 
              svg.selectAll("rect")
                .style("fill",function(ad){
                  var couleur = ad.select ? "url(#grad"+ad.d.dico.INDEX+")" : colorNeutre;  
                  return couleur;               
                })
              //renvoie le concept
              chart.addIemlCode(d);  
              /*zoom ou dezoom
              if(d.select){
                var bb = cpt.node().getBBox(); 
                var x = bb.x, y = bb.y
                , w = bb.width, h = bb.height;
                svg.transition().duration(delay).attr("viewBox",x+" "+y+" "+w+" "+h);
              }else                
                svg.transition().duration(delay).attr("viewBox","0 0 "+(width+margin.right)+" "+(height+margin.bottom))
              */                  
            });
          gEnter = gConcept.append("rect")
            .attr("rx", 10)
            .attr("ry", 10)
            .style("pointer-events","all")
            .style("fill",function(d){
              if(d.d.type=="sans dégradé")
                return "url(#grad"+d.d.dico.INDEX+")";              
              else
                return colorNeutre;              
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
              if(d.d.type=='abs')v=margin.top + margin.top*marge;
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
              if(d.d.type=='abs')v=height - margin.top - margin.bottom - margin.top*marge;
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

        gTexte = gConcept.append("text")
        .style('font-size', function(d){
          var v;
          if(d.d.type=='matrice')v=24;//cxScale.bandwidth()/2;
          if(d.d.type=='abs')v=8;//cxScale.bandwidth()/2;
          if(d.d.type=='ord')v=8;//cxScale.bandwidth()/2;
          if(d.d.type=='item')v=8;//cxScale.bandwidth()/3;
          return v+"px";
        })
        .style('writing-mode', function(d){
          var v;
          if(d.d.type=='matrice')v='';
          if(d.d.type=='abs')v='tb';
          if(d.d.type=='ord')v='';
          if(d.d.type=='item')v='';
          return v;
        })
        .style('text-anchor', function(d){
          var v;
          if(d.d.type=='matrice')v='middle';
          if(d.d.type=='abs')v='end';
          if(d.d.type=='ord')v='end';
          if(d.d.type=='item')v='middle';
          return v;
        })
        .attr("class", function(d){
          return 'class_'+d.d.type;
        })
        .attr("x", function(d){
          var v;
          if(d.d.type=='matrice')v=width/2;
          if(d.d.type=='abs')v=cxScale(d.d.value)+(cxScale.bandwidth()/2);
          if(d.d.type=='ord')v= width-margin.left-4;
          if(d.d.type=='item')v=cxScale(d.d.abs)+(cxScale.bandwidth()/2);
          return v;
        })
        .attr("y", function(d){
          var v;
          if(d.d.type=='matrice')v=margin.top*marge;
          if(d.d.type=='abs')v=height - margin.bottom;
          if(d.d.type=='ord')v=cyScale(d.d.value)+(cyScale.bandwidth()/2);
          if(d.d.type=='item')v=cyScale(d.d.ord)+(cyScale.bandwidth()/2);
          return v;
        })
        .text(function(d){
          return d.d.dico.FR;              
        })
        .call(wrap, cxScale.bandwidth())

        /*ajoute le zoom
        var view = svg.append("rect")
          .attr("class", "zoom")
          .attr("width", width)
          .attr("height", height)
          //.style("fill","white")
          //.style("opacity",0.1)
          .style("cursor","move")
          .style("fill","none")
          .style("pointer-events","all")
          .call(zoom);
        */

        //Redimensionne le svg
        svg.attr("width",window.innerWidth)
          .attr("height",window.innerHeight)
          .attr("viewBox","0 0 "+(width+margin.right)+" "+(height+margin.bottom))
          .attr("preserveAspectRatio","xMidYMid meet");

  
      });
    }
  
    //merci beaucoup à https://bl.ocks.org/guypursey/f47d8cd11a8ff24854305505dbbd8c07
    function wrap(text, width) {
      text.each(function() {
        var text = d3.select(this);
        if(text.attr('class')=='class_item'){// text.attr('class')!='class_matrice' || 
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
        }
      })
    }

    function zoomFunction(){
      console.log(d3.event.transform)    
      // update matrice
      svg.attr("transform", d3.event.transform)
    };

    // The x-accessor for the path generator; xScale ∘ xValue.
    function X(d) {
      return cxScale(d[0]);
    }
  
    // The x-accessor for the path generator; yScale ∘ yValue.
    function Y(d) {
      return cyScale(d[1]);
    } 

    var voisTexte = 'visible';
    chart.showHideText= function() {
      voisTexte = voisTexte == 'visible' ? 'hidden' : 'visible'
      svg.selectAll('text').style('visibility',voisTexte);
    } 
    var voisLimite = 1;
    chart.showHideLimite= function() {
      voisLimite = voisLimite == 1 ? 0 : 1
      svg.selectAll('rect').style('stroke-width',voisLimite);
    } 
    chart.addIemlCode= function(d) {
      if(parent.addIemlCode)parent.addIemlCode(d);
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