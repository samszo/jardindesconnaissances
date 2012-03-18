/**
 * 
 * 
 * merci à http://bl.ocks.org/1629464
 */
function selectbar(config) {
	this.id = config.id;  
	this.d = config.d;
	this.deb;  
	this.fin;
	this.fncDragEnd = config.fncDragEnd;
	this.wSel=config.wSel;
	this.xCntxInv = config.xCntxInv;
	this.sst = config.sst;
	this.sb = function() {
	  var wBar = config.wBar, width = config.width,
	  	hSel = config.hSel, left = config.left, top = config.top,
	  	xCntx = config.xCntx, xCntxInv = config.xCntxInv, svg = config.svg,
	  	xUnit = config.xUnit, self=this;
	
	  var drag = d3.behavior.drag()
	  	.origin(Object)
	  	.on("drag", dragmove)
	  	.on("dragend", dragend);
	  var dragright = d3.behavior.drag()
	  	.origin(Object)
	  	.on("drag", rdragresize)
	  	.on("dragend", dragend);
	  var dragleft = d3.behavior.drag()
	  	.origin(Object)
	  	.on("drag", ldragresize)
	  	.on("dragend", dragend);

	  var newg = svg.append("g")
	  	.attr("id", self.id)
	  	.attr("transform", "translate(" + left + "," + top + ")")
	  	.data(self.d);
	  var dragrect = newg.append("rect")
	  	.attr("id", "active")
	  	.attr("x", function(d) { self.d = d; return d.x; })
	  	.attr("y", function(d) { return d.y; })
	  	.attr("height", hSel)
	  	.attr("width", self.wSel)
	  	.attr("fill-opacity", .5)
	  	.attr("cursor", "move")
	  	.on("click", dragend)
	  	.call(drag);
	  var dragbarleft = newg.append("rect")
	  	.attr("x", function(d) { return d.x - (wBar/2); })
	  	.attr("y", function(d) { return d.y + (wBar/2); })
	  	.attr("height", hSel - wBar)
	  	.attr("id", "dragleft")
	  	.attr("width", wBar)
	  	.attr("fill", "lightblue")
	  	.attr("fill-opacity", .5)
	  	.attr("cursor", "ew-resize")
	  	.call(dragleft);
	  var dragbarright = newg.append("rect")
	  	.attr("x", function(d) { return d.x + self.wSel - (wBar/2); })
	  	.attr("y", function(d) { return d.y + (wBar/2); })
	  	.attr("id", "dragright")
	  	.attr("height", hSel - wBar)
	  	.attr("width", wBar)
	  	.attr("fill", "lightblue")
	  	.attr("fill-opacity", .5)
	  	.attr("cursor", "ew-resize")
	  	.call(dragright);
	  var plusright = newg.append("text")
	  	.attr("x", function(d) { return d.x + self.wSel; })
	  	.attr("y", function(d) { return d.y + (wBar/2); })
	  	.text("+")
	  	.attr("cursor", "e-resize")
	  	.on("click", plusR);
	  var moinsright = newg.append("text")
	  	.attr("x", function(d) { return d.x + self.wSel + 2; })
	  	.attr("y", function(d) { return d.y + hSel; })
	  	.text("-")
	  	.attr("cursor", "w-resize")
	  	.on("click",moinsR);
	  var plusleft = newg.append("text")
	  	.attr("x", function(d) { return d.x - (wBar/2); })
	  	.attr("y", function(d) { return d.y + (wBar/2); })
	  	.text("+")
	  	.attr("cursor", "w-resize")
	  	.on("click",plusL);
	  var moinsleft = newg.append("text")
	  	.attr("x", function(d) { return d.x - (wBar/2); })
	  	.attr("y", function(d) { return d.y + hSel; })
	  	.text("-")
	  	.attr("cursor", "e-resize")
	  	.on("click",moinsL);

	  function moinsR(d){
		  var m = xCntxInv(d.x);
		  m -= xUnit;
		  var diff = d.x - xCntx(m);
		  self.wSel = self.wSel - diff;
		  bougeR(d);
	  }

	  function plusR(d){
		  var m = xCntxInv(d.x);
		  m += xUnit;
		  var diff = xCntx(m)-d.x;
		  self.wSel = self.wSel + diff;
		  bougeR(d);
	  }
	  function bougeR(d){
		  dragrect.attr("width", self.wSel);
		  dragbarright.attr("x", d.x + self.wSel - (wBar/2));
		  plusright.attr("x", d.x + self.wSel);
		  moinsright.attr("x", d.x + self.wSel + 2);
		  dragend(d);		  
	  }
	  function moinsL(d){
		  var m = xCntxInv(d.x);
		  m += xUnit;
		  var dx = xCntx(m);
		  var diff = dx-d.x;
		  self.wSel = self.wSel - diff;
		  d.x = dx;
		  bougeL(d);
	  }
	  function plusL(d){
		  var m = xCntxInv(d.x);
		  m -= xUnit;
		  var dx = xCntx(m);
		  var diff = d.x-dx;
		  self.wSel = self.wSel + diff;
		  d.x = dx;
		  bougeL(d);
	  }
	  function bougeL(d){
		  dragrect.attr("width", self.wSel);
		  dragrect.attr("x", d.x);
		  dragbarleft.attr("x", d.x - (wBar/2));
		  plusleft.attr("x", d.x - (wBar/2));
		  moinsleft.attr("x", d.x - (wBar/2));
		  dragend(d);		  
	  }
	  
	  function dragend(d){
		 self.d = d;
		 self.show();
	  }

	  function dragmove(d) {
	  	      dragrect
	  	          .attr("x", d.x = Math.max(0, Math.min(width - self.wSel, d3.event.x)));
	  	      dragbarleft
	  	          .attr("x", function(d) { return d.x - (wBar/2); });
	  	      dragbarright
	  	          .attr("x", function(d) { return d.x + self.wSel - (wBar/2); });
	  	      plusright.attr("x", d.x + self.wSel);
			  moinsright.attr("x", d.x + self.wSel + 2);
			  plusleft.attr("x", d.x - (wBar/2));
			  moinsleft.attr("x", d.x - (wBar/2));    
	  	}
	  function ldragresize(d) {
	  	      var oldx = d.x;
	  	     //Max x on the right is x + width - dragbarw
	  	     //Max x on the left is 0 - (dragbarw/2)
	  	      d.x = Math.max(0, Math.min(d.x + self.wSel - (wBar / 2), d3.event.x));
	  	      self.wSel = self.wSel + (oldx - d.x);
	  	      dragbarleft
	  	        .attr("x", function(d) { return d.x - (wBar / 2); });
	  	       
	  	      dragrect
	  	        .attr("x", function(d) { return d.x; })
	  	        .attr("width", self.wSel);
			  plusleft.attr("x", d.x - (wBar/2));
			  moinsleft.attr("x", d.x - (wBar/2));

	  	}

	  	function rdragresize(d) {
	  	     //Max x on the left is x - width
	  	     //Max x on the right is width of screen + (dragbarw/2)
	  	     var dragx = Math.max(d.x + (wBar/2), Math.min(width, d.x + self.wSel + d3.event.dx));

	  	     //recalculate width
	  	     self.wSel = dragx - d.x;

	  	     //move the right drag handle
	  	     dragbarright.attr("x", function(d) { return dragx - (wBar/2);});

	  	     //resize the drag rectangle
	  	     //as we are only resizing from the right, the x coordinate does not need to change
	  	     dragrect.attr("width", self.wSel);

	  	     plusright.attr("x", d.x + self.wSel);
			  moinsright.attr("x", d.x + self.wSel + 2);
	  	}
  };
  
  this.params = function() {			
		return {"deb":this.deb, "fin":this.fin, "id":this.id};
	  };

  this.show = function() {
	  	//affiche la sélection
	  	this.fin = this.xCntxInv(this.d.x+this.wSel)-1;
	  	this.deb = this.xCntxInv(this.d.x);
	  	var r = this.fncDragEnd([this.deb, this.fin], this.id, this.sst);
	  	this.fin = r.fin;
	  	this.deb = r.deb;
	  };
	  
  return this.sb();
}