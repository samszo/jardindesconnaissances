
var idUti=0, arrIEMLitem, arrIEMLmatrices = [{
        IEML: "M:M:.a.-M:M:.a.-f.o.-'",
        FR: "rôles sociaux"
    }, {
        IEML: "M:M:.-M:.O:.-'",
        FR: "qualités"
    }, {
        IEML: "O:O:.O:M:.-",
        FR: "cycle de travail"
    }],
    arrMnuAffichage = [{
        fct: showHideTexte,
        lib: "Affiche / Masque les textes"
    }, {
        fct: showHideLimite,
        lib: "Affiche / Masque les limites"
    }]
urlMatriceIeml = "../ice/ieml?code=", bddNom = 'flux_formsem',
    ifIeml = document.getElementById("ifMatriceIEML").contentWindow,
    cptLigne = true, evtCellIEML = false, arrForms = [], arrQR = [],
    arrDico = [], iemlMatrice = [],
    arrReponses = [], iemlCartoForce = false;



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
            return d.TAILLE == 1;
        });    
    arrIEMLmatrices = arrDico.filter(function(d){
        return d.TAILLE > 1;
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
        data.forEach(function(d){
            var f = JSON.parse(d.note);
            arrForms.push(f);
        })
        w2ui.gForms.records = arrForms;
        w2ui.gForms.refresh();
        /*
        d3.select("#mnuFormDispo").selectAll("a").data(data).enter().append("a")
            .attr("class", "dropdown-item")
            .attr("href", "#")
            .text(function (d) {
                return d.doc_id+' - '+d.titre;
            })
            .on('click', function (d) {
                //execute la function définie
                d3.json('../ice/getform?idBase=flux_formsem&reponse=1&idForm='+d.doc_id, function (err, dataF){
                    chargeDataForm(dataF);
                }); 
            });
        */
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
$('#btnSauver').click(function () {

    if(verifForm()){
        patienter('Enregistrement du formulaire...');

        var params = getParamsForm(),                
            jsonData = {
                "params": params,
                "idBase": params.iptBddID
            };
        //simplifie la définition des liens
        var arrQuestionSimples = [];    
        arrQR.forEach(function(q){
            var rs = {
                'recid':q.recid,
                'idForm':q.idForm,
                'nbProp':q.nbProp,
                'txtQ':q.txtQ,
                'txtQIeml':q.txtQieml,
                'liens':[],
                'propositions':q.propositions
            }
            q.liens.forEach(function(l){
                rs.liens.push({
                    'source':l.source.key ? l.source.key : l.source,
                    'target':l.target.key ? l.target.key : l.target,
                    'recidS':l.recidS,
                    'recidT':l.recidT,
                    'idEdge':l.idEdge,
                    'idQuest':l.idQuest,
                    'index':l.index,
                    'levenshtein':l.levenshtein,
                    'reltype':l.reltype,
                    'value':l.value
                });
            });
            arrQuestionSimples.push(rs);            
        });
        //simplifie les réponses
        var arrReponsesSimples = [];    
        arrReponses.forEach(function(r){
            //construction de la réponse finale
            var rsR = {
                'recidQuest':r.pc[0].recidQuest,
                'idsDico':'',
                'idForm':r.idForm,
                'idUti':r.idUti,
                't':r.t,
                'lat':r.g ? r.g.lat : 0,
                'lng':r.g ? r.g.lng : 0,
                'pre':r.g ? r.g.pre : 0
            }
            //construction des choix
            rsR.c = [];
            r.c.forEach(function(c){
                rsR.c.push({'recidQuest':c.recidQuest,'idDico': c.idDico});
            });
            //construction des possibilités de choix
            rsR.pc = [];
            r.pc.forEach(function(pc){
                rsR.pc.push({'recidQuest':pc.recidQuest,'idDico': pc.idDico});
            });
            //construction du processus
            rsR.p = [];
            r.p.forEach(function(p){
                rsR.p.push({'t':p.t,'v':p.v,'idDico':p.idDico});
            });            
            arrReponsesSimples.push(rsR);

        });

        //enregistre les paramètres du formulaire
        var result = sauveForm(jsonData, arrQuestionSimples, arrReponsesSimples);

    }
})

function sauveForm(form, questions, reponses){

    $.post("../ice/sauveform", {'idBase':form.idBase,'form':form,'questions':questions,'reponses':reponses},
        function(data){
            if(data.erreur){
                w2alert(data.erreur);
            }else{
                //mise à jour des identifiant avec les données enregistrées
                $('#iptIdForm').val(data.idForm);
                data.q.forEach(function(bddQ){
                    arrQR.forEach(function(q){
                        if(q.recid==bddQ.recid)q.idQ = bddQ.idQ;
                        q.propositions.forEach(function(p){
                            bddQ.p.forEach(function(bddP){
                                if(p.recid==bddP.recid)p.idP = bddP.idP;
                            });
                        })
                        q.liens.forEach(function(l){
                            bddQ.liens.forEach(function(bddL){
                                if(l.idEdge==bddL.idEdge)l.idL = bddL.idL;
                            });
                        });
                    });                    
                });
                data.r.forEach(function(bddR){
                    arrReponses.forEach(function(r){
                        if(r.t==bddR.t && r.idUti == bddR.idUti)r.idR = bddR.idR;
                    });                    
                });

                w2alert('Le formulaire est enregistré.')
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

function sauveFormValue(q, sync){

    $.ajax({
        type: 'POST',
        url: "../ice/sauveform",
        data: q,
        success: function(result){
            return result;
        },
        dataType: "json",
        async:sync,
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError);
        }
      });

}

$('#btnExport').click(function () {

    if(verifForm()){
        var params = getParamsForm();
        var jsonData = {
            "questions": arrQR,
            "params": params,
            "reponses": arrReponses
        };
        exportJson(JSON.stringify(jsonData), 'formulaireSemantique.json', 'text/plain');    
    }

})

function verifForm(){
    let rep = true;
    if (w2ui.gQuestions.getChanges().length > 0) {
        w2alert('Veuillez enregistrer les questions.');
        rep = false;
    }
    if (w2ui.gProposition.getChanges().length > 0) {
        w2alert('Veuillez enregistrer les réponses.');
        rep = false;
    }

    return rep;

}

function getParamsForm() {
    var params = {
        'iptForm': $('#iptForm').val(),
        'iptFormIeml': $('#iptFormIeml').val(),
        'chkGeo': $('#chkGeo').val(),
        'chkTime': $('#chkTime').val(),
        'chkBDD': $('#chkBDD').val(),
        'iptBddID': $('#iptBddID').val()
    };
    return params;
}

function exportJson(content, fileName, contentType) {
    var a = document.createElement("a");
    var file = new Blob([content], {
        type: contentType
    });
    a.href = URL.createObjectURL(file);
    a.download = fileName;
    a.click();
}

$('#btnImport').click(function () {

    w2confirm('ATTENTION les paramètres actuels seront supprimés.')
        .yes(function () {
            $('#modGetFic').modal('show');
        })
        .no(function () {
            console.log('NO');
        });
})
//initialise le champ d'import des fichiers
$('#fileImport').w2field('file', {
    max: 1
});

$('#btnValidImport').click(function () {
    var f = $('#fileImport').data('selected');
    fr = new FileReader();
    fr.onload = receivedText;
    fr.readAsText(f[0].file);

    function receivedText(e) {
        let lines = e.target.result;
        try {
            var data = JSON.parse(lines);
            chargeDataForm(data);
            $('#modGetFic').modal('hide');
        } catch (error) {
            w2alert('Les données ne sont pas au bon format.');
        }
        $('#fileImport').w2field('file', {
            max: 1
        });
    }
})
function chargeDataForm(data){

    //corrige les boolean
    data.questions.forEach(function(q){
        q.propositions.forEach(function(p){
            if(p.isGen != typeof "boolean" && p.isGen == 'false')
                p.isGen = false;
            if(p.isMasque != typeof "boolean" && p.isMasque == 'false')
                p.isMasque = false;
        });
    });

    //charge les grids
    arrQR = data.questions;
    w2ui.gQuestions.records = arrQR;
    w2ui.gQuestions.refresh();
    //charge les paramètres de formulaire
    for (const k in data.params) {
        $('#' + k).val(data.params[k]);
    }
    //charge les réponses
    arrReponses = data.reponses;
}


$('#btnGenForm').click(function () {
    creaForm();
})
$('#btnClearRep').click(function () {
    w2confirm('ATTENTION toutes les réponses seront supprimées.')
        .yes(function () {
            arrReponses = [];
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
        field: 'IdForm',
        caption: 'IDF',
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
        var record = this.get(event.recid);
        //récupère les données du formulaire
        if(record.idForm){
            d3.json('../ice/getform?idBase='+bddNom+'&reponse=1&idForm='+record.idForm, function (err, dataF){
                chargeDataForm(dataF);
            });     
        }else{
            w2ui.gQuestions.records = arrQR = record.questions;
            w2ui.gQuestions.refresh();
            w2ui.gProposition.clear();
            w2ui.gProposition.refresh();            
        }
    },
    onDelete: function (event) {
        if (event.force) {
            /** TODO:verifie si des réponses sont déjà donnée*/
            w2ui.gQuestions.clear();
            w2ui.gQuestions.refresh();
            w2ui.gProposition.clear();
            w2ui.gProposition.refresh();
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
                var max = d3.max(w2ui.gForms.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                arrForms.push({
                    idForm: '',
                    recid: i,
                    txtForm:'Nouveau formulaire',
                    iemlForm:'',
                    bTemps: true,
                    bGeo:true,
                    bdd:bddNom,
                    questions:[]
                });
                w2ui.gForms.records=arrForms;
                w2ui.gForms.refresh();
            }
        }
    },
    records: arrQR
});
getForms();
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
        w2ui['gProposition'].clear();
        var record = this.get(event.recid);
        w2ui.gProposition.records = record.propositions;
        w2ui.gProposition.refresh();
    },
    onDelete: function (event) {
        if (event.force) {
            //verifie si des réponses sont déjà données

            w2ui.gProposition.clear();
            w2ui.gProposition.refresh();
            var f = w2ui.gForms.get(s[0]);        
            f.questions=arrQR;

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
                var s = w2ui.gForms.getSelection();
                if (s.length == 0) {
                    w2alert('Veuillez sélectionner un formulaire.');
                    return;
                }
                if (w2ui.gForms.getChanges().length > 0) {
                    w2alert('Veuillez enregistrer les formulaires.');
                    return;
                }
                var f = w2ui.gForms.get(s[0]);        

                var max = d3.max(w2ui.gQuestions.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                var q = {
                    recid: i,
                    txtQ:'nouvelle question ?',
                    propositions: [],
                    recidForm: f.recid,
                    idForm: f.idForm,
                    nbProp: 6
                };
                arrQR.push(q);
                f.questions=arrQR;
                w2ui.gQuestions.records = arrQR;
                w2ui.gQuestions.refresh();
            }
        }
    },
    records: arrQR
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
            size: '50px',
            sortable: true,
            resizable: true
        },
        {
            field: 'recidQuest',
            caption: 'IDQ',
            size: '50px',
            hidden: true,
            sortable: true,
            resizable: true,
        },
        {
            field: 'txtR',
            caption: 'Proposition',
            size: '30%',
            sortable: true,
            resizable: true,
            editable: {
                type: 'text'
            }
        },
        {
            field: 'iemlR',
            caption: 'Code IEML',
            size: '200px',
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
                var r = w2ui.gQuestions.get(s[0]);
                var max = d3.max(w2ui.gProposition.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                evtCellIEML = {
                    'target': 'gReponse',
                    'index': i,
                    'value': true,
                    'recidQuest': r['recid']
                };
                w2ui.gProposition.header = "Proposition(s) pour la question : " + r['txtQ'];
                $('#modalIemlMatrice').modal('show');
                //w2ui.gReponse.add({ recid: w2ui.gReponse.records.length + 1,recidQuest: r['recid']});
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
        if (event.force) {
            var s = w2ui[event.target].getSelection();
            //TODO:érifie si le formulaire a déjà été utilisé

            //supprime les concept généré
            s.forEach(function (id) {
                var r = w2ui[event.target].get(id);
                deleteCptGen(r);
            });
        }
    },
    onSave: function (event) {
        w2ui.gProposition.getChanges().forEach(function (c) {
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


function getCptDefinition(r) {

    patienter('Génération des concepts...');
    //récupère la définition du concept génératif
    d3.json(urlIeml + r.iemlR, function (err, data) {
        var gen = getIemlRela(data);
        var first = true;
        var max = d3.max(w2ui.gProposition.records.map(function (d) {
            return d.recid
        }));
        var i = max ? max + 1 : 1;

        gen.forEach(function (g) {
            if (g.dico) {
                var isGen = g.value == r.iemlR ? true : false;
                var nr = {
                    recid: i,
                    'recidQuest': r.recidQuest,
                    'txtR': g.dico.FR,
                    'iemlR': g.value,
                    'iemlRelaType': g.reltype,
                    'recidParent': r.recid,
                    'iemlRParent': r.iemlR,
                    'taille': g.dico.TAILLE,
                    'layer': g.dico.LAYER,
                    'idDico': g.dico.INDEX,
                    'iemlRParent': r.iemlR,
                    'isGen': isGen,
                    'isMasque': false,
                    'isValid': isGen
                };
                //vérifie les doublons
                w2ui.gProposition.add(nr);
                i++;
                first = false;
            }
        });
        calculePropositionLiens();
        patienter('', true);
    });

}

function deleteCptGen(r) {
    if (r) {
        patienter('Suppression des concepts générés...');
        var arrG = w2ui.gProposition.records.filter(function (g) {
            return g.recidParent == r.recid;
        })
        arrG.forEach(function (g) {
            w2ui.gProposition.remove(g.recid);
        })
        patienter('', true);
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
            var r = {
                recid: w2ui.gProposition.records.length + 1,
                'recidQuest': evtCellIEML.recidQuest,
                'iemlR': ieml,
                'isGen': true
            };
            getCptDefinition(r);

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


function calculeReponseNombre() {

    arrReponses.forEach(function (d) {

    })
}

function calculePropositionLiens() {

    //pour chaque question
    arrQR.forEach(function (q) {
        //récupère les parents des réponses
        var arrParent = q.propositions.filter(function (r) {
            return r.isGen;
        });
        //pour chaque parent
        q.liens = [];
        arrParent.forEach(function (rp) {
            //calcule les liens avec les autres réponses
            q.propositions.forEach(function (ra) {
                if (rp.iemlR != ra.iemlR && !ra.isMasque && !rp.isMasque) {
                    q.liens.push({
                        'levenshtein': levenshteinDistance(rp.iemlR, ra.iemlR),
                        'recidS': rp.recid,
                        'recidT': ra.recid,
                        'source': rp.iemlR + "",
                        'target': ra.iemlR + "",
                        'value': 1,
                        'reltype': ra.iemlRelaType,
                        'idEdge': ra.recidQuest + '_' + ra.recidParent + '_' + ra.recid
                    });
                }
            })
        })
    });

}


function creaForceCarto(data, div) {

    calculePropositionLiens();

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
    var arrRepTot = [], max = 1;
    arrReponses.forEach(function (d) {
        d.c.forEach(function (c) {
            var k = c.recidQuest + '_' + c.idDico;
            if (arrRepTot[k]) arrRepTot[k].nb++;
            else arrRepTot[k] = {
                nb: 1,
                id: k
            };
            max = max < arrRepTot[k].nb ? arrRepTot[k].nb : max;
        })
    });
    return {'m':max,'r':arrRepTot};
}

function creaTitleCarto() {

    //création des cartes
    d3.select("#formTest-result").selectAll(".row").remove();
    var carto = d3.select("#formTest-result").selectAll(".row").data(arrQR).enter().append("div")
        .attr('class', 'row');
    //création des titres
    carto.append('h4').text(function (d) {
        return d.txtQ;
    });

    return carto;

}


//merci beaucoup à  https://bl.ocks.org/mbostock/7833311
function creaHexaCarto() {

    calculePropositionLiens();

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

function creaForm() {
    var p = getParamsForm();
    var arrProcess = [];
    /*
    'iptForm':$('#iptForm').val()
    ,'iptFormIeml':$('#iptFormIeml').val()
    ,'chkGeo':$('#chkGeo').val()
    ,'chkTime':$('#chkTime').val()
    */
    d3.select("#genForm").remove();
    var c = d3.select("#formTest-form").append('div').attr('id', 'genForm');
    c.append('h1').text(p.iptForm);
    var f = c.append('form');
    //création des questions
    var q = f.selectAll('.form-group').data(arrQR)
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
            return "rQ" + d.recidQuest;
        })
        .attr("class", "form-check-input")
        .attr("type", "checkbox")
        .attr("value", function (d) {
            return "r" + d.recid;
        })
        .on("click", function (d) {
            arrProcess.push({
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
            var c = d3.selectAll("#formTest-form input:checked").data();
            var pc = d3.selectAll("#formTest-form input").data();
            arrReponses.push({
                't': new Date().toISOString().slice(0, 19).replace('T', ' '),
                'g': getGeoInfos(),
                'p': arrProcess,
                'idUti':idUti,
                'c': c,
                'pc': pc,
            });
            creaForm();
            //creaHexaCarto();
            creaForceCarto();
        });
}

function getAleaProposition(arrR, nb) {
    var r = [];
    //sélectionne les réponses valides
    var v = arrR.filter(function (d) {
        return d.isValide;
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

function patienter(message, fin) {

    if (fin) {
        w2popup.unlock();
        w2popup.close();
    } else {
        w2popup.open({
            width: 500,
            height: 300,
            title: message,
            body: '<div id="ppPatienter" class="w2ui-centered"></div>',
            showMax: false,
            showClose: false
        });
        w2popup.lock("Merci de patienter...", true);
    }


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