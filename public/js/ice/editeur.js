var arrMatrices = [{
        code: "M:M:.a.-M:M:.a.-f.o.-'",
        lib: "rôles sociaux"
    }, {
        code: "M:M:.-M:.O:.-'",
        lib: "qualités"
    }, {
        code: "O:O:.O:M:.-",
        lib: "cycle de travail"
    }],
    arrMnuAffichage = [{
        fct: showHideTexte,
        lib: "Affiche / Masque les textes"
    }, {
        fct: showHideLimite,
        lib: "Affiche / Masque les limites"
    }]
urlMatriceIeml = "../ice/ieml?code=",
    ifIeml = document.getElementById("ifMatriceIEML").contentWindow,
    cptLigne = true, evtCellIEML = false, arrQR = [],
    arrDico = [], iemlMatrice = [],
    arrReponses = [], iemlCartoForce = false;

//chargement du dico IEML
d3.json(urlDico, function (err, data) {
    arrDico = data;
});

//création des menus
d3.select("#mnuMatrices").selectAll("a").data(arrMatrices).enter().append("a")
    .attr("class", "dropdown-item")
    .attr("href", "#")
    .text(function (d) {
        return d.lib
    })
    .on('click', function (d) {
        //modifie la source de l'iframe
        d3.select('#ifMatriceIEML').attr('src', urlMatriceIeml + d.code);
        //affiche les menus
        d3.select('#sltAffichage a').attr('class', "nav-link dropdown-toggle")
    });
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
            pb = d3.select('#ppPatienter')
            .append('div')
            .attr('id',"pbPatienter")                
            .attr('class',"progress")                
            .style('top',"200px")                
            .append('div')
            .attr('class',"progress-bar progress-bar-striped progress-bar-animated")                
            .attr('role',"progressbar")                
            .attr('aria-valuenow',"1")                
            .attr('aria-valuemin',"0")                
            .attr('aria-valuemax',"100")                
            .style('width', "1%"),                
            jsonData = {
                "params": params,
                "idBase": params.iptBddID,
                "query":"form"
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
                'reponses':q.reponses
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
        arrReponses.forEach(function(q){
            //construction de la réponse finale
            var rs = {
                'recidQuest':0,
                'idsDico':'',
                'idForm':q.idForm,
                't':q.t,
                'lat':q.g.lat,
                'lng':q.g.lng,
                'pre':q.g.pre
            }
            q.r.forEach(function(r){
                rs.recidQuest = r.recidQuest;
                rs.idsDico += r.idDico+',';
            });
            rs.idsDico = rs.idsDico.substring(0,rs.idsDico.length-1);
            //contrustion du processus
            rs.p = [];
            q.p.forEach(function(p){
                rs.p.push({'t':p.t,'v':p.v,'idDico':p.d.idDico});
            });            
            arrReponsesSimples.push(rs);

        });

        //enregistre les paramètres du formulaire
        var result = sauveForm(jsonData, arrQuestionSimples, arrReponsesSimples, pb);

    }
})

function sauveForm(form, questions, reponses, pb){

    //compte les liens
    //on ajoute les liens
    var nbLiens = 0;
    questions.forEach(function(q){
        nbLiens += q.liens.length;
    });
    var pcProgress = 100/(questions.length+reponses.length+nbLiens), i = 1;
    $.post("../ice/sauveform", {'idBase':form.idBase,'form':form,'questions':questions,'reponses':reponses},
        function(data){
            if(data.erreur){
                w2alert(data.erreur);
            }else{
                //enregistre les questions
                questions.forEach(function(q){
                    q.idBase = form.idBase;
                    q.idForm = data.rs.doc_id;
                    q.query = 'question'; 
                    var liens = q.liens;
                    //pour gérer le problème de l'enregistrement complet du formulaire
                    delete q.liens;
                    $.ajax({
                        type: 'POST',
                        url: "../ice/sauveform",
                        data: q,
                        success: function(d){
                            if(d){
                                //enregistre les liens
                                liens.forEach(function(l){
                                    l.idBase = form.idBase;
                                    l.idQuest = d.rs.doc_id;
                                    l.query = 'lien'; 
                                    sauveFormValue(l,false);
                                    i++;
                                    pb.style('width', pcProgress*i + '%');                
                                })
                                //met à jour les réponse avec l'identifiant de la question
                                reponses.forEach(function(r){
                                    r.idForm = data.rs.doc_id;
                                    r.r.forEach(function(v){
                                        if(v['recidQuest']==v['recid'])v['idDocParent']=d.rs.doc_id;
                                    })
                                });            
                            }
                        },
                        dataType: "json",
                        async:false,
                        error: function (xhr, ajaxOptions, thrownError) {
                            console.log(thrownError);
                        }
                      });
                    q.liens = liens;
                    i++;
                    pb.style('width', pcProgress*i + '%');
                });
                //enregistre les réponses
                reponses.forEach(function(r){
                    r.idBase = form.idBase;
                    r.idForm = data.rs.doc_id;
                    r.query = 'reponse'; 
                    sauveFormValue(r,false);
                    i++;
                    pb.style('width', pcProgress*i + '%');
                });
        
                w2alert('Le formulaire est enregistré.')
                return data;
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
    if (w2ui.gReponse.getChanges().length > 0) {
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
            $('#modGetFic').modal('hide');
        } catch (error) {
            w2alert('Les données ne sont pas au bon format.');
        }
        $('#fileImport').w2field('file', {
            max: 1
        });
    }
})
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
        if (w2ui.gReponse.getChanges().length > 0) {
            w2alert('Veuillez enregistrer les réponses.');
            return;
        }
        w2ui['gReponse'].clear();
        var record = this.get(event.recid);
        w2ui.gReponse.records = record.reponses;
        w2ui.gReponse.refresh();
    },
    onDelete: function (event) {
        if (event.force) {
            //verifie si des réponses sont déjà donnée

            w2ui.gReponse.clear();
            w2ui.gReponse.refresh();
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
                var max = d3.max(w2ui.gQuestions.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                arrQR.push({
                    recid: i,
                    reponses: [],
                    nbProp: 6
                });
                w2ui.gQuestions.records = arrQR;
                w2ui.gQuestions.refresh();
            }
        }
    },
    records: arrQR
});
$('#gridReponses').w2grid({
    name: 'gReponse',
    header: 'Liste des réponses',
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
            caption: 'Réponse',
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
                var max = d3.max(w2ui.gReponse.records.map(function (d) {
                    return d.recid
                }));
                var i = max ? max + 1 : 1;
                evtCellIEML = {
                    'target': 'gReponse',
                    'index': i,
                    'value': true,
                    'recidQuest': r['recid']
                };
                w2ui.gReponse.header = "Réponse(s) à la question : " + r['txtQ'];
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
        w2ui.gReponse.getChanges().forEach(function (c) {
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
        var max = d3.max(w2ui.gReponse.records.map(function (d) {
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
                    'isGen': isGen
                };
                //vérifie les doublons
                w2ui.gReponse.add(nr);
                i++;
                first = false;
            }
        });
        calculeReponseLiens();
        patienter('', true);
    });

}

function deleteCptGen(r) {
    if (r) {
        patienter('Suppression des concepts générés...');
        var arrG = w2ui.gReponse.records.filter(function (g) {
            return g.recidParent == r.recid;
        })
        arrG.forEach(function (g) {
            w2ui.gReponse.remove(g.recid);
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
                recid: w2ui.gReponse.records.length + 1,
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

function calculeReponseLiens() {

    //pour chaque question
    arrQR.forEach(function (q) {
        //récupère les parents des réponses
        var arrParent = q.reponses.filter(function (r) {
            return r.isGen;
        });
        //pour chaque parent
        q.liens = [];
        arrParent.forEach(function (rp) {
            //calcule les liens avec les autres réponses
            q.reponses.forEach(function (ra) {
                if (rp.iemlR != ra.iemlR && !ra.isMasque) {
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

    calculeReponseLiens();

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
        d.r.forEach(function (r) {
            var k = r.recidQuest + '_' + r.idDico;
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

    calculeReponseLiens();

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
            return getAleaReponse(d.reponses, d.nbProp);
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
                'd': d
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
            var n = d3.selectAll("#formTest-form input:checked").data();
            arrReponses.push({
                't': new Date().toISOString().slice(0, 19).replace('T', ' '),
                'g': getGeoInfos(),
                'p': arrProcess,
                'r': n,
            });
            creaForm();
            //creaHexaCarto();
            creaForceCarto();
        });
}

function getAleaReponse(arrR, nb) {
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