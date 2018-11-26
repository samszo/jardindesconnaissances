/**
Objet poru gérer l'animation d'un svg à partir de l'identifiant des éléments graphiques 
 */
function svg_animation(config) {
    config = typeof config !== 'undefined' ? config : {};
    this.svg = config.svg ? config.svg : d3.select("body").select("svg");
    this.left = config.left ? config.left : 10;
    this.bottom = config.bottom ? config.bottom : 30;
    this.delay = config.delay ? config.delay : 1000;
    this.max = config.max ? config.max : 10;
    this.min = config.min ? config.min : 0;
    this.seq = config.seq ? config.seq : 0;
    this.step = config.step ? config.step : 1;
    this.body = 	d3.select("body");
    this.keys = [];
    this.slide = 0;
    var t = this;
	//AJOUT du navigateur
	this.nav = this.body.append("div")
    		.attr("id",'navigAnim')
    		.style("position",'absolute')
    		.style("left",this.left+'px')
    		.style("bottom",this.bottom+'px');	
	this.nav.append("label")
    			.attr("for",'numAnim')
    			.text("Anim = ")
	    		.append("span")
	    			.attr("id",'numAnim-value')
	    			.text(this.min);
	this.nav.append("input")
		.attr("id",'numAnim')
		.attr("type",'range')
		.attr("max",this.max)
		.attr("min",this.min)
		.attr("value",this.seq)
		.attr("step",this.step)
		.on("input", function() {
	    	t.change(this.value);	
		});
	this.nav.append("span")
		.text(this.max)
		.attr("id",'numAnim-max');
    //récupère les éléments à animer
    var arrE = svg.selectAll("*")[0],keys;
    this.anims = [];
    arrE.forEach(function(e){
        if(e.getAttribute('anim')){
            var a = e.getAttribute('anim').split('_');
            a.forEach(function(ani){
                //var json = '[{"i":"0.1","a":"cache","d":1,"opt":"france"},{"i":"1.3","a":"montre"}]';
                var j = JSON.parse(ani);
                j.forEach(function(a){
                    a.d = a.d ? a.d : t.delay;
                    //pour gérer les clefs multiples
                    var nbK=0;
                    if(t.anims[a.i]){
                        t.anims[a.i].nb++;
                        nbK=t.anims[a.i].nb
                        a.i=a.i+'.'+nbK;
                    }
                    var s = a.i.split('.')[0];
                    a.e = e;
                    a.nb = nbK;
                    a.slide = s;                
                    //enregistre l'animation
                    t.anims[a.i]= a;    
                });
            })
        }    
    });
    //ordonne les animations
    this.keys = Object.keys(t.anims).sort();
    //enregistre l'ordre des animations
    //et recalcule les delais des clefs multiples
    t.keys.forEach(function(k,i){
        t.anims[k].ordre=i;
        if(t.anims[k].nb){
            var lastK = k+t.anims[k].nb-1;
            t.anims[lastK].d=t.anims[k].d;
            t.anims[k].d=0;
        }
    });        

    //met à jour le navigateur d'image s'il existe
	d3.select("#numAnim")
    			.attr("max",t.keys.length-1);
	d3.select("#numAnim-max")
		.text(t.keys.length-1);

    this.nextSlide = function(s)  {
        t.slide = s;
        t.keys.some(function(k) {
            if(t.anims[k].slide==s){
                t.seq = t.anims[k].ordre;
                return true;
            }
          });
        t.nextAnim();
    }

    this.nextAnim = function()  {
        if(t.seq<t.keys.length && t.anims[t.keys[t.seq]].slide == t.slide){
            var a = t.anims[t.keys[t.seq]];
            //var json = '[{"i":"0.1","a":"cache","d":1,"opt":"france"},{"i":"1.3","a":"montre"}]';
            console.log(t.seq+" : "+t.keys[t.seq]+" = "+a.a);	
            console.log(a);	
            if(a.a=='cache'){
                 d3.select(a.e).transition().duration(a.d).attr("visibility","hidden").each("end", t.nextAnim);
            }
            if(a.a=='montre'){
                d3.select(a.e).transition().duration(a.d).attr("visibility","visible").each("end", t.nextAnim);
            }
            if(a.a=='transforme'){
                var src = d3.select('#'+a.opt);
                if(src[0][0].localName=='ellipse'){
                    src.transition().duration(a.d)
                        .attr("cx",d3.select(a.e).attr("cx"))
                        .attr("cy",d3.select(a.e).attr("cy"))
                        .attr("rx",d3.select(a.e).attr("rx"))
                        .attr("ry",d3.select(a.e).attr("ry"))
                        .attr("style",d3.select(a.e).attr("style"))
                        .each("end", t.nextAnim);
                }
            }
            changeNavig();
            t.seq ++;
        }
    }
    
    function changeNavig(){
        // adjust the range text
        //console.log(numIma+" "+slide);
        d3.select("#numAnim-value").text(t.seq);
        d3.select("#numAnim").property("value", t.seq); 	
    }

    this.change = function() {

    };

    //this.nextSlide(0);
}