<?php
/*sauvegarde les bases de données
$scripts[0]['nom'] = 'Sauvegarde flux_diigo';
$scripts[0]['minutes'] = '0';
$scripts[0]['heures'] = '3';
$scripts[0]['jour'] = '*';
$scripts[0]['jourSemaine'] = '1-7';
$scripts[0]['mois'] = '1-12';
$scripts[0]['URLScript'] = WEB_ROOT.'/bdd/CreaDump.php?idBase=flux_diigo&login=flux&mdp=Mars2014';

$scripts[1]['nom'] = 'Sauvegarde spip_proverbe';
$scripts[1]['minutes'] = '1';
$scripts[1]['heures'] = '3';
$scripts[1]['jour'] = '*';
$scripts[1]['jourSemaine'] = '1-7';
$scripts[1]['mois'] = '1-12';
$scripts[1]['URLScript'] = WEB_ROOT.'/bdd/CreaDump.php?idBase=spip_proverbe&login=flux&mdp=Mars2014';

$scripts[2]['nom'] = 'Sauvegarde flux_proverbes';
$scripts[2]['minutes'] = '2';
$scripts[2]['heures'] = '3';
$scripts[2]['jour'] = '*';
$scripts[2]['jourSemaine'] = '1-7';
$scripts[2]['mois'] = '1-12';
$scripts[2]['URLScript'] = WEB_ROOT.'/bdd/CreaDump.php?idBase=flux_proverbes&login=flux&mdp=Mars2014';
*/
$scripts[3]['nom'] = 'Sauvegarde Diigo pour Luckysemiosis';
$scripts[3]['minutes'] = '15';
$scripts[3]['heures'] = '14';
$scripts[3]['jour'] = '*';
$scripts[3]['jourSemaine'] = '*';
$scripts[3]['mois'] = '1-12';
$scripts[3]['URLScript'] = WEB_ROOT.'/public/flux/diigo?q=saveRecent&login=luckysemiosis&mdp=samszo';

/*
$scripts[1]['minutes'] = '0, 15, 30, 45';

$scripts[1]['heures'] = '*';

$scripts[1]['jour'] = '*';

$scripts[1]['jourSemaine'] = '1-6';

$scripts[1]['mois'] = '*';

$scripts[1]['URLScript'] = 'http://localhost/coucou.php';
*/