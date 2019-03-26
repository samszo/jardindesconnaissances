class ieml {
    constructor(params) {
        var me = this;
        this.sep = [':','.','-'];
        this.dico = false;
        this.fctCallBackInit = params.fctCallBackInit ? params.fctCallBackInit : false;
        this.urlAPI = '../flux/ieml/';
        this.urlDico = params.urlDico ? params.urlDico : this.urlAPI+'?format=json';
        this.getDico = function () {
            if (patienter)
                patienter('Chargement dictionnaire IEML');
            $.post(me.urlDico, {}, function (data) {
                me.dico = data;
                if(me.fctCallBackInit)me.fctCallBackInit();
            }, "json")
                .fail(function (e) {
                    w2alert("erreur");
                })
                .always(function () {
                    if (patienter)
                        patienter('', true);
                });
        };
        this.getDesc = function (code, cb) {
            if (patienter)patienter('Chargement données IEML');
            $.post(me.urlAPI, {'f':'getDicoItem','ieml':code}, function (data) {
                if(cb)cb(data);
                if (patienter)patienter('', true);
                }, "json")
                .fail(function (e) {
                    w2alert("erreur");
                })
                .always(function () {
                    if (patienter)
                        patienter('', true);
                });
            
            //var files = ["../../data/ieml/M-.I-.n.-(relations).json", "../../data/ieml/M-.I-.n.-(tables).json"];
            /*
            var files = [me.urlAPI + code + "/relations/?format=json", me.urlAPI + code + "/tables/?format=json"];
            var promises = [];
            files.forEach(function (url) {
                promises.push(d3.json(url));
            });
            Promise.all(promises).then(function (values) {
                if(cb)cb(values);
                if (patienter)patienter('', true);
            });
            */
        };
        this.getItem = function (code) {
            var items = me.dico.filter(function (d, i) {
                return d.ieml == code;
            });
            //if(!items[0])throw new Error("Item IEML introuvable : "+code);
            if(!items[0])console.log("Item IEML introuvable : "+code);
            return items[0];
        };
        this.getAttribute = function (cpt) {
            if(!cpt.posi)cpt.posi = this.getPosi(cpt);
            return cpt.Attribute ? cpt.Attribute : cpt.posi[0];
        };
        this.getSubstance = function (cpt) {
            if(!cpt.posi)cpt.posi = this.getPosi(cpt);
            return cpt.Substance ? cpt.Substance : cpt.posi[1];
        };
        this.getMode = function (cpt) {
            if(!cpt.posi)cpt.posi = this.getPosi(cpt);
            return cpt.Mode ? cpt.Mode : cpt.posi[2];
        };
        this.getPosi = function (cpt) {
            let arrP = cpt.ieml.split(me.sep[cpt.layer-1]);
            arrP.forEach(function(p, i){
                if(p.length==1 && me.sep.indexOf(p)==-1)arrP[i]=p+me.sep[cpt.layer-1];
            });
            return arrP;
        };
        this.levenshteinDistance = function (a, b) {
            if (!a || !b) {
                console.log('levenshteinDistance invalide : ' + a + ', ' + b);
                return null;
            }
            // Create empty edit distance matrix for all possible modifications of
            // substrings of a to substrings of b.
            const distanceMatrix = Array(b.length + 1).fill(null).map(() => Array(a.length + 1).fill(null));
            // Fill the first row of the matrix.
            // If this is first row then we're transforming empty string to a.
            // In this case the number of transformations equals to size of a substring.
            for (let i = 0; i <= a.length; i += 1) {
                distanceMatrix[0][i] = i;
            }
            // Fill the first column of the matrix.
            // If this is first column then we're transforming empty string to b.
            // In this case the number of transformations equals to size of b substring.
            for (let j = 0; j <= b.length; j += 1) {
                distanceMatrix[j][0] = j;
            }
            for (let j = 1; j <= b.length; j += 1) {
                for (let i = 1; i <= a.length; i += 1) {
                    const indicator = a[i - 1] === b[j - 1] ? 0 : 1;
                    distanceMatrix[j][i] = Math.min(distanceMatrix[j][i - 1] + 1, // deletion
                        distanceMatrix[j - 1][i] + 1, // insertion
                        distanceMatrix[j - 1][i - 1] + indicator);
                }
            }
            return distanceMatrix[b.length][a.length];
        };
        this.getDico();
    }
}





/*
function getIemlItems(data){
    var head, nbCol,items,mat=[],matrice = [];

    nbCol = data.table.tree.Tables[0].Col;
    items = data.table.tree.Tables[0].table[0].slice;        
    //construction des items
    items.forEach(function(n, i){
        var a = ((i-1) % nbCol)-1;
        getItemDico(n);
        if(n.background == "header-noun" || n.background == "header-verb"){
            if(i==0){
                n.type = 'matrice';
                head = n;
                head.abs=[];head.ord=[];head.items=[];
            }else{
                if(i <= nbCol){
                    n.type = 'abs';
                    n.abs=a;
                    head.abs.push(n.value);
                }else{
                    n.type = 'ord';
                    head.ord.push(n.value);
                    n.ord=head.ord.length-1
                }
            }
            mat.push(n);
        } else if (n.background == "noun" || n.background == "verb"){
            n.type = 'item';
            n.numabs=a;
            n.numord=head.ord.length-1
            n.abs=head.abs[a];
            n.ord=head.ord[n.numord];
            head.items.push(n.value);
            mat.push(n);
        }
    });
    return mat;
}
function getIemlRela(data){
    var rela = [];        
    //construction des items
    data.rela.forEach(function(r, i){
        r.rellist.forEach(function(i){
            var nr = {'reltype':r.reltype,'value':i.ieml};
            getItemDico(nr);
            rela.push(nr);
        });
    });
    return rela;
}
*/
