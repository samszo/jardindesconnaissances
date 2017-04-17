<?php
/**
 * Sample script to convert an CSV (ie. exported form MS Excel file) into
 * RDF SKOSXL format.
 *
 * @file csv2skosxl.php
 * @license Licensed under WTFPL (http://www.wtfpl.net/txt/copying/)
 * @author Cristian Romanescu <cristian.romanescu@eaudeweb.ro>
 * 
 * Ajout de la gestion multilingue
 * @author Samuel Szoniecky <samuel.szoniecky@univ-paris8.fr>
 * 
 * 
 */

/*exemple d'utilisation
if ($argc != 4) {
  echo "Usage: php csv2skosxl.php http://your.base.uri/terms input.csv output.xml" . PHP_EOL;
  exit(-1);
}

$baseUri = $argv[1];
$csvFile = $argv[2];
$xmlFile = $argv[3];

CSV2SkosXLConverter::exportToXML($baseUri, $csvFile, $xmlFile);
*/

/**
 * Class CSV2RDFConverter takes CSV file as input and writes SKOSXL RDF format.
 * CSV has the following columns:
 *  - column 1: (REQUIRED) ID of the concept, ie. a slug - see example below
 *  - column 2: (REQUIRED) Term in english
 *  - column 3: (OPTIONAL) Broader concept, use corresponding ID from column 1
 *  - column 4: (OPTIONAL) Concept definition
 *  - column 5: (OPTIONAL) Note (skos:note)
 *  - column 6: (OPTIONAL) Identifier, non-human languages (skos:notation)
 *
 *  Example
 *
 * |concept| prefLabel| broader| definition | note | notation |
 * |cheetah| Cheetah  | mammal | Four legs  | Fast | CHEETH-01|
 * |mammal | Mammals  | animal | Warm blood |      |          |
 * |animal | Animals  |        | All animals|      |          |
 *
 * Limitations (easily overcome by toying with the script):
 *
 * 1. Only the above specified columns are supported
 * 2. prefLabel is reified in order to be loadable into VocBench tool
 * 
 */
class CSV2SkosXLConverter {

  private static $scheme = 'http://your.domain.org/terms';

  public static function exportToXML($scheme, $csvFile, $xmlFile) {
    self::$scheme = $scheme;
    $terms = self::parseFile($csvFile);
    $content = self::toXML($terms);
    file_put_contents($xmlFile, $content);
  }  
  
  /**
   */
  static function parseFile($filename) {
    $terms = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
      try {
        $i = 0;
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if (!empty($row[0]) && $i++ != 0) {
            $term = new stdClass();
            $term->id = self::slugify($row[0]);
            $term->prefLabel = $row[1];
            $term->broader = !empty($row[2]) ? $row[2] : NULL;
            $term->definition = !empty($row[3]) ? $row[3] : NULL;
            $term->note = !empty($row[4]) ? $row[4] : NULL;
            $term->notation = !empty($row[5]) ? $row[5] : NULL;
            $terms[$term->id] = $term;
          }
        }
      }
      catch (Exception $e) {
        fclose($handle);
      }
    } else {
      throw new Exception('Failed to open input file');
    }

    // Remove invalid broader term (skos:Broader points to invalid concepts)
    foreach($terms as &$term) {
      if (!empty($term->broader) && !array_key_exists($term->broader, $terms)) {
        echo "Removing invalid broader term: {$term->broader}" . PHP_EOL;
        $term->broader = NULL;
      }
    }

    return $terms;
  }


  public static function toXML($terms) {
    $scheme = self::$scheme;
    // Start building up a RDF graph
    $guid = self::guid();
    $ret = array(
      <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF
        xmlns="$scheme"
        xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
        xmlns:owl="http://www.w3.org/2002/07/owl#"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns:dct="http://purl.org/dc/terms/"
        xmlns:skos="http://www.w3.org/2004/02/skos/core#"
        xmlns:skosxl="http://www.w3.org/2008/05/skos-xl#"
        xmlns:ann="http://art.uniroma2.it/ontologies/annotation#">

    <rdf:Description rdf:about="$scheme">
        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#ConceptScheme" />
        <skosxl:prefLabel rdf:resource="$scheme/xl-$guid" />
    </rdf:Description>
    <rdf:Description rdf:about="$scheme/xl-$guid">
        <rdf:type rdf:resource="http://www.w3.org/2008/05/skos-xl#Label" />
        <skosxl:literalForm xml:lang="en">Your beloved thesaurus</skosxl:literalForm>
    </rdf:Description>
EOT
    );
    foreach ($terms as $term) {
      $uri = $scheme . '/' . $term->id;
      $output  = "    <rdf:Description rdf:about=\"$uri\">" . PHP_EOL;
      $output .= "        <rdf:type rdf:resource=\"http://www.w3.org/2004/02/skos/core#Concept\" />" . PHP_EOL;
      $output .= "        <skos:inScheme rdf:resource=\"$scheme\" />" . PHP_EOL;
      // Make it root concept
      if (empty($term->broader)) {
        $output .= "        <skos:topConceptOf rdf:resource=\"$scheme\" />" . PHP_EOL;
      }

      // skos:note
      if (!empty($term->note)) {
        $output .= "        <skos:note>{$term->note}</skos:note>" . PHP_EOL;
      }

      // skos:notation
      if (!empty($term->notation)) {
        $output .= "        <skos:notation>{$term->notation}</skos:notation>" . PHP_EOL;
      }

      // skos:definition
      $definition = '';
      if (!empty($term->definition)) {
        $definition_uri = self::addDefinition($term, $definition);
        $output .= "        <skos:definition rdf:resource=\"{$definition_uri}\" />" . PHP_EOL;
      }

      // skos:prefLabel
      $prefLabel = '';
      $label_uri = self::addReifiedPrefLabel($term, $prefLabel);
      $output .= "        <skosxl:prefLabel rdf:resource=\"$label_uri\" />" . PHP_EOL;
      $output .= "    </rdf:Description>" . PHP_EOL;
      if (!empty($prefLabel)) {
        $output .= PHP_EOL . $prefLabel;
      }

      $output .= $definition;

      // skos:broader
      if (!empty($term->broader)) {
        $b_uri = $scheme . '/' . $term->broader;
        $output .= PHP_EOL;
        $output .= "    <rdf:Description rdf:about=\"$uri\">" . PHP_EOL;
        $output .= "        <skos:broader rdf:resource=\"$b_uri\" />" . PHP_EOL;
        $output .= "    </rdf:Description>" . PHP_EOL;
      }
      $ret[] = $output;
    }
    $ret[] = '</rdf:RDF>';
    return implode($ret, PHP_EOL);
  }


  public static function addDefinition($term, &$buffer, $language = 'en') {
    $uri = self::$scheme . '/def_' . $term->id;
    $description = self::xml_entities($term->definition);
    $buffer .= "    <rdf:Description rdf:about=\"$uri\">" . PHP_EOL;
    $buffer .= "        <rdf:value xml:lang=\"$language\">$description</rdf:value>" . PHP_EOL;
    $buffer .= "    </rdf:Description>" . PHP_EOL;
    return $uri;
  }

  public static function addReifiedPrefLabel($term, &$buffer, $language = 'en') {
    $uri = self::$scheme . '/xl_' . $language . '_' . $term->id;
    $title = self::xml_entities($term->prefLabel);
    $buffer .= "    <rdf:Description rdf:about=\"$uri\">" . PHP_EOL;
    $buffer .= "        <rdf:type rdf:resource=\"http://www.w3.org/2008/05/skos-xl#Label\" />" . PHP_EOL;
    $buffer .= "        <skosxl:literalForm xml:lang=\"$language\">$title</skosxl:literalForm>" . PHP_EOL;
    $buffer .= "    </rdf:Description>" . PHP_EOL;
    return $uri;
  }

  static function xml_entities($string) {
    return strtr(
      $string,
      array(
        "<" => "&lt;",
        ">" => "&gt;",
        '"' => "&quot;",
        "'" => "&apos;",
        "&" => "&amp;",
      )
    );
  }

  static function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    // trim
    $text = trim($text, '-');
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // lowercase
    $text = strtolower($text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    if (empty($text)) {
      return 'n-a';
    }
    return $text;
  }

  static function guid($lowercase = TRUE) {
    $charid = strtoupper(md5(uniqid(rand(), TRUE)));
    $hyphen = chr(45);// "-"
    $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
    return $lowercase ? strtolower($uuid) : $uuid;
  }
}
