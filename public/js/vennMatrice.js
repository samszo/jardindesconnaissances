function vennMatrice() {
    var margin = {top: 20, right: 20, bottom: 20, left: 20},
        width = 400,
        height = 400,
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
        cxScale = d3.scaleBand(),
        cyScale = d3.scaleBand();
  
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
  
        // Otherwise, create the skeletal chart.
        var gEnter = svg.selectAll("ellipse").data(data).enter().append("ellipse")
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
          })
 
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