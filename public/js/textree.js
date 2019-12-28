class textree {
    constructor(params) {
        var me = this;
        this.text = params.text ? params.text : false;
        this.data = params.data ? params.data : false;
        this.cont = d3.select("#"+params.idCont);
        this.fontSize = params.fontSize ? params.fontSize : 18;
        this.urlDataMenu = params.urlDataMenu ? params.urlDataMenu : "../data/menuTextree.json";
        this.dataMenu = params.dataMenu ? params.dataMenu : {
            name: 'main',
            color: 'magenta',
            children: [{
              name: 'a',
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

        var svg, w, h, wMenu, hMenu, divMenu, svgMenu, color, hierarchie, divTexte, opacity=1;

        this.init = function () {

            //charge les data des menus
            if(me.urlDataMenu){
                d3.json(me.urlDataMenu).then(function(dt) {
                    me.dataMenu = dt;
                });    
            }

            //analyse du texte            
            if(!me.data)me.analyseText();
            hierarchie = d3.hierarchy(me.data);
            console.log(hierarchie);

            color = d3.scaleOrdinal().range(d3.quantize(d3.interpolateMagma, 4));

            //calcul la taille du menu
            hMenu = wMenu = this.cont.node().clientHeight/2;

            //ajoute le div du menu
            divMenu = d3.select('body').append("div")
                .attr("id", "txtreeMenu")
                .style('position','absolute')
                .style('height',hMenu+'px')
                .style('width',wMenu+'px')
                .style('display','none');
            
            //ajoute le texte
            divTexte = this.cont.selectAll('.txtree').data([hierarchie]).enter().append("div")
                .attr("class", "txtree")
                .style('height','100%')
                .style('padding',me.fontSize+'px')
                .style('margin',me.fontSize+'px')
                .style('background-color',d => me.getColor(d.depth,opacity))
                .on('click',me.clickTexte);

            var divPhrases = divTexte.selectAll('.txtreePhrase').data(function(d){
                return d.children
                }).enter().append("div")
                .attr("class", "txtreePhrase")
                .style('float','left')
                //.style('height',(me.fontSize*3)+'px')
                .style('padding',(me.fontSize/2)+'px')
                .style('padding-bottom',(me.fontSize)+'px')
                .style('margin-left',(me.fontSize/5)+'px')
                .style('margin-top',(me.fontSize/5)+'px')
                .style('background-color',d => me.getColor(d.depth,opacity))
                .on('click',me.clickPhrase);

            var divMots = divPhrases.selectAll('.txtreeMot').data(function(d){
                return d.children
                }).enter().append("div")
                .attr("class", "txtreeMot")
                .style('float','left')
                //.style('height',(me.fontSize*2)+'px')
                .style('padding',(me.fontSize/3)+'px')
                .style('padding-bottom',(me.fontSize)+'px')
                //.style('padding-top',(me.fontSize)+'px')
                .style('margin-left',(me.fontSize/10)+'px')
                .style('background-color',d => me.getColor(d.depth,opacity))
                .on('click',me.clickMot);
            var divCaract = divMots.selectAll('.txtreeCaract').data(function(d){
                    return d.children ? d.children : []; 
                }).enter().append("div")
                .attr("class", "txtreeCaract")
                .style('float','left')
                .style('height',(me.fontSize*1.5)+'px')
                .style('padding',(me.fontSize/4)+'px')
                .style('margin-left',(me.fontSize/15)+'px')
                .style('font-size',(me.fontSize)+'px')
                .style('background-color',d => me.getColor(d.depth,opacity))
                .text(function(d){
                    return d.data.txt;
                })
                .on('click',me.clickCaract);
           
        };

        //fonction pour contruire le menu dynamique
        this.showMenu = function(d,t,e){
            /*position centrée sur le div
            let x = t.offsetLeft+(t.offsetWidth/2)-(wMenu/2);
            let y = t.offsetTop+(t.offsetHeight/2)-(hMenu/2);
            */
            //position centrée sur le click
            let x = e.pageX-(wMenu/2);
            let y = e.pageY-(hMenu/2);
            divMenu
                .style('left',x+'px')
                .style('top', y+'px')
                .style('display','block');
            //déselectionne tous les div
            d3.hierarchy(this.cont.selectAll('.txtree').node()).descendants().forEach(e => 
                d3.select(e.data).style('background-color',me.getColor(e.depth,opacity)));            
            //sélectionne le div clicker et ses enfants
            d3.hierarchy(t).descendants().forEach(e => 
                d3.select(e.data).style('background-color','white'));
            //
            //supprime/affiche le menu
            if(!svgMenu){
                svgMenu = new menuSunburst({'idCont':divMenu.attr('id'),'data':me.dataMenu,'width':hMenu});
            }else
                svgMenu.show();
                  
        }        

        //fonctions pour gérer les évenements
        this.clickTexte = function(d){
            console.log(d);
            me.showMenu(d,this,d3.event);
            d3.event.stopPropagation();
        };
        this.clickPhrase = function(d){
            console.log(d);
            me.showMenu(d,this,d3.event);
            d3.event.stopPropagation();
        };
        this.clickMot = function(d){
            console.log(d);
            me.showMenu(d,this,d3.event);
            d3.event.stopPropagation();
        };
        this.clickCaract = function(d){
            console.log(d);
            me.showMenu(d,this,d3.event);
            d3.event.stopPropagation();
        };

        // Fonction pour analyser le texte
        //merci à https://github.com/raitucarp/paratree
        this.analyseText = function() {
            me.data = {'txt':me.text,'type':'paragraphe','children':me.parsePhrases(me.text)};
            console.log(me.data);
        }

        this.parsePhrases = function(text) {
            return text
            //merci à https://stackoverflow.com/questions/11761563/javascript-regexp-for-splitting-text-into-sentences-and-keeping-the-delimiter
            .match( /[^\.!\?]+[\.!\?]+/g )
            .map(function(t, i){return {'txt':t, 'type':'phrase', 'children':me.parseMots(t)};});
        }        

        this.parseMots = function(text) {
            return text
            .split(/\s{1,}/gi)
            .map(function(t, i){return {'txt':t, 'type':'mot', 'children':me.parseCaracteres(t)};});
        }        

        this.parseCaracteres = function(text) {
            return text
            .split("")
            .map(function(t, i){return {'txt':t, 'type':'caractere'};});
        }        
          
        this.getColor = function(i,o){
            let c = d3.color(color(i));
            c.opacity = o;
            return c;
        }

        this.init();
    }
}

  
