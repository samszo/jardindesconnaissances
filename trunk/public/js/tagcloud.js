/**
 * 
 * 
 * merci à http://www.jasondavies.com/wordcloud/
 */
function tagcloud(config) {
	this.idDoc = config.idDoc;  
	this.txt = config.txt;  
	this.data = config.data;
	// From 
	// Jonathan Feinberg's cue.language, see lib/cue.language/license.txt.
	// 
	this.stopWords = /^(vousmême|puisqu|estàdire|très|cela|alors|donc|etc|for|tant|au|en|un|une|aux|et|mais|par|c|d|du|des|pour|il|ici|lui|ses|sa|son|je|j|l|m|me|moi|mes|ma|mon|n|ne|pas|de|sur|on|se|soi|notre|nos|qu|s|même|elle|t|que|celà|la|le|les|te|toi|leur|leurs|eux|y|ces|ils|ce|ceci|cet|cette|tu|ta|ton|tes|à|nous|ou|quel|quels|quelle|quelles|qui|avec|dans|sans|vous|votre|vos|été|étée|étées|étés|étant|suis|es|est|sommes|êtes|sont|serai|seras|sera|serons|serez|seront|serais|serait|serions|seriez|seraient|étais|était|étions|étiez|étaient|fus|fut|fûmes|fûtes|furent|sois|soit|soyons|soyez|soient|fusse|fusses|fût|fussions|fussiez|fussent|ayant|eu|eue|eues|eus|ai|as|avons|avez|ont|aurai|auras|aura|aurons|aurez|auront|aurais|aurait|aurions|auriez|auraient|avais|avait|avions|aviez|avaient|eut|eûmes|eûtes|eurent|aie|aies|ait|ayons|ayez|aient|eusse|eusses|eût|eussions|eussiez|eussent|i|me|my|myself|we|us|our|ours|ourselves|you|your|yours|yourself|yourselves|he|him|his|himself|she|her|hers|herself|it|its|itself|they|them|their|theirs|themselves|what|which|who|whom|whose|this|that|these|those|am|is|are|was|were|be|been|being|have|has|had|having|do|does|did|doing|will|would|should|can|could|ought|i'm|you're|he's|she's|it's|we're|they're|i've|you've|we've|they've|i'd|you'd|he'd|she'd|we'd|they'd|i'll|you'll|he'll|she'll|we'll|they'll|isn't|aren't|wasn't|weren't|hasn't|haven't|hadn't|doesn't|don't|didn't|won't|wouldn't|shan't|shouldn't|can't|cannot|couldn't|mustn't|let's|that's|who's|what's|here's|there's|when's|where's|why's|how's|a|an|the|and|but|if|or|because|as|until|while|of|at|by|for|with|about|against|between|into|through|during|before|after|above|below|to|from|up|upon|down|in|out|on|off|over|under|again|further|then|once|here|there|when|where|why|how|all|any|both|each|few|more|most|other|some|such|no|nor|not|only|own|same|so|than|too|very|say|says|said|shall)$/;
	this.punctuation = /["“!()&*+,-\.\/:;<=>?\[\\\]^`\{|\}~]+/g;
	this.elision = /[’']+/g;
	this.wordSeparators = /[\s\u3031-\u3035\u309b\u309c\u30a0\u30fc\uff70]+/g;
	
	this.tc = function() {
	    var fill = d3.scale.category20b(),
		w = 512,
		h= 128,
		scale = 1,
		complete = 0,
		statusText = d3.select("#status_"+this.idDoc),
		fontSize,
		maxLength = 30,
		self = this;
		
	    if(this.txt) this.data=parseText(this.txt);
		var max = this.data.length;
	    
		var svg = d3.select("#vis_"+this.idDoc).append("svg")
			.attr("width", w)
			.attr("height", h);
		var background = svg.append("g"),
			vis = svg.append("g")
				.attr("transform", "translate(" + [w >> 1, h >> 1] + ")"); 
		
		var ext = d3.extent(this.data.map(function(x) { return parseInt(x.value); }));
		fontSize = d3.scale.log().domain([ext[0],ext[0]]).range([16, 128]);
		d3.layout.cloud().size([w, h])
			.words(this.data)
		    .rotate(0)
		    .fontSize(function(d) { 
		    	var n = d.value*16;
		    	return n; 
		    	})
			.text(function(d) { return d.key; })
		    .on("word", progress)
		    .on("end", draw)
		    .start();
		    
	
		function draw(words) {
			var text = vis.selectAll("text")
		        .data(words)
			    .enter().append("text")
		    	  	.style("fill", function(d) { return fill(d.text.toLowerCase()); })
		        	.style("font-size", function(d) { 
		        		return d.size + "px"; 
		        		})
			        .attr("text-anchor", "middle")
		    	    .attr("transform", function(d) { return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")"; })
		        	.text(function(d) { return d.text; })
		        	.on("click", function(d) {alert(d.text);})
		        	;
		}
		function progress(d) {
			statusText.text(++complete + "/" + max);
		}
				
		function parseText(text) {
			tags = {};
			var cases = {};
			text.split(self.wordSeparators).forEach(function(word) {
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
				if (word.length <= 3) return;
				word = word.substr(0, maxLength);
				cases[word.toLowerCase()] = word;
				tags[word = word.toLowerCase()] = (tags[word] || 0) + 1;
			});
			tags = d3.entries(tags).sort(function(a, b) { return b.value - a.value; });
			tags.forEach(function(d) { d.key = cases[d.key]; });
			return tags;
		} 		
  };
  	  
  return this.tc();
}