<?php
//merci à http://matthieu.developpez.com/execution_periodique/
trace("DEBUT");
require_once( "../../application/configs/config.php" );

set_time_limit(0);

ignore_user_abort(1);

require_once 'script.php';



register_shutdown_function(fini());



while(1){

        if (file_exists("stop.php")) { 
        	$message = "script arrêté. Effacez le fichier STOP avant de reprendre";
        	trace($message);        	 
        	die($message); 
        }

        
        $next = getNextExecutionTime();                 /* on récupère lheure (timestamp) de la prochaine exécution */

		$indexScript = getNextExecutionScript();        /* on récupère le numéro du prochain script à exécuter */

		$dodo = $next - time();                         /* le temps en seconde qu'il faut pour arriver à $next */

		sleep($dodo);                                   /* on dort jusqu'à ce qu'il soit temps d'exécuter le script */

		fopen($scripts[$indexScript]['URLScript'], 'r'); /* on lance le script. */

		/* on enregistre l'execution */
		trace($scripts[$indexScript]['nom']." next=".$next." dodo=".$dodo);		
		
		/* fopen peut être remplacé par une autre méthode, (shell_exec...) */

		$scripts[$indexScript]['prochain'] = setNextExecutionTimeForScript($indexScript); /* prochaine exécution */

}

trace("FIN");

function fini()

{

	trace("fini");
	fopen('./ERREUR', 'w');

}


function trace($message){
	$date = new DateTime();
	$strDate = $date->format('Y-m-d_H-i-s');	
	$t = fopen('traces.txt', 'a');
	fputs($t, $strDate.' - '.$message.PHP_EOL);
	fclose($t);	
}

function getNextExecutionScript()

{

	global $scripts;



	foreach($scripts as $index => $script)

	{

		if($script['prochain'] < $min || !(isset($min)))

		{

			$min = $script['prochain'];

			$minIndex = $index;

		}

	}

	return $minIndex;

}

function getNextExecutionTime()

{

	global $scripts;



	foreach($scripts as $script)

	{

		if($script['prochain'] < $min || !(isset($min)))

		{

			$min = $script['prochain'];

		}

	}

	return $min;

}

function buildScriptsNext()

{

	global $scripts;



	foreach($scripts as $index => $val)

	{

		$scripts[$index]['prochain'] = setNextExecutionTimeForScript($index);

	}

}


function setNextExecutionTimeForScript($indexScript)

{

	global  $scripts, $a, $m, $j, $h, $min;



	$aNow = date("Y");

	$mNow = date("m");

	$jNow = date("d");

	$hNow = date("H");

	$minNow = date("i")+1;



	$a = $aNow;

	$m = $mNow - 1;



	while(prochainMois($indexScript) != -1)                 /* on parcourt tous les mois de l'intervalle demandé */

	{                                                       /* jusqu'à trouver une réponse convenable */

		if ($m != $mNow || $a != $aNow)                 /*si ce n'est pas ce mois ci */

		{

			$j = 0;

			if (prochainJour($indexScript) == -1)   /* le premier jour trouvé sera le bon. */

			{                                       /*  -1 si l'intersection entre jour de semaine */

				/* et jour du mois est nulle */

				continue;                       /* ...auquel cas on passe au mois suivant */

			}else{                                  /* s'il y a un jour */

				$h=-1;

				prochainHeure($indexScript);    /* la première heure et la première minute conviendront*/

				$min = -1;

				prochainMinute($indexScript);

				return mktime($h, $min, 0, $m, $j, $a);

			}

		}else{                                          /* c'est ce mois ci */

			$j = $jNow-1;

			while(prochainJour($indexScript) != -1) /* on cherche un jour à partir d'aujourd'hui compris */

			{

				if ($j > $jNow)                 /* si ce n'est pas aujourd'hui */

				{                               /* on prend les premiers résultats */

					$h=-1;

					prochainHeure($indexScript);

					$min = -1;

					prochainMinute($indexScript);

					return mktime($h, $min, 0, $m, $j, $a);

				}

				if ($j == $jNow)                /* même algo pour les heures et les minutes */

				{

					$h = $hNow - 1;

					while(prochainHeure($indexScript) != -1)

					{

						if ($h > $hNow)

						{

							$min = -1;

							prochainMinute($indexScript);

							return mktime($h, $min, 0, $m, $j, $a);

						}

						if ($h == $hNow)

						{

							$min = $minNow - 1;

							while(prochainMinute($indexScript) != -1)

							{

								if ($min > $minNow) { return mktime($h, $min, 0, $m, $j, $a); }



								/* si c'est maintenant, on l'exécute directement */

								if ($min == $minNow)

								{

									fopen($scripts[$indexScript]['URLScript'], 'r');

								}

							}

						}

					}

				}

			}

		}

	}

}

function parseFormat($min, $max, $intervalle)

{

	$retour = Array();



	if ($intervalle == '*')

	{

		for($i=$min; $i<=$max; $i++) $retour[$i] = TRUE;

		return $retour;

	}else{

		for($i=$min; $i<=$max; $i++) $retour[$i] = FALSE;

	}



	$intervalle = explode(',', $intervalle);

	foreach ($intervalle as $val)

	{

		$val = explode('-', $val);

		if (isset($val[0]) && isset($val[1]))

		{

			if ($val[0] <= $val[1])

			{

				for($i=$val[0]; $i<=$val[1]; $i++) $retour[$i] = TRUE;  /* ex : 9-12 = 9, 10, 11, 12 */

			}else{

				for($i=$val[0]; $i<=$max; $i++) $retour[$i] = TRUE;     /* ex : 10-4 = 10, 11, 12... */

				for($i=$min; $i<=$val[1]; $i++) $retour[$i] = TRUE;     /* ...et 1, 2, 3, 4 */

			}

		}else{

			$retour[$val[0]] = TRUE;

		}

	}

	return $retour;

}

function prochainMois($indexScript)

{

	global $a, $m, $scripts;

	$valeurs = parseFormat(1, 12, $scripts[$indexScript]['mois']);

	do

	{

		$m++;

		if ($m == 13)

		{

			$m=1;

			$a++;           /*si on a fait le tour, on réessaye l'année suivante */

		}

	}while($valeurs[$m] != TRUE);

}

function prochainJour($indexScript)

{

	global $a, $m, $j, $scripts;

	$valeurs = parseFormat(1, 31, $scripts[$indexScript]['jour']);

	$valeurSemaine = parseFormat(0, 6, $scripts[$indexScript]['jourSemaine']);

	do{

		$j++;

		/* si $j est égal au nombre de jours du mois + 1 */

		if ($j == date('t', mktime(0, 0, 0, $m, 1, $a))+1) { return -1; }

		$js = date('w', mktime(0, 0, 0, $m, $j, $a));

	}while($valeurs[$j] != TRUE || $valeurSemaine[$js] != TRUE);

}
	
function prochainHeure($indexScript)

{

	global $h, $scripts;

	$valeurs = parseFormat(0, 23, $scripts[$indexScript]['heures']);

	do{

		$h++;

		if ($h == 24) { return -1; }

	}while($valeurs[$h] != TRUE);

}

function prochainMinute($indexScript)

{

	global $min, $scripts;

	$valeurs = parseFormat(0, 59, $scripts[$indexScript]['heures']);

	do{

		$min++;

		if ($min == 60) { return -1; }

	}while($valeurs[$min] != TRUE);

}