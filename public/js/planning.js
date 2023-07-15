/**
 * Class permettant de gérer l'affichage des planning google et le calcul de stats de formation
 * ATTENTION le composant CalHeatMap nécessite d3.V3
 * 
 */
class planning {
    constructor(params) {
        var me = this;
        this.idCal = params.idCal;
        this.dtMin = params.dtMin;
        this.dtMax = params.dtMax;
        this.calY = new CalHeatMap();	
        this.gData = false;
        this.cours = params.cours;
        this.inters = params.inters;
        this.resp = params.resp;
        this.cumulType = {};
        this.cumulInt = {};
        this.cumulCou = {};
        this.cumulPlanInt = [];
        this.divResult = params.divResult;
        var diffDate = dateDiff(this.dtMin, this.dtMax), 
        nbMois = parseInt(diffDate.day/30), 
        sizeCell = 18, 
        planningFormat1 = d3.time.format("%A %d %B %Y de %H:%M"), 
        planningFormat2 = d3.time.format(" à %H:%M"), 
        jourFormat = d3.time.format("%x"), 
        heureFormat = d3.time.format("%X"), 
        arrCreaMotif = [], nbMotif=0,
        color = d3.scale.linear().domain([0,this.cours.length])
            .interpolate(d3.interpolateHcl)
            .range([d3.rgb("#007AFF"), d3.rgb('#FFF500')]);
        
        var gridCoursNbHeure = {
            header: "Nombre d'heure par cours",
            show: {toolbar		: true,
                    toolbarReload   : false,
                    toolbarColumns  : true,
                    toolbarSearch   : true,
                    toolbarAdd      : false,
                    toolbarDelete   : false,
                    toolbarSave		: false,
                    header: true, 
                    selectColumn: false,
                    columnHeaders: true},
            name: 'gridCoursNbHeure',         
            columns: [               
                { field: 'recid', caption: 'ID', type: 'text', size: '100px', hidden: true },
                { field: 'diplome', caption: "Diplôme", type: 'text', size: '100px', sortable: true },
                { field: 'UE', caption: "UE", type: 'text', size: '100px', sortable: true },
                { field: 'EC', caption: "EC", type: 'text', size: '100px', sortable: true },
                { field: 'apogee', caption: 'Apogée', type: 'int', size: '100px', sortable: true },
                { field: '%CM', caption: "% CM", type: 'text', size: '100px', sortable: true },
                { field: '%TD', caption: "% TD", type: 'text', size: '100px', sortable: true },
                { field: '%TP', caption: "% TP", type: 'text', size: '100px', sortable: true },
                { field: 'nbHeure', caption: "Nb d'heure", type: 'text', size: '100px', sortable: true },
                { field: 'nbHeureTD', caption: "Nb d'heure TD", type: 'text', size: '100px', sortable: true},
                { field: 'intervenants', caption: "Intervenants", type: 'text', size: '100%', sortable: true },
            ],
            toolbar: {
                items: [{id: 'export',type: 'button',text: 'Exporter',icon: 'fas fa-file-export'}],
                onClick: function (event) {
                    if (event.target == 'export') {
                        me.showCSV(w2ui['gridCoursNbHeure'].records);
                    }
                }
            }
        };	 

        var gridItvsNbHeure = {
            header: "Nombre d'heure par intervenants",
            show: {toolbar		: true,
                    toolbarReload   : false,
                    toolbarColumns  : true,
                    toolbarSearch   : true,
                    toolbarAdd      : false,
                    toolbarDelete   : false,
                    toolbarSave		: false,
                    header: true, 
                    selectColumn: false,
                    columnHeaders: true},
            name: 'gridItvsNbHeure',         
            columns: [               
                { field: 'recid', caption: 'ID', type: 'text', size: '100px', hidden: true },
                { field: 'prenom', caption: "Prénom", type: 'text', size: '100px', sortable: true },
                { field: 'nom', caption: "Nom", type: 'text', size: '100px', sortable: true },
                { field: 'nbHeure', caption: "Nb d'heure", type: 'text', size: '100px', sortable: true },
                { field: 'nbHeureTD', caption: "Nb d'heure TD", type: 'text', size: '100px', sortable: true},
                { field: 'cours', caption: "cours", type: 'text', size: '100%', sortable: true },
            ],
            toolbar: {
                items: [{id: 'export',type: 'button',text: 'Exporter',icon: 'fas fa-file-export'}],
                onClick: function (event) {
                    if (event.target == 'export') {
                        me.showCSV(w2ui['gridItvsNbHeure'].records);
                    }
                }
            }
        };	 

        var gridPlanItvs = {
            header: "Planning des intervenants",
            show: {toolbar		: true,
                    toolbarReload   : false,
                    toolbarColumns  : true,
                    toolbarSearch   : true,
                    toolbarAdd      : false,
                    toolbarDelete   : false,
                    toolbarSave		: false,
                    header: true, 
                    selectColumn: false,
                    columnHeaders: true},
            name: 'gridPlanItvs',         
            columns: [               
                { field: 'recid', caption: 'ID', type: 'text', size: '100px', hidden: true },
                { field: 'intervenant', caption: "Intervenant", type: 'text', size: '30%', sortable: true },
                { field: 'date', caption: "Date", type: 'text', size: '30%', sortable: true },
                { field: 'cours', caption: "Cours", type: 'text', size: '30%', sortable: true },
                { field: 'lieu', caption: "Lieu", type: 'text', size: '100px', sortable: true},
            ],
            toolbar: {
                items: [{id: 'export',type: 'button',text: 'Exporter',icon: 'fas fa-file-export'}],
                onClick: function (event) {
                    if (event.target == 'export') {
                        me.showCSV(w2ui['gridPlanItvs'].records);
                    }
                }
            }

        };	 

        var gridRecapItvsCours = {
            header: "Récapitulatif intervenants / cours",
            show: {toolbar		: true,
                    toolbarReload   : false,
                    toolbarColumns  : true,
                    toolbarSearch   : true,
                    toolbarAdd      : false,
                    toolbarDelete   : false,
                    toolbarSave		: false,
                    header: true, 
                    selectColumn: false,
                    columnHeaders: true},
            name: 'gridRecapItvsCours',         
            columns: [               
                { field: 'recid', caption: 'ID', type: 'text', size: '100px', hidden: true },
                { field: 'formation', caption: "Formation", type: 'text', size: '30%', sortable: true },
                { field: 'intervenant', caption: "Intervenant", type: 'text', size: '30%', sortable: true },
                { field: 'cours', caption: "Cours", type: 'text', size: '30%', sortable: true },
                { field: 'apogee', caption: "Apogée", type: 'text', size: '100px', sortable: true},
                { field: 'semestre', caption: "Semestre", type: 'text', size: '100px', sortable: true},
                { field: 'nbH', caption: "Nb. d'heure", type: 'text', size: '100px', sortable: true},
            ],
            toolbar: {
                items: [{id: 'export',type: 'button',text: 'Exporter',icon: 'fas fa-file-export'}],
                onClick: function (event) {
                    if (event.target == 'export') {
                        me.showCSV(w2ui['gridRecapItvsCours'].records);
                    }
                }
            }
        };	 

        var gridTypeItvsCours = {
            header: "Nb d'heure par type d'intervenant",
            show: {toolbar		: true,
                    toolbarReload   : false,
                    toolbarColumns  : true,
                    toolbarSearch   : true,
                    toolbarAdd      : false,
                    toolbarDelete   : false,
                    toolbarSave		: false,
                    header: true, 
                    selectColumn: false,
                    columnHeaders: true},
            name: 'gridTypeItvsCours',         
            columns: [               
                { field: 'recid', caption: 'ID', type: 'text', size: '100px', hidden: true },
                { field: 'Type', caption: "Type intervenant", type: 'text', size: '130px', sortable: true },
                { field: 'nbHeure', caption: "Nb. d'heure", type: 'text', size: '130px', sortable: true},
                { field: 'nbHeureTD', caption: "Nb. d'heure chargées", type: 'text', size: '130px', sortable: true},
                { field: 'nbHeureTuto', caption: "Nb. d'heure tutorat", type: 'text', size: '130px', sortable: true},
                { field: 'nbHeureStage', caption: "Nb. d'heure stage", type: 'text', size: '130px', sortable: true},
                { field: 'nbHeureJury', caption: "Nb. d'heure jury", type: 'text', size: '130px', sortable: true},
                { field: 'nbHeurePro', caption: "Nb. d'heure contrat", type: 'text', size: '130px', sortable: true},
            ],
            toolbar: {
                items: [{id: 'export',type: 'button',text: 'Exporter',icon: 'fas fa-file-export'}],
                onClick: function (event) {
                    if (event.target == 'export') {
                        me.showCSV(w2ui['gridTypeItvsCours'].records);
                    }
                }
            }
        };	         

        this.showCSV = function(data){
            let csv = me.convertToCSV(data);
            w2popup.open({
                title: 'Données CSV',
                body: '<div class="w2ui-centered" style="">'
                    //+'<label for="csv">Les données :</label>'
                    +'<textarea id="csv" name="csv" rows="'+data.length+'" cols="100">'
                    +csv
                    +'</textarea>',
                width:800,     // width in px
                height:600,     // height in px
            });
        }
        
        this.init = function () {
            let urlData = "../planning/events?idCal="+me.idCal+"&timeMax="+me.dtMax.toISOString()+"&timeMin="+me.dtMin.toISOString();
            //urlData = "../../data/planning/eventsDWM_18-19.json";
         
            this.patienter();

            this.calY.init({
                domain:"month", subDomain: "day",cellSize: sizeCell, subDomainTextFormat: "%d", cellPadding: 4, domainMargin: 6
                ,range:nbMois
                ,legend: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ,start: me.dtMin
                ,data:urlData
                ,onClick: function(date, nb) {
                    //récupère les datas correspondant à la date
                    var rs = me.getData(date);
                    //affiche les datas
                    me.showData(rs);
                }
                ,afterLoadData: parser
                ,displayLegend: false, tooltip:false
                ,previousSelector: "#btnPrev"
                ,nextSelector: "#btnNext"											
                });				           
        };

        this.patienter = function () {
            w2popup.open({
                width: 500,
                height: 300,
                title: 'Chargement des données',
                body: '<div class="w2ui-centered"></div>',
                showMax: false
            });
            w2popup.lock('Patientez...', true);
        }
        

        this.update = function(){
            this.patienter();
            let urlData = "../planning/events?idCal="+me.idCal+"&timeMax="+me.dtMax.toISOString()+"&timeMin="+me.dtMin.toISOString();
            me.calY.update(urlData,true,me.calY.RESET_ALL_ON_UPDATE);
        };        

        this.destroy = function(){
            me.calY.destroy();
        };        


        // JSON to CSV Converter
        //merci à https://stackoverflow.com/questions/8847766/how-to-convert-json-to-csv-format-and-store-in-a-variable
        this.convertToCSV = function(objArray) {
            var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
            //récupère le lignes d'entète
            var str = Object.keys(array[0]).join()+ '\r\n';       

            for (var i = 0; i < array.length; i++) {
                var line = '';
                for (var index in array[i]) {
                    if (line != '') line += ','

                    line += array[i][index];
                }

                str += line + '\r\n';
            }

            return str;
        }

        // Fonction formater les données
        function parser(data) {

            if(!data){
                w2popup.close();
                w2alert('Pas de données dans cette agenda.').done(function () {return;});
                return;
            } 

            me.gData = me.gData ? me.gData : data;
        
            //initialisation des cumuls
            for(var i=0; i < me.inters.length; i++){
                me.cumulType[me.inters[i]["Type"]]={"nbHeure":0,'nbHeureTD':0,'nbHeureTuto':0,'nbHeurePro':0,'nbHeureStage':0,'nbHeureJury':0,'Type':me.inters[i]["Type"]};
                me.cumulInt[me.inters[i]["Nom"]]= {'nbHeure':0,'nbHeureTD':0,'cours':[]};
            }
            for(var i=0; i < me.cours.length; i++){
                me.cumulCou[me.cours[i]["apogee"]]= {'nbHeure':0,'nbHeureTD':0,'intervenants':[]};
            }
        
            //calcule les data pour le calendrier
            var rs = [];
            var arrMotif = [];
            var idMotif;				
            var oldDate = 0;
            var idxDate = 0;
            var iC;
            me.gData.forEach(function(d) {
                if(d.summary){
                    //récupère le code du cours
                    var arrCours = d.summary.split(":")
                    //vérifie si le cours est valide
                    if(arrCours.length >= 3){
                        var ec = arrCours[0].trim();
                        var interv = arrCours[2].trim();
                        //récupère le cours
                        iC = me.cours.filter(function(c){
                            return c['apogee']==ec;
                        });
                        if(iC.length==0){
                             console.log("index du cours non trouvé :"+ec);
                        }
                        d['cours']=iC[0];
                        
                        //récupère les intervenants
                        d['intervenants']=[];
                        var arrInts = interv.split(",");							
                        arrInts.forEach(function(npInt){
                            var arrInt = npInt.trim().split(" ");
                            var iI;
                            //vérifie si l'intervenenat a un prénom
                            if(arrInt.length > 1)
                                iI = me.inters.filter(function(itv){
                                    return itv['Nom']==arrInt[1] && itv['Prénom']==arrInt[0];
                                });
                            else
                                iI = me.inters.filter(function(itv){
                                    return itv['Nom']==arrInt[0];
                                });
                            if(iI.length==0){
                                 console.log("index de l'intervenant non trouvé :"+interv);
                            }
                            d['intervenants'].push(iI[0]);
                        });
        
                        //on ne prend en compte qu'une date par jour
                        var curDate = new Date(d.start);
                        var finDate = new Date(d.end);
                        var sStart = jourFormat(curDate);
                        var dateStart = jourFormat.parse(sStart);
                        var heureStart = curDate.getHours();
                        if(d.intervenants.length && d.cours){
                            var nbH = d.duree/3600;
                            var nbHCM = d.cours['%CM'] ? (d.cours['%CM']/100)*nbH : 0;
                            var nbHTD = d.cours['%TD'] ? (d.cours['%TD']/100)*nbH : 0;
                            var nbHTP = d.cours['%TP'] ? (d.cours['%TP']/100)*nbH : 0;
                            var nbTD = (nbHCM*1.5)+nbHTP+nbHTD;
                            nbTD = nbTD == 0 ? nbH : Math.round(nbTD*100)/100;
                            //console.log(dateStart+' '+iC+' '+iI+' CM='+nbHCM.toFixed(2)+' TD='+nbHTD.toFixed(2)+' TP='+nbHTP.toFixed(2)+' ='+nbTD);
                            //incrémente le compteur de d.cours
                            me.cumulCou[d.cours["apogee"]].cours = d.cours;
                            me.cumulCou[d.cours["apogee"]]["nbHeure"] += nbH;
                            me.cumulCou[d.cours["apogee"]]["nbHeureTD"] += nbTD;
                            //incrémente le compteur des intervenants
                            d['intervenants'].forEach(function(itv){
                                me.cumulInt[itv["Nom"]].itv = itv;
                                me.cumulInt[itv["Nom"]]["nbHeure"] += nbH;
                                me.cumulInt[itv["Nom"]]["nbHeureTD"] += nbTD;
                                //incrémente le compteur des types d'intervenants
                                if(ec=="TUTORAT"){
                                    me.cumulType[itv["Type"]]["nbHeureTuto"]+=nbH;
                                }else if(ec=="STAGE"){
                                    me.cumulType[itv["Type"]]["nbHeureStage"]+=nbH;
                                }else if(ec=="CONTRAT"){
                                    me.cumulType[itv["Type"]]["nbHeurePro"]+=nbH;
                                }else if(ec=="JURY"){
                                    me.cumulType[itv["Type"]]["nbHeureJury"]+=nbH;
                                }else{
                                    me.cumulType[itv["Type"]]["nbHeure"]+=nbH;
                                    me.cumulType[itv["Type"]]["nbHeureTD"]+=nbTD;
                                }
    
                                //ajoute les liens entre les intervenants et les cours
                                var libCours = d.cours["apogee"]+" - "+d.cours["EC nom court"];
                                var itemFind = findArrayObjProp(me.cumulInt[itv["Nom"]]["cours"],"cours",libCours); 
                                if(itemFind==-1)
                                    me.cumulInt[itv["Nom"]]["cours"].push({"cours":libCours,"nbSeance":1,"nbHeure":nbH,"nbHeureTD":nbTD});
                                else{
                                    me.cumulInt[itv["Nom"]]["cours"][itemFind].nbSeance += 1;
                                    me.cumulInt[itv["Nom"]]["cours"][itemFind].nbHeure += nbH;
                                    me.cumulInt[itv["Nom"]]["cours"][itemFind].nbHeureTD += nbTD;
                                }
    
                                // ajoute les liens entre les cours et les intervenants
                                itemFind = findArrayObjProp(me.cumulCou[d.cours["apogee"]]["intervenants"],"Nom",itv["Nom"]); 
                                if(itemFind==-1)
                                    me.cumulCou[d.cours["apogee"]]["intervenants"].push({"Prénom":itv["Prénom"],"Nom":itv["Nom"],"nbSeance":1,"nbHeure":nbH,"nbHeureTD":nbTD});
                                else{
                                    me.cumulCou[d.cours["apogee"]]["intervenants"][itemFind].nbSeance += 1;
                                    me.cumulCou[d.cours["apogee"]]["intervenants"][itemFind].nbHeure += nbH;
                                    me.cumulCou[d.cours["apogee"]]["intervenants"][itemFind].nbHeureTD += nbTD;
                                }
    
                                //ajoute le détail du planning par intervenant
                                me.cumulPlanInt.push({'recid':me.cumulPlanInt.length,'intervenant':itv["Prénom"]+" "+itv["Nom"],'dateTri':curDate,'date':planningFormat1(curDate)+planningFormat2(finDate)
                                    ,'cours':d.cours["apogee"]+" - "+d.cours["EC nom court"],'lieu':d.location});
                                
                                idxDate = dateStart.getTime()/1000;
                                //la valeur correspond au code couleur correspondant à la journée
                                //rs[idxDate]=parseInt(dataCour[iC]['code jour']);
                                //construction du motif
                                if(oldDate != idxDate && oldDate!=0){
                                    //on ajoute le motif
                                    idMotif = creaMotif(arrMotif);
                                    //la valeur correspond à l'identifiant du motif
                                    rs[oldDate]=idMotif;//nbMotif;
                                    //rinitialise les valeurs
                                    arrMotif = [];
                                    oldDate = 0;
                                }
                                //on calcule la position du motif
                                var y = (sizeCell/24)*heureStart;
                                var h = (sizeCell/24)*nbH;
                                //on ajoute une couleur au motif
                                arrMotif.push({id:d.cours.recid, color:color(d.cours.recid), 'y':y, 'h':h});										
                                oldDate = idxDate;
                                
                            });
                        }
                    }else{
                        console.log("résumé de l'événement au mauvais format");
                        console.log(d);
                    }
                }else{
                    console.log("pas de résumé dans l'évènement : ");
                }
            });

            /*
            //création du dernier motif
            if(dataCour[iC]){
                idMotif = creaMotif(arrMotif);
                rs[oldDate]=idMotif;
            }
        
            //création des styles pour les cours
            var style = ""; 
            dataCour.forEach(function(d) {
                style += ".q"+d['code couleur']+"{fill: "+arrColor[d['code jour']]+";background-color: "+arrColor[d['code jour']]+";}";
            });
            var S = document.createElement('style');
            S.setAttribute("type", "text/css");
            var T = document.createTextNode(style);
                S.appendChild(T);
            var H = document.getElementsByTagName('head')[0];
            H.appendChild(S);
            */

            w2popup.close();

            return rs;

        };

        this.getData = function(date){
            var filtered = me.gData.filter(function(d) {
                var start = new Date(d.start);
                return start.toDateString() == date.toDateString();	
            });
            return filtered;
        }        

        this.getNbHeure = function(){
            let data = [];
            for (var k in me.cumulCou) {
                if(k!="undefined" && me.cumulCou[k].cours){
                    let itvs = "", cc = me.cumulCou[k];
                    cc.intervenants.forEach(function(i){
                        //itvs += Object.values(i).join()+' / ';
                        itvs += i['Prénom']+' '+i['Nom']+' '+i['nbSeance']+' séances = '+i['nbHeure']+' H '+i['nbHeureTD']+' HTD / ';
                    });
                    data.push({
                        'recid':cc.cours.recid,
                        'diplome':cc.cours.niveau+' '+cc.cours.parcours,
                        'UE':cc.cours['UE nom court'],
                        'EC':cc.cours['EC nom court'],
                        'apogee':cc.cours['apogee'],
                        '%CM':cc.cours['%CM'],
                        '%TD':cc.cours['%TD'],
                        '%TP':cc.cours['%TP'],
                        'nbHeure':cc.nbHeure,
                        'nbHeureTD':cc.nbHeureTD,
                        'intervenants':itvs,                    
                    });
                }
            }
            gridCoursNbHeure.records = data;
            //initialise le grid actif par défaut
            initGrid(me.divResult,gridCoursNbHeure);
        }

        this.getNbHInt = function(){
            let data = [];
            for (var k in me.cumulInt) {
                if(k!="undefined" && me.cumulInt[k].nbHeure){
                    let cours = "", itv = me.cumulInt[k];
                    itv.cours.forEach(function(i){
                        //itvs += Object.values(i).join()+' / ';
                        cours += i['cours']+' : '+i['nbSeance']+' séances = '+i['nbHeure']+' H -> '+i['nbHeureTD']+' HTD / ';
                    });
                    data.push({
                        'recid':itv.itv.recid,
                        'prenom':itv.itv['Prénom'],
                        'nom':itv.itv['Nom'],
                        'nbHeure':itv.nbHeure,
                        'nbHeureTD':itv.nbHeureTD,
                        'cours':cours,                    
                    });
                }
            }
            gridItvsNbHeure.records = data;
            //initialise le grid actif par défaut
            initGrid(me.divResult,gridItvsNbHeure);

        }

        this.getPlanInt = function(){
            let data = [];

            //tri le tableau
            me.cumulPlanInt.sort(function mysortfunction(a, b) {

                var o1 = a.intervenant.toLowerCase();
                var o2 = b.intervenant.toLowerCase();

                var p1 = a.dateTri;
                var p2 = b.dateTri;

                if (o1 != o2) {
                    if (o1 < o2) return -1;
                    if (o1 > o2) return 1;
                    return 0;
                }
                if (p1 < p2) return -1;
                if (p1 > p2) return 1;
                return 0;
                });
            
            gridPlanItvs.records = me.cumulPlanInt;
            //initialise le grid actif par défaut
            initGrid(me.divResult,gridPlanItvs);

        }

        this.getRecapIntCours = function(){
            let data = [];

            for (var k in me.cumulCou) {
                if(k!="undefined" && me.cumulCou[k].cours){
                    let itvs = "", cc = me.cumulCou[k];
                    cc.intervenants.forEach(function(i){
                        let r = {
                            'recid':cc.cours.recid,
                            'formation':cc.cours.niveau+'-'+cc.cours.parcours,
                            'intervenant':i['Prénom']+' '+i['Nom'],
                            'cours':cc.cours['EC nom court'],
                            'apogee':k,
                            'semestre':cc.cours.Semestre,
                            'nbH':i['nbHeureTD']
                        };
                        data.push(r);
                    });
                }
            }

            gridRecapItvsCours.records = data;
            //initialise le grid actif par défaut
            initGrid(me.divResult,gridRecapItvsCours);

        }        

        this.getNbHTypeInt = function(){
            let data = [], recid = 1;
            for (var k in me.cumulType) {
                let r = me.cumulType[k];
                r.recid = recid;
                data.push(r);
                recid ++;
            }
            gridTypeItvsCours.records = data;
            //initialise le grid actif par défaut
            initGrid(me.divResult,gridTypeItvsCours);

        }        

       function creaMotif(arrMotif){
            //création de l'identifiant du motif
            var id = "";
            arrMotif.forEach(function(d, i) {
                id += d.id;
            });
            //vérifie si le motif existe
            if(!arrCreaMotif[id]){					
                //création du motif
                arrCreaMotif[id] = nbMotif;
                me.calY.root.append("pattern")
                    .attr("id", "motif_"+arrCreaMotif[id])
                    .attr("patternUnits", "objectBoundingBox")
                    .attr("x", 0).attr("y", 0)
                    .attr("width", sizeCell).attr("height", sizeCell)
                    .selectAll("rect")
                    .data(arrMotif)
                    .enter().append("rect")
                    .attr("fill", function(d) { return d.color; })
                    .attr("x", 0)				
                    .attr("y", function(d) { return d.y; })				
                    .attr("width", sizeCell)				
                    .attr("height",  function(d) { return d.h; });
                nbMotif ++;									
            }
            //console.log("creaMotif: nbMotif="+nbMotif+", idMotif="+id+" => "+arrCreaMotif[id]);
            return arrCreaMotif[id];
        }
    

       function findArrayObjProp(arr, prop, val){
            var verif = -1;
            arr.forEach(function(d, i) {
                if(d[prop]==val) verif=i;
            });
            return verif;
        }

        function dateDiff(date1, date2){
            var diff = {};                          // Initialisation du retour
            var tmp = date2 - date1;
         
            tmp = Math.floor(tmp/1000);             // Nombre de secondes entre les 2 dates
            diff.sec = tmp % 60;                    // Extraction du nombre de secondes
         
            tmp = Math.floor((tmp-diff.sec)/60);    // Nombre de minutes (partie entière)
            diff.min = tmp % 60;                    // Extraction du nombre de minutes
         
            tmp = Math.floor((tmp-diff.min)/60);    // Nombre d'heures (entières)
            diff.hour = tmp % 24;                   // Extraction du nombre d'heures
             
            tmp = Math.floor((tmp-diff.hour)/24);   // Nombre de jours restants
            diff.day = tmp;
             
            return diff;
        }			

        this.init();
    }
}