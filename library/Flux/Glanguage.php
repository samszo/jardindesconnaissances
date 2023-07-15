<?php
/**
 * Flux_Glanguage
 * Class qui gère les flux de l'API google natural language
 * cf. https://cloud.google.com/natural-language/docs/
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

//Imports the Google Cloud client library
use Google\Cloud\Language\LanguageClient;

class Flux_Glanguage extends Flux_Site{

	var $client;
	var $language;
	var $config;
	
	
    /**
     * Constructeur de la classe
     *
     * @param  string 	$idBase
     * @param  boolean 	$bTrace
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	

    		//TODO:implémenter google cloud plateform
    		/*ATTENTION on utilise l'API sans le client
    		$this->client = new Google_Client();
		$this->client->setClientId(KEY_GOOGLE_CLIENT_ID);
		$this->client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);
		*/

    		//on récupère la racine des documents
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
    		
    		$this->idTagSent = $this->dbT->ajouter(array('code'=>'sentiment','parent'=>$this->idTagRoot));
    		$this->idTagSyntaxe = $this->dbT->ajouter(array('code'=>'syntaxe','parent'=>$this->idTagRoot));
    		
    		$this->config = [
    		    'projectId' => GOOGLE_PROJECT_ID,
    		    'keyFile'=>json_decode(file_get_contents(GOOGLE_ACCOUNT_FILE), true)
    		];
    		
    		
    		// Create the Natural Language client
    		$this->language = new LanguageClient($this->config);
    		
    }

    
    /**
     * Find the sentiment in text.
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function analyzeSentiment($text)
    {
        $this->trace(__METHOD__." ".$text);
                        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $sentiment = $this->cache->load($uMd5);
        if(!$sentiment){
            
            // Call the analyzeSentiment function
            $annotation = $this->language->analyzeSentiment($text);
            
            //get document and sentence sentiment information
            $sentiment = $annotation->sentiment();
            if($this->bTrace){
                printf('Document Sentiment:' . PHP_EOL);
                printf('  Magnitude: %s' . PHP_EOL, $sentiment['magnitude']);
                printf('  Score: %s' . PHP_EOL, $sentiment['score']);
                printf(PHP_EOL);
            }
            $sentiment['sentences']=$annotation->sentences();
            if($this->bTrace){
                foreach ($annotation->sentences() as $sentence) {
                    printf('Sentence: %s' . PHP_EOL, $sentence['text']['content']);
                    printf('Sentence Sentiment:' . PHP_EOL);
                    printf('  Magnitude: %s' . PHP_EOL, $sentence['sentiment']['magnitude']);
                    printf('  Score: %s' . PHP_EOL, $sentence['sentiment']['score']);
                    printf(PHP_EOL);
                }
            }
            $this->cache->save($sentiment, $uMd5);
        }
        $this->trace("reponse de google = ",$sentiment);
        return $sentiment;        
        
    }
    
    /**
     * Find the entities in text.
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function analyzeEntities($text)
    {
        $this->trace(__METHOD__." ".$text);
        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $entities = $this->cache->load($uMd5);
        if(!$entities){
            
            // Call the analyzeSentiment function
            $annotation = $this->language->analyzeEntities($text);
                        
            // Print out information about each entity
            $entities = $annotation->entities();
            if($this->bTrace){
                foreach ($entities as $entity) {
                    printf('Name: %s' . PHP_EOL, $entity['name']);
                    printf('Type: %s' . PHP_EOL, $entity['type']);
                    printf('Salience: %s' . PHP_EOL, $entity['salience']);
                    if (array_key_exists('wikipedia_url', $entity['metadata'])) {
                        printf('Wikipedia URL: %s' . PHP_EOL, $entity['metadata']['wikipedia_url']);
                    }
                    if (array_key_exists('mid', $entity['metadata'])) {
                        printf('Knowledge Graph MID: %s' . PHP_EOL, $entity['metadata']['mid']);
                    }
                    printf(PHP_EOL);
                }
            }
            $this->cache->save($entities, $uMd5);
        }
        $this->trace("reponse de google = ",$entities);
        return $entities;
        
    }
    
    /**
     * Find the syntax in text.
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function analyzeSyntax($text)
    {
        $this->trace(__METHOD__." ".$text);
        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $tokens = $this->cache->load($uMd5);
        if(!$tokens){
            
            // Call the analyzeSentiment function
            $annotation = $this->language->analyzeSyntax($text);
                        
            // Print syntax information. See https://cloud.google.com/natural-language/docs/reference/rest/v1/Token
            // to learn about more information you can extract from Token objects.
            $tokens = $annotation->tokens();
            if($this->bTrace){
                foreach ($tokens as $token) {
                    printf('Token text: %s' . PHP_EOL, $token['text']['content']);
                    printf('Token part of speech: %s' . PHP_EOL, $token['partOfSpeech']['tag']);
                    printf(PHP_EOL);
                }
            }
            $this->cache->save($tokens, $uMd5);
        }
        $this->trace("reponse de google = ",$tokens);
        return $tokens;
        
    }
    
    /**
     * Find the EntitySentiment in text.
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function analyzeEntitySentiment($text)
    {
        $this->trace(__METHOD__." ".$text);
        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $entities = $this->cache->load($uMd5);
        if(!$entities){
            
            // Call the analyzeEntitySentiment function
            $response = $this->language->analyzeEntitySentiment($text);
            $info = $response->info();
            $entities = $info['entities'];
            if($this->bTrace){
                // Print out information about each entity
                foreach ($entities as $entity) {
                    printf('Entity Name: %s' . PHP_EOL, $entity['name']);
                    printf('Entity Type: %s' . PHP_EOL, $entity['type']);
                    printf('Entity Salience: %s' . PHP_EOL, $entity['salience']);
                    printf('Entity Magnitude: %s' . PHP_EOL, $entity['sentiment']['magnitude']);
                    printf('Entity Score: %s' . PHP_EOL, $entity['sentiment']['score']);
                    printf(PHP_EOL);
                }
            }
            $this->cache->save($entities, $uMd5);
        }
        $this->trace("reponse de google = ",$entities);
        return $entities;
        
    }
    
    /**
     * Find the classifyText in text.
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function analyzeClassifyText($text)
    {
        $this->trace(__METHOD__." ".$text);
        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $categories = $this->cache->load($uMd5);
        if(!$categories){
            
            // Call the classifyText function
            $response = $this->language->classifyText($text);
            $categories = $response->categories();
            if($this->bTrace){
                // Print out information about each category
                foreach ($categories as $category) {
                    printf('Category Name: %s' . PHP_EOL, $category['name']);
                    printf('Confidence: %s' . PHP_EOL, $category['confidence']);
                    printf(PHP_EOL);
                }
            }
            $this->cache->save($categories, $uMd5);
        }
        $this->trace("reponse de google = ",$categories);
        return $categories;
        
    }
    
    /**
     * Find les annotations pour un texte
     * ```
     * analyze_sentiment('Do you know the way to San Jose?');
     * ```
     *
     * @param string $text The text to analyze.
     *
     */
    function annotateText($text)
    {
        $this->trace(__METHOD__." ".$text);
        
        //gestion du cache
        $uMd5 = md5(__METHOD__.$text);
        $infos = $this->cache->load($uMd5);
        if(!$infos){
            
            // Call the classifyText function
            $response = $this->language->annotateText($text);
            $infos = $response->info();
            if($this->bTrace){
                // Print out information about each category
                foreach ($infos as $k => $v) {
                    $this->trace($k, $v);
                }
            }
            $this->cache->save($infos, $uMd5);
        }
        $this->trace("reponse de google = ",$infos);
        return $infos;
        
    }
    
    
    /**
     * Fonction pour enregistrer une analyse de texte
     *
     * @param  	string 		$text
     * @param  	int     		$idDoc
     * @param  	array 		$types
     * @param  	array 		$ids
     *
     * @return	array
     *
     */
    function sauveAnalyseTexte($text, $idDoc, $types=['analyzeEntities'], $ids=false){

        $this->trace(__METHOD__." ".$text." ".$idDoc,$types);
                
        $arrReponse = array();
        
        //execute les analyses
        foreach ($types as $t) {
            switch ($t) {
                case 'analyzeEntities':
                    $arrReponse[$t] = $this->analyzeEntities($text);
                    break;
                case 'analyzeClassifyText':
                    $arrReponse[$t] = $this->analyzeClassifyText($text);
                    break;
                case 'analyzeEntitySentiment':
                    $arrReponse[$t] = $this->analyzeEntitySentiment($text);
                    break;
                case 'analyzeSyntax':
                    $arrReponse[$t] = $this->analyzeSyntax($text);
                    break;
                case 'analyzeSentiment':
                    $arrReponse[$t] = $this->analyzeSentiment($text);
                    break;
            }
        }
        
        //création du document d'analyse
        if(!$ids){
            $rs[] = $this->dbD->ajouter(array("parent"=>$idDoc,"titre"=>__METHOD__,"note"=>json_encode($arrReponse)),true, true);
        }else{
            foreach ($ids as $idDoc) {
                $rs[] = $this->dbD->ajouter(array("parent"=>$idDoc,"titre"=>__METHOD__,"note"=>json_encode($arrReponse)),true, true);
            }
        }
        return $rs;
    }
        
    
}