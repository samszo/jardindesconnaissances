/**
 * 
 * 
 * merci à http://www.jasondavies.com/wordcloud/
 */
function tagcloud(config) {
	this.idDoc = config.idDoc;  
	this.idExi = config.idExi; 
	this.sauve = config.sauve; 
	this.exi;
	this.global = config.global;  
	this.verif = config.verif;  
	this.txt = config.txt;  
	this.data = config.data;
	this.term = config.term;
	this.utiWords = config.utiWords;
	this.poidsTag = 1;
	this.urlJson = config.urlJson;
	this.div = config.div;
	// From 
	// Jonathan Feinberg's cue.language, see lib/cue.language/license.txt.
	// 
	this.stopWords = /^(estce|vousmême|puisqu|estàdire|très|cela|alors|donc|etc|for|tant|au|en|un|une|aux|et|mais|par|c|d|du|des|pour|il|ici|lui|ses|sa|son|je|j|l|m|me|moi|mes|ma|mon|n|ne|pas|de|sur|on|se|soi|notre|nos|qu|s|même|elle|t|que|celà|la|le|les|te|toi|leur|leurs|eux|y|ces|ils|ce|ceci|cet|cette|tu|ta|ton|tes|à|nous|ou|quel|quels|quelle|quelles|qui|avec|dans|sans|vous|votre|vos|été|étée|étées|étés|étant|suis|es|est|sommes|êtes|sont|serai|seras|sera|serons|serez|seront|serais|serait|serions|seriez|seraient|étais|était|étions|étiez|étaient|fus|fut|fûmes|fûtes|furent|sois|soit|soyons|soyez|soient|fusse|fusses|fût|fussions|fussiez|fussent|ayant|eu|eue|eues|eus|ai|as|avons|avez|ont|aurai|auras|aura|aurons|aurez|auront|aurais|aurait|aurions|auriez|auraient|avais|avait|avions|aviez|avaient|eut|eûmes|eûtes|eurent|aie|aies|ait|ayons|ayez|aient|eusse|eusses|eût|eussions|eussiez|eussent|i|me|my|myself|we|us|our|ours|ourselves|you|your|yours|yourself|yourselves|he|him|his|himself|she|her|hers|herself|it|its|itself|they|them|their|theirs|themselves|what|which|who|whom|whose|this|that|these|those|am|is|are|was|were|be|been|being|have|has|had|having|do|does|did|doing|will|would|should|can|could|ought|i'm|you're|he's|she's|it's|we're|they're|i've|you've|we've|they've|i'd|you'd|he'd|she'd|we'd|they'd|i'll|you'll|he'll|she'll|we'll|they'll|isn't|aren't|wasn't|weren't|hasn't|haven't|hadn't|doesn't|don't|didn't|won't|wouldn't|shan't|shouldn't|can't|cannot|couldn't|mustn't|let's|that's|who's|what's|here's|there's|when's|where's|why's|how's|a|an|the|and|but|if|or|because|as|until|while|of|at|by|for|with|about|against|between|into|through|during|before|after|above|below|to|from|up|upon|down|in|out|on|off|over|under|again|further|then|once|here|there|when|where|why|how|all|any|both|each|few|more|most|other|some|such|no|nor|not|only|own|same|so|than|too|very|say|says|said|shall)$/;
	this.punctuation = /["“!()&*+,-\.\/:;<=>?\[\\\]^`\{|\}~]+/g;
	this.elision = /[’'’0123456789]+/g;
	this.wordSeparators = /[\s\u3031-\u3035\u309b\u309c\u30a0\u30fc\uff70]+/g;
	
	this.tc = function() {
	    var fill = d3.scale.category20b(),
		w = 640, 
		h = 128, hpt,
		complete = 0,
		statusText = d3.select("#status_"+this.idDoc),
		maxLength = 30,
		maxTag = 1000, colorTag = "black",
		self = this,
		posiTxt = d3.select("#select_txt_"+this.idDoc);

		var max, svg, background,tooltip,ext,fontSize;
	    
		var arrId = this.idDoc.split("_")
		this.exi = arrId.length > 3 && arrId[3] == this.idExi;  
	    
	    if(config.w) w = config.w;
	    if(config.h) h = config.h;
	    if(config.colorTag) colorTag = config.colorTag;
	    
	    if(!self.div)self.div=d3.select("#vis_"+this.idDoc);
	    
	    if(posiTxt){
		    hpt  = posiTxt.clientHeight;
	    	if(hpt>h)h=hpt;
	    }

	    if(this.urlJson){
			d3.json(self.urlJson, function(donnes) {
				self.data = donnes;
				init();
			});
	    }else{
	    	init();
	    }
	    
		function init() {

		    if(self.data){
		    	if(self.verif)self.verif = self.data;
		    	self.data = parseData();
		    }
		    if(this.txt){
		    	self.data=parseText();
		    	//hypertextualise seulement les sélections des utilisateurs
		    	if(self.exi){
			    	hypertextualise();	    		
		    	}
			    //colorise le term de la recherche
			    showTerm();
		    	posiTxt.innerHTML = self.txt;
		    }
		    
			max = self.data.length;
			
			d3.select("#svg_"+self.idDoc).remove();
			
			svg = self.div.append("svg")
				.attr("id", "svg_"+self.idDoc)
				.attr("width", w)
				.attr("height", h);
			background = svg.append("g"),
				vis = svg.append("g")
					.attr("transform", "translate(" + [w >> 1, h >> 1] + ")"); 

			tooltip = d3.select("body")
			    .append("div")
			    .attr("class", "term")
			    .style("position", "absolute")
			    .style("z-index", "10")
			    .style("visibility", "hidden")
			    .style("font","32px sans-serif")
			    .style("background-color","white")		    
			    .text("a simple tooltip");
			
			ext = d3.extent(self.data.map(function(x) { return parseInt(x.value); }));
			fontSize = d3.scale.log().domain([ext[0],ext[1]]).range([8, 128]);
			d3.layout.cloud().size([w, h])
				.words(self.data)
			    .rotate(0)
			    .spiral("rectangular")
			    .fontSize(function(d) {
			    	var n = d.value*16;
			    	if(self.exi){
			    		var uw = inUtiWords(d.key);
			    		if(uw) n = uw.value*8;
			    	}
			    	if(self.global)n=fontSize(d.value);
			    	if(n>h)n=h/2;
			    	return n; 
			    	})
				.text(function(d) { 
					return d.key; 
					})
			    .on("word", progress)
			    .on("end", draw)
			    .start();			
		}
	    

		    	
		function draw(words) {
			var text = vis.selectAll("text")
		        .data(words)
			    .enter().append("text")
		    	  	//.style("fill", function(d) { return fill(d.text.toLowerCase()); })
		    	  	.style("fill", function(d) {
		    	  		if(self.exi && inUtiWords(d.text))
		    	  			return "steelblue"; 
		    	  		if(self.term && self.term.indexOf(d.text)>0)
		    	  			return "blue";
		    	  		else
		    	  			return colorTag;
		    	  	})
		        	.style("font-size", function(d) { 
		        		return d.size + "px"; 
		        		})
			        .attr("text-anchor", "middle")
		    	    .attr("transform", function(d) { return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")"; })
		        	.text(function(d) { return d.text; })
		        	.on("click", function(d) {
		        		var mt = d3.select(this);
		        		if(self.global){
		        			vis.selectAll("text").style("fill","black");
		        			d3.select(this).style("fill","blue");
		        			chargeTag(d);	
		        		}
		        		if(self.verif){
		        			var c = "red";
        					self.poidsTag = -1;
		        			self.verif.forEach(function(v) {
		        				if(v.code == d.text && v.on==1){
		        					c = "green";
		        					self.poidsTag = 1;
		        					return;
		        				}
		        			});		        				
		        			d3.select(this).style("fill",c);
		        		}
		        		if(self.exi || self.sauve){
							console.log(self.idDoc+" "+d.text+" "+self.poidsTag);
							saveTag(d.text, self.poidsTag, "tag_"+self.idDoc);
		        		} 
		        	})
		        	.on("mouseover", function(d, i) { 
		        		if(self.exi){
		        			var c;
		        			if(self.poidsTag<1)c="yellow"; else c="red";
		        			d3.select(this).style("fill", c);
		        		}
		        		if(self.global) 
		        			return tooltip.style("visibility", "visible");		        		
		        		})
		        	.on("mouseout", function(d, i) { 
		        		if(self.exi) d3.select(this).style("fill", "black");
		        		if(self.global) return tooltip.style("visibility", "hidden");
		        		})
	    	        .on("mousemove", function(d, i){
	    	        	if(self.global) return tooltip
			        		.style("top", (event.pageY+10)+"px")
			        		.style("left",(event.pageX+10)+"px")
	    	        		.text(d.text);
	    	        	})
		        	.attr("cursor", function() { if(self.exi || self.global || self.verif) return "pointer";})
		        	;
		}
		function progress(d) {
			statusText.text(++complete + "/" + max);
		}
				
		function parseText() {
			tags = {};
			var cases = {};
			self.txt.split(self.wordSeparators).forEach(function(word) {
				var j = word.search("&quot;");
				if(j==0){
					word = word.substr(6);
				}else if(j>0){
					word = word.substr(0, j);
				}
				var i = word.search(self.elision);
				if(i>0)word = word.substr(i+1);
				word = word.replace(self.punctuation, "");
				if (self.stopWords.test(word.toLowerCase())) return;
				if (word.length <= 2) return;
		    	if(self.exi){
		    		var uw = inUtiWords(word);
		    		if(uw.value<0) return;
		    	}				
				word = word.substr(0, maxLength);
				cases[word.toLowerCase()] = word;
				tags[word = word.toLowerCase()] = (tags[word] || 0) + 1;
			});
			tags = d3.entries(tags).sort(function(a, b) { return b.value - a.value; });
			tags.forEach(function(d) {d.key = cases[d.key];});
			return tags;
		}

		function parseData() {
			tags = {};
			var cases = {};
			var j=0;
			self.data.forEach(function(d) {
				if (d.value <= 0) return;
				if(j>maxTag) return;
				var word = d.code;
				var i = word.search(self.elision);
				if(i>0) word = word.substr(i+1);
				word = word.replace(self.punctuation, "");
				var wlc = word.toLowerCase();
				if (self.stopWords.test(wlc)) return;
				if (word.length <= 2) return;
				word = word.substr(0, maxLength);
				cases[wlc] = word;
				//vérifie s'il faut additionner les tags
				if(tags[wlc]){
					tags[wlc] = parseInt(tags[wlc]) + parseInt(d.value);					
				}else
					tags[wlc] = d.value;
				j++;
			});
			tags = d3.entries(tags).sort(function(a, b) { return b.value - a.value; });
			tags.forEach(function(d) {
				d.key = cases[d.key];
				});
			return tags;
		}
		
		function inUtiWords(txt) {
			 for(var i= 0; i < self.utiWords.length; i++)
			 {
				 if(txt.toLowerCase()==self.utiWords[i]['code']){
					 return self.utiWords[i];					 
				 } 
			 }
			 return false;
		}
		function hypertextualise() {
			 var d, reg, str;
			 for(var i= 0; i < self.data.length; i++)
			 {
				 d = self.data[i];
				 reg=new RegExp("("+d.key+")", "g");
				 str = "<span id='tag_"+self.idDoc+"' class='tag' v='"+d.value+"'>$1</span>";
				 self.txt =  self.txt.replace(reg, str);
			 }
		}

		function showTerm() {
			var arr = self.term.split(" and ");
			if(arr.length==1) arr = self.term.split(" or ");
			 for(var i= 0; i < arr.length; i++)
			 {
				 reg=new RegExp("("+arr[i]+")", "g");
				 str = "<span id='tag_"+self.idDoc+"' class='term' >$1</span>";
				 self.txt =  self.txt.replace(reg, str);				 
			 }			
		}
		
  };  
  
  return this.tc();
}