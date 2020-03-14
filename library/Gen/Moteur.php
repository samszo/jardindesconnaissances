<?php
/**
 * Generateur Framework
 *
 * LICENSE
 *
 * This source file is subject to the Artistic/GPL license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.generateur.com/license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@generateur.com so we can send you a copy immediately.
 *
 * @category   Generateur
 * @package    Moteur
 * @copyright  Copyright (c) 2010 J-P Balpe (http://www.balpe.com)
 * @license    http://framework.generateur.com/license/Artistic/GPL License
 * @version    $Id: Moteur.php 0 2010-09-20 15:26:09Z samszo $
 */

/**
 * Concrete class for generating Fragment for Generateur.
 *
 * @category   Generateur
 * @package    Moteur
 * @copyright  Copyright (c) 2020 S Szoniecky (http://www.samszo.univ-paris8.fr) 2010 J-P Balpe (http://www.balpe.com)
 * @license    http://framework.generateur.com/license/Artistic/GPL License
 */
class Gen_Moteur extends Flux_Site{

	var $urlDesc;
	var $xmlDesc;
	var $texte;
	var $class;
	var $arrSegment;
	var $arrClass;
	var $arrDicos;
	var $ordre;
	var $segment;
	var $potentiel=0;
	var $detail;
	var $typeChoix = "alea";
	var $cache;
	var $forceCalcul;
	var $finLigne = "<br/>";
	var $showErr = false;
	var $maxNiv = 10;
	var $niv = 0;
	var $arrDoublons = array();
	var $arrPosi;
	var $arrCaract = array();
	var $verif = false;
	var $timeDeb;
	var $timeMax = 1000;
	var $xml;
	var $xmlRoot;
	var $xmlGen;
	var $coupures=array();
	//pour l'optimisation
    var $bTrace = false;
    var $echoTrace = false;
    var $temps_debut;
    var $temps_inter;
    var $temps_nb=0;
	var $arrEli = array("a", "e", "é", "ê", "i","o","u","y","h");
	var $arrEliCode = array(195);
	var $arrStructure = [];
	var $arrReseau;
	var $arrId;

    
    /**
     * Fonction du moteur
     *
     * @param string $idBase
     * @param string $urlDesc
     * @param string $forceCalcul
     * @param string $xmlDesc
     *
     */
	public function __construct($idBase=false, $urlDesc="", $forceCalcul = false, $xmlDesc=false) {

		parent::__construct($idBase);

		$this->urlDesc = $urlDesc;
		if($this->urlDesc=="")$this->urlDesc=APPLICATION_PATH.'/configs/LangageDescripteur.xml';	
		if(!$xmlDesc) $this->xmlDesc = simplexml_load_file($this->urlDesc);
		else $this->xmlDesc = $xmlDesc;
		$this->forceCalcul = $forceCalcul;	
	}
	
    /**
     * Fonction du moteur
     *
     *
     */
	private function setCache(){
		$frontendOptions = array(
	    	'lifetime' => 86400, //  temps de vie du cache de 1 jour
	        'automatic_serialization' => true
		);
	   	$backendOptions = array(
			// Répertoire où stocker les fichiers de cache
	   		'cache_dir' => ROOT_PATH.'/tmp/'
		);
		// créer un objet Zend_Cache_Core
		$this->cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);				
	}
	
	
	
    /**
     * récupère les dictionnaires pour une oeuvre
     *
     * @param int $idOeu
     * 
     * @return array
     */
	public function getDicosOeuvre($idOeu){
		$dbODU = new Model_DbTable_Gen_oeuvresxdicosxutis($this->db);
		$arrDico = $dbODU->findByIdOeu($idOeu);		
		foreach ($arrDico as $dico) {
			if(substr($dico["type"],0,7)=="pronoms" ){
				if($arrVerifDico["pronoms"]){
					$arrVerifDico["pronoms"] .= ", ".$dico["id_dico"];
				}else{
					$arrVerifDico["pronoms"]=$dico["id_dico"];
				}			
			}
			if($dico["type"]=="négations" ){
				if($arrVerifDico["negations"]){
					$arrVerifDico["negations"] .= ", ".$dico["id_dico"];
				}else{
					$arrVerifDico["negations"]=$dico["id_dico"];
				}			
			}
			if($arrVerifDico[$dico["type"]]){
				$arrVerifDico[$dico["type"]] .= ", ".$dico["id_dico"];
			}else{
				$arrVerifDico[$dico["type"]]=$dico["id_dico"];
			}
		}
		return $arrVerifDico;
	}
	
    /**
     * Fonction du moteur
     *
     * @param array $arrTextes
     * @param array $arrDicos
     * @param boolean $trace
     * 
     * @return array
     */
	public function Tester($arrTextes, $arrDicos, $trace=false){

		$this->forceCalcul = true;
		$this->arrDicos = $arrDicos;	
		$this->showErr = true;
		//
    	$this->bTrace = $trace; // pour afficher les traces   	
    	$this->temps_debut = microtime(true);
		$this->trace("DEBUT ".__METHOD__);
		
		foreach ($arrTextes as $texte) {
			$this->Generation($texte);
			$arrResult[]=['text'=>$this->texte,'details'=>$this->detail];	
		}
		
		$this->trace("FIN ".__METHOD__);
		
		return $arrResult; 				
	}

    /**
     * Fonction du moteur
     *
     * @param string $texte
     * @param array $arrDicos
     * 
     * @return array
     */
	public function getArbreGen($texte, $arrDicos){

		$this->arrDicos = $arrDicos;
		$this->xml = new DOMDocument('1.0', 'UTF-8');
		$this->xml->appendChild(new DOMComment('arbre de génération de '.$texte));
		$this->xmlRoot = $this->xml->appendChild(new DOMElement('gen'));
		$xDicos = $this->xml->createElement('dicos');
		foreach ($arrDicos as $type=>$dico) {
			$xDico = $this->xml->createElement('dico');
			$xAtt = $this->xml->createAttribute('ids');
			$nText = $this->xml->createTextNode($dico);
			$xAtt->appendChild($nText); 				
			$xDico->appendChild($xAtt);
			$xAtt = $this->xml->createAttribute('type');
			$nText = $this->xml->createTextNode($type);
			$xAtt->appendChild($nText); 				
			$xDico->appendChild($xAtt);
			$xDicos->appendChild($xDico);
		}
		$this->xmlRoot->appendChild($xDicos);		
		$this->Verifier($texte);
		echo $this->xml->saveXML();
	}
	
	/**
     * Fonction du moteur
     *
     * @param string $texte
     * @param boolean $getTexte
     * @param boolean $cache
     *
     */
	public function Generation($texte, $getTexte=true, $cache=false){
		
		$this->trace("DEBUT ".__METHOD__);
		if(!$cache){
			$this->setCache();
			$this->timeDeb = microtime(true);
		}else{
			$this->cache = $cache;		
		}
		
		$this->arrClass = array();
		$this->ordre = 0;
		$this->segment = 0;
		$this->arrClass[$this->ordre]["generation"] = $texte;
		$this->arrClass[$this->ordre]["niveau"] = $this->niv;
		if($this->niv > $this->maxNiv){
			$this->arrClass[$this->ordre]["ERREUR"] = "problème de boucle trop longue : ".$texte;			
			throw new Exception("problème de boucle trop longue.<br/>".$this->detail);			
		}
		
		//parcourt l'ensemble de la chaine
		for($i = 0; $i < strlen($texte); $i++)
        {
        	//sortie de boucle quand trop long
        	$t = microtime(true)-$this->timeDeb;
        	if($t > $this->timeMax){
        		$this->arrClass[$this->ordre]["ERREUR"] = "problème d'exécution trop longue : ".substr($texte, 0, $i)." ~ ".substr($texte, $i);			
			    //on ajoute les caractères
				$this->ordre ++;
        		$this->arrClass[$this->ordre]["texte"] = "~";
			    $this->arrSegment[$this->segment]["ordreFin"]= $this->ordre;
				break; 
        	}

        	$c = $texte[$i];
			$this->trace(__METHOD__." : ".$i." = ".$c);
        	if($c == "["){
        		//c'est le début d'une classe on initialise les positions
        		$this->arrPosi = false;
        		//on récupère la valeur de la classe et la position des caractères dans la chaine
        		$i = $this->traiteClass($texte, $i);
        	/*
        	}elseif($c == "="){
        		//c'est le début d'une notification de format
        		$i = $this->traiteFormat($texte, $i);
        	*/
        	}elseif($c == "F"){//obsolete
        		if($texte[$i+1]=="F"){
	        		//c'est la fin du segment
			        $this->arrSegment[$this->segment]["ordreFin"]= $this->ordre;
					$this->segment ++;
	        		$i++;
	        	}else{
	        		//on ajoute le caractère
					$this->arrClass[$this->ordre]["texte"] = $c;
	        	}
        	}else{
        		//on ajoute le caractère
				$this->arrClass[$this->ordre]["texte"] = $c;
        	}
			$this->arrClass[$this->ordre]["niveau"] = $this->niv;
        	$this->ordre ++;
        }
                
        //on calcule le texte
        if($getTexte){
        	$this->genereTexte();
        }
        
		$this->trace($this->texte);
		$this->trace("FIN ".__METHOD__);
        return $this->texte;
	}

    /**
     * Fonction du moteur
     *
     * @param string $texte
     * @param array $arrDicos
     * @param boolean $html
     *
     */
	public function Verifier($texte, $arrDicos, $html=false){
		
		$this->setCache();
		$this->verif = true;				
		$this->ordre = 0;
		$this->typeChoix = "tout";
		$this->arrDicos = $arrDicos;
		$this->showErr = true;	
		$this->forceCalcul = true;
		$this->maxNiv = 3;
		$this->timeMax = 10;
		
		if($this->niv > 2) return;
		//création de l'xml de réponse
		$this->xml = new DOMDocument('1.0', 'UTF-8');
		$this->xml->appendChild(new DOMComment('Vérification de '.$texte));
		$this->xmlRoot = $this->xml->appendChild(new DOMElement('gen'));
		
		$this->Generation($texte);
				
        if($html){
			return $this->arrayVersHTML($this->arrClass);    	
        }else{
        	return $this->texte;
        }
       
	}
	
    /**
     * Fonction du moteur
     *
     *
     */
	public function genereTexte(){

		$this->texte = "";
		$txtCondi = true;
		$imbCondi= false;
		
		//vérifie la présence de segments
		if(count($this->arrSegment)>0){
			//choix aleatoire d'un segment
			mt_srand($this->make_seed());
	        $a = mt_rand(0, count($this->arrSegment)-1);        
			$ordreDeb = $this->arrSegment[$a]["ordreDeb"];
			$ordreFin = $this->arrSegment[$a]["ordreFin"];
		}else{
			$ordreDeb = 0;
			$ordreFin = count($this->arrClass)-1;
		}
		
		for ($i = $ordreDeb; $i <= $ordreFin; $i++) {
			$this->ordre = $i;
			$texte = "";
			if(isset($this->arrClass[$i])){
				$arr = $this->arrClass[$i];
				if(isset($arr["texte"])){
					//vérifie le texte conditionnel
					if($arr["texte"]=="<"){
				        //choisi s'il faut afficher
				        $this->potentiel ++;
						mt_srand($this->make_seed());
				        $a = mt_rand(0, 1000);        
				        if($a>500){
				        	$txtCondi = false;
					        //vérifie si le texte conditionnel est imbriqué
					        //pour sauter à la fin de la condition
					        if(isset($this->arrClass[$i+2]["texte"]) && $this->arrClass[$i+2]["texte"]=="|"){
					        	for ($j = $this->ordre; $j <= $ordreFin; $j++) {
					        		if($this->arrClass[$j]["texte"]=="|"
					        			&& $this->arrClass[$j+1]["texte"]==$this->arrClass[$i+1]["texte"]
						        		&& $this->arrClass[$j+2]["texte"]==">"){
						        			$i=$j+2;
						        			$j=$ordreFin;		
					        		}
					        	}
					        }
				        }else{
					        //vérifie si le texte conditionnel est imbriqué
					        //attention pas plus de 10 imbrications
					        if(isset($this->arrClass[$this->ordre+2]["texte"]) && $this->arrClass[$this->ordre+2]["texte"]=="|"){
					        	$imbCondi[$this->arrClass[$this->ordre+1]["texte"]] = true;
					        	$i+=2;
					        }
				        }
					}elseif($arr["texte"]==">"){
				        //vérifie les conditionnels imbriqué
				        if($imbCondi){
				        	if(isset($imbCondi[$this->arrClass[$this->ordre-1]["texte"]])){
					        	$txtCondi = true;
					        	$c = substr($this->texte,-2,1);
					        	if($c=="|"){
						        	$this->texte = substr($this->texte,0,-2);
					        	}
					        	//supprime la condition imbriquée
					        	unset($imbCondi[$this->arrClass[$this->ordre-1]["texte"]]);
					        }else{
					        	//cas des conditions dans condition imbriquée
								$txtCondi = true;					        	
					        }
				        }else{
							$txtCondi = true;
				        }
					}elseif($arr["texte"]=="{"){
						//on saute les crochets de test
					    for ($j = $this->ordre; $j <= $ordreFin; $j++) {
					    	if($this->arrClass[$j]["texte"]=="}"){
					    		$i = $j+1;	
					    	}
					    }
					}elseif($txtCondi){
						if($arr["texte"]=="%"){
							$texte .= $this->finLigne;	
						}else{
							$texte .= $arr["texte"];
						}
					}
				}elseif(isset($arr["ERREUR"]) && $this->showErr){
					$this->texte .= '<ul><font color="#de1f1f" >ERREUR:'.$this->arrayVersListHTML($arr).'</font></ul>';	
				}else{
					if($txtCondi){
						$det = "";
						$sub = "";
						$adjs = "";
						$verbe = "";
						 
						if(isset($arr["determinant"])){
							$det = $this->genereDeterminant($arr);					
						}
						
						if(isset($arr["substantif"])){					
							$sub = $this->genereSubstantif($arr);						
						}					
						
						if(isset($arr["adjectifs"])){
							//$k=0;					
							foreach($arr["adjectifs"] as $adj){
								//if($k>0)
								$adjs .= " ";
								$adjs .= $this->genereAdjectif($adj);
								//$k++;
							}
						}
		
						if(isset($arr["verbe"])){					
							$verbe = $this->genereVerbe($arr);
						}					
						
						if(isset($arr["syntagme"])){					
							$texte .= $arr["syntagme"]["lib"];
						}					
											
						$texte .= $det.$sub." ".$adjs.$verbe;
					}					
				}
				if($texte!=""){
					$this->arrClass[$i]["texte"] = $texte;
					$this->texte .= $texte;
				}
			}
		}
		
		//gestion des espace en trop
		//$this->texte = preg_replace('/\s\s+/', ' ', $this->texte); //problème avec à qui devient illisible ???
		$this->texte = str_replace("  "," ",$this->texte);
		$this->texte = str_replace("  "," ",$this->texte);
		$this->texte = str_replace("  "," ",$this->texte);
		$this->texte = str_replace("  "," ",$this->texte);
		$this->texte = str_replace("  "," ",$this->texte);
		$this->texte = str_replace("  "," ",$this->texte);
				
		$this->texte = str_replace("’ ","’",$this->texte);
		$this->texte = str_replace("' ","'",$this->texte);
		$this->texte = str_replace(" , ",", ",$this->texte);
		$this->texte = str_replace(" .",".",$this->texte);
		$this->texte = str_replace(" 's","'s",$this->texte);
		$this->texte = str_replace(" -","-",$this->texte);
		$this->texte = str_replace("- ","-",$this->texte);
		$this->texte = str_replace("( ","(",$this->texte);
		$this->texte = str_replace(" )",")",$this->texte);
		
		//gestion des majuscules sauf pour twitter
		if(strrpos($this->arrDicos["concepts"],"96")===false){
			$this->genereMajuscules();
		}

		//mise en forme du texte
		$this->coupures();
		
		//création du tableau de génération
		if($this->showErr)$this->detail = "ordreDeb=$ordreDeb ordreFin=$ordreFin<br/>".$this->arrayVersHTML($this->arrClass);
		
	}

    /**
     * Fonction du moteur
     *
     *
     */
	public function coupures(){
		$this->trace("DEBUT ".__METHOD__);
		//mise en forme du texte
		/*coupure de phrase*/
		if(count($this->coupures)==2){
			$this->texte .= " ";
			$LT = strlen($this->texte);
			$nbCaractCoupure = mt_rand($this->coupures[0], $this->coupures[1]);
			$i = $nbCaractCoupure;
			while(($i+$this->coupures[1]) < $LT) {
				//trouve la coupure
				$c = substr($this->texte, $i, 1);
				$this->trace($nbCaractCoupure." ".$c." ".$i."/".$LT);
				$go = true;
				$j = $i;
				while ($go) {
					if($c == "" || $c == " " || $c == "," || $c == "." || $c == ";"){
						$go=false;
						$i = $j;
					}elseif ($j==0){
						//coupe jusqu'au prochain espace
						$i = strpos($this->texte, ' ', $i);
						$go=false;
					}else{
						$j --;
						$c = substr($this->texte, $j, 1);
					}
				}
				$this->texte = trim(substr($this->texte, 0, $i)).$this->finLigne.trim(substr($this->texte, $i));
				$nbCaractCoupure = mt_rand($this->coupures[0], $this->coupures[1]);
				$i += $nbCaractCoupure+strlen($this->finLigne);
				$LT = strlen($this->texte);
			}
			
		}
		$this->trace("FIN ".__METHOD__);
	}
	
	/**
     * Fonction du moteur
     *
     *
     */
	public function genereMajuscules(){
		//merci à http://uk.php.net/manual/fr/function.preg-replace-callback.php#101774

		//suprime les espaces avant
		$this->texte = trim($this->texte);
		
			$this->texte = preg_replace_callback(
				'|(?:\?)(?:\s*)(\w{1})|Ui',
				create_function('$matches', 'return "? ".strtoupper($matches[1]);'), ucfirst($this->texte)
			); 
			$this->texte = preg_replace_callback(
				'|(?:\.)(?:\s*)(\w{1})|Ui',
				create_function('$matches', 'return ". ".strtoupper($matches[1]);'), ucfirst($this->texte)
			); 
			$this->texte = preg_replace_callback(
				'|(?:\!)(?:\s*)(\w{1})|Ui',
				create_function('$matches', 'return "! ".strtoupper($matches[1]);'), ucfirst($this->texte)
			); 
			$this->texte = preg_replace_callback(
				'|(?:<br/>)(?:\s*)(\w{1})|Ui',
				create_function('$matches', 'return "<br/> ".strtoupper($matches[1]);'), ucfirst($this->texte)
			); 
			
			$this->texte = $this->sentence_cap($this->finLigne,$this->texte);			
			
	}

    /**
     * Fonction du moteur
     *
     * @param string $impexp
     * @param boolean $sentence_split
     *
     */
	function sentence_cap($impexp, $sentence_split) {
	    //merci à http://fr.php.net/manual/fr/function.ucfirst.php#73895
		$textbad=explode($impexp, $sentence_split);
	    $newtext = array();
	    foreach ($textbad as $sentence) {
	        $sentencegood=ucfirst($sentence);
	        $newtext[] = $sentencegood;
	    }
	    $textgood = implode($impexp, $newtext);
	    return $textgood;
	}	
	
    /**
     * Fonction du moteur
     *
     * @param array $arr
     *
     */
	public function genereSubstantif($arr){

		$txt = $arr["substantif"]["s"];
		$vecteur = $this->getVecteur("pluriel",-1);
		if($vecteur["pluriel"]){												
			$txt = $arr["substantif"]["p"];
		}
		
		$txt = $arr["substantif"]["prefix"].$txt;
				
		return $txt;
	}
	
    /**
     * Fonction du moteur
     *
     * @param array $arr
     *
     */
	public function generePronom($arr){
		
		//par défaut la terminaison = 3
		$arr["terminaison"] = 3;
		$pluriel = false;
		
		//vérifie la présence d'un d&terminant
		if(isset($arr["determinant_verbe"])){
			if($arr["determinant_verbe"][6]!=0){
				//pronom indéfinie
				$arr["prosuj"] = $this->getPronom($arr["determinant_verbe"][6],"sujet_indefini");
				//$arr["terminaison"] = 3;
			}elseif($arr["determinant_verbe"][2]==0){
				//pas de pronom
				$arr["prosuj"] = "";
				//$arr["terminaison"] = 3;				
			}
			
			//pronom définie
			$numP = $arr["determinant_verbe"][2];
			$pr = "";
			//définition des terminaisons et du pluriel
			if($numP==6){
				//il/elle singulier
				$pr = "[a_il]";
				$pluriel = false;
				$arr["terminaison"] = 3;				
			}	
			if($numP==7){
				//il/elle pluriel
				$pr = "[a_il]";
				$pluriel = true;
				$arr["terminaison"] = 6;				
			}	
			if($numP==8){
				//pas de pronom singulier
				$pr = "[a_zéro]";
				$pluriel = false;
				$arr["terminaison"] = 3;				
			}	
			if($numP==9){
				//pas de pronom pluriel
				$pr = "[a_zéro]";
				$pluriel = true;
				$arr["terminaison"] = 6;				
			}
			if($numP==1 || $numP==2){
				$pluriel = false;
				$arr["terminaison"] = $numP;				
			}
			if($numP==4 || $numP==5){
				$pluriel = true;
				$arr["terminaison"] = $numP;				
			}
			
			if($arr["prosuj"]==""){
				if($numP>=6){	
					//récupère le vecteur
					$vecteur = $this->getVecteur("genre",-1,$arr["determinant_verbe"][7]);
					$genre = $vecteur["genre"];     	
					//génère le pronom
					$m = new Gen_Moteur($this->idBase,$this->urlDesc,$this->forceCalcul, $this->xmlDesc);
					$m->showErr = $this->showErr;
					$m->timeDeb = $this->timeDeb;
					$m->arrDicos = $this->arrDicos;
					$m->Generation($pr,false,$this->cache);
					$m->arrClass[0]["vecteur"]["genre"]=$genre;
					$m->arrClass[0]["vecteur"]["pluriel"]=$pluriel;						
					$this->potentiel += $m->potentiel;
					$m->genereTexte();						
					$arr["prosuj"]["lib"] = strtolower($m->texte);
					$arr["prosuj"]["lib_eli"] = strtolower($m->texte);;
				}else{
					$arr["prosuj"] = $this->getPronom($numP,"sujet");
				}
			}

			//pronom complément
			if($arr["determinant_verbe"][3]!=0 || $arr["determinant_verbe"][4]!=0){
				$numPC = $arr["determinant_verbe"][3].$arr["determinant_verbe"][4];
				$arr["prodem"] = $this->getPronom($numPC,"complément");
		        if(substr($arr["prodem"]["lib"],0,1)== "["){
					//récupère le vecteur
					$vecteur = $this->getVecteur("genre",-1,$arr["determinant_verbe"][7],"substantif");
					$genre = $vecteur["genre"];     	
					//vérifie s'il y a un blocage du genre et du nombre
					if(substr($arr["prodem"]["lib"],0,3)=="[=x"){
						$genre = 1;
						$pluriel = "";
					}elseif(substr($arr["prodem"]["lib"],0,2)=="[="){
						//récupère le renvoie du genre et du nombre	
						$vecteur = $this->getVecteur("genre",-1,substr($arr["prodem"]["lib"],2,1),"substantif");
						$genre = $vecteur["genre"];     	
					}
					//génère le pronom
					$m = new Gen_Moteur($this->idBase,$this->urlDesc,$this->forceCalcul,$this->xmlDesc);
					$m->showErr = $this->showErr;
					$m->timeDeb = $this->timeDeb;
					$m->arrDicos = $this->arrDicos;
					$m->Generation($arr["prodem"]["lib"],false,$this->cache);
					$m->arrClass[0]["vecteur"]["genre"]=$genre;
					$m->arrClass[0]["vecteur"]["pluriel"]=$pluriel;						
					$this->potentiel += $m->potentiel;
					$m->genereTexte();						
					$arr["prodem"]["lib"] = strtolower($m->texte);
		        }
			}			
		}		
		return $arr;
	}

    /**
     * Fonction du moteur
     *
     * @param array $arr
     *
     */
	public function genereDeterminant($arr){

		$det = "";
		if($vecteur = $this->getVecteur("elision", 1)){			
			//calcul le déterminant
			if($vecteur["elision"]==0 && $vecteur["genre"]==1){
				$det = $arr["determinant"][0]["lib"]." ";
			}
			if($vecteur["elision"]==0 && $vecteur["genre"]==2){
				$det = $arr["determinant"][1]["lib"]." ";
			}
			if($vecteur["elision"]==1 && $vecteur["genre"]==1){
				$det = $arr["determinant"][2]["lib"];
			}
			if($vecteur["elision"]==1 && $vecteur["genre"]==2){
				$det = $arr["determinant"][3]["lib"];
			}
		}
		
		return $det;
	}
		
    /**
     * Fonction du moteur
     *
     * @param string 	$type
     * @param string 	$dir
     * @param int 		$num
     * @param string 	$classType
     *
     */
	public function getVecteur($type,$dir,$num=1,$classType=false){
		
		//pour les verbes
		if($num==0)$num=1;
		
		$vecteur = false;
		$j = 1;
		if($dir>0){
			for ($i = $this->ordre; $i < count($this->arrClass); $i++) {
				if(isset($this->arrClass[$i]["vecteur"][$type])){
					if(!$classType){
						if($num == $j){
							return $this->arrClass[$i]["vecteur"];
						}else{
							$j ++;							
						}
					}else{
						//pour éviter de récupérer le vecteur d'un adjectif
						if(isset($this->arrClass[$i][$classType])){
							if($num == $j){
								return $this->arrClass[$i]["vecteur"];
							}else{
								$j ++;							
							}
						}
					}
				}
			}
		}
		if($dir<0){
			for ($i = $this->ordre; $i >= 0; $i--) {
				//on récupère le vecteur 
				if(isset($this->arrClass[$i]["vecteur"][$type])){
					if(!$classType){
						if($num == $j){
							return $this->arrClass[$i]["vecteur"];
						}else{
							$j ++;							
						}
					}else{
						//pour éviter de récupérer le vecteur d'un adjectif
						if(isset($this->arrClass[$i][$classType])){
							if($num == $j){
								return $this->arrClass[$i]["vecteur"];
							}else{
								$j ++;							
							}
						}
					}
				}
			}
		}
		return $vecteur;
	}
		
	
    /**
     * Fonction du moteur
     *
     * @param array $adj
     *
     */
	public function genereAdjectif($adj){

       	$txt = "";

		$vecteur = $this->getVecteur("pluriel",-1);
		
		//calcul le nombre
		$n = "_s"; 		
		if($vecteur["pluriel"]){												
			$n = "_p";
		}
		
		//calcul le genre
		$g = "m"; 		
		if(isset($vecteur["genre"])){												
			if($vecteur["genre"]==2) $g = "f";
		}else{
			$vecteurGenre = $this->getVecteur("genre",-1);
			if($vecteurGenre["genre"]==2) $g = "f";			
		}
		
		$txt = $adj["prefix"].$adj[$g.$n]." ";
		
		return $txt;
	}

    /**
     * Fonction du moteur
     *
     * @param array $arr
     *
     */
	public function genereTerminaison($arr){
		
		//par défaut la terminaison est 3eme personne du présent
		$num = 2;
		
		if(isset($arr["determinant_verbe"])){
			$temps= $arr["determinant_verbe"][1]-1;
			
			if($arr["determinant_verbe"][1]==1){
				$temps= 0;
			}
			if($arr["determinant_verbe"][1]==7){
				$temps= 0;
			}
			//formule de calcul de la terminaison temps + persones
			$num = ($temps*6)+$arr["terminaison"]-1;
			 		
			if($arr["determinant_verbe"][1]==8){
				$num= 36;
			}
			if($arr["determinant_verbe"][1]==9){
				$num= 37;
			}
		}	
		//correction terminaison négative
		if($num == -1)$num = 2;
					
		$txt = $this->getTerminaison($arr["verbe"]["id_conj"],$num);

		//gestion des terminaisons ---
		if($txt=="---")$txt="";
		
		return $txt;
	}
	
    /**
     * Fonction du moteur
     *
     * @param mixte $cls
     *
     */
	public function getCarac($cls){
		
		if(is_string($cls)){
			$this->arrClass[$this->ordre]["texte"] = $cls;			
		}else{
			if(isset($cls["id_adj"])){
				$this->arrClass[$this->ordre]["adjectifs"][] = $cls;
			}
			if(isset($cls["id_dtm"])){
				$this->arrClass[$this->ordre]["adjectifs"][] = $cls;
			}
			if(isset($cls["id_sub"])){
				$this->getSubstantif("",$cls);
			}
			if(isset($cls["id_verbe"])){
				$this->getVerbe("",$cls);
			}			
		}		
		
	}

    /**
     * Fonction du moteur
     *
     * @param array $haystack
     * @param array $needles
     *
     */
	function strpos_array($haystack, $needles) {
	    //merci à http://www.php.net/manual/en/function.strpos.php#102773
		if ( is_array($needles) ) {
	        foreach ($needles as $str) {
	            if ( is_array($str) ) {
	                $pos = strpos_array($haystack, $str);
	            } else {
	                $pos = strpos($haystack, $str);
	            }
	            if ($pos !== FALSE) {
	                return $pos;
	            }
	        }
	    } else {
	        return strpos($haystack, $needles);
	    }
	}
	
	
    /**
     * Fonction du moteur
     *
     * @param array $arr
     *
     */
	public function genereVerbe($arr){

		/*
		Position 1 : type de négation
		Position 2 : temps verbal
		Position 3 : pronoms sujets définis
		Positions 4 ET 5 : pronoms compléments
		Position 6 : ordre des pronoms sujets
		Position 7 : pronoms indéfinis
		Position 8 : Place du sujet dans la chaîne grammaticale
		*/
		$arr["debneg"]="";
		$arr["finneg"]="";

		//dans le cas d'un verbe théorique on ne fait rien
		if($arr["class"][1]=="v_théorique") return "";
		
		//vérifie s'il faut récupérer le 
		$arr = $this->getDerterminantVerbe($arr);    	
		
		//génère le pronom
		$arr = $this->generePronom($arr);    	
		
		//récupère la terminaison
		$term = $this->genereTerminaison($arr);
		
		//construction du centre
		$centre = $arr["verbe"]["prefix"].$term;
		
		//construction de l'élision
		$eliVerbe = $arr["verbe"]["elision"];
		if($eliVerbe==0){
			$eliVerbe = $this->isEli($centre);
		}
		
		$verbe="";
		if(isset($arr["determinant_verbe"])){
			//génère la négation
			if($arr["determinant_verbe"][0]!=0){
				$arr["finneg"] = $this->getNegation($arr["determinant_verbe"][0]);
				if($eli==0){
					$arr["debneg"] = "ne ";	
				}else{
					if(isset($arr["prodem"]) && !$this->isEli($arr["prodem"]["lib"])){
						$arr["debneg"] = "ne ";
					}else{
						$arr["debneg"] = "n'";						
					}
				}
			}
			
			//construction de la forme verbale
			$verbe = "";
			/*gestion de l'infinitif 
			 * 
			 */
			if($arr["determinant_verbe"][1]==9 || $arr["determinant_verbe"][1]==7){
				$verbe = $centre;
				if($arr["prodem"]!=""){
					if($arr["prodem"]["num"]!=39 && $arr["prodem"]["num"]!=40 && $arr["prodem"]["num"]!=41){
						if(!$this->isEli($verbe) || $arr["prodem"]["lib"] == $arr["prodem"]["lib_eli"]){
							$verbe = $arr["prodem"]["lib"]." ".$verbe; 
						}else{
							$verbe = $arr["prodem"]["lib_eli"].$verbe; 
							$eli=0;
						}
					}
				}
				//les deux parties de la négation se placent avant le verbe pour les valeurs 1,2, 3, 4, 7 et 8
				//uniquement pour l'infinitif 
				if($arr["determinant_verbe"][1]==9 && (
					$arr["determinant_verbe"][0]==1 || $arr["determinant_verbe"][0]==2 
					|| $arr["determinant_verbe"][0]==3 
					|| $arr["determinant_verbe"][0]==4 
					|| $arr["determinant_verbe"][0]==7 
					|| $arr["determinant_verbe"][0]==8)){
					$verbe = "ne ".$arr["finneg"]." ".$verbe;
				}else{
					if($this->isEli($verbe) && $arr["finneg"]!=""){
						$verbe = "n'".$verbe." ".$arr["finneg"];
					}else{
						$verbe = $arr["debneg"].$verbe." ".$arr["finneg"];
					}
				}
				if($arr["prodem"]!=""){
					//le pronom complément se place en tête lorsqu’il a les valeurs 39, 40, 41
	                if($arr["prodem"]["num"]==39 || $arr["prodem"]["num"]==40 || $arr["prodem"]["num"]==41){
						if(!$this->isEli($verbe) || $arr["prodem"]["lib"] == $arr["prodem"]["lib_eli"]){
							$verbe = $arr["prodem"]["lib"]." ".$verbe; 
						}else{
							$verbe = $arr["prodem"]["lib_eli"].$verbe; 
							$eli=0;
						}
					}
				}								
			}		
			//gestion de l'ordre inverse
			if($arr["determinant_verbe"][5]==1){
				$verbe = $centre."-";
				if($arr["prodem"]!=""){
					if(!$this->isEli($verbe)){
						$verbe = $arr["prodem"]["lib"]." ".$verbe; 
					}else{
						$verbe = $arr["prodem"]["lib_eli"].$verbe; 
					}
				}	
				$c = substr($centre,strlen($centre)-1);
				if(($c == "e" || $c == "a") && $arr["terminaison"]==3){
					$verbe .= "t-"; 
				}elseif($c == "e" && $arr["terminaison"]==1){
					$verbe = substr($verbe,0,-2)."é-"; 
				}
				if($this->isEli($verbe) && $arr["debneg"]!=""){
					$verbe = "n'".$verbe.$arr["prosuj"]["lib"]." ".$arr["finneg"]; 
				}else{
					$verbe = $arr["debneg"]." ".$verbe.$arr["prosuj"]["lib"]." ".$arr["finneg"];
				}
			}
		}
		//gestion de l'ordre normal
		if($verbe==""){
			$verbe = $centre." ".$arr["finneg"];
			if($arr["prodem"]!=""){
				//si le pronom eli = le pronom normal on met un espace
				if(!$this->isEli($verbe) || $arr["prodem"]["lib"] == $arr["prodem"]["lib_eli"]){
					$verbe = $arr["prodem"]["lib"]." ".$verbe; 
				}else{
					$verbe = $arr["prodem"]["lib_eli"].$verbe; 
					$eli=0;
				}
			}	
			if($arr["debneg"]!=""){
				if($this->isEli($verbe)){
					$verbe = "n'".$verbe; 
				}else{
					$verbe = $arr["debneg"].$verbe; 
				}
			}	
			if($arr["prosuj"]!=""){
				if($this->isEli($verbe)){
					//vérification de l'apostrophe
					if (strrpos($arr["prosuj"]["lib_eli"], "'") === false) { 
						$verbe = $arr["prosuj"]["lib_eli"]." ".$verbe; 
					}else
						$verbe = $arr["prosuj"]["lib_eli"].$verbe; 
				}else{
					$verbe = $arr["prosuj"]["lib"]." ".$verbe; 
				}
			}	
		}
		
		return $verbe;
	}

	public function isEli($s){
		if(in_array($s[0], $this->arrEli) || in_array(ord($s), $this->arrEliCode))return true;
		else return false;
	}
	
	public function getDerterminantVerbe($arr){

		//si le déterminant est défini on ne fait rien
		if(isset($arr["determinant_verbe"]))return $arr;
		
		//if(!isset($arr["determinant_verbe"])){
			//vérifie la présence d'un verbe théorique avant la fin de la séquence générative
			$verbeTheo = false;
			$deterPrec = false;
			for ($i = $this->ordre; $i >= 0; $i--) {
				if(isset($this->arrClass[$i]) && isset($this->arrClass[$i]["class"][1])){
					if($this->arrClass[$i]["class"][1]=="v_théorique"){
						$arr["determinant_verbe"] = $this->arrClass[$i]["determinant_verbe"];
						$verbeTheo = true;
						$i=-1;						
					}
				}
				//vérifie la fin de la séquence générative
				if(isset($this->arrClass[$i]["generation"]))$i=-1;
			}
			if(!$verbeTheo){
				//vérifie si le déterminant n'est pas défini avant une génération de verbe en verbe
				for ($i = $this->ordre-1; $i >= 0; $i--) {
					if(isset($this->arrClass[$i]) && isset($this->arrClass[$i]["determinant_verbe"])){
						$arr["determinant_verbe"] = $this->arrClass[$i]["determinant_verbe"];
						$i=-1;
						$deterPrec = true;
					}
					//vérifie la fin de la séquence générative
					if(isset($this->arrClass[$i]["generation"]))$i=-1;
				}
			}
			if(!$deterPrec && !isset($arr["determinant_verbe"])){
				//3eme personne sans pronom
				$arr["prosuj"] = "";
				$arr["terminaison"] = 3;
			}
		//}
		
		return $arr;
	}
	
    /**
     * Fonction du moteur
     *
     * @param mixte $class
     *
     */
	public function getClass($class){

		if($class=="")return;		

		//gestion du changement de position de la classe
		$arrPosi=explode("@", $class);
        if(count($arrPosi)>1){
        	$this->arrPosi=explode("@", $class);
        	$this->trace("récupère le vecteur du déterminant ".$this->ordre);
        	$vDet = $this->arrClass[($this->ordre)]["vecteur"];
        	$ordreDet = $this->ordre;
        	//change l'ordre pour que la class substantif soit placée après
        	$this->ordre ++;
        	$this->arrClass[$this->ordre]["vecteur"] = $vDet; 
        	//calcul le substantifs
        	$this->getClass($this->arrPosi[1]);
        	$vSub = $this->arrClass[($this->ordre)]["vecteur"];
        	//redéfini l'ordre pour que la class adjectif soit placée avant
        	$this->ordre --;
        	//avec le vecteur du substantif
        	$this->arrClass[$this->ordre]["vecteur"] = $vSub; 
        	//calcul l'adjectif
        	$this->getClass($this->arrPosi[0]);
        	$vAdj = $this->arrClass[($this->ordre-1)]["vecteur"];        	
        	//rédifini l'élision et le genre du déterminant avec celui de l'adjectif
        	$this->arrClass[$ordreDet]["vecteur"]["elision"]=$vAdj["elision"];
        	$this->arrClass[$ordreDet]["vecteur"]["genre"]=$vSub["genre"];
        	
        	return;
        }
        
		$this->arrClass[$this->ordre]["class"][] = $class;

		//vérifie si la class est un déterminant
		if(is_numeric($class)){
			$this->getDeterminant($class);
		}
		
		//vérifie si la class possède un déterminant de class
		$c = strpos($class,"_");
		if($c>0){
			$arr = explode("_",$class);			
			switch ($arr[0]) {
				case "a":
					$this->getAdjectifs($class);
					break;
				case "m":
					$this->getSubstantif($class);
					break;
				case "v":
					$this->getVerbe($class);
					break;
				case "s":
					$this->getSyntagme($class,false);
					break;
			}
		}elseif(substr($class,0,5)=="carac"){
			//la class est un caractère
			$classSpe = str_replace("carac", "carac_", $class);
			$this->getClassSpe($classSpe);
		}else{
			//vérifie si la class possède un blocage d'information
			$c = strpos($class,"#");
			if($c>0){
				$classSpe = str_replace("#","",$class); 
				$this->getSyntagme($classSpe);	
			}
	
			//vérifie si la class possède un blocage d'information
			if(substr($class,0,1)=="=" && is_numeric(substr($class,1,1))){
				$this->getBlocage($class);	
			}        
			if(substr($class,0,1)=="=" && substr($class,1,1)=="x"){
				$this->getBlocage($class);	
			}        
			
			//vérifie si la class est un type spécifique
			$c = strpos($class,"-");
			if($c>0){
				$classSpe = substr($class,0,$c)."_".substr($class,$c+1);
				$this->getClassSpe($classSpe);
			}			
		}        		
	}

    /**
     * Fonction du moteur
     *
     * @param mixte $class
     *
     */
	public function getClassSpe($class){

		if(get_class($class)=="Gen_Moteur"){
			$this->getClassMoteur($class);
		}elseif(is_array($class)){
			return $class;
		}else{		
			$cls = $this->getAleaClass($class);
			if(is_string($cls)){
				$this->arrClass[$this->ordre]["texte"] = $cls;			
			}elseif(get_class($cls)=="Gen_Moteur"){
				$this->getClassMoteur($cls);
			}else{
				$this->getClassType($cls);
			}		
		}
	}

    /**
     * Fonction du moteur
     *
     * @param mixte $moteur
     *
     */
	public function getClassMoteur($moteur){
		$ordreDep = $this->ordre; 
		//ajoute les classes générées
		foreach($moteur->arrClass as $k=>$c){
			$this->ordre ++;
			//dans le cas d'un changement de position
			if(count($this->arrPosi)>1){
	        	//change l'ordre pour que la class soit placée après
	        	$this->ordre ++;
				$this->arrClass[$this->ordre] = $this->arrClass[($this->ordre-1)];
	        	//redéfini l'ordre pour que la class soit placée avant sans écraser la class précédente
	        	$this->ordre --;
			}
			//gestion du pluriel sur les caractères
	        if(isset($this->arrClass[$ordreDep]["class"][1]) && substr($this->arrClass[$ordreDep]["class"][1],0,5)=="carac"){
	        	//ajoute le pluriel le cas échéant
		        if(isset($this->arrClass[$ordreDep]["vecteur"]["pluriel"])){       	
					$c["vecteur"]["pluriel"]=$this->arrClass[$ordreDep]["vecteur"]["pluriel"];
		        }
	        }
			
			$this->arrClass[$this->ordre] = $c;
		}
		//ajoute les caractères générées
		foreach($moteur->arrCaract as $k=>$c){
			$this->arrCaract[$k] = $c;
		}
		//ajoute les déterminants
		if($this->verif){
			
		}		

	}
	
	
    /**
     * Fonction du moteur
     *
     * @param mixte $cls
     *
     */
	public function getClassType($cls){

		if(isset($cls["id_adj"])){
		    $this->arrClass[$this->ordre]["adjectifs"][] = $cls;
		}
		if(isset($cls["id_sub"])){
		    $this->getSubstantif("",$cls);
		}
		if(isset($cls["id_verbe"])){
		    $this->getVerbe("",$cls);
		}
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $txt
     * @param int $i
     *
     */
	public function getClassVals($txt,$i=0){

		if(!$txt){
	        return "";
		}
		
		$deb = strpos($txt,"[",$i);
		$fin = strpos($txt,"]",$deb+1);
		
		//dans le cas de ado par exemple = pas de crochet
		if($deb===false){
	        $class = $txt;
		}elseif(!$fin){
			$this->arrClass[$this->ordre]["ERREUR"] = "Item mal formaté.<br/>il manque un ] : ".$txt."<br/>";			
        	return array("deb"=>$deb,"fin"=>strlen($txt),"valeur"=>"","arr"=>"");
		}else{
			//on récupère la valeur de la classe
	        $class = substr($txt, $deb+1, -(strlen($txt)-$fin));
		}
		//$this->arrClass[$this->ordre]["class"][] = $class;
        
        
        //on récupère la définition de l'accord 
        $arr = explode("||",$class);
	    $class = $arr[0];        	 	
        if(count($arr)>1){
	        $this->arrClass[$this->ordre]["accord"] = $arr[1];
        }
        
        //on récupère le tableau des class
        $arr = explode("|",$class);        		        
        
        return array("deb"=>$deb,"fin"=>$fin,"valeur"=>$class,"arr"=>$arr);
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     *
     */
	public function getAdjectifs($class){

        $d = strpos($class,"∏");        
        if($d){        	
        	//récupère les adjectifs
	        $arrAdj=explode("∏", $class);        
	        //récupère la définition des adjectifs
	        foreach($arrAdj as $a){
		        $this->arrClass[$this->ordre]["adjectifs"][] = $this->getAleaClass($a);
	        }        	
        }else{
	        $this->arrClass[$this->ordre]["adjectifs"][] = $this->getAleaClass($class);        	
        }               

	    //met à jour l'élision dans le cas d'un changement de position
		if(count($this->arrPosi)>1){
        	$this->arrClass[$this->ordre]["vecteur"]["elision"] = $this->arrClass[$this->ordre]["adjectifs"][0]["elision"];       	
        	//redéfini l'ordre pour que la suite de la génération
        	$this->ordre ++;
        }
        
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     *
     */
	public function getBlocage($class){

        //récupère le numéro du blocage
        $num=substr($class,1);
        
	    if($num=="x"){
        	//on applique le masculin singulier
	        $this->arrClass[$this->ordre]["vecteur"]["blocage"] = true; 
	    	$this->arrClass[$this->ordre]["vecteur"]["pluriel"] = false; 
	        $this->arrClass[$this->ordre]["vecteur"]["genre"] = 1;
        }else{
        	$classType = false;
        	//vérifie si on doit prendre le substantif de l'adjectif
        	//selon jpb la vérification n'est pas nécessaire
        	$classType="substantif";
        	
	        //Récupère les informations de genre
        	$vecteur = $this->getVecteur("genre",-1,$num,$classType);
        	if(!isset($vecteur["genre"])){
        		$genre = 1;
        	}else{
        		$genre = $vecteur["genre"];
        	}
        	if(!isset($vecteur["pluriel"])){
        		//récupère le vecteur de pluriel
		        $vecteurPlus = $this->getVecteur("pluriel",-1,$num,$classType);        		
        		$pluriel = $vecteurPlus["pluriel"];
        	}else{
        		$pluriel = $vecteur["pluriel"];
        	}
        	$this->arrClass[$this->ordre]["vecteur"]["pluriel"] = isset($pluriel) ? $pluriel : false; 
	        $this->arrClass[$this->ordre]["vecteur"]["genre"] = $genre;         	
        }
        
        
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     *
     */
	public function getDeterminant($class){

        $arrClass = false;

        //vérifie si le déterminant est pour un verbe
        if(strlen($class) > 6){
        	$intD = intval($class);
        	if($intD==0 && $this->ordre > 0){
        		//vérifie si le determinant n'est pas transmis
        		$strD = $this->arrClass[$this->ordre]["determinant_verbe"];
        		for($i = $this->ordre-1; $i >= 0; $i--){
        			if(intval($this->arrClass[$i]["determinant_verbe"])!=0){
						$class = $this->arrClass[$i]["determinant_verbe"];
						$i=-1;        				
        			}
        		}
        	}
			$this->arrClass[$this->ordre]["determinant_verbe"] = $class;
	        return $class;
        }       	
        
        //vérifie s'il faut chercher le pluriel
        $pluriel = false;
        if($class >= 50){
        	$pluriel = true;
        	$class = $class-50;
        }       			
        //vérifie s'il faut chercher le déterminant
        if($class!=99 && $class!=0){
        	$c = md5("getDeterminant_".$this->arrDicos["déterminants"]."_".$class."_".$pluriel);
        	if($this->forceCalcul)$this->cache->remove($c);
			if(!$arrClass = $this->cache->load($c)) {
		        $tDtr = new Model_DbTable_Gen_determinants($this->db);
	        	$arrClass = $tDtr->obtenirDeterminantByDicoNumNombre($this->arrDicos["déterminants"],$class,$pluriel);        				
			    $this->cache->save($arrClass, $c);
			}
        }
        
        if($class==0){
        	$class=0;
        	//vérifie si le determinant n'est pas transmis
        	//la transmission se fait par [=x...]
        	/*
        	for($i = $this->ordre-1; $i > 0; $i--){
        		if(isset($this->arrClass[$i]["vecteur"])){
	        		if(intval($this->arrClass[$i]["determinant"])!=0){
						$arrClass = $this->arrClass[$i]["determinant"];
						$pluriel = $this->arrClass[$i]["vecteur"]["pluriel"];
						$i=-1;        				
	        		}
        		}
        	}
			*/
        }

		//ajoute le vecteur
		$this->arrClass[$this->ordre]["vecteur"]["pluriel"] = $pluriel; 
        
        //ajoute le déterminant
		$this->arrClass[$this->ordre]["determinant"] = $arrClass;
                        
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param boolean $arrClass
     *
     */
	public function getSubstantif($class, $arrClass=false){

        //récupération du substantif
        if(!$arrClass) $arrClass = $this->getAleaClass($class);

        if($arrClass){
        	
	        //ajoute le vecteur
	        $this->arrClass[$this->ordre]["vecteur"]["genre"] = $arrClass["genre"];
	        $this->arrClass[$this->ordre]["vecteur"]["elision"] = $arrClass["elision"];
	        
	        //ajoute le substantif
	        $this->arrClass[$this->ordre]["substantif"] = $arrClass;
		        	        
        }        
        return $arrClass;
	}

    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param boolean $direct
     *
     */
	public function getSyntagme($class, $direct=true){
		
		//vérifie si le syntagme direct #
		if($direct){

	        $c = md5("getSyntagme_".$this->arrDicos['syntagmes']."_".$class);
        	if($this->forceCalcul)$this->cache->remove($c);
	        if(!$arrClass = $this->cache->load($c)) {
	        	//récupère la définition de la class
	        	$table = new Model_DbTable_Gen_syntagmes($this->db);
	        	$arrClass = $table->obtenirSyntagmeByDicoNum($this->arrDicos['syntagmes'],$class);
				if(is_object($arrClass) && get_class($arrClass)=="Exception"){
		    		$this->arrClass[$this->ordre]["ERREUR"] = $arrClass->getMessage();//."<br/><pre>".$arrCpt->getTraceAsString()."</pre>";
		    		$arrClass = array("lib" => "");
				}else{	   	    
			   	    $this->cache->save($arrClass,$c);
				}
	        	
				$this->cache->save($arrClass,$c);
			}
	        	        
	        $syn = $arrClass["lib"];
	        if(substr($syn,0,1)== "["){
	        	$this->traiteClass($syn);
	        }else{
		        $this->arrClass[$this->ordre]["syntagme"] = $arrClass;
	        }
		}else{
	        $arrClass = $this->getAleaClass($class);
	        if(isset($arrClass["lib"])){
	        	$this->arrClass[$this->ordre]["syntagme"] = $arrClass;			
	        }
		}
		return $arrClass;
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param int $i
     *
     */
	public function traiteClass($class, $i=0){

		$arrClass = $this->getClassVals($class,$i);	        	
		foreach($arrClass["arr"] as $cls){
			if($this->verif){
				$this->xmlGen = $this->xml->createElement('gen',$cls);
				$this->xmlRoot->appendChild($this->xmlGen);		
			}		
			$this->getClass($cls);
        }	        	
		return $arrClass["fin"];
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param int $i
     *
     */
	public function traiteFormat($class, $i=0){

		$deb = $i+1;
		$fin = strpos($class,"$",$deb);

		//on récupère les valeurs de format
        $txt = substr($class, $deb, -(strlen($class)-$fin));

        //enregistre le numéro du segment
        $this->arrSegment[$this->segment]["num"] = $txt[0].$txt[1];
        //enregistre l'indice de répétition
        $this->arrSegment[$this->segment]["repetition"] = $txt[8];
        //enregistre l'ordre de départ
        $this->arrSegment[$this->segment]["ordreDeb"]= $this->ordre;
        
        //vérifie si le texte est en prose ou en poésie
        $forme = $txt[9].$txt[10];
        if($forme=="00"){
			$this->arrClass[$this->ordre]["format"]["page"] = "prose";        	
        }
        if($forme=="01"){
			$this->arrClass[$this->ordre]["format"]["page"] = "poésie en vers-régulier";        	
        }
        if($forme=="02"){
			$this->arrClass[$this->ordre]["format"]["page"] = "poésie en vers-défini";        	
        }
        if($forme=="XX"){
			$this->arrClass[$this->ordre]["format"]["page"] = "poésie en vers-libre";        	
        }
        if($forme=="03"){
			$this->arrClass[$this->ordre]["format"]["page"] = "poésie en vers définis centrés";        	
        }
        
        return $fin;
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     *
     */
	public function getNegation($class){

        $c = md5("getNegation_".$this->arrDicos['negations']."_".$class);
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$arrClass = $this->cache->load($c)) {
        	//récupère la définition de la class
        	$table = new Model_DbTable_Gen_negations($this->db);
        	$arrClass = $table->obtenirNegationByDicoNum($this->arrDicos['negations'],$class);
			$this->cache->save($arrClass,$c);
		}
        
        return $arrClass["lib"];
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param string $type
     *
     */
	public function getPronom($class, $type){


        $c = md5("getPronom_".$this->arrDicos["pronoms"]."_".$class."_".$type);
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$arrClass = $this->cache->load($c)) {
			//récupère la définition de la class
       		$table = new Model_DbTable_Gen_pronoms($this->db);
        	$arrClass = $table->obtenirPronomByDicoNumType($this->arrDicos['pronoms'],$class,$type);
        	if(is_array($arrClass))	$this->cache->save($arrClass,$c);
		}
		if(is_object($arrClass) && get_class($arrClass)=="Exception"){
	        $this->arrClass[$this->ordre]["ERREUR"] = $arrClass->getMessage();//."<br/><pre>".$arrClass->getTraceAsString()."</pre>";
			return "";
		}
		
		return $arrClass;										

	}

    /**
     * Fonction du moteur
     *
     * @param int $idConj
     * @param int $num
     *
     */
	public function getTerminaison($idConj, $num){


        $c = md5("getTerminaison_".$idConj."_".$num);
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$arrClass = $this->cache->load($c)) {
			//récupère la définition de la class
        	$table = new Model_DbTable_Gen_terminaisons($this->db);
        	$arrClass = $table->obtenirConjugaisonByConjNum($idConj, $num);
			$this->cache->save($arrClass,$c);
		}
		
		if(is_object($arrClass) && get_class($arrClass)=="Exception"){
	        $this->arrClass[$this->ordre]["ERREUR"] = $arrClass->getMessage();//."<br/><pre>".$arrClass->getTraceAsString()."</pre>";
			return "";
		}
		return $arrClass["lib"];
												
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     * @param boolean $arrClass
     *
     */
	public function getVerbe($class, $arrClass=false){

        //récupération du verbe
        if(!$arrClass) $arrClass = $this->getAleaClass($class);
        
		if($arrClass){
	        //ajoute le verbe
	        $this->arrClass[$this->ordre]["verbe"] = $arrClass;
	                
	        $this->arrClass[$this->ordre]["elision"] = $arrClass["elision"];
		}
		        
        return $arrClass;
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $class
     *
     */
	public function getAleaClass($class){
		
        //cherche la définition de la class
        $arrCpt = $this->getClassDef($class);
        
        //cas des class caract
        if(substr($class,0,7)=="carac_t" || substr($class,0,5)=="carac"){
        	//on vérifie que le caractère n'est pas déjà calculé
        	if(isset($this->arrCaract[$class])){
        		//on retourne le carac déjà choisi
        		return $this->getClassSpe($this->arrCaract[$class]);
        	}else{
        		//on récupère les possibilité de caracX
        		$classCarac = str_replace("carac_t","carac_",$class);
		        $arrCptCarac = $this->getClassDef($classCarac);
        		//on ajoute à la liste de choix les possibilité de caractX
		        foreach($arrCptCarac["dst"] as $cptCarac){
					$arrCpt["dst"][] = $cptCarac;        	
		        }
        	}
        	
        }
        
        //cas des classes théoriques et des erreurs
        if(count($arrCpt["dst"])<1){
        	return false;
        }
		$this->arrClass[$this->ordre]["concept"]["idConcept"] = $arrCpt["src"]["id_concept"];
		$this->arrClass[$this->ordre]["concept"]["idDico"] = $arrCpt["src"]["id_dico"];
		
        //enregistre le potentiel
        $this->potentiel += count($arrCpt["dst"]);
        
        if($this->typeChoix=="tout" && $this->niv < 1 && count($arrCpt["dst"]) > 1){
        	$this->verifClass($arrCpt);
			$cpt = false;
        }else{
	        //choisi un concept aléatoirement
	        //initialise le random
			mt_srand($this->make_seed());
        	
	        $a = mt_rand(0, count($arrCpt["dst"])-1);        
	        
	        $cpt = $this->getClassGen($arrCpt["dst"][$a],$class);        
		}

        if($cpt){
        	//conserve le parent pour le lien vers l'administration
        	$cpt["idParent"] = $arrCpt["src"]["id_concept"];
	        //vérifie s'il faut conserver un caractx
	        if(substr($class,0,7)=="carac_t"){
	        	$this->arrCaract[$class] = $arrCpt["dst"][$a];
	        }
        }
        
        //vérifie s'il faut transférer le déterminant de verbe
        if($arrCpt["src"]["type"]=="v" && isset($this->arrClass[$this->ordre-1]["determinant_verbe"]) && !isset($this->arrClass[$this->ordre-1]["verbe"])){       	
        	$this->arrClass[$this->ordre]["determinant_verbe"]=$this->arrClass[$this->ordre-1]["determinant_verbe"];
        }
        //vérifie s'il faut transférer le déterminant dus substantif
        if(($arrCpt["src"]["type"]=="m" || $arrCpt["src"]["type"]=="carac") && isset($this->arrClass[$this->ordre-1]["vecteur"]["pluriel"])){       	
				$this->arrClass[$this->ordre]["vecteur"]["pluriel"]=$this->arrClass[$this->ordre-1]["vecteur"]["pluriel"];
        }
        
		return $cpt; 			
		
	}

    /**
     * Fonction du moteur
     *
     *
     */
	function verifClass($arrCpt)
	{
        //pour la vérification
        $this->arrClass[$this->ordre]["texte"] = "%";        	
        $this->ordre++;
        $j=1;
        foreach($arrCpt["dst"] as $dst){
        	//ajoute le texte de la génération
        	$id = $j;
        	if($dst["id_gen"])$id = $dst["id_gen"];
        	if($dst["id_adj"])$id = $dst["id_adj"];
        	if($dst["id_verbe"])$id = $dst["id_verbe"];
        	if($dst["id_sub"])$id = $dst["id_sub"];
        	$this->arrClass[$this->ordre]["texte"] = $id." : ";
	        $this->ordre++;
	        //calcul la vérification
        	$cpt = $this->getClassGen($dst, $class);
	        if($cpt){
	        	$this->getClassType($dst);
	        }
	        $this->verifTransfert($arrCpt, $dst); 	                	
        	$j++;
        	$this->ordre++;
        	$this->arrClass[$this->ordre]["texte"] = "%";        	
        	$this->ordre++;
        }
		
	}
	
    /**
     * Fonction du moteur
     *
     *
     */
	function verifTransfert($arrCpt, $dst){
        //vérifie s'il faut transférer le déterminant de verbe
        if(($arrCpt["src"]["type"]=="v" || isset($dst["id_verbe"])))
        {
	        for($i = $this->ordre-1; $i >= 0; $i--){
        		if(intval($this->arrClass[$i]["determinant_verbe"])!=0){
					$class = $this->arrClass[$i]["determinant_verbe"];
					$i=-1;        				
        		}
        	}
        	$this->arrClass[$this->ordre]["determinant_verbe"]=$class;
        }
        //vérifie s'il faut transférer le vecteur du substantif
        if(($arrCpt["src"]["type"]=="m" || isset($dst["id_sub"])))
        {
	        for($i = $this->ordre-1; $i >= 0; $i--){
        		$c = $this->arrClass[$i];
	        	if(isset($c["vecteur"])){
        			foreach ($c["vecteur"] as $k => $v) {
			        	$this->arrClass[$this->ordre]["vecteur"][$k]=$v;
        			}
        		}
        		if(isset($c["determinant"])){
		        	$this->arrClass[$this->ordre]["determinant"]=$c["determinant"];		        			
        		}
	        }
        }
		
	}
	
	
    /**
     * Fonction du moteur
     *
     *
     */
	function make_seed()
	{
	  list($usec, $sec) = explode(' ', microtime());
	  return (float) $sec + ((float) $usec * 100000);
	}
	
    /**
     * Fonction du moteur
     *
     * @param string $cpt
     * @param string $class
     *
     */
	public function getClassGen($cpt,$class){
		
        //Vérifie si le concept est un générateur
        if(isset($cpt["id_gen"])){
        	//générer l'expression
			$m = new Gen_Moteur($this->idBase,$this->urlDesc,$this->forceCalcul,$this->xmlDesc);
			$m->showErr = $this->showErr;
			$m->timeDeb = $this->timeDeb;
			$m->niv = $this->niv+1;
			$m->arrDicos = $this->arrDicos;
			$m->arrCaract = $this->arrCaract;
						
			//génére la classe
			$m->Generation($cpt['valeur'],false,$this->cache);				
			
			if($this->verif){
				//vérifie si un déterminant est définie
				if(isset($this->arrClass[0]["determinant"])){
					$m->arrClass[(count($m->arrClass)-1)]["determinant"]=$this->arrClass[0]["determinant"];
					foreach ($this->arrClass[0]["vecteur"] as $k => $v) {
			        	$m->arrClass[(count($m->arrClass)-1)]["vecteur"][$k]=$v;
        			}
				}
				if(isset($this->arrClass[0]["determinant_verbe"]))$m->arrClass[(count($m->arrClass)-1)]["determinant_verbe"]=$this->arrClass[0]["determinant_verbe"];
				//genere le texte
				$m->genereTexte();
				$this->arrClass[$this->ordre]["texte"] = $m->texte;
				return false;					
			}
			
			/*vérifie que la classe n'a pas déjà été générée
			if($this->in_multiarray($cpt['valeur'],$this->arrClass)){
				$this->arrDoublons[] = array('niv'=>$this->niv,'ordre'=>$this->ordre,'valeur'=>$cpt['valeur'],'moteur'=>$m);		
			}
			*/
			
	        //vérifie s'il faut conserver un caractx
	        if(substr($class,0,7)=="carac_t"){
	        	$this->arrCaract[$class] = $m;
	        }
			
			
			//récupère les classes générées
			$this->getClassMoteur($m);
			$this->potentiel += $m->potentiel;
			$cpt = false;
		}
		
		return $cpt; 			
		
	}

    /**
     * Fonction du moteur
     *
     * @param mixte $elem
     * @param mixte $array
     *
     */
	function in_multiarray($elem, $array)
    {
        // if the $array is an array or is an object
         if( is_array( $array ) || is_object( $array ) )
         {
             // if $elem is in $array object
             if( is_object( $array ) )
             {
                 $temp_array = get_object_vars( $array );
                 if( in_array( $elem, $temp_array ) )
                     return TRUE;
             }
            
             // if $elem is in $array return true
             if( is_array( $array ) && in_array( $elem, $array ) )
                 return TRUE;
                
            
             // if $elem isn't in $array, then check foreach element
             foreach( $array as $array_element )
             {
                 // if $array_element is an array or is an object call the in_multiarray function to this element
                 // if in_multiarray returns TRUE, than return is in array, else check next element
                 if( ( is_array( $array_element ) || is_object( $array_element ) ) && $this->in_multiarray( $elem, $array_element ) )
                 {
                     return TRUE;
                     exit;
                 }
             }
         }
        
         // if isn't in array return FALSE
         return FALSE;
    } 	

    /**
     * Fonction du moteur pour transformer la structure hiérarchique d'un générateur en réseau
	 * 
	{
		"nodes": [
			{
			"id": "Myriel",
			"group": 1
			},
			{
			"id": "Napoleon",
			"group": 1
			},
		],
		"links": [
			{
			"source": "Napoleon",
			"target": "Myriel",
			"value": 1
			},
			{
			"source": "Mlle.Baptistine",
			"target": "Myriel",
			"value": 8
			},
		]
	}
     *
     * @param array $s
     * @param array $nivMax
 
	 * @return array
     */
    public function getReseauStructure($s,$nivMax=-1){

		if(!$this->arrReseau){
			$this->arrReseau = array("nodes"=>array(),"links"=>array());
		}
		if($s["id"]=='4_451_syn'){
			$t = 1;
		}
		//conserve les enfants pour le traitement récursif
		$child = $s['children'];
		//supprime les enfants pour les infos du réseau
		unset($s['children']);
		//vérifie la création de doublons
		if($this->arrId[$s["id"]]){
			//incrémentation de la taille
			$this->arrId[$s["id"]]['nb'] ++;
			$i = $this->arrId[$s["id"]]['i'];
			$this->arrReseau["nodes"][$i]['size']=$this->arrId[$s["id"]]['nb'];
		}else{
			//création du noeud
			$this->arrReseau["nodes"][] = array("id"=>$s["id"],"group"=>$s["type"],"obj"=>$s,"size"=>1);
			$i = $this->arrReseau["nodes"] ? count($this->arrReseau["nodes"])-1 : 0;
			$this->arrId[$s["id"]] = array('nb'=>1,'i'=>$i);
		}

		foreach ($child as $c) {
			if($nivMax<0 || $nivMax >= $c['niv']){
				$this->getReseauStructure($c,$nivMax);
				$idLink = $s["id"].'-'.$c["id"];
				//vérifie la création de doublons
				if($this->arrId[$idLink]){
					//incrémentation de la taille
					$i = $this->arrId[$idLink]['i'];
					$this->arrId[$idLink]['nb'] ++;
					$this->arrReseau["links"][$i]["value"]=$this->arrId[$idLink]['nb'];
				}else{
					//création du lien
					$this->arrReseau["links"][] = array("source"=>$s["id"],"target"=>$c["id"],"value"=>1);	
					$i = $this->arrReseau["links"] ? count($this->arrReseau["links"])-1 : 0;	
					$this->arrId[$idLink] = array('nb'=>1,'i'=>$i);
				}
			}
		}

		return $this->arrReseau;
	}
	

    /**
     * Fonction du moteur
     *
     * @param string 	$class
     * @param int	 	$niv
     * @param string	$id
     * @param string	$nivMax

	 * @return array
     */
    public function parseGen($texte){

		$re = '/\[[^\]]*\]/';
		
		preg_match_all($re, $texte, $stc, PREG_SET_ORDER);

		$iDeb = 0;
		$dataGen = array();
		for ($i=0; $i < count($stc); $i++) { 
			//récupère les positions du générateur
			$posi1 = strpos($texte, $stc[$i][0], $iDeb);
			$posi1fin = $posi1+strlen($stc[$i][0]);
			$posi2 = $posi1+1;
			if($i==0 && $posi1 > 0){
				//ajout le texte avant le premier générateur
				$t = substr($texte, 0, $posi1);
				$dataGen[] = array('v'=>$t,'t'=>'txt');
			}
			//ajoute le générateur
			$dataGen[]=array('v'=>$stc[$i][0],'t'=>'gen');
			//vérifie la présence de texte
			if($stc[($i+1)])
				$posi2 = strpos($texte, $stc[$i+1][0], $iDeb+1);                    
			if($posi2 > $posi1fin){
				//ajoute les textes intermédiaires
				$t = substr($texte, $posi1fin, $posi2-$posi1fin);
				$dataGen[] = array('v'=>$t,'t'=>'txt');
			}
			$iDeb = $posi1fin;                
		}
		//ajout le texte après le dernier générateur
		if($iDeb < count($texte)){
			$t = substr($texte, $iDeb);
			$dataGen[] = array('v'=>$t,'t'=>'txt');
		}
		return $dataGen;
	}
	

    /**
     * Fonction du moteur
     *
     * @param string 	$class
     * @param int	 	$niv
     * @param string	$id
     * @param string	$nivMax

	 * @return array
     */
    public function getStructure($texte, $niv=0, $id='root',$nivMax=-1){

		$this->setCache();
		$keyCache = md5("getStructure".$texte.$nivMax);
		if($this->forceCalcul)$this->cache->remove($keyCache);
		if(!$structure = $this->cache->load($keyCache)) {
			//if($niv>3)return [];

			$structure = array("size"=>($niv+1),"id"=>$id,"niv"=>$niv,"type"=>'texte',"name"=>$texte,"children"=>array());

			//récupère la structure du texte
			$gens = $this->parseGen($texte);
			$iTxt = 0;
			foreach ($gens as $g) {
				$iTxt ++;					
				if($g['t']=='txt'){
					//traitement des textes libres
					$gen = array(
						'id'=>'txt_'.$iTxt,
						'niv'=>$niv,
						'size'=>1,'name'=>$g['v'],'type'=>$g['t'],'children'=>array()
					);
					$structure['children'][]=$gen;
				}else{
					//vérifie s'il faut créer une sous class
					if($texte!=$g['v'])
						$structure['children'][]=$this->getStructure($g['v'], $niv+1, $id='txt_'.$iTxt,$nivMax);
					else{
						//traitement des générateurs
						$this->Generation($g['v'],false);
						foreach($this->arrClass as $cls){
							//on ne traite que les niveaux 0
							if($cls['niveau']===0){
								foreach ($cls['class'] as $class) {
									if(!is_numeric($class)){
										//récupération de la class
										if(strpos($class,"#")){
											//la class est un déterminant
											$classSpe = str_replace("#","",$class); 
											$arrClass = $this->getSyntagme($classSpe);	
										}elseif(strpos($class,"_")){
											//la class possède un déterminant de class
											$arrClass = $this->getClassDef($class);
										}elseif(substr($class,0,5)=="carac"){
											//la class est un caractère
											$classSpe = str_replace("carac", "carac_", $class);
											$arrClass = $this->getClassDef($classSpe);
										}elseif($c = strpos($class,"-")){
											//la class est un type spécifique
											$classSpe = substr($class,0,$c)."_".substr($class,$c+1);
											$arrClass = $this->getClassDef($classSpe);
										} 
										//construction de l'item       	
										if($arrClass['src']['id_gen'])
											$gen = array(
												'id'=>$arrClass['src']['id_dico'].'_'.$arrClass['src']['id_gen'].'_gen',
												'niv'=>$niv,
												'id_dico'=>$arrClass['src']['id_dico'],'id_gen'=>$arrClass['src']['id_gen']
												,'size'=>1,'name'=>$arrClass['src']['valeur'],'children'=>array(),'type'=>'gen'
											);
										elseif($arrClass['id_syn'])
											$gen = array(
												'id'=>$arrClass['id_dico'].'_'.$arrClass['id_syn'].'_syn',
												'niv'=>$niv,
												'id_dico'=>$arrClass['id_dico'],'id_syn'=>$arrClass['id_syn']
												,'size'=>1,'name'=>$arrClass['lib'],'num'=>$arrClass['num'],'ordre'=>$arrClass['ordre']
												,'children'=>array(),'type'=>'syn'
											);
										else
											$gen = array(
												'id'=>$arrClass['src']['id_dico'].'_'.$arrClass['src']['id_concept'].'_'.$arrClass['src']['type'],
												'niv'=>$niv,
												'id_dico'=>$arrClass['src']['id_dico'],'id_concept'=>$arrClass['src']['id_concept']
												,'size'=>1,'name'=>$arrClass['src']['lib'],'type'=>$arrClass['src']['type'],'children'=>array()
											);
										//récupération des enfants
										foreach ($arrClass['dst'] as $dst) {
											if($dst['valeur']){
												$id = $dst['id_dico'].'_'.$dst['id_gen'].'_gen';
												if($nivMax<0 || $nivMax >= $niv){
													$genStruct = $this->getStructure($dst['valeur'],$niv+1,$id,$nivMax);
													$gen['children'][] = $genStruct;
												}else $gen['children']=[]; 
											}else{
												$dst['name']=$dst['prefix'];
												$dst['niv']=$niv;
												$dst['id']=$this->getIdClass($dst);									
												$dst['size']=1;
												$gen['children'][]=$dst;
											}
										}
									}elseif($cls['determinant']){
										$gen = array(
											'id'=>$cls['determinant'][0]['id_dico'].'_'.$class.'_dtm',
											'niv'=>$niv,
											'id_dico'=>$cls['determinant'][0]['id_dico']
											,'size'=>1,'name'=>$class,'type'=>'det','children'=>array()
										);
										foreach ($cls['determinant'] as $d) {
											$gen['children'][] = array(
												'id'=>$d['id_dico'].'_'.$d['id_dtm'].'_dtm',
												'niv'=>$niv,
												'id_dico'=>$d['id_dico'],'id_dtm'=>$d['id_dtm']
												,'size'=>1,'name'=>$d['lib'],'type'=>'det','ordre'=>$d['ordre']
												,'children'=>array()
											);
										}
									}elseif($cls['determinant_verbe']){
										$arr = $this->generePronom($cls);
										$gen = array(
											'id'=>$cls['concept'][0]['id_dico'].'_'.$class.'_detverb',
											'niv'=>$niv,
											'id_dico'=>$cls['concept']['idDico']
											,'size'=>1,'name'=>$class,'type'=>'detverb','children'=>array()
										);
										if($arr['prosuj']){
											$gen['children'][] = array(
												'id'=>$arr['prosuj']['id_dico'].'_'.$arr['prosuj']['id_pronom'].'_'.$arr['prosuj']['type'],
												'niv'=>$niv,
												'id_dico'=>$arr['prosuj']['id_dico'],'id_pronom'=>$arr['prosuj']['id_pronom']
												,'size'=>1,'name'=>$arr['prosuj']['lib'],'type'=>$arr['prosuj']['type'],'num'=>$arr['prosuj']['num']
												,'children'=>array()
											);
										}
										if($arr['prodem']){
											$gen['children'][] = array(
												'id'=>$arr['prodem']['id_dico'].'_'.$arr['prodem']['id_pronom'].'_'.$arr['prodem']['type'],
												'niv'=>$niv,
												'id_dico'=>$arr['prodem']['id_dico'],'id_pronom'=>$arr['prodem']['id_pronom']
												,'size'=>1,'name'=>$arr['prodem']['lib'],'type'=>$arr['prodem']['type'],'num'=>$arr['prodem']['num']
												,'children'=>array()
											);
										}
									}
									if($gen['id_dico']){
										$structure['children'][]=$gen;
									}
								}
							}
						}						
					} 					
				}
			}
			$this->cache->save($structure, $keyCache);
		}

		return $structure;

	}

	/**
     * Fonction du moteur pour récupérer l'identifiant d'une class
     *
     * @param mixte $class
     *
	 * @return string
     */
    public function getIdClass($class){
		$id = $class['id_dico'].'_';
		if($class['id_adj'])$id.=$class['id_adj'].'_adj';
		if($class['id_sub'])$id.=$class['id_sub'].'_sub';
		if($class['id_verbe'])$id.=$class['id_verb'].'_verbe';
		if($class['id_syn'])$id.=$class['id_syn'].'_syn';
		if($id == $class['id_dico'].'_')
			$l=1;
		
		return $id;

	}


	/**
     * Fonction du moteur
     *
     * @param mixte $class
     *
     */
    public function getClassDef($class){


        $c = md5("getClassDef_".$this->arrDicos['concepts']."_".$class);
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$arrCpt = $this->cache->load($c)) {
			//récupère la définition de la class
			$arrClass=explode("_", $class);
	        $table = new Model_DbTable_Gen_concepts($this->db);	
	   	    $arrCpt = $table->obtenirConceptDescription($this->arrDicos['concepts'],$arrClass);
			if(is_object($arrCpt) && get_class($arrCpt)=="Exception"){
	    		$this->arrClass[$this->ordre]["ERREUR"] = $arrCpt->getMessage();//."<br/><pre>".$arrCpt->getTraceAsString()."</pre>";
	    		$arrCpt = false;
			}else{	   	    
		   	    $this->cache->save($arrCpt,$c);
			}
		}
				
        return $arrCpt;
	}

	
	
    /**
     * Fonction du moteur
     *
     * @param string $chaine
     * @param string $action
     *
     */
	public function traiteAction($chaine, $action){

		//applique l'action défini dans la description du langage
		switch ($action['type']) {
			case 'explode':
				//pour améliorer le explode des saut de ligne
				$c = str_replace("\\r","- -",$action['char']);
				//met la chaine dans un tableau
				$arr = explode($c, $chaine);
				//boucle sur les fragments de chaine optenus
				for ($i = 0 ; $i < count($arr); $i++) {
					//foreach ($arr as $frag) {
					$frag = $arr[$i];
					//vérifie si le traitement comporte des sous actions
					if(count($action->children())==0){
						$this->AjoutFrag($frag, $action);
					}else{
						//boucle sur les sous actions de l'action
						foreach ($action->children() as $act) {
							//vérifie si l'action est liée à la fin du tableau
							switch ($act['type']) {
								case "VerifFin":
									if($i==(count($arr)-1)){
										$this->traiteAction($frag, $act->action);					
									}else{
										$this->traiteAction($frag, $act->NoVerifAction);					
									}
									break;								
								case "VerifDeb":
									if($i==0){
										$this->traiteAction($frag, $act->action);					
									}else{
										$this->traiteAction($frag, $act->NoVerifAction);					
									}
									break;								
								default:
									$this->traiteAction($frag, $act);								
									break;
							}							
						}
					}
				}
				break;				
			case 'VerifSubstr':
				$c = substr($chaine, $action['deb'],$action['length']);
				if($c==$action['val']){
					//on exécute l'action suivante
					$this->traiteAction($chaine, $action->action);					
				}else{
					$this->traiteAction($chaine, $action->NoVerifAction);					
				}
				break;
		}

	}	


	
    /**
     * Mise en forme d'un tableau d'erreur en liste HTML
     *
     * @param array $var
     *
     * @return string
     */
	function arrayVersListHTML($var){
	  $out = '<li>';
	  foreach($var as $k=>$v){
	    if(is_array($v)){
	      $out .= '<ul>'.$this->arrayVersListHTML($v).'</ul>';
	    }else{
	      $out .= " ".$k." ".$v;
	    }
	  }
	  return $out.'</li>';
	}

    /**
     * Fonction du moteur
     *
     * @param string $tab
     * @param string $col1
     * @param string $col2
     * @param int $bordure
     *
     */
	function arrayVersHTML($tab, $col1 = "Cl&eacute;", $col2 = "Valeur", $bordure = 1)
	{
 		//http://www.phpsources.org/scripts471-PHP.htm
 		//modifier pour prendre en compte récursivement les tableaux   
 	    /* le chiffre doit être positif */
	    $bordure = (int) $bordure;
	    if ($bordure < 1) $bordure = 1;
	
	    /* le style CSS 
	    (rappel : il est préférable d'utiliser une feuiille externe plutôt que des styles internes aux balises) */
	    $style = "border: {$bordure}px solid black;font-style: bold;color:black;"; // les accolades permettent de coller la valeur numérique à "px"
		$styleErr = "border: {$bordure}px solid black;font-style: bold;color:red;";
		$styleTxt = "border: {$bordure}px solid black;font-style: bold;color:green;";
		
		$url = WEB_ROOT."/public/index.php/index/modifier/type/";
		
		/* génération de la première ligne, avec les libellés balisés comme cellules d'entête */
	    /* explications sur le scope="col" : l'accessibilité, lire l'article http://www.pompage.net/pompe/autableau/ */
	    $aafficher = "<table style='border-collapse: collapse; $style'>\n<tr>
	    <th scope='col' style='$style'>$col1</th> 
	    <th scope='col' style='$style'>$col2</th>\n</tr>\n";
	    
	    /* génération de chaque ligne ; col de gauche : la clé, celle de droite : la valeur correspondante
	    à chaque tour de boucle on ajoute le string généré à la suite du précédent (opérateur .=) */
	    foreach($tab as $cle => $valeur)
	    {
	        $aafficher .= "<tr style='$style'>
			    <td style='$style'>$cle</td>";
			//cas particulier des tableaux d'adjectifs
			if($cle==="adjectifs"){
				foreach($valeur as $adj){
		    		$admin = $url."adjectif/id/".$adj["id_adj"]."/idParent/".$adj["idParent"];
		    		$valeur = "";//$this->arrayVersHTML($adj);
			    	$aafficher .= "<td style='$styleTxt'><a href='".$admin."'>admin</a> $valeur</td>\n</tr>\n";
				}
			}else{
			    if(is_array($valeur)){
			    	$valeur = $this->arrayVersHTML($valeur);
			    }
			    switch ($cle) {
			    	case "ERREUR":
				    	$aafficher .= "<td style='".$styleErr."'>$valeur</td>\n</tr>\n";
				    	break;
			    	case "texte":
				    	$aafficher .= "<td style='$styleTxt'>$valeur</td>\n</tr>\n";
				    	break;
				    case "concept":
				    	$valeur = "";
			    		$admin = $url."concept/id/".$tab["concept"]["idConcept"]."/idParent/".$tab["concept"]["idDico"];
				    	$aafficher .= "<td style='$styleTxt'><a href='".$admin."'>admin</a> $valeur</td>\n</tr>\n";
				    	break;
				    case "verbe":
				    	$valeur = "";
				    	$admin = $url."verbe/id/".$tab["verbe"]["id_verbe"]."/idParent/".$tab["verbe"]["idParent"];
				    	$aafficher .= "<td style='$styleTxt'><a href='".$admin."'>admin</a> $valeur</td>\n</tr>\n";
				    	break;
				    case "substantif":
			    		$valeur = "";
				    	$admin = $url."substantif/id/".$tab["substantif"]["id_sub"]."/idParent/".$tab["substantif"]["idParent"];
				    	$aafficher .= "<td style='$styleTxt'><a href='".$admin."'>admin</a> $valeur</td>\n</tr>\n";
				    	break;
				    case "syntagme":
			    		$valeur = "";
				    	$admin = $url."DicoSyntagme/id/".$tab["syntagme"]["id_syn"]."/idParent/".$tab["syntagme"]["id_dico"];
				    	$aafficher .= "<td style='$styleTxt'><a href='".$admin."'>admin</a> $valeur</td>\n</tr>\n";
				    	break;
				    case "determinant":
			    		$valeur = "";
			    		$aafficher .= "<td style='$style'>$valeur</td>\n</tr>\n";
				    	break;
				    default:
			    		$aafficher .= "<td style='$style'>$valeur</td>\n</tr>\n";
			    	break;
			    }
			}
	    }
	    
	    /* on ferme le tableau HTML (nécessaire pour la validité) */
	    $aafficher .= "</table>\n";
	    
	    return $aafficher;
	}
	
	
}
?>