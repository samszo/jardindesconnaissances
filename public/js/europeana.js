/*code pour gérer l'API europeana
cf. https://pro.europeana.eu/resources/apis/search
*/
class europeana {
    constructor(params) {
        //propriétés génériques
        var me = this;
        this.apikey = params.apikey;
        this.svgId = params.svgId;
        this.urlE = "https://www.europeana.eu/api/v2/search.json?wskey="+this.apikey;
        

        this.findAleaImage = function (e, cb) {
            let q = e.html().replace(' ','+');
            let url = me.urlE+'&media=true&thumbnail=true&query='+q;         
            d3.json(url, function(error, data) {
                if (error) throw error;
                //récupère une image aléatoire
                let nItem = Math.floor(Math.random() * Math.floor(data.items.length));
                let item = data.items[nItem];
                cb(e, item);
            });
        }

        this.findImages = function (e, cb, start) {
            if(!start)start=1;
            let q = e.html().replace(' ','+');
            let url = me.urlE+'&media=true&thumbnail=true&type=IMAGE&start='+start+'&query='+q;         
            d3.json(url, function(error, data) {
                if (!data.success) console.log(data.error);
                cb(e, data.items);
                if(data.totalResults > data.itemsCount)me.findImages(e, cb, start+data.itemsCount);
            });
        }

    }
}
