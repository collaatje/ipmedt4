<?php
/** TC
 * Met de klasse CurlLoginBlackboard kan er ingelogd worden op de Blackboard site van Hogeschool Leiden
 * en kunnen er pagina's opgevraagd worden van Blackboard
 * 
 * @author Tim Colla
 * @version 1.0
 * @see CurlLogin
 */
class CurlLoginBlackboard extends CurlLogin
{
	public function __construct($cookie = '')
	{
		parent::__construct($cookie);
	}
	
	/** TC
	* Deze methode stuurt een POST request naar de opgegeven url om zo in te loggen
	* @param $url De url uit het action-attribuut de <form> tag
	* @param $data Een array met alle POST-data in de vorm van
	* 			array('user_id' => 'studentnummer',
	*				  'password' => 'wachtwoord');
	*
	* @return Html-code
	*/
	public function login($url, $data, $isEncoded = false)
	{
		// Basis string voor Blackboard
		$postData = 'action=login&remote-user=&new_loc=&auth_type=&one_time_token=&password=&Login.x=34&Login.y=12';
		
		foreach($data as $key => $value)
		{
			// Wachtwoord coderen naar de vorm die Blackboard verwacht
			if($key == 'password' && !$isEncoded)
			{
				$encoded_pw = urlencode(base64_encode($value));
				$encoded_pw_unicode = urlencode($this->b64_unicode($value));
				
				$postData .= '&encoded_pw='.$encoded_pw.'&encoded_pw_unicode='.$encoded_pw_unicode;
			}
			else // Andere data toevoegen aan de string
			{
				$postData .= '&'.$key.'='.urlencode(urldecode($value));
//				echo urlencode(urldecode($value)).'<br />';
			}
		}
		
		return parent::login($url, $postData);
	}
	
	/** TC
	* Deze methode haalt alle mededelingen op
	*
	* @return array([] => array ( [titel] => 'titel',
	*							  [mededelingen] => array([] => 'mededeling')
	*							  )
	*				);
	*/
	public function getMededelingen()
	{
		$return = array();
		
		$source = $this->getPostPage('http://blackboard.hsleiden.nl/webapps/portal/tab', 'action=refreshAjaxModule&modId=_4_1&tabId=_1_1');
		
		$courses = explode('target="_top"', $source); // Source splitten per course
		//array_shift($courses);
		
		for($i = 1; $i < count($courses); $i++)// as $id => $course)
		{
			//$course = utf8_decode($courses[$i]);
			//echo $course;
			// $matches[1] bevat de titel
			preg_match('#>([^<]*)</a>.*mededelingen(.*)#si', $courses[$i], $matches); // Course titel zoeken
			//print_r($matches);
			//die();
			if(count($matches) > 0) //Mededeling(en) gevonden
			{
				/** TC $match_all[1] array met de urls
				* $match_all[2] array met de mededeling titels
				*/
				preg_match_all('#<a.*href="(.*)".*>(.*)</a>#sUi', $matches[0], $match_all); // Course mededelingen zoeken
				//print_r($match_all);die();
				// Mededeling titel in de return array zetten
				$return[] = array('titel' => $matches[1]);
				
				$lastArrayId = count($return)-1;
				
				$url = $match_all[1][0]; //Url van mededelingen pagina
				
				$page = ($this->getPage('http://blackboard.hsleiden.nl'.$url)); // Mededelingen pagina ophalen

				foreach($match_all[2] as $titel)
				{
					preg_match('#'.preg_quote($titel).'</em></b></font>.*<font.*>(.*)</font></td>.*<td.*><font.*>Gepost door:#sUi', $page, $match_mededeling); // Mededeling tekst zoeken

					$bericht = $match_mededeling[1];

					$bericht = preg_replace('#<font size="2" face="Arial, Helvetica, sans-serif"><b>Cursuskoppeling:.*</b><a.*>.*</a></font>#sUi', '', $bericht);
					$bericht = strip_tags($bericht, '<b><u><i><font><br>'); // Alle ongewenste tags weghalen
					
					
					//$bericht = str_replace(array("\n\r", "\r\n", "\r", "\n"), '', $bericht);
					
					// Mededeling in de return array zetten
					$return[$lastArrayId]['mededelingen'][] = array('titel' => $titel,
																	'bericht' => '<![CDATA['.trim($bericht).']]>');
				}
			}
		}
		
		return $return;
	}
	
	/** TC
	* Deze methode haalt alle mededelingen in xml-formaat
	*/
	public function getMededelingenXML()
	{
		$mededelingenArray = $this->getMededelingen();
		
		$return = '<?xml version="1.0" encoding="utf-8"?>
						<courses>'."\n";
		
		foreach($mededelingenArray as $id => $course)
		{
			$return .= '<course>'."\n";
			
			$return .= '<courseTitel>'.utf8_encode($course['titel']).'</courseTitel>'."\n";
			$return .= '<aantalMededelingen>'.count($course['mededelingen']).'</aantalMededelingen>'."\n";
			$return .= '<mededelingen>'."\n";
			
			foreach($course['mededelingen'] as $mededeling)
			{
				$return .= '<mededeling>'."\n";
				$return .= '<titel>'.$mededeling['titel'].'</titel>'."\n";
				$return .= '<bericht>'.($mededeling['bericht']).'</bericht>'."\n";
				$return .= '</mededeling>'."\n";
			}
			
			$return .= '</mededelingen>'."\n";
			
			$return .= '</course>'."\n";
		}
		
		$return .= '</courses>';
		
		return $return;
	}
	
	public function b64_unicode($s)
	{ 
		return $this->binl2b64($this->str2binl($s));
	}

	/** TC
	* Geef de ASCII waarde van het karakter in $str op plaats $i
	*
	* @param $str De string waarin gezocht moet worden
	* @param $i De plaats van het karakter in de string
	* @return ASCII waarde
	*/
	public function charCodeAt($str, $i)
	{
		return ord(substr($str, $i, 1));
	}
	
	/** TC
	 * Convert an array of little-endian words to a base-64 string
	 */
	public function binl2b64($binarray)
	{
		$hexcase = 1;  // hex output format. 0 - lowercase; 1 - uppercase
		$b64pad  = "="; // base-64 pad character. "=" for strict RFC compliance
		$chrsz   = 16; // bits per input character. 8 - ASCII; 16 - Unicode
		
		$tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
		$str = "";
		for($i = 0; $i < count($binarray) * 4; $i += 3)
		{
			if(isset($binarray[$i   >> 2]))
				$nummer1 = ((($binarray[$i   >> 2] >> 8 * ( $i   %4)) & 0xFF) << 16);
			else
				$nummer1 = 0;
			
			if(isset($binarray[$i+1 >> 2]))
				$nummer2 = ((($binarray[$i+1 >> 2] >> 8 * (($i+1)%4)) & 0xFF) << 8 );
			else
				$nummer2 = 0;
				
			if(isset($binarray[$i+2 >> 2]))
				$nummer3 = (($binarray[$i+2 >> 2] >> 8 * (($i+2)%4)) & 0xFF);
			else
				$nummer3 = 0;
				
			$triplet = $nummer1 | $nummer2 |  $nummer3;
		
			for($j = 0; $j < 4; $j++)
			{
				if($i * 8 + $j * 6 > count($binarray) * 32) $str .= $b64pad;
				else $str .= $tab{(($triplet >> 6*(3-$j)) & 0x3F)};
			}
		}
		return $str;
	}
	
	/** TC
	 * Convert a string to an array of little-endian words
	 * If chrsz is ASCII, characters >255 have their hi-byte silently ignored.
	 */
	public function str2binl($str)
	{
		$hexcase = 1;  // hex output format. 0 - lowercase; 1 - uppercase
		$b64pad  = "="; // base-64 pad character. "=" for strict RFC compliance
		$chrsz   = 16; // bits per input character. 8 - ASCII; 16 - Unicode
		
		$bin = array();
	  
		for($i =0 ; $i < ceil(strlen($str)/2); $i++)
			$bin[$i] = 0;
	
		$mask = (1 << $chrsz) - 1;
		for($i = 0; $i < strlen($str) * $chrsz; $i += $chrsz)
			$bin[$i>>5] |= ($this->charCodeAt($str, ($i / $chrsz)) & $mask) << ($i%32);
	
		return $bin;
	}
}
?>