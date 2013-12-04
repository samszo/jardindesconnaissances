<?php
/**
 * Classe qui gère les flux audio
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * THANKS
 * This code was adapted by [Andrew Freiday](http://andrewfreiday.com).
 * Based on the [PHP MP3 Waveform Generator](https://github.com/afreiday/php-waveform-png).
 * For getWave : http://www.phpclasses.org/browse/file/26978.html
 */
require_once( "../library/mp3file.php" );

class Flux_Audio extends Flux_Site{

  
  // how much detail we want. Larger number means less detail
  // (basically, how many bytes/frames to skip processing)
  // the lower the number means longer processing time
  var $DETAIL = 800;
  
  var $ffmpeg;
  var $encodage = " -aq 0 -ab 8k -ar 8000 -acodec libvorbis ";
  
	/**
	* constructeur de la classe
	* 
	* @param string $idBase
	* 
	* return Flux_Audio
	* 
	*/
	public function __construct($idBase=false)
    {
    	$this->ffmpeg = FFMEPG;
    	parent::__construct($idBase);
    }

	/**
	* trouve la valeur heaxdécimale
	* 
	* @param number $byte1
	* @param number $byte2
	*
	* return number
	*/
	function findValues($byte1, $byte2){
	    $byte1 = hexdec(bin2hex($byte1));                        
	    $byte2 = hexdec(bin2hex($byte2));                        
		return ($byte1 + ($byte2*256));
	}
    
	/**
	* calcul les coordonnées de la forme audio
	* 
	* @param string $filename
	*
	* return array
	*/
	function getWave($filename) {
		
		$c = str_replace("::", "_", __METHOD__)."_".md5($filename); 
	   	$arrCoor = false;//$this->cache->load($c);
        if(!$arrCoor){
		
        	//récupère les informations du fichier pour calculer le temps
			$arrInfos = $this->wavechunk($filename);			
			
	      /**
	       * Below as posted by "zvoneM" on
	       * http://forums.devshed.com/php-development-5/reading-16-bit-wav-file-318740.html
	       * as findValues() defined above
	       * Translated from Croation to English - July 11, 2011
	       */
	      $handle = fopen($filename, "r");
	      // wav file header retrieval
	      $heading[] = fread($handle, 4);
	      $heading[] = bin2hex(fread($handle, 4));
	      $heading[] = fread($handle, 4);
	      $heading[] = fread($handle, 4);
	      $heading[] = bin2hex(fread($handle, 4));
	      $heading[] = bin2hex(fread($handle, 2));
	      $heading[] = bin2hex(fread($handle, 2));
	      $heading[] = bin2hex(fread($handle, 4));
	      $heading[] = bin2hex(fread($handle, 4));
	      $heading[] = bin2hex(fread($handle, 2));
	      $heading[] = bin2hex(fread($handle, 2));
	      $heading[] = fread($handle, 4);
	      $heading[] = bin2hex(fread($handle, 4));
	      
	      // wav bitrate 
	      $peek = hexdec(substr($heading[10], 0, 2));
	      $byte = $peek / 8;
	      
	      // checking whether a mono or stereo wav
	      $channel = hexdec(substr($heading[6], 0, 2));
	      
	      $ratio = ($channel == 2 ? 40 : 80);
	      
	      // start putting together the initial canvas
	      // $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
	      $data_size = floor((filesize($filename) - 44) / ($ratio + $byte) + 1);
	      //calcule la valeur d'une seconde
	      $size_second =  floor($arrInfos["seconds"])/$data_size;
	      //calcule le détail pour avoir une ligne par seconde
	      $this->DETAIL = round($data_size/floor($arrInfos["seconds"]));
	      $data_point = 0;
		  $nbX = 0;
	      while(!feof($handle) && $data_point < $data_size){
	        if ($data_point++ % $this->DETAIL == 0) {
	          $bytes = array();
	          
	          // get number of bytes depending on bitrate
	          for ($i = 0; $i < $byte; $i++)
	            $bytes[$i] = fgetc($handle);
	          
	          switch($byte){
	            // get value for 8-bit wav
	            case 1:
	              $data = $this->findValues($bytes[0], $bytes[1]);
	              break;
	            // get value for 16-bit wav
	            case 2:
	              if(ord($bytes[1]) & 128)
	                $temp = 0;
	              else
	                $temp = 128;
	              $temp = chr((ord($bytes[1]) & 127) + $temp);
	              $data = floor($this->findValues($bytes[0], $temp) / 256);
	              break;
	          }
	          
	          // skip bytes for memory optimization
	          fseek($handle, $ratio, SEEK_CUR);
	          
	          // draw this data point
	          // data values can range between 0 and 255        
	          $x1 = number_format($data_point / $data_size * 100, 2);
	          $y1 = number_format($data / 255 * 100, 2);
	          $y2 = 100 - $y1;
			  	//calcul la date
			  	$date = $this->getFormatDate(floor($data_point*$size_second));
				
	          // don't bother plotting if it is a zero point
	          if ($y1 != $y2){
	            $arrCoor[] = array("x1"=>$x1, "y1"=>$y1, "x2"=>$nbX, "y2"=>$y2, "date"=>$date, "data"=>$data, "point"=>$data_point);
	            $nbX++;   	          	
	          }
	          
	        } else {
	          // skip this one due to lack of detail
	          fseek($handle, $ratio + $byte, SEEK_CUR);
	        }
	      }
	      // close and cleanup
	      fclose($handle);
	      //ajoute un point final
		  $date = $this->getFormatDate(floor($arrInfos["seconds"]));
		  $arrCoor[] = array("x1"=>$x1, "y1"=>$y1, "x2"=>$nbX, "y2"=>$y2, "date"=>$date);
	      // met en cache le résultat
		  $this->cache->save($arrCoor, $c);
        }
	      
	    return $arrCoor;
	}
	
	function getFormatDate($d){
		$dt = new DateTime('@' . $d, new DateTimeZone('UTC'));
		//utilise la date 0 de javascript = 1 janvier 1970
		$date = "01/01/1970 ".$dt->format('G').":".$dt->format('i').":".$dt->format('s');
		
		return $date;
	}

    function wavechunk($file) {

    	$this->fp = fopen($file, 'rb');
        rewind($this->fp);

        $riff_fmt = 'a4ID/VSize/a4Type';
        $riff_cnk = @unpack($riff_fmt, fread($this->fp, 12));

        if($riff_cnk['ID'] != 'RIFF' || $riff_cnk['Type'] != 'WAVE') {
            return -1;
        }

        $format_header_fmt = 'a4ID/VSize';
        $format_header_cnk = @unpack($format_header_fmt, fread($this->fp, 8));

        if($format_header_cnk['ID'] != 'fmt ' || !in_array($format_header_cnk['Size'], array(16, 18))) {
            return -2;
        }

        $format_fmt = 'vFormatTag/vChannels/VSamplesPerSec/VAvgBytesPerSec/vBlockAlign/vBitsPerSample'.($format_header_cnk['Size'] == 18 ? '/vExtra' : '');
        $format_cnk = @unpack($format_fmt, fread($this->fp, $format_header_cnk['Size']));

        if($format_cnk['FormatTag'] != 1) {
            return -3;
        }

        if(!in_array($format_cnk['Channels'], array(1, 2))) {
            return -4;
        }

        $fact_fmt = 'a4ID/VSize/Vdata';
        $fact_cnk = @unpack($fact_fmt, fread($this->fp, 12));

        if($fact_cnk['ID'] != 'fact') {
            fseek($this->fp, ftell($this->fp) - 12);
        }

        $data_fmt = 'a4ID/VSize';
        $data_cnk = @unpack($data_fmt, fread($this->fp, 8));

        if($data_cnk['ID'] != 'data') {
            return -5;
        }

        if($data_cnk['Size'] % $format_cnk['BlockAlign'] != 0) {
            return -6;
        }

        $this->data = fread($this->fp, $data_cnk['Size']);
        $this->blockfmt = $format_cnk['Channels'] == 1 ? 'sLeft' : 'sLeft/sRight';

        $this->blocktotal = $data_cnk['Size'] / 4;
        $this->blocksize = $format_cnk['BlockAlign'];

        $return = array
            (
            'Channels' => $format_cnk['Channels'],
            'SamplesPerSec' => $format_cnk['SamplesPerSec'],
            'AvgBytesPerSec' => $format_cnk['AvgBytesPerSec'],
            'BlockAlign' => $format_cnk['BlockAlign'],
            'BitsPerSample' => $format_cnk['BitsPerSample'],
            'Extra' => $format_cnk['Extra'],
            'seconds' => ($data_cnk['Size'] / $format_cnk['AvgBytesPerSec'])
            );

        return $return;

    } 

    function convertMp3ToOgg($src, $dst){
    	
    	/*merci à 
    	 * http://www.jcartier.net/spip.php?article36
    	 * http://doc.ubuntu-fr.org/ffmpeg
    	 * 
    	 */
    	
		$cmd = $this->ffmpeg." -i ".$src." -aq 0 -ab 8k -ar 8000 -acodec libvorbis ".$dst." 2>&1";		
		exec($cmd, $arr);

		return $arr;
    }

    function coupe($src, $dst, $deb, $dur){
    	
    	/*merci à 
    	 * http://doc.ubuntu-fr.org/ffmpeg
    	 * 
    	 * pour écraser
    	 * http://www.ffmpeg.org/ffmpeg.html#toc-Main-options
    	 * 
    	 */
    	
		$cmd = $this->ffmpeg." -ss ".$deb." -t ".$dur." -i ".$src." ".$dst." 2>&1";		
		exec($cmd, $arr);

		return $arr;
    }

	/**
	* récupère les informations d'un fichier ogg
	* 
	* @param string $src
	*
	* return array
	*/
	function getOggInfos($src) {
		$cmd = $this->ffmpeg." -i ".$src." 2>&1";		
		exec($cmd, $arr);
		$data = false;
		if(count($arr)>13){
			//compile les informations
			$infos1 = split(",",$arr[12]);
			$infos2 = split(",",$arr[13]);
			$data['Encoding'] = 'vorbis';
	        $data['Channel Mode'] = $infos2[2];
	        $data['Bitrate'] = $infos2[4];
	        $dur = substr($infos1[0],11);
	        $data['Length hh:mm:ss'] = $dur;
	        $dur = split(":",$dur);
	        $nbSec = ($dur[0]*3600)+($dur[1]*60)+$dur[2];
	        $data['Length'] = $nbSec;
		}
				
		return $data;
	}    

	/**
	* récupère les informations d'un fichier mp3
    * 
    * @param string $filename
    *
    * return array
    */
    function getMp3Infos($filename) {
    	$m = new mp3file($filename);
        return $m->get_metadata();              
	}
	
}