var prefUrl = "", idBase="", uti={},urlController = '';
function deconnexion(redir){
    window.location.assign(prefUrl+'auth/deconnexion?redir='+redir);
}
function connexion(redir){
    window.location.assign(prefUrl+'auth/connexion?redir='+redir+'&idBase='+idBase);
}

function patienter(message, fin) {
    if (typeof w2ui !== 'undefined') return;
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

//merci beaucoup à https://bl.ocks.org/guypursey/f47d8cd11a8ff24854305505dbbd8c07
function wrap(text, width) {
    text.each(function() {
        var text = d3.select(this);
        var words = text.text().split(/\s+/).reverse(),
        word,
        line = [],
        lineNumber = 0,
        lineHeight = 1.1, // ems
        y = text.attr("y"),
        x = text.attr("x"),
        dy = 0;//parseFloat(text.attr("dy")),
        if(words.length>1)y -= lineHeight*4;//cyScale.bandwidth()/3;
        var tspan = text.text(null).append("tspan").attr("x", x).attr("y", y).attr("dy", dy + "em");
        while (word = words.pop()) {
            line.push(word)
            tspan.text(line.join(" "))
            if (tspan.node().getComputedTextLength() > width) {
            line.pop()
            tspan.text(line.join(" "))
            line = [word]
            tspan = text.append("tspan").attr("x", x).attr("y", y).attr("dy", `${++lineNumber * lineHeight + dy}em`).text(word)
            }
        }        
    }) 
}

function initConnexion(url){
    urlController = url;
    d3.select('#btnConnexion')
        .on('click', function () {
            if(uti.login=='Anonyme')
                showGoogleAuth();
            else if( d3.select('#txtUti').html)
                deconnexion(urlController);
            else
                connexion(urlController);
            //console.log("connexion ou déconnexion de l'utilisateur");
        });
}

function showUti() {
    let btn = '<i class="fas fa-sign-in-alt"></i>';
    let u = uti.login;                          
    if (uti.login == 'Anonyme') {
        btn = "<img src=\'../img/google.png\' style=\'height: 30px;\' alt=\'connexion Google\' />";
        u='Connectez-vous';
    }  
    d3.select('#txtUti').html(u);
    d3.select('#btnConnexion').html(btn);
}

function showGoogleAuth(){
    window.location.href = "../auth/google?idBase="+idBase+"&redir=/"+urlController;
}