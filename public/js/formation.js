/**
 * function javascript génériques pour la gestion des formations
 */

//variables thématiques
var nodesCour,rootCour, treeCour, dataCour, colCour;
//variable intéractivité
var bClick = false, display = "none", selectCour, selectUE, selectParc;

//variables affichage
var maxHeureStat = 192, sclHeureStat = d3.scale.linear().range([0, 100]).domain([0, maxHeureStat]),
    sclHeureComp = d3.scale.linear().range([0, 100]).domain([0, maxHeureStat*2]);
;


/**
 * Import Export
 */
$('#btnExport').click(function(){
    var jsonData = {"IntCour":dataIntCour, "cours":dataCour,"intervenants":dataInt};
    exportJson(JSON.stringify(jsonData), 'paragrapheFormations.json', 'text/plain');
})
$('#btnExportCSV').click(function(){
    JSONToCSVConvertor(dataCour,'Formation',true);
})

$('#btnImport').click(function(){
    $('#modGetFic').modal('show');
})
//initialise le champ d'import des fichiers
$('#fileImport').w2field('file', {max:1});

$('#btnValidImport').click(function(){
    var f = $('#fileImport').data('selected');
    fr = new FileReader();
    fr.onload = receivedText;
    fr.readAsText(f[0].file);
    function receivedText(e) {
        let lines = e.target.result;
        try {
            var data = JSON.parse(lines); 
            dataIntCour = data.IntCour;
            dataCour = data.cours
            dataInt = data.intervenants;
            buildPage();
            $('#modGetFic').modal('hide');
        }catch(error) {
            w2alert(error);
        }
        $('#fileImport').w2field('file', {max:1});
    }
})

function exportJson(content, fileName, contentType) {
    var a = document.createElement("a");
    var file = new Blob([content], {type: contentType});
    a.href = URL.createObjectURL(file);
    a.download = fileName;
    a.click();
}
/** FIN Import Export */

/** Gestion des grid */
function initGrid(id, g){
    if(w2ui[g.name])w2ui[g.name].destroy();
    let grid = $("#"+id).w2grid(g);
    grid.refresh();
}

/**
 * merci beaucoup à http://jsfiddle.net/hybrid13i/JXrwM/
 * @param {*} JSONData 
 * @param {*} ReportTitle 
 * @param {*} ShowLabel 
 */
function JSONToCSVConvertor(JSONData, ReportTitle, ShowLabel) {
    //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
    var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;
    
    var CSV = '';    
    //Set Report title in first row or line
    
    CSV += ReportTitle + '\r\n\n';

    //This condition will generate the Label/Header
    if (ShowLabel) {
        var row = "";
        
        //This loop will extract the label from 1st index of on array
        for (var index in arrData[0]) {
            
            //Now convert each value to string and comma-seprated
            row += index + ',';
        }

        row = row.slice(0, -1);
        
        //append Label row with line break
        CSV += row + '\r\n';
    }
    
    //1st loop is to extract each row
    for (var i = 0; i < arrData.length; i++) {
        var row = "";
        
        //2nd loop will extract each column and convert it in string comma-seprated
        for (var index in arrData[i]) {
            row += '"' + arrData[i][index] + '",';
        }

        row.slice(0, row.length - 1);
        
        //add a line break after each row
        CSV += row + '\r\n';
    }

    if (CSV == '') {        
        alert("Invalid data");
        return;
    }   
    
    //Generate a file name
    var fileName = "formation_";
    //this will remove the blank-spaces from the title and replace it with an underscore
    fileName += ReportTitle.replace(/ /g,"_");   
    
    //Initialize file format you want csv or xls
    var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);
    
    // Now the little tricky part.
    // you can use either>> window.open(uri);
    // but this will not work in some browsers
    // or you will not get the correct file extension    
    
    //this trick will generate a temp <a /> tag
    var link = document.createElement("a");    
    link.href = uri;
    
    //set the visibility hidden so it will not effect on your web-layout
    link.style = "visibility:hidden";
    link.download = fileName + ".csv";
    
    //this part will append the anchor tag and remove it after automatic click
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}