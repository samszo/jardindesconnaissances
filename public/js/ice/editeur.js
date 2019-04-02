
prefUrl = '../'
var idUti=0, arrIEMLitem, 
    arrMnuAffichage = [{
        fct: showHideTexte,
        lib: "Affiche / Masque les textes"
    }, {
        fct: showHideLimite,
        lib: "Affiche / Masque les limites"
    }],
    urlMatriceIeml = "../ice/ieml?code=", bddNom = 'flux_formsem',
    ifIeml = document.getElementById("ifMatriceIEML").contentWindow,
    cptLigne = true, evtCellIEML = false, arrForms = [], sltForms, sltQuest,
    arrDico = [], iemlMatrice = [], iemlCartoForce = false;

getForms();
    

//chargement du dico IEML
d3.json(urlDico, function (err, data) {
    arrDico = data;
    arrDico.sort(function (a, b) {
        return a.FR.localeCompare(b.FR);
      });
    arrDico.forEach(function(c){
        c.text = c.FR;
    })
    var extentTaille = d3.extent(arrDico.map(function(d)  {
        return d.TAILLE;
        }));
    arrIEMLitem = arrDico.filter(function(d){
            return d.TAILLE <= 2;
        });    
    arrIEMLmatrices = arrDico.filter(function(d){
        return d.TAILLE > 2;
    });    
    /*création des menus
    d3.select("#mnuMatrices").selectAll("a").data(arrIEMLmatrices).enter().append("a")
        .attr("class", "dropdown-item")
        .attr("href", "#")
        .text(function (d) {
            return d.FR
        })
        .on('click', function (d) {
            //modifie la source de l'iframe
            d3.select('#ifMatriceIEML').attr('src', urlMatriceIeml + d.IEML);
            //affiche les menus
            d3.select('#sltAffichage a').attr('class', "nav-link dropdown-toggle")
        });
    */
    d3.select("#mnuAffichage").selectAll("a").data(arrMnuAffichage).enter().append("a")
        .attr("class", "dropdown-item")
        .attr("href", "#")
        .text(function (d) {
            return d.lib
        })
        .on('click', function (d) {
            //execute la function définie
            d.fct(d);
        });

    //gestion du champ multiselect
    $('#enum-itemIeml').w2field('enum', { 
        items: arrIEMLitem, 
        max: 1,
        openOnFocus: true,
        onAdd: function (event) {
            var c = 'white';//colorScale(event.item.count);
            event.item.style = 'background-color:'+c+'; border: 1px solid black;';
            sltConcept = event.item;
            //modifie la source de l'iframe
            d3.select('#ifMatriceIEML').attr('src', urlMatriceIeml + sltConcept.IEML);
            //affiche les menus
            d3.select('#sltAffichage a').attr('class', "nav-link dropdown-toggle")
        },
        onRemove: function(event){
            //removeGraphique();
            console.log(event);
        },
        renderItem: function (item, index, remove) {
            var c = 'red';// colorScale(item.count);
            var style = 'padding-right: 3px; color:black; background-color:'+c+';text-shadow: 1px 1px 3px white;';
            var html = remove + '<span class="fa-trophy" style="'+ style +'; margin-left: -4px;"></span>' + item.FR;
            return html;
        },
        renderDrop: function (item, options) {
            var c = 'green';//colorScale(item.count);
            var style = 'padding-right: 3px; color:black;text-shadow: 1px 1px 3px '+c+';';
            return '<span class="fa-star"></span><span style="'+ style +'">'+item.FR+'</span>';
        }
    });        
    $('#enum-matriceIeml').w2field('enum', { 
        items: arrIEMLmatrices, 
        max: 1,
        openOnFocus: true,
        onAdd: function (event) {
            var c = 'white';//colorScale(event.item.count);
            event.item.style = 'background-color:'+c+'; border: 1px solid black;';
            sltConcept = event.item;
            //modifie la source de l'iframe
            d3.select('#ifMatriceIEML').attr('src', urlMatriceIeml + sltConcept.IEML);
            //affiche les menus
            d3.select('#sltAffichage a').attr('class', "nav-link dropdown-toggle")
        },
        onRemove: function(event){
            //removeGraphique();
            console.log(event);
        },
        renderItem: function (item, index, remove) {
            var c = 'red';// colorScale(item.count);
            var style = 'padding-right: 3px; color:black; background-color:'+c+';text-shadow: 1px 1px 3px white;';
            var html = remove + '<span class="fa-trophy" style="'+ style +'; margin-left: -4px;"></span>' + item.FR;
            return html;
        },
        renderDrop: function (item, options) {
            var c = 'green';//colorScale(item.count);
            var style = 'padding-right: 3px; color:black;text-shadow: 1px 1px 3px '+c+';';
            return '<span class="fa-star"></span><span style="'+ style +'">'+item.FR+'</span>';
        }
    });        

});

function getForms(){

    //récupère la liste de formulaire enregistré
    d3.json('../ice/getlisteform?idBase='+bddNom, function (err, data) {
        var i = 1;
        data.forEach(function(d){
            var f = JSON.parse(d.note);
            setBoolean([f]);
            arrForms.push(f);
            i++;
        })
        w2ui.gForms.records = arrForms;
        w2ui.gForms.refresh();
    })
}

//gestion des boutons
$('#btnCreerForm').click(function () {
    var idEML = $('#sltMatrices').val();
    var txtStat = $("#sltMatrices option:selected").text();
});
$("#btnCptLigne input:radio").on('change', function () {
    cptLigne = $(this).attr('id') == 'cptCumul' ? false : true;
    console.log("cptLigne=" + cptLigne);
})

function addToBDD(dataSource, table){

    $.post("../ice/addform", {'data':dataSource,'table':table},
        function(data){
            if(data.erreur){
                w2alert(data.erreur);
            }else{
                //mis à jour des grids
                if(table=='form'){
                    data.result.questions=[];
                    arrForms.push(data.result);
                    w2ui.gForms.records=arrForms;
                    w2ui.gForms.refresh();
                    w2ui.gQuestions.clear();
                    w2ui.gQuestions.refresh();
                    w2ui.gProposition.clear();
                    w2ui.gProposition.refresh();    
                }
                if(table=='question'){
                    data.result.propositions=[];
                    sltForms.questions.push(data.result);
                    w2ui.gQuestions.records = sltForms.questions;
                    w2ui.gQuestions.refresh();
                    w2ui.gProposition.clear();
                    w2ui.gProposition.refresh();    
                }
                if(table=='prop'){
                    getCptDefinition(data.result);
                }
                if(table=='props'){
                    addPropositionLiens(data.result);            
                }
                if(table=='liens'){
                    //mise à jour des liens
                    dataSource.forEach(function(p){
                        p.liens.forEach(function(l){
                            data.result.forEach(function(lbdd){
                                if(lbdd.idEdge==l.idEdge)l.idL=lbdd.idL;
                            })
                        })
                        if(!sltQuest.propositions)sltQuest.propositions=[];
                        sltQuest.propositions.push(p);
                    })
                    //mise à jour des grid
                    w2ui.gProposition.records = sltQuest.propositions;
                    w2ui.gProposition.refresh();    
                }
                if(table=='reponse'){
                    sltForms.reponses.push(dataSource);
                    creaForm();
                    creaForceCarto();        
                }
                w2alert("L'ajout est fait.");
            }					 		
        }, "json")
    .fail(function(e) {
        w2alert( "erreur" );
    })
    .always(function() {
        d3.select('#pbPatienter').remove();
        patienter('',true);    
    });
}

function updateBDD(dataSource, table){

    $.post("../ice/updateform", {'data':dataSource,'table':table},
        function(data){
            if(data.erreur){
                w2alert(data.erreur);
            }else{
                w2alert("Les modifications sont enregistrées.");
            }					 		
        }, "json")
    .fail(function(e) {
        w2alert( "erreur" );
    })
    .always(function() {
        d3.select('#pbPatienter').remove();
        patienter('',true);    
    });
}


function deleteToBDD(dataSource, table){

    patienter('Supression '+table+' en cours...');
    $.post("../ice/deleteform", {'data':dataSource,'table':table},
        function(data){
            if(data.erreur){
                w2alert(data.erreur);
            }else{
                //mis à jour des grids
                if(table=='form'){
                    w2ui.gForms.refresh();
                    w2ui.gQuestions.clear();
                    w2ui.gQuestions.refresh();
                    w2ui.gProposition.clear();
                    w2ui.gProposition.refresh();    
                }
                if(table=='question'){
                    w2ui.gQuestions.refresh();
                    w2ui.gProposition.clear();
                    w2ui.gProposition.refresh();    
                }
                if(table=='prop'){
                    w2ui.gProposition.refresh();    
                }
                w2alert('Les supressions sont faites.')
            }					 		
        }, "json")
    .fail(function(e) {
        w2alert( "erreur" );
    })
    .always(function() {
        d3.select('#pbPatienter').remove();
        patienter('',true);    
    });
}


function chargeDataForm(data){

    //corrige les boolean et les recid
    data.forms.forEach(function(f){
        f.questions.forEach(function(q){
            if(q.propositions)setBoolean(q.propositions);
        });
    });

    //charge les grids
    w2ui.gQuestions.records = sltForms.questions = data.forms[0].questions;
    w2ui.gQuestions.refresh();
    w2ui.gProposition.clear();
    w2ui.gProposition.refresh();            
    //charge les réponses
    w2ui.gReponse.records = sltForms.reponses = data.reponses;
    w2ui.gReponse.refresh();

}


$('#btnGenForm').click(function () {
    creaForm();
})
$('#btnClearRep').click(function () {
    w2confirm('ATTENTION toutes les réponses seront supprimées.')
        .yes(function () {
            sltForms.reponses = [];
        })
        .no(function () {
            console.log('NO');
        });
})
$('#btnVizRepo').click(function () {
    //creaHexaCarto();
    creaForceCarto();
})



//gestion des fonction de menus
function showHideTexte(d) {
    ifIeml.iemlMatrice.showHideText();
}

function showHideLimite(d) {
    ifIeml.iemlMatrice.showHideLimite();
}


//création des grids
$('#gridForms').w2grid({
    name: 'gForms',
    header: 'Liste des formulaires',
    show: {
        toolbar: true,
        footer: true,
        header: true,
        toolbarSave: true,
        toolbarDelete: true,
        selectColumn: false,
        multiSelect: false,
    },
    columns: [{
        field: 'idForm',
        caption: 'IDF',
        size: '50px',
        hidden: true,
        sortable: true,
        resizable: true
        },{
            field: 'recid',
            caption: 'ID',
            size: '50px',
            hidden: true,
            sortable: true,
            resizable: true
        },
        {
            field: 'txtForm',
            caption: 'Nom du formulaire',
            size: '50%',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'iemlForm',
            caption: 'Code IEML',
            size: '50%',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'bTemps',
            caption: 'Temps de réponse',
            size: '120px',
            sortable: true,
            resizable: true,
            style: 'text-align: center',
            editable: {
                type: 'checkbox',
                style: 'text-align: center'
            }
        },
        {
            field: 'bGeo',
            caption: 'Position Géo',
            size: '100px',
            sortable: true,
            resizable: true,
            style: 'text-align: center',
            editable: {
                type: 'checkbox',
                style: 'text-align: center'
            }
        },
        {
            field: 'bdd',
            caption: 'Base de données',
            size: '110px',
            sortable: true,
            resizable: true
        },
    ],
    onClick: function (event) {
        if (w2ui.gProposition.getChanges().length > 0) {
            w2alert('Veuillez enregistrer les propositions.');
            return;
        }
        w2ui['gProposition'].clear();
        if (w2ui.gQuestions.getChanges().length > 0) {
            w2alert('Veuillez enregistrer les questions.');
            return;
        }
        w2ui['gQuestions'].clear();
        sltForms = this.get(event.recid);
        //récupère les données du formulaire
        if(sltForms.idForm){
            d3.json('../ice/getform?idBase='+bddNom+'&reponse=1&idForm='+sltForms.idForm, function (err, dataF){
                chargeDataForm(dataF);
            });     
        }else{
            w2ui.gQuestions.records = sltForms.questions;
            w2ui.gQuestions.refresh();
            w2ui.gProposition.clear();
            w2ui.gProposition.refresh();            
        }
    },
    onDelete: function (event) {

        var s = w2ui.gForms.getSelection();
    
        if (event.force) {
            //récupère les idForm
            let ids = [];
            s.forEach(function(id){
                let f = w2ui.gForms.get(id);
                if(f.idForm)ids.push(f.idForm);
            });
            //met à jour la base si besoin
            if(ids.length){
                deleteToBDD(ids,'form');    
            }
        }else{
            //Vérifie la présence de réponse
            let nbR = 0, nbQ=0, nbP=0;
            s.forEach(function(id){
                let f = w2ui.gForms.get(id);
                nbQ += f.questions ? f.questions.length : 0;
                if(f.questions){
                    f.questions.forEach(function(q){
                        nbP += q.propositions ? q.propositions.length : 0;
                    });
                }
                nbR += f.reponses ? f.reponses : 0;

            })
            if(nbR > 0 || nbQ > 0 || nbP > 0){
                let m = 'Vous allez supprimer : <br/>'
                    +s.length+' formulaire(s)<br/>'
                    +nbQ+' question(s)<br/>'
                    +nbP+' proposition(s)<br/>'
                    +nbR+' réponse(s)<br/>';
                w2obj.grid.prototype.msgDelete = m;
            }
        }
    },
    onSave: function (event) {
        let arrC = w2ui.gForms.getChanges();
        updateBDD(arrC, 'form');    
    },
    toolbar: {
        items: [{
            id: 'add',
            type: 'button',
            caption: 'Ajouter',
            icon: 'w2ui-icon-plus'
        }],
        onClick: function (event) {
            if (event.target == 'add') {
               addToBDD({'bGeo':true,'bTemps':true,'bdd':bddNom,
                'iemlForm':'','txtForm':'Nouveau formulaire'},'form');
            }
        }
    },
    records: arrForms
});
$('#gridQuestions').w2grid({
    name: 'gQuestions',
    header: 'Liste des questions',
    show: {
        toolbar: true,
        footer: true,
        header: true,
        toolbarSave: true,
        toolbarDelete: true,
        selectColumn: false,
        multiSelect: false,
    },
    columns: [{
        field: 'idQ',
        caption: 'IDQ',
        size: '50px',
        sortable: true,
        resizable: true
        },{
            field: 'recid',
            caption: 'ID',
            size: '50px',
            sortable: true,
            resizable: true
        },
        {
            field: 'txtQ',
            caption: 'Question',
            size: '50%',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'txtQieml',
            caption: 'Code IEML',
            size: '50%',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'nbProp',
            caption: 'Nb. proposition',
            size: '100px',
            sortable: true,
            resizable: true,
            render: 'int',
            editable: {
                type: 'int',
                min: 1,
                max: 100
            }
        },
    ],
    onClick: function (event) {
        if (w2ui.gProposition.getChanges().length > 0) {
            w2alert('Veuillez enregistrer les propositions.');
            return;
        }
        w2ui.gProposition.clear();
        sltQuest = this.get(event.recid);
        if(sltQuest.propositions){
            w2ui.gProposition.records = sltQuest.propositions;            
            w2ui.gProposition.refresh();
        }else{
            if(sltQuest.idQ){
                d3.json('../ice/getform?idBase='+bddNom+'&reponse=1&idQ='+sltQuest.idQ, function (err, data){
                    //correction des valeurs boolean
                    setBoolean(data.propositions);
                    sltQuest.propositions=data.propositions;
                    w2ui.gProposition.records = sltQuest.propositions;            
                    w2ui.gProposition.refresh();
                });     
            }    
        }
},
    onSave: function (event) {
        let arrC = w2ui.gQuestions.getChanges();
        updateBDD(arrC, 'question');    
    },
    onDelete: function (event) {
        var s = w2ui.gQuestions.getSelection();
    
        if (event.force) {
            //récupère les idForm
            let ids = [];
            s.forEach(function(id){
                let f = w2ui.gQuestions.get(id);
                if(f.idQ)ids.push(f.idQ);
            });
            //met à jour la base si besoin
            if(ids.length){
                deleteToBDD(ids, 'question');    
            }
        }else{
            //Vérifie la présence de réponse
            let nbR = 0, nbQ=0, nbP=0;
            s.forEach(function(id){
                let q = w2ui.gQuestions.get(id);
                nbP += q.propositions ? q.propositions.length : 0;
                let f = w2ui.gForms.get(q.idForm);
                f.reponses.forEach(function(r){
                    nbR += r.idQ == q.idQ ? 1 : 0;
                })
            })
            if(nbP > 0){
                let m = 'Vous allez supprimer : <br/>'
                    +s.length+' question(s)<br/>'
                    +nbP+' proposition(s)<br/>'
                    +nbR+' réponse(s)<br/>';
                w2obj.grid.prototype.msgDelete = m;
            }
        }
    },
    toolbar: {
        items: [{
            id: 'add',
            type: 'button',
            caption: 'Ajouter',
            icon: 'w2ui-icon-plus'
        }],
        onClick: function (event) {
            if (event.target == 'add') {
                var f = getParamsForm();
                if(!f) return;        
                addToBDD({txtQ:'nouvelle question ?',recidForm: f.recid,idForm: f.idForm,nbProp: 6},'question');
            }
        }
    },
});
$('#gridPropositions').w2grid({
    name: 'gProposition',
    header: 'Liste des propositions',
    show: {
        toolbar: true,
        footer: true,
        header: true,
        toolbarSave: true,
        toolbarDelete: true,
    },
    columns: [{
            field: 'recid',
            caption: 'ID',
            size: '30px',
            hidden: true,
            sortable: true,
            resizable: true
        },
        {
            field: 'idQ',
            caption: 'IDQ',
            size: '50px',
            hidden: true,
            sortable: true,
            resizable: true,
        },
        {
            field: 'txtR',
            caption: 'Proposition',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'iemlR',
            caption: 'Code IEML',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            },
        },
        {
            field: 'isValide',
            caption: 'Valide',
            size: '60px',
            sortable: true,
            resizable: true,
            style: 'text-align: center',
            editable: {
                type: 'checkbox',
                style: 'text-align: center'
            }
        },
        {
            field: 'isMasque',
            caption: 'Masquer',
            size: '80px',
            sortable: true,
            resizable: true,
            style: 'text-align: center',
            editable: {
                type: 'checkbox',
                style: 'text-align: center'
            }
        },
        {
            field: 'isGen',
            caption: 'Génératif',
            size: '80px',
            sortable: true,
            resizable: true,
            style: 'text-align: center',
            editable: {
                type: 'checkbox',
                style: 'text-align: center'
            }
        },
        {
            field: 'recidParent',
            caption: 'Généré par',
            size: '100px',
            hidden: true,
            sortable: true,
            resizable: true,
            style: 'text-align: center'
        },
        {
            field: 'iemlRParent',
            caption: 'Généré par',
            size: '120px',
            sortable: true,
            resizable: true
        },
        {
            field: 'iemlRelaType',
            caption: 'Relation',
            size: '130px',
            sortable: true,
            resizable: true
        }

    ],
    toolbar: {
        items: [{
            id: 'add',
            type: 'button',
            caption: 'Ajouter',
            icon: 'w2ui-icon-plus'
        }],
        onClick: function (event) {
            if (event.target == 'add') {
                //récupère l'identifiant de la question
                var s = w2ui.gQuestions.getSelection();
                if (s.length == 0) {
                    w2alert('Veuillez sélectionner une question.');
                    return;
                }
                if (w2ui.gQuestions.getChanges().length > 0) {
                    w2alert('Veuillez enregistrer la question.');
                    return;
                }
                sltQuest = w2ui.gQuestions.get(s[0]);
                var max = d3.max(w2ui.gProposition.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                evtCellIEML = {
                    'target': 'gReponse',
                    'index': i,
                    'value': true,
                    'idQ': sltQuest['idQ']
                };
                w2ui.gProposition.header = "Proposition(s) pour la question : " + sltQuest['txtQ'];
                $('#modalIemlMatrice').modal('show');
                //les grilles IEML déclenche la function d'ajout
            }
        }
    },
    records: [],
    onEditField: function (event) {
        console.log('value', event.value);
        /*
        if(event.column==3){
        evtCellIEML = event;
        $('#modalIemlMatrice').modal('show');
        }else evtCellIEML=false;
        */
    },
    onDelete: function (event) {
        var s = w2ui.gProposition.getSelection();
    
        if (event.force) {
            //récupère les id
            let ids = [];
            s.forEach(function(id){
                let f = w2ui.gProposition.get(id);
                if(f.idP)ids.push(f.idP);
            });
            //met à jour la base si besoin
            if(ids.length){
                deleteToBDD(ids, 'prop');    
            }
        }else{
            //Vérifie la présence de réponse
            let nbR = 0;
            s.forEach(function(id){
                let p = w2ui.gProposition.get(id);
                let q = w2ui.gQuestions.get(p.idQ);
                let f = w2ui.gForms.get(q.idForm);
                f.reponses.forEach(function(r){
                    nbR += r.idP==p.idP ? 1 : 0;
                })
            })
            let m = 'Vous allez supprimer : <br/>'
                +nbR+' réponse(s)<br/>';
            w2obj.grid.prototype.msgDelete = m;
        }
    },
    onSave: function (event) {
        let arrC = w2ui.gProposition.getChanges();
        updateBDD(arrC, 'prop');    
        arrC.forEach(function (c) {
            if ('isGen' in c) {
                var r = w2ui[event.target].get(c.recid);
                if (c.isGen) {
                    //récupère la définition du concept génératif
                    getCptDefinition(r);
                } else {
                    //suppression des concept généré
                    deleteCptGen(r);
                }
            }
        });
    }
});
$('#gridReponses').w2grid({
    name: 'gReponse',
    header: 'Liste des réponses',
    show: {
        toolbar: true,
        footer: true,
        header: true,
        toolbarSave: false,
        toolbarDelete: true,
    },
    columns: [{
            field: 'recid',
            caption: 'ID',
            size: '30px',
            hidden: true,
            sortable: true,
            resizable: true
        },
        {
            field: 'idQ',
            caption: 'IDQ',
            size: '50px',
            hidden: true,
            sortable: true,
            resizable: true,
        },
        {
            field: 'idUti',
            caption: 'Utilisateur',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 't',
            caption: 'Date',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            },
        },
        {
            field: 'g',
            caption: 'Date',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            },
        },
        {
            field: 'c',
            caption: 'Date',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            },
        },
        {
            field: 'pc',
            caption: 'Date',
            size: '100px',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            },
        }
    ],
    records: [],
    onDelete: function (event) {

    }
});


function setBoolean(propositions){
    //correction des valeurs boolean
    propositions.forEach(function(p){
        if(p.iemlR){
            if((p.isGen == 'false' || p.isGen == '0'))
                p.isGen = false;
            else
                p.isGen = true;
            if((p.isMasque == 'false' || p.isMasque == '0'))
                p.isMasque = false;
            else
                p.isMasque = true;
            if((p.isValide == 'false' || p.isValide == '0'))
                p.isValide = false;
            else                            
                p.isValide = true;
        }
        if(p.idForm){
            if((p.bTemps == 'false' || p.bTemps == '0'))
                p.bTemps = false;
            else                            
                p.bTemps = true;
            if((p.bGeo == 'false' || p.bGeo == '0'))
                p.bGeo = false;
            else                            
                p.bGeo = true;                
        }
    });    

}

function getCptDefinition(r) {

    //récupère la définition du concept génératif
    d3.json(urlIeml + r.iemlR, function (err, data) {
        var gen = getIemlRela(data);
        var props = [];
        gen.forEach(function (g) {
            if (g.dico) {
                let isGen = g.value == r.iemlR ? 1 : 0;
                let idP = isGen ? r.idP : 0;
                let nr = {
                    'idP': idP,
                    'idQ': r.idQ,
                    'txtR': g.dico.FR,
                    'iemlR': g.value,
                    'iemlRelaType': g.reltype,
                    'iemlRParent': r.iemlR,
                    'taille': g.dico.TAILLE,
                    'layer': g.dico.LAYER,
                    'idDico': g.dico.INDEX,
                    'iemlRParent': r.iemlR,
                    'isGen': isGen,
                    'isMasque': 0,
                    'isValide': isGen
                };
                /** TODO:vérifie les doublons */ 
                props.push(nr);
            }
        });
        addToBDD(props,'props')
    });


}

function deleteCptGen(r) {
    if (r) {
        var ids=[], arrG = w2ui.gProposition.records.filter(function (g) {
            return g.recidParent == r.recid;
        })
        arrG.forEach(function (g) {
            w2ui.gProposition.remove(g.recid);
            ids.push(g.recid);
        })
        deleteToBDD(ids,'prop');
    }
}

function addIemlCode(cpt) {
    console.log(cpt);
    var cellVal = evtCellIEML.value;
    //vérifie s'il faut ajouter des lignes
    if (cptLigne) {
        if (!cellVal) {
            w2ui[evtCellIEML.target].get((evtCellIEML.index + 1)).txtR = cpt.d.dico.FR;
            w2ui[evtCellIEML.target].refreshCell((evtCellIEML.index + 1), 'txtR');
            w2ui[evtCellIEML.target].get((evtCellIEML.index + 1)).iemlR = cpt.d.value;
            w2ui[evtCellIEML.target].refreshCell((evtCellIEML.index + 1), 'iemlR');
        } else {
            var ieml = cpt.d.value;
            /*
            cpt.dico=cpt.d.dico;
            var r = { recid: w2ui.gReponse.records.length + 1, 'recidQuest':evtCellIEML.recidQuest
                , 'txtR': cpt.dico.FR, 'iemlR': ieml, 'cpt':cpt};
            w2ui[evtCellIEML.target].add(r);
            */
           patienter('Génération des concepts...');
           //ajoute la proposition initiale
           var r = {
                recid: w2ui.gProposition.records.length + 1,
                'idQ': evtCellIEML.idQ,
                'iemlR': ieml,
                'isGen': 1
            };
            addToBDD(r,'prop');
        }
    } else {
        //cumul les concepts
    }

}

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    // newly activated tab
    if (e.target.id == 'formTest-tab') {
        creaForm();
        //creaHexaCarto();
        creaForceCarto();
    }
    e.relatedTarget // previous active tab
})


function addPropositionLiens(props) {
    //récupère les parents des réponses        
    var propsParent = props.filter(function (r) {
        r.liens = [];
        return r.isGen;
    });
    //pour chaque proposition parente
    propsParent.forEach(function (rp) {
        //calcule les liens avec les autres réponses
        props.forEach(function (ra) {
            if (rp.iemlR != ra.iemlR && !ra.isMasque && !rp.isMasque) {
                rp.liens.push({
                    'levenshtein': levenshteinDistance(rp.iemlR, ra.iemlR),
                    'idPsource': rp.idP,
                    'idPtarget': ra.idP,
                    'source': rp.iemlR + "",
                    'target': ra.iemlR + "",
                    'value': 1,
                    'reltype': ra.iemlRelaType,
                    'idEdge': ra.idQ + '_' + ra.idParent + '_' + ra.idP
                });
            }
        })
    })
    addToBDD(props, 'liens');    

}


function creaForceCarto(data, div) {

    if(!sltForms) return;

    var height = $('#ifMatriceIEML').height() - $('#navViz').height(),
        width = $('#formTest-result').width(),
        carto = creaTitleCarto();

    //construction de la cartographie
    iemlCartoForce = iemlForce();
    iemlCartoForce.height(height);
    iemlCartoForce.width(width);
    carto.call(iemlCartoForce);

    //calcule les réponses
    var cr = calculeReponse();    

    //mis à jour avec des rayons
    iemlCartoForce.maxRep(cr.m);
    for (var prop in cr.r) {
        rt = cr.r[prop];
        iemlCartoForce.changeRayonNoeud(rt.id, rt.nb);
    }

}

function calculeReponse(){
    //calcule les réponses
    var arrRepTot = [], max=[];
    sltForms.reponses.forEach(function(r){
        r.c.forEach(function (c) {
            var k = c.idQ + '_' + c.idDico;
            if (arrRepTot[k]) arrRepTot[k].nb++;
            else arrRepTot[k] = {
                nb: 1,
                id: k
            };
            //calcule le max pour chaque réponse
            if(!max[c.idQ])max[c.idQ]= arrRepTot[k].nb;
            max[c.idQ] = max[c.idQ] < arrRepTot[k].nb ? arrRepTot[k].nb : max[c.idQ];
        })
    });    
    return {'m':d3.max(max),'r':arrRepTot};
}

function creaTitleCarto() {

    //création des cartes
    d3.select("#formTest-result").selectAll(".row").remove();
    var carto = d3.select("#formTest-result").selectAll(".row").data(sltForms.questions).enter().append("div")
        .attr('class', 'row');
    //création des titres
    carto.append('h4').text(function (d) {
        return d.txtQ;
    });

    return carto;

}


//merci beaucoup à  https://bl.ocks.org/mbostock/7833311
function creaHexaCarto() {

    var height = $('#ifMatriceIEML').height() - $('#navViz').height(),
        width = $('#formTest-result').width(),
        radius = 10,
        margin = ({
            top: 20,
            right: 20,
            bottom: 20,
            left: 20
        }),
        carto = creaTitleCarto();
    //création des svg
    var svg = carto.append("svg")
        .attr('height', height)
        .attr('width', width);

    var delta = 0.001,
        i = 0,
        j,
        n = 2000, // Total number of points.
        rx = d3.randomNormal(width / 2, 80),
        ry = d3.randomNormal(height / 2, 80),
        //points = d3.range(n).map(function() { return [rx(), ry()]; });
        points = d3.range(n).map(function (d, i) {
            return [d, height / 2];
        });

    var color = d3.scaleSequential(d3.interpolateLab("white", "steelblue"))
        .domain([0, 20]);

    var hexbin = d3.hexbin()
        .radius(radius)
        .extent([
            [margin.left, margin.top],
            [width - margin.right, height - margin.bottom]
        ]);

    var hexagon = svg.selectAll("path")
        .data(hexbin(points))
        .enter().append("path")
        .attr("d", hexbin.hexagon(radius - 0.5))
        .attr("transform", function (d) {
            return "translate(" + d.x + "," + d.y + ")";
        })
        .attr("fill", function (d) {
            return color(d.length);
        });

}

function getParamsForm(){

    var s = w2ui.gForms.getSelection();
    if (s.length == 0) {
        w2alert('Veuillez sélectionner un formulaire.');
        return;
    }
    if (w2ui.gForms.getChanges().length > 0) {
        w2alert('Veuillez enregistrer les formulaires.');
        return;
    }
    return w2ui.gForms.get(s[0]);        

}

function creaForm() {
    var dF = getParamsForm();
    if(!dF) return;
    var arrProcess = [];

    d3.select("#genForm").remove();
    var c = d3.select("#formTest-form").append('div').attr('id', 'genForm');
    c.append('h1').text(dF.txtForm);
    var f = c.append('div');
    //création des questions
    var q = f.selectAll('.form-group').data(dF.questions)
        .enter().append("div").attr('class', 'form-group')
        .attr("id", function (d, i) {
            return "formQ" + i;
        });

    //création du label
    q.append("h2")
        .text(function (d) {
            return d.txtQ
        });
    //création des réponses
    var r = q.selectAll('.form-check form-check-inline')
        .data(function (d) {
            return getAleaProposition(d.propositions, d.nbProp);
        })
        .enter().append("div").attr('class', 'form-check form-check-inline');
    //ajoute le bouton de sélection    
    r.append("input")
        .attr("id", function (d) {
            return "rQ" + d.idQ;
        })
        .attr("class", "form-check-input")
        .attr("type", "checkbox")
        .attr("value", function (d) {
            return "r" + d.recid;
        })
        .on("click", function (d) {
            if(!d.p)d.p=[];
            d.p.push({
                'idP': d.idP,
                'v': this.checked,
                't': new Date().toISOString().slice(0, 19).replace('T', ' '),
                'g': getGeoInfos(),
                'idDico': d.idDico
            });
            //iemlCartoForce.changeRayonNoeud(d,this.checked ? 1 : -1);            
        });
    //ajoute le label de la réponse    
    r.append("label")
        .attr("for", function (d) {
            return "rQ" + d.recidQuest;
        })
        .attr("class", "form-check-label")
        .text(function (d) {
            return d.txtR;
        });

    //ajoute le bouton d'enregistrement
    f.append("button").attr('class', 'btn btn-primary')
        .text("Enregistrer")
        .on("click", function (d) {
            //récupère les réponses
            addToBDD(getReponse(), 'reponse');             
        });
}

function getReponse(){

    var dtC = d3.selectAll("#formTest-form input:checked").data();
    var dtPC = d3.selectAll("#formTest-form input").data();
    var g = getGeoInfos();
    //construction des réponses
    var r = {'idForm':sltForms.idForm, 'idUti':idUti,
        't':new Date().toISOString().slice(0, 19).replace('T', ' '),
        'lat':g ? g.lat : 0,
        'lng':g ? g.lng : 0,
        'pre':g ? g.pre : 0,
        'c':[],'pc':[],'p':[]};
    dtC.forEach(function(c){
        r.c.push({
            'idQ':c.idQ,
            'idDico':c.idDico,
            'idP':c.idP
        });
    });
    //construction des possibilités de choix
    r.pc = [];
    dtPC.forEach(function(pc){
        r.pc.push({'idQ':pc.idQ,'idDico': pc.idDico,'idP':pc.idP});
        //construction du processus
        if(pc.p){
            pc.p.forEach(function(p){
                r.p.push({'t':p.t,'v':p.v,'idP':p.idP,'idDico':p.idDico});
            });
        }
    });

    return r;
}

function getAleaProposition(arrR, nb) {
    if(!arrR)return [];
    var r = [];
    //sélectionne les réponses valides
    var v = arrR.filter(function (d) {
        return d.isValide && !d.isMasque;
    })
    //ajoute au hasard une des réponses valides
    //r.push(v[d3.randomUniform(v.length-1)()]);
    //ajoute les réponses valides
    v = d3.shuffle(v);
    r = r.concat(v);

    //sélectionne les réponses nonvalides
    var nv = arrR.filter(function (d) {
        return !d.isValide && !d.isMasque;
    })
    //ajoute au hasard le nombre des réponses nonvalides
    nv = d3.shuffle(nv);
    r = r.concat(nv.slice(0, (nb - r.length)));

    //renvoie la liste des réponses
    return d3.shuffle(r);

}

// Fluid layout doesn't seem to support 100% height; manually set it
$(window).resize(function () {
    $('#ifMatriceIEML').height($(window).height() -
        $("header .navbar").height() -
        $(".footer").height() -
        $("#nbGauche").height() -
        20
    );
})
$(window).resize();