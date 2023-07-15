var demo_tasks = {
	"data":[
		{"id":1, "text":"H2PTM 4.0", "start_date":"01-01-2020", "duration":"730", "progress": 0, "open": true, "priority": "2"},
        
        //2020
        {"id":2, "text":"Atelier 1 : éditorialisation scientifique", "start_date":"28-01-2020", "duration":"63", "parent":"1", "progress": 0, "open": false, "priority": "3"},
        
        {"id":3, "text":"séance 1 : Présentation de l’atelier, des intervenants, des enjeux, des attendues", "start_date":"28-01-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":4, "text":"séance 2 : Orages cérébraux à partir des visuels que les étudiants auront conçus", "start_date":"04-02-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":5, "text":"séance 3 : Préparation des notes d’intention", "start_date":"11-02-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":6, "text":"séance 4 : Jury notes d’intention, choix des scenarii", "start_date":"18-02-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":7, "text":"séance 5 : Conception des prototypes", "start_date":"25-02-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":8, "text":"séance 6 : Développement des prototypes", "start_date":"03-03-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":9, "text":"séance 7 : Développement des prototypes", "start_date":"10-03-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":10, "text":"séance 8 : Développement des prototypes", "start_date":"17-03-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":11, "text":"séance 9 : Préparation du jury et finalisation du dossier de conception", "start_date":"17-03-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":12, "text":"séance 10 : Jury Final", "start_date":"31-03-2020", "parent":"2", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        
        {"id":13, "text":"V. Béta : Conception et développement de la plateforme d’intelligence collective version Béta", "start_date":"01-04-2020", "parent":"1", "duration":"152", "progress": 0, "open": false, "priority": "1"},
        {"id":136, "text":" - 1 Stagiares", "start_date":"01-04-2020", "parent":"1", "duration":"152", "progress": 0, "open": true, "priority": "5"},
        
        {"id":14, "text":"S. 1 : Sprint 1", "start_date":"01-04-2020", "parent":"13", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":15, "text":"1. définition des scénarios", "start_date":"02-04-2020", "parent":"14", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":16, "text":"2. spécifications des tests fonctionnels", "start_date":"03-04-2020", "parent":"14", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":17, "text":"3. réalisation des tâches", "parent":"14", "start_date":"05-04-2020", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":18, "text":"4. documentation", "parent":"14","start_date":"20-04-2020",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":19, "text":"5. livraison", "parent":"14", "start_date":"22-04-2020", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        
        {"id":20, "text":"S. 2 : Sprint 2", "start_date":"01-05-2020", "parent":"13", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":21, "text":"1. définition des scénarios", "start_date":"02-05-2020", "parent":"20", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":22, "text":"2. spécifications des tests fonctionnels", "start_date":"03-05-2020", "parent":"20", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":23, "text":"3. réalisation des tâches", "parent":"20", "start_date":"05-05-2020", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":24, "text":"4. documentation", "parent":"20","start_date":"20-05-2020",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":25, "text":"5. livraison", "parent":"20", "start_date":"22-05-2020", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":26, "text":"S. 3 : Sprint 3", "start_date":"01-06-2020", "parent":"13", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":27, "text":"1. définition des scénarios", "start_date":"02-06-2020", "parent":"26", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":28, "text":"2. spécifications des tests fonctionnels", "start_date":"03-06-2020", "parent":"26", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":29, "text":"3. réalisation des tâches", "parent":"26", "start_date":"05-06-2020", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":30, "text":"4. documentation", "parent":"26","start_date":"20-06-2020",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":31, "text":"5. livraison", "parent":"26", "start_date":"22-06-2020", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":32, "text":"S. 4 : Sprint 4", "start_date":"01-07-2020", "parent":"13", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":33, "text":"1. définition des scénarios", "start_date":"02-07-2020", "parent":"32", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":34, "text":"2. spécifications des tests fonctionnels", "start_date":"03-07-2020", "parent":"32", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":35, "text":"3. réalisation des tâches", "parent":"32", "start_date":"05-07-2020", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":36, "text":"4. documentation", "parent":"32","start_date":"20-07-2020",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":37, "text":"5. livraison", "parent":"32", "start_date":"22-07-2020", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":38, "text":"S. 5 : Sprint 5", "start_date":"01-08-2020", "parent":"13", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":39, "text":"1. définition des scénarios", "start_date":"02-08-2020", "parent":"38", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":40, "text":"2. spécifications des tests fonctionnels", "start_date":"03-08-2020", "parent":"38", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":41, "text":"3. réalisation des tâches", "parent":"38", "start_date":"05-08-2020", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":42, "text":"4. documentation", "parent":"38","start_date":"20-08-2020",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":43, "text":"5. livraison", "parent":"38", "start_date":"22-08-2020", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":87, "text":"Journée d'étude", "parent":"1", "start_date":"15-10-2020", "duration":"3", "progress": 0, "open": false, "priority": "4"}, 
        
        //2021
        {"id":44, "text":"Atelier 2 : éditorialisation scientifique", "start_date":"28-01-2021", "duration":"63", "parent":"1", "progress": 0, "open": false, "priority": "3"},
        
        {"id":45, "text":"séance 1 : Présentation de l’atelier, des intervenants, des enjeux, des attendues", "start_date":"28-01-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":46, "text":"séance 2 : Orages cérébraux à partir des visuels que les étudiants auront conçus", "start_date":"04-02-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":47, "text":"séance 3 : Préparation des notes d’intention", "start_date":"11-02-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":48, "text":"séance 4 : Jury notes d’intention, choix des scenarii", "start_date":"18-02-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":49, "text":"séance 5 : Conception des prototypes", "start_date":"25-02-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":50, "text":"séance 6 : Développement des prototypes", "start_date":"03-03-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":51, "text":"séance 7 : Développement des prototypes", "start_date":"10-03-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":52, "text":"séance 8 : Développement des prototypes", "start_date":"17-03-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":53, "text":"séance 9 : Préparation du jury et finalisation du dossier de conception", "start_date":"17-03-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        {"id":54, "text":"séance 10 : Jury Final", "start_date":"31-03-2021", "parent":"44", "duration":"1", "progress": 0, "open": true, "priority": "3"},
        
        {"id":55, "text":"V. 0 : Conception et développement de la plateforme d’intelligence collective version 0", "start_date":"01-04-2021", "parent":"1", "duration":"152", "progress": 0, "open": false, "priority": "1"},
        {"id":134, "text":" - 1 Stagiares", "start_date":"01-04-2021", "parent":"1", "duration":"152", "progress": 0, "open": true, "priority": "5"},
        
        {"id":56, "text":"S. 1 : Sprint 1", "start_date":"01-04-2021", "parent":"55", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":57, "text":"1. définition des scénarios", "start_date":"02-04-2021", "parent":"56", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":58, "text":"2. spécifications des tests fonctionnels", "start_date":"03-04-2021", "parent":"56", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":59, "text":"3. réalisation des tâches", "parent":"56", "start_date":"05-04-2021", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":60, "text":"4. documentation", "parent":"56","start_date":"20-04-2021",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":61, "text":"5. livraison", "parent":"56", "start_date":"22-04-2021", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        
        {"id":62, "text":"S. 2 : Sprint 2", "start_date":"01-05-2021", "parent":"55", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":63, "text":"1. définition des scénarios", "start_date":"02-05-2021", "parent":"62", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":64, "text":"2. spécifications des tests fonctionnels", "start_date":"03-05-2021", "parent":"62", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":65, "text":"3. réalisation des tâches", "parent":"62", "start_date":"05-05-2021", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":66, "text":"4. documentation", "parent":"62","start_date":"20-05-2021",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":67, "text":"5. livraison", "parent":"62", "start_date":"22-05-2021", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":68, "text":"S. 3 : Sprint 3", "start_date":"01-06-2021", "parent":"55", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":69, "text":"1. définition des scénarios", "start_date":"02-06-2021", "parent":"68", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":70, "text":"2. spécifications des tests fonctionnels", "start_date":"03-06-2021", "parent":"68", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":71, "text":"3. réalisation des tâches", "parent":"68", "start_date":"05-06-2021", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":72, "text":"4. documentation", "parent":"68","start_date":"20-06-2021",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":73, "text":"5. livraison", "parent":"68", "start_date":"22-06-2021", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":74, "text":"S. 4 : Sprint 4", "start_date":"01-07-2021", "parent":"55", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":75, "text":"1. définition des scénarios", "start_date":"02-07-2021", "parent":"74", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":76, "text":"2. spécifications des tests fonctionnels", "start_date":"03-07-2021", "parent":"74", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":77, "text":"3. réalisation des tâches", "parent":"74", "start_date":"05-07-2021", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":78, "text":"4. documentation", "parent":"74","start_date":"20-07-2021",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":79, "text":"5. livraison", "parent":"74", "start_date":"22-07-2021", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":80, "text":"S. 5 : Sprint 5", "start_date":"01-08-2021", "parent":"55", "duration":"30", "progress": 0, "open": true, "priority": "1"},
        {"id":81, "text":"1. définition des scénarios", "start_date":"02-08-2021", "parent":"80", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 
        {"id":82, "text":"2. spécifications des tests fonctionnels", "start_date":"03-08-2021", "parent":"80", "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":83, "text":"3. réalisation des tâches", "parent":"80", "start_date":"05-08-2021", "duration":"14", "progress": 0, "open": true, "priority": "1"}, 
        {"id":84, "text":"4. documentation", "parent":"80","start_date":"20-08-2021",  "duration":"2", "progress": 0, "open": true, "priority": "1"}, 
        {"id":85, "text":"5. livraison", "parent":"80", "start_date":"22-08-2021", "duration":"1", "progress": 0, "open": true, "priority": "1"}, 

        {"id":86, "text":"H2PTM 21", "parent":"1", "start_date":"15-10-2021", "duration":"3", "progress": 0, "open": false, "priority": "4"}, 
        
	],
	"links":[
        {"id":"1","source":"1","target":"2","type":"1"},
        //2020
		{"id":"2","source":"2","target":"3","type":"1"},
		{"id":"3","source":"2","target":"4","type":"1"},
		{"id":"4","source":"2","target":"5","type":"1"},
		{"id":"5","source":"2","target":"6","type":"1"},
		{"id":"6","source":"2","target":"7","type":"1"},
		{"id":"7","source":"2","target":"8","type":"1"},
		{"id":"8","source":"2","target":"9","type":"1"},
		{"id":"9","source":"2","target":"10","type":"1"},
		{"id":"10","source":"2","target":"11","type":"1"},
		{"id":"11","source":"2","target":"12","type":"1"},
        {"id":"12","source":"2","target":"13","type":"1"},
        
		{"id":"13","source":"13","target":"14","type":"1"},
        {"id":"14","source":"14","target":"15","type":"1"},
		{"id":"15","source":"14","target":"16","type":"1"},
		{"id":"16","source":"14","target":"17","type":"1"},
		{"id":"17","source":"14","target":"18","type":"1"},
		{"id":"18","source":"14","target":"19","type":"1"},

        {"id":"19","source":"14","target":"20","type":"1"},        
        {"id":"20","source":"20","target":"21","type":"1"},
		{"id":"21","source":"20","target":"22","type":"1"},
		{"id":"22","source":"20","target":"23","type":"1"},
		{"id":"23","source":"20","target":"24","type":"1"},
		{"id":"24","source":"20","target":"25","type":"1"},

		{"id":"30","source":"20","target":"26","type":"1"},
        {"id":"25","source":"26","target":"27","type":"1"},
		{"id":"26","source":"26","target":"28","type":"1"},
		{"id":"27","source":"26","target":"29","type":"1"},
		{"id":"28","source":"26","target":"30","type":"1"},
		{"id":"29","source":"26","target":"31","type":"1"},

		{"id":"31","source":"26","target":"32","type":"1"},
        {"id":"32","source":"32","target":"33","type":"1"},
		{"id":"33","source":"32","target":"34","type":"1"},
		{"id":"34","source":"32","target":"35","type":"1"},
		{"id":"35","source":"32","target":"36","type":"1"},
		{"id":"36","source":"32","target":"37","type":"1"},

		{"id":"37","source":"32","target":"38","type":"1"},
        {"id":"38","source":"38","target":"39","type":"1"},
		{"id":"39","source":"38","target":"40","type":"1"},
		{"id":"40","source":"38","target":"41","type":"1"},
		{"id":"41","source":"38","target":"42","type":"1"},
        {"id":"42","source":"38","target":"43","type":"1"},    

        //2021
        {"id":"90","source":"1","target":"44","type":"1"},

        {"id":"43","source":"44","target":"45","type":"1"},
		{"id":"44","source":"44","target":"46","type":"1"},
		{"id":"45","source":"44","target":"47","type":"1"},
		{"id":"46","source":"44","target":"48","type":"1"},
		{"id":"47","source":"44","target":"49","type":"1"},
		{"id":"48","source":"44","target":"50","type":"1"},
		{"id":"49","source":"44","target":"51","type":"1"},
		{"id":"50","source":"44","target":"52","type":"1"},
		{"id":"51","source":"44","target":"53","type":"1"},
		{"id":"52","source":"44","target":"54","type":"1"},
        {"id":"53","source":"44","target":"55","type":"1"},

        {"id":"54","source":"44","target":"56","type":"1"},
        {"id":"55","source":"56","target":"57","type":"1"},
		{"id":"56","source":"56","target":"58","type":"1"},
		{"id":"57","source":"56","target":"59","type":"1"},
		{"id":"58","source":"56","target":"60","type":"1"},
		{"id":"59","source":"56","target":"61","type":"1"},

        {"id":"60","source":"44","target":"56","type":"1"},        
        {"id":"62","source":"56","target":"57","type":"1"},
		{"id":"62","source":"56","target":"58","type":"1"},
		{"id":"63","source":"56","target":"59","type":"1"},
		{"id":"64","source":"56","target":"60","type":"1"},
		{"id":"65","source":"56","target":"61","type":"1"},

		{"id":"66","source":"44","target":"62","type":"1"},
        {"id":"67","source":"62","target":"63","type":"1"},
		{"id":"68","source":"62","target":"64","type":"1"},
		{"id":"69","source":"62","target":"65","type":"1"},
		{"id":"70","source":"62","target":"66","type":"1"},
		{"id":"71","source":"62","target":"67","type":"1"},

		{"id":"72","source":"44","target":"68","type":"1"},
        {"id":"73","source":"68","target":"69","type":"1"},
		{"id":"74","source":"68","target":"70","type":"1"},
		{"id":"75","source":"68","target":"71","type":"1"},
		{"id":"76","source":"68","target":"72","type":"1"},
		{"id":"77","source":"68","target":"73","type":"1"},

		{"id":"78","source":"44","target":"74","type":"1"},
        {"id":"79","source":"74","target":"75","type":"1"},
		{"id":"80","source":"74","target":"76","type":"1"},
		{"id":"81","source":"74","target":"77","type":"1"},
		{"id":"82","source":"74","target":"78","type":"1"},
        {"id":"83","source":"74","target":"79","type":"1"},    

		{"id":"84","source":"44","target":"80","type":"1"},
        {"id":"85","source":"80","target":"81","type":"1"},
		{"id":"86","source":"80","target":"82","type":"1"},
		{"id":"87","source":"80","target":"83","type":"1"},
		{"id":"88","source":"80","target":"84","type":"1"},
        {"id":"89","source":"80","target":"85","type":"1"},    

        {"id":"91","source":"1","target":"86","type":"1"},
        {"id":"92","source":"1","target":"87","type":"1"},

        
    ]
};

