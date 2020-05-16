class matricetriple {
    constructor(params) {
        var me = this;
        this.cont = d3.select("#"+params.idCont);
        this.margin = params.margin ? params.margin : 10;
        this.width = params.width ? params.width-this.margin : 400-this.margin;
        this.height = params.height ? params.height-this.margin : 400-this.margin;
        this.dataUrl = params.dataUrl ? params.dataUrl : false;
        this.data = params.data ? params.data :{'sujets':[],'objets':[],'predicats':[]}; 
        this.concepts = params.concepts ? params.concepts : false; 
        this.matrice = params.matrice ? params.matrice : false; 
        this.keytriple = {'sujets':[],'objets':[],'predicats':[]};            


        var svg, container, color, grille = this.width > this.height ? this.height/6 : this.width/6
            ,scB, isSelect = {'key':[],'tri':[]}, tooltip,
            //https://github.com/d3/d3-scale-chromatic  
            colorSelectRange = d3.scaleSequential().domain([0, 3]).interpolator(d3['interpolatePlasma']),
            colorSelect = colorSelectRange(3),
            colorDeselect = colorSelectRange(0);            

        this.init = function () {
        
            color = d3.scaleOrdinal(d3.schemeCategory10);

            if(!me.dataUrl && me.matrice)me.data=getRegularMatriceTriple(me.matrice);

            svg = this.cont.append("svg")
                .attr("width", me.width+'px')
                .attr("height", me.height+'px')
                .style("background-color","black");                
            container = svg.append("g");
            
            svg.call(
                d3.zoom()
                    .scaleExtent([.1, 4])
                    .on("zoom", function() { container.attr("transform", d3.event.transform); })
            );

            /*
            var gGrille = container.append("g")
                .attr("class", "grille");
            gGrille.selectAll("rect")
                .data(getDataGrille(10, 10))
                .enter()
                .append("rect")
                .attr("x", d => grille*d.l)
                .attr("y", d => grille*d.c)
                .attr("width", grille)
                .attr("height", grille)
                .style("fill", "rgba(203, 250, 25, 0.1)")
                .style("stroke", "rgba(0, 250, 0, 0.8)")
                .style("pointer-events", "none"); // to prevent mouseover/drag capture
            */

            //construction des échelles
            scB = d3.scaleBand()
                .paddingInner(0)
                .paddingOuter(0)
                .domain(me.data.sujets.map(s=>s.name))
                .range([grille, me.height-(grille*2)]);

            //création des labels
            var gSujet = container.append("g")
                .attr("class", "labelSujet")
                .attr("transform",d => "rotate(-30,"+(grille*2)+","+(grille*2)+")");
            gSujet.selectAll("rect")
                .data(me.data.sujets)
                .enter()
                .append("rect")
                .attr('id',d => d.id)
                .attr("x", grille)
                .attr("y", d => grille+scB(d.name))
                .attr("width", grille)
                .attr("height", scB.bandwidth())
                .style("fill", colorDeselect)
                .style("stroke", colorSelect)
                .style("pointer-events", "none"); // to prevent mouseover/drag capture
            gSujet.selectAll("text")
                .data(me.data.sujets)
                .enter()
                .append("text")
                .attr('id',d => 'txt'+d.id)
                .text(d => d.name)
                .attr("x", 10+grille)
                .attr("y", d => grille+scB(d.name)+(scB.bandwidth()/2))
                .attr("dy", 0)                
                .style("fill", colorSelect)
                .style("font-family", "Arial")
                .style("font-size", 12)
                .on('click',selectKey)
                .style("cursor", "cell");

            var gObjet = container.append("g")
                .attr("class", "labelObjet")
                .attr("transform","rotate(-90,"+(grille*2)+","+(grille)+")");
            gObjet.selectAll("rect")
                .data(me.data.objets)
                .enter()
                .append("rect")
                .attr('id',
                d => d.id)
                .attr("x", grille)
                .attr("y", d => scB(d.name))
                .attr("width", grille)
                .attr("height", scB.bandwidth())
                .style("fill", colorDeselect)
                .style("stroke", colorSelect)
                .style("pointer-events", "none"); // to prevent mouseover/drag capture
            gObjet.selectAll("text")
                .data(me.data.objets)
                .enter()
                .append("text")
                .attr('id',d => 'txt'+d.id)
                .text(d => d.name)
                .attr("x", 10+grille)
                .attr("y", d => scB(d.name)+(scB.bandwidth()/2))
                .attr("dy", 0)                
                .style("fill", colorSelect)
                .style("font-size", 12)
                .style("font-family", "Arial")
                .on('click',selectKey)
                .style("cursor", "cell");

            var gPredicat = container.append("g")
                .attr("class", "labelPredicat")
                .attr("transform",d => "rotate(30,"+((grille*2)+(scB.bandwidth()*me.data.objets.length))+","+(grille*2)+")");
            gPredicat.selectAll("rect")
                .data(me.data.predicats)
                .enter()
                .append("rect")
                .attr('id',d => d.id)
                .attr("x", (grille*2)+(scB.bandwidth()*me.data.sujets.length))
                .attr("y", d => grille+scB(d.name))
                .attr("width", grille)
                .attr("height", scB.bandwidth())
                .style("fill", colorDeselect)
                .style("stroke", colorSelect)
                .style("pointer-events", "none"); // to prevent mouseover/drag capture  
            gPredicat.selectAll("text")
                .data(me.data.predicats)
                .enter()
                .append("text")
                .attr('id',d => 'txt'+d.id)
                .text(d => d.name)
                .attr("x", 10+(grille*2)+(scB.bandwidth()*me.data.objets.length))
                .attr("y", d => grille+scB(d.name)+(scB.bandwidth()/2))                
                .attr("dy", 0)                
                .style("fill", colorSelect)
                .style("font-family", "Arial")
                .style("font-size", 12)
                .on('click',selectKey)
                .style("cursor", "cell")
                //.call(wrap, scB.bandwidth())
                ;

            //création des triangles
            drawPavageTriangle();   
                        
            //ajout du tooltip
            tooltip = d3.select("body").append("div")
                .attr("class", "tooltip")
                .style('position','absolute')
                .style('padding','4px')
                .style('background-color',colorDeselect)
                .style('color',colorSelect)
                .style('pointer-events','none');

        }

        function selectKey(d){
            //d3.select('.pavage').selectAll('polygon').style('fill',colorDeselect);
            d.select = d.select ? false : true;
            container.select('#'+d.id).style('fill',d.select ? colorSelect : colorDeselect);
            container.select('#txt'+d.id).style('fill',d.select ? colorDeselect : colorSelect);
            container.selectAll('.'+d.id).style('fill',t=>{
                if(d.select) t.select++ 
                else t.select--;
                //t.color = c == colorSelect ? d3.color(t.color).darker() : d3.color(t.color).brighter();
                t.color = colorSelectRange(t.select);
                return t.color;
            });
        }

        this.hide = function(){
          svg.attr('visibility',"hidden");
        }
        this.show = function(){
          svg.attr('visibility',"visible");
        }

        function getRegularMatriceTriple(mat){
            let treeConcept = getConceptChild(mat, 0);
            for (const [key, value] of Object.entries(me.keytriple)) {
                let i = 0
                treeConcept.forEach(c => {
                    let keyval = {};
                    for (const [k, v] of Object.entries(c)) {
                        keyval[k]=v;
                    }
                    keyval.id = key+i;
                    keyval.select = false;
                    keyval.color = colorDeselect;
                    value.push(keyval);
                    i++;
                });

            } 
            return me.keytriple;
        }

        function getConceptChild(mat, i){
            let treeConcept=[];
            mat[i].forEach(c => {
                let cpt = {'name':c};
                if(mat[i+1])
                    cpt.children = getConceptChild(mat, i+1);
                treeConcept.push(cpt);
            })
            return treeConcept;
        }


        //merci à https://gist.github.com/hunminkoh/3ddebac95a29f9407255
        function equiTriangle (w, x, y, pointe){
            let theta = 60 * Math.PI / 180.0;
            let points = [];
            if(pointe){
                points.push({'x':x, 'y':y})
                points.push({'x':points[0].x + w * Math.cos(theta), 'y':points[0].y + w * Math.sin(theta)})                    
                points.push({'x':points[0].x + w * Math.cos(theta*2), 'y':points[0].y + w * Math.sin(theta*2)})                    
            }else{
                points = [
                    {'x':x, 'y':y},
                    {'x':x + w * Math.cos(theta), 'y':y + w * Math.sin(theta)},
                    {'x':x+w, 'y':y},
                ];
            }
            return points;

        }

         function getTriangleHeight (w, x, y){
                var p = equiTriangle(w, x, y);
                var b = Math.sqrt(Math.pow(p[2].x-p[1].x, 2) + Math.pow(p[2].y-p[1].y, 2)) /2;
                var c = Math.sqrt(Math.pow(p[1].x-p[0].x,  2) + Math.pow(p[1].y-p[0].y,  2));    
                return Math.sqrt((c*c)-(b*b));
              };            

        function getSequence(){
            //calcul la régularité du pavage
            var nbConcept = me.data.sujets.length*me.data.objets.length*me.data.predicats.length;
            var nbTriangleBand = 1;            
            var nbTrianglePavage = nbTriangleBand*me.data.sujets.length*nbTriangleBand*me.data.predicats.length;
            while (nbTrianglePavage < nbConcept) {                
                nbTriangleBand ++;
                nbTrianglePavage = nbTriangleBand*me.data.sujets.length*nbTriangleBand*me.data.predicats.length;               
            }
            //calcule le nombre de vide
            var nbVide = nbTrianglePavage-nbConcept;
            if(nbVide==0)return {'nbParBand':nbTriangleBand,'max':nbConcept+2,'num':nbTriangleBand+1};
            //calcul la place des vides
            //var max = nbVide/(me.data.sujets.length*nbTriangleBand);
            var max = nbConcept/nbVide;            
            var num = Number.isInteger(max) ? nbTriangleBand+1 : 1;
            return {'nb':nbTrianglePavage,'nbParBand':nbTriangleBand,'max':max,'num':num};
        }

        function drawPavageTriangle(){
            //calcul la régularité du pavage
            var seq = getSequence();
            var largeur = scB.bandwidth()/seq.nbParBand;
            var hauteur = getTriangleHeight(largeur, 0, 0);
            //création de la liste des points
            var polygons = [];
            var t = 0, x, y = grille*2, pointe = false
                , col = 0, minCol = 0
                , maxCol = seq.nbParBand*me.data.sujets.length
                , vides=[];
            for (let i = 0; i < me.data.sujets.length; i++) {
                for (let j = 0; j < me.data.objets.length; j++) {
                    for (let k = 0; k < me.data.predicats.length; k++) {
                        console.log(t+" = "+i+" "+j+" "+k+" => "+t%3+" : "+(i*j*k));
                        let s = me.data.sujets[i];
                        //prise en compte des triangles vides
                        //séquence pour 3 concepts = plein 1, plein 2, plein 3, vide 1
                        //on commence par plein 3
                        if(seq.num > seq.max){
                            vides.push({'t':t,'col':col, 'maxCol':maxCol, 'minCol':minCol,'pointe':pointe,'y':y});
                            if(!pointe){
                                pointe = true;
                                col++;
                            }else{
                                pointe = false
                            } 
                            seq.num = 1;
                            //on compte aussi les vides 
                            //pour gérer le dépassement du périmètre
                            t++;
                        }

                        //gestion du changement de ligne
                        if(col >= maxCol){
                            maxCol --;
                            minCol += largeur/2;
                            col = 0;
                            y += hauteur;
                            pointe = false; 
                        }
                        //vérifie si on dépasse le périmètre
                        if(maxCol <= 0){
                            console.log('vide:'+t+' col:'+col+' minCol:'+minCol+' maxCol:'+maxCol);
                            //on rempli le dernier vide du périmétre
                            let v = vides.pop();
                            v = vides.pop();
                            while(v.col >= v.maxCol){
                                v = vides.pop();
                            }
                            col=v.col;
                            minCol=v.minCol;
                            pointe=v.pointe;
                            y=v.y;                            
                        }
                        //calcul des coordonnées
                        x = (grille*2)+minCol+(largeur*col);                        
                        let points = equiTriangle(largeur, x, y, pointe);
                        let poly = {'w':largeur,'color':colorSelectRange(0)
                            ,'name':t+":"+i+" "+j+" "+k
                            ,'points':points
                            ,'keys':[i,j,k]
                            ,'select':0
                            };
                        poly.cx = (poly.points[0].x + poly.points[1].x + poly.points[2].x) / 3;
                        poly.cy = (poly.points[0].y + poly.points[1].y + poly.points[2].y) / 3;
                        polygons.push(poly);        
                        //alternance de la pointe du triangle en haut = pointe
                        //ajoute une colonne si pointe en bas
                        if(!pointe){
                            col++;
                            pointe=true;
                        }else pointe=false;
                        //incrémente les compteurs
                        seq.num ++;
                        t++;
                    }
                }
            }

            //création du pavage
            var gPavage = container.append("g").attr("class", "pavage");
            gPavage.selectAll("polygon").data(polygons)
                .enter().append("polygon")
                .attr("class",d=>{
                    let i=0, c='';
                    for (const [key, value] of Object.entries(me.keytriple)) {
                        c+=key+d.keys[i]+' ';
                        i++;
                    }
                    return c;
                })
                .attr("points",function(d) {
                    var pointStr = "";
                    for(var i = 0; i<d.points.length; i++){
                        pointStr+= [d.points[i].x,d.points[i].y].join(",");
                        pointStr+=" ";
                    }
                    return pointStr;
                    })            
                .attr("fill", d => d.color)
                .attr("stroke",colorSelect)
                .attr("stroke-width",1)
                .on('mouseover', function(d){
                    //d3.select(this).attr('transform','scale(2)');
                    tooltip.transition()
                       .duration(700)
                       .style("opacity", 1);
                })
                .on('mousemove', function(d){      
                    getTooltip(d);
                })
                .on('mouseout', function(d){      
                    //d3.select(this).attr('transform','');
                    tooltip.transition()
                           .duration(500)
                           .style("opacity", 0);
                });            /*            
            gPavage.selectAll("text").data(polygons)
                .enter().append("text")
                .attr("x",d => d.cx)            
                .attr("y",d => d.cy)
                .text(d => d.name)            
                .attr("fill", d => "black")
                .attr("text-anchor","middle")
                .style("font-family", "Arial")
                .style("font-size", 12);    
            */        

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

        function getDataGrille(nbL, nbC){
            var dataset = []
            for (var i = 0; i < nbL; i++) {
                for (var j = 0; j < nbC; j++) {
                    dataset.push({"l":i, "c":j});
                }
            }
            return dataset
        }

        //merci beaucoup à https://bl.ocks.org/mbostock/7555321
        //pas mis en place car problème avec la rotation
        function wrap(text, width) {
            text.each(function() {
              var text = d3.select(this),
                  words = text.text().split(/\s+/).reverse(),
                  word,
                  line = [],
                  lineNumber = 0,
                  lineHeight = 1.1, // ems
                  y = text.attr("y"),
                  dy = parseFloat(text.attr("dy")),
                  tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", dy + "em");
              while (word = words.pop()) {
                line.push(word);
                tspan.text(line.join(" "));
                if (tspan.node().getComputedTextLength() > width) {
                  line.pop();
                  tspan.text(line.join(" "));
                  line = [word];
                  tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
                }
              }
            });
        }

        function getTooltip(d){
            //calcule les élément du tooltip
            //if(totalTemps[dt.temps]==0)totalTemps[dt.temps] = 0.1;			
            var s = me.data.sujets[d.keys[0]];	    	
            var o = me.data.objets[d.keys[1]];	    	
            var p = me.data.predicats[d.keys[2]];	    	
            tooltip.html("<h3>Sujet : "+s.name+"</h3>"
                +"<h3>Objet : "+o.name+"</h3>"
                +"<h3>Predicat : "+p.name+"</h3>"
                )
                .style("left", (d3.event.pageX + 12) + "px")
                .style("top", (d3.event.pageY - 28) + "px");
	    }

        if(me.dataUrl){
            d3.json(me.dataUrl).then(function(graph) {
                me.data = graph;
                me.init();
            });    
        }else{
            me.init();
        }

    }
}

  


