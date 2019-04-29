/*  This is a basic attempt to use d3 to process transitional slides from SVG files (i.e. Inkscape)
    The main objective here was to keep everything as simple as possible
        
    To create a slide, simply create a rectangle in inkscape where you set the object ID to "slide_" + a number
    This number can be either integer or float, so "slide_1","slide_2" and "slide_3.56" all work.
    
    The script sorts the slide-numbers and transitions between slides based on the final sorted order.  
    
    It is easy to add slides into a pre-existing ordering, simply by chosing a floating number in the middle of the two
    Example: If we want to add slide between "slide_2" and "slide_3" we simply add a rectancle called "slide_2.5"
    
    Keyboard definitions:
        Right arrow:    next slide
        Left arrow:     previous slide
        Home:           first slide
        End:            last slide
   
    Please be aware that you need to remove any layer transition is the svg file (or put the layer transition to 0,0)
    
    Each slide has a scale_x and scale_y functions that have pre-set range to the slide boundaries and a default
    domain of [0,1000], allowing the user to position objects easily onto each slide.
    
    
    ziggy.jonsson.nyc@gmail.com
*/
  
var svg, son, pSon, video, pVideo, objVideo, slides={},
    slide, delay, gH, gW, idAut;
// Timer variables
var timeoutHandle, now, startTime, isStarted = false, elapsedTime = 0, clock = Date, arrNav=[];

function oralite(s,options) {
    svg = s;
    delay = options.delay ? options.delay : 5000;
    gH  = options.height ? options.height : 600;
    gW  = options.width ? options.width : 800;

    //supprime les élément de navigation
    d3.select("#navig").remove();
    d3.select("#divSon").remove();
    
	//AJOUT du navigateur
	var nav = d3.select("body").append("div")
    		.attr("id",'navig')
    		.style("position",'absolute')
    		.style("left",'10px')
    		.style("bottom",'0px');
	
	nav.append("label")
    			.attr("for",'numSlide')
    			.text("Diapo = ")
	    		.append("span")
	    			.attr("id",'numSlide-value')
	    			.text("...");
	nav.append("input")
		.attr("id",'numSlide')
		.attr("type",'range')
		.attr("max",'10')
		.attr("min",'0')
		.attr("value",'1')
		.attr("step",'1')
		.on("input", function() {
	    	changeImage(this.value);	
		});
	nav.append("span")
		.text("38")
		.attr("id",'numSlide-max');
	//ajoute le lecteur de son
	son = d3.select("body").append("div")
		.attr("id",'divSon')
		.style("position",'absolute')
		.style("left",(gW/2)+'px')
		.style("bottom",(gH/2)+'px')
		.style('display','none')
		.style('border-style','solid');
	son.append("div")
		.text("FERMER")
		.style("text-align",'center')
		.style("cursor", "pointer")
		.style("background-color","white")
		.on("click",function(d){
				son.style('display','none');
				document.getElementById('playerSon').pause();
			})
	pSon = son.append("audio")
			.attr("id",'playerSon')
			.attr("autoplay",false)	
			.attr("controls",true);

	//ajoute le lecteur de vidéo
	video = d3.select("body").append("div")
		.attr("id",'divVideo')
		.style("position",'absolute')
		//.style("left",(gW/2)+'px')
		//.style("bottom",(gH/2)+'px')
		.style("height",'100%')
		.style("width",'100%')
		.style('display','none')
		.style('border-style','solid');
	pVideo = video.append("video")
			.attr("id",'playerVideo')
			.style("height",'100%')
			.style("width",'100%')
			.attr("autoplay",false)	
			.attr("controls",true)
			.html("Sorry, your browser doesn't support embedded videos.");
	objVideo = document.getElementById("playerVideo");

	//dimensionne le svg
	svg.attr("preserveAspectRatio","xMidYMid meet");
	svg.attr("width",gW);
	svg.attr("height",gH-10);

    rects = svg.selectAll("rect")._groups[0];
    slides = [];
    for (i=0;i<rects.length;i++) {
        id = rects[i].id;
        if (id.slice(0,6)=='slide_') { 
            slides[id.slice(6)]=rects[i] ;
            rects[i].scale_x = d3.scaleLinear().range([rects[i].x.baseVal.value,rects[i].x.baseVal.value+rects[i].width.baseVal.value]).domain([0,1000]);
            rects[i].scale_y = d3.scaleLinear().range([rects[i].y.baseVal.value,rects[i].y.baseVal.value+rects[i].height.baseVal.value]).domain([0,1000]);
        }

    }

    keys = Object.keys(slides).sort();
    slides.keys = keys;
    
    //ajoute un curseur sur les images avec un onclick
    var imgs = svg.selectAll("image")
    	.on("mouseover",function(d) {
	        if(this.getAttribute("onclick"))d3.select(this).style("cursor", "pointer");
	      })
        .on("mouseout",function(d) {
	    	  if(this.getAttribute("onclick"))d3.select(this).style("cursor", "default");
	      });    		
    //ajoute un curseur sur les textes avec un onclick
    svg.selectAll("text")
    	.on("mouseover",function(d) {
	        if(this.getAttribute("onclick"))d3.select(this).style("cursor", "pointer");
	      })
	    .on("mouseout",function(d) {
	    	  if(this.getAttribute("onclick"))d3.select(this).style("cursor", "default");
	      });    		
    //ajoute un curseur sur les g avec un onclick
    svg.selectAll("g")
    	.on("mouseover",function(d) {
	        if(this.getAttribute("onclick"))d3.select(this).style("cursor", "pointer");
	      })
	    .on("mouseout", function(d) {
	    	  if(this.getAttribute("onclick"))d3.select(this).style("cursor", "default");
	      });    		
    
    //cache les éléments à cacher
    var gs = svg.selectAll(".cache").style("opacity", 0);	    

    //met à jour le navigateur d'image s'il existe
		d3.select("#numSlide")
						.attr("max",keys.length-1);
		d3.select("#numSlide-max")
			.text(keys.length-1);
		
		//lance le minuteur
		resetTimer();		
		toggleTimer();

		//gestion des événements
		d3.select("body").on("touchmove", function() { if(slide<keys.length-1) {slide++; console.log(slide);}});

    d3.select(window).on("keydown", function() {
			console.log("Touche DEB : "+d3.event.keyCode+" = "+slide+" : "+keys[slide]);
        
			//récupère l'élément pour gérer les attributes de navigation
			let rct = d3.select('#'+slides[keys[slide]].id);
			//vérifie si une video est en cours
			console.log('gereSocket:media-type='+rct.attr('media-type')+' currentTime='+objVideo.currentTime+' duration='+objVideo.duration+' -> '+rct.attr('duration'));
			if(rct.attr('media-type')=="video" && objVideo.currentTime != 0 && objVideo.currentTime < objVideo.duration)return;
			console.log('OK for next');

				//vérifie le changement d'auteur
        if(d3.event.keyCode > 64 && d3.event.keyCode < 92){
            //récupère le nouveau slide
            idAut = d3.event.keyCode-65;
            var curSlide = keys[slide].split('_');
            var autSlide = idAut+'_'+curSlide[1]+'_'+curSlide[2];
            console.log("autSlide : "+autSlide);
            for (let index = 0; index < keys.length; index++) {
                if(autSlide==keys[index]){
										//gestion du websocket
										slide=index; 
										gereSocket({action: 'auteur',s:slide,a:idAut});
                }  
            }
        }    

        switch (d3.event.keyCode) {
          case 37: {
						if (slide>0) {
							slide=slide-1; 
							gereSocket({action: 'navigue',s: slide});
						};
        	  break}
          case 39: {
        	  if(slide<keys.length-1) {
        		  slide++; 
							gereSocket({action: 'navigue',s: slide});
        	  };
        	  break}
          case 36: {
        	  slide = 0;
						gereSocket({action: 'navigue',s: slide});
        	  break}
          case 35: {
        	  slide = keys.length -1; 
						gereSocket({action: 'navigue',s: slide});
        	  break}
        }
				console.log("Touche FIN : "+d3.event.keyCode+" = "+slide+" : "+keys[slide]);
        
     });

		// Start with the first slide si aucun slide n'est en cours
		if(typeof slide == 'undefined')slide=0
		gereSocket({action: 'navigue', s: slide});

		changeImage(0);

    return slides
}


// Toggle timer state
function toggleTimer (){
		isStarted = !isStarted;
		if(isStarted){
				startTime = clock.now();
				tickInstant();
		}else {
				clearTimeout(timeoutHandle);
		}
}


function resetTimer(){
		clearTimeout(timeoutHandle);
		isStarted = false;
		elapsedTime = now = startTime = 0;
		arrNav=[];
}


function tickInstant(time) {
		if(time)elapsedTime=time;
		now = clock.now();
		elapsedTime = elapsedTime + now - startTime;
		startTime = now;	
		let n = Date.now();
		setTimeout(tickInstant, 1000 - n % 1000);
}

function gereSocket(params){

		if(websocket){
			websocket.send(JSON.stringify(params));
		}else{
			next_slide()
		}			
}

function next_slide()  {

		//traitement de changement de slide
		clearVideo();
		clearAnimation();
    let vb = slides[keys[slide]].x.baseVal.value+" "+slides[keys[slide]].y.baseVal.value+" "+slides[keys[slide]].width.baseVal.value+" "+slides[keys[slide]].height.baseVal.value;
		svg.transition().duration(delay).attr("viewBox",vb);

		//vérification de la présence de vidéo
		let rct = d3.select('#'+slides[keys[slide]].id);
		if(rct.attr('media-type')=="video")joueVideo(rct);
		console.log("vb : "+vb);	

		//vérification de la présence d'un générateur
		let doc = document.getElementById('txt_'+keys[slide]);
		if(doc){
			let txt = d3.select('#txt_'+keys[slide]);
			if(txt.attr('generateur'))generateur(txt);
		}


    changeNavig(slide);
}

function showWebPage(url){
	console.log(url);
	if(bW2UI){
		//affiche l'url dans un iframe
		var html = '<iframe id="ifDiag" src="'+url+'" />';
	    w2popup.open({
	        width: 500,
	        height: 300,
	        body:html,
	        title: url,
	        showClose: true,
	        onOpen  : function (event) {
	            event.onComplete = function () {
	                	w2popup.max();
	            };
	        },	        
	    });		
	}else{
		document.getElementById("ifDiag").setAttribute("src",url);
		var u = document.getElementById("ifURL");
		u.setAttribute("href",url);
		u.innerHTML=url;
		diagIframe.showModal();		
	}
}

function changeImage(numIma){
   	// adjust the range text
	if(svg){
		slide = numIma;	
		next_slide();
	}
}

function changeNavig(numIma){
   	// adjust the range text
   	//console.log(numIma+" "+slide);
	d3.select("#numSlide-value").text(numIma);
	d3.select("#numSlide").property("value", numIma);
	arrNav.push({'numIma':numIma,'t':elapsedTime}); 	
}

function showCache(id){
	d3.select("#"+id).style("opacity", 1);	
}

// https://developers.google.com/youtube/iframe_api_reference?hl=fr
// 2. This code loads the IFrame Player API code asynchronously.
var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// 3. This function creates an <iframe> (and YouTube player)
//    after the API code downloads.
var player;
function onYouTubeIframeAPIReady() {
 /* 
 player = new YT.Player('player', {
    height: '360',
    width: '640',
    videoId: 'M7lc1UVf-VE',
    events: {
      'onReady': onPlayerReady,
      'onStateChange': onPlayerStateChange
    }
  });
  */
}

// 4. The API will call this function when the video player is ready.
function onPlayerReady(event) {
  event.target.playVideo();
}

// 5. The API calls this function when the player's state changes.
//    The function indicates that when playing a video (state=1),
//    the player should play for six seconds and then stop.
var done = false;
function onPlayerStateChange(event) {
  if (event.data == YT.PlayerState.PLAYING && !done) {
    setTimeout(stopVideo, 6000);
    done = true;
  }
}
function stopVideo() {
  player.stopVideo();
}
function playYoutube(idVideo){
	//affiche l'url dans un iframe
	var html = '	<div id="player"></div>';
    w2popup.open({
        width: 500,
        height: 300,
        body:html,
        title: 'Vidéo YouTube',
        showClose: true,
        onOpen  : function (event) {
            event.onComplete = function () {
                	w2popup.max();
                	  player = new YT.Player('player', {
                		    height: '100%',
                		    width: '100%',
                		    videoId: idVideo,
                		    events: {
                		      'onReady': onPlayerReady,
                		      'onStateChange': onPlayerStateChange
                		    }
                		  });
                	
            };
        },	        
    });			
}
function playSon(url){
	son.style('display','block');
	pSon.attr('autoplay',true)
		.attr("src",url);	
}
function joueVideo(obj){
	let bb = obj.node().getBBox()
	video.style('display','block');
	video.style('width',bb.width+'px');
	video.style('height',bb.height+'px');
	video.style('top',((gH/2)+bb.y-(bb.height/2))+'px');
	video.style('left',((gW/2)+bb.x-(bb.width/2))+'px');
	pVideo.attr('autoplay',true)
		.attr("src",obj.attr('media-file'));
	if(obj.attr('media-size')=='Fullscreen')
		objVideo.requestFullscreen();	
}
function clearVideo(){
	video.style('display','none');
	document.getElementById('playerVideo').pause();
}
function clearAnimation(){
	d3.selectAll('.europeana').remove();
}

function getParamUrl(param) {
	var vars = {};
	window.location.href.replace( location.hash, '' ).replace( 
		/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
		function( m, key, value ) { // callback
			vars[key] = value !== undefined ? value : '';
		}
	);

	if ( param ) {
		return vars[param] ? vars[param] : null;	
	}
	return vars;
}
function generateur(n) {
	if(n.attr('generateur')=='getImageText')getImageText(n)
}
function getImageText(txt) {
	if(typeof erpna !== "undefined"){
		//transforme les span en images
		let bb = txt.node().getBBox();
		txt.selectAll('tspan').each(function(){
			//erpna.findAleaImage(d3.select(this),setImageAnim);
			erpna.findImages(d3.select(this),setImagesAnim);
		})	
	}
}
function setImagesAnim(e, items){
	items.forEach(function(i){
		if(i.type!='SOUND'){
			setImageAnim(e, i);
			console.log(i.type);
		}
	});
}

function setImageAnim(e, item){
		//item vient d'europeana
		let imgSrc = item.edmPreview[0];
		//récupère la position du rectangle
		let idRect = e.attr('id').replace('txt','slide');
		let bb = d3.select('#'+idRect).node().getBBox();
		//ajoute l'image dans la scene avec une position aléatoire
		let aleaX = d3.randomUniform(bb.x, bb.x+bb.width);
		let aleaY = d3.randomUniform(bb.y, bb.y+bb.height);
		//dans une temporalité aléatoire
		let aleaTime = d3.randomUniform(3000, 6000);

		let img = d3.select("#"+svgId).append('image')
				.attr('id',item.id)
				.attr('class','europeana')
				.attr('height',"0%")
				.attr('x',aleaX())
				.attr('y',aleaY())
				.attr('xlink:href',imgSrc);
		//ajoute l'effet d'animation
		img.transition()
				.duration(aleaTime())
				.delay(aleaTime())
				.on("start", function repeat() {
						d3.active(this)
								.attr("height", "100%")
								.attr("x", bb.x)
								.attr("y", bb.y)
						.transition()
								.attr("height", "0%")
								.attr("x", aleaX())
								.attr("y", aleaY())
						.transition()
								.on("start", repeat);
				});                
	
}