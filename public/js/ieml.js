
function getIemlItems(data){
    var head, nbCol,items,mat=[],matrice = [];

    nbCol = data.table.tree.Tables[0].Col;
    items = data.table.tree.Tables[0].table[0].slice;        
    //construction des items
    items.forEach(function(n, i){
        var a = ((i-1) % nbCol)-1;
        getIemlDico(n);
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
            getIemlDico(nr);
            rela.push(nr);
        });
    });
    return rela;
}

function getIemlDico(n){
    var items = arrDico.filter(function(d,i){
        return d.IEML == n.value;
    });
    n.dico = items[0];
}

function getHierarchie(cpt) {

}

function levenshteinDistance(a, b) {

    if(!a || !b){
        console.log('levenshteinDistance invalide : '+a+', '+b);
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
        distanceMatrix[j][i] = Math.min(
          distanceMatrix[j][i - 1] + 1, // deletion
          distanceMatrix[j - 1][i] + 1, // insertion
          distanceMatrix[j - 1][i - 1] + indicator, // substitution
        );
      }
    }
  
    return distanceMatrix[b.length][a.length];
  }