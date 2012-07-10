<?php
/** TC
 * Met de klasse Rooster kan het rooster worden opgehaald 
 * 
 * @author Tim Colla
 * @version 1.0
 */
class Rooster
{	
	private $roosterUrl;
	private $roosterData;
	private $lokalen;
	
	private $dagBeginTime;
	private $dagEindTime;
	
	public function __construct($url)
	{
		$this->roosterUrl = $url;
		$this->roosterData = file_get_contents($url);
		
		$this->lokalen = array('G3.076', 'G3.110');
		
		$this->dagBeginTime =  strtotime('08:30');
		$this->dagEindTime =  strtotime('22:00');
	}
	
	/** TC
	* Deze methode geeft de rooster-data in array-formaat
	*
	* @return array([dd-mm-YYY] => array( [] => array(
	*												   [start] => 'HH:ii',
	*												   [eind] => 'HH:ii',
	*												   [locatie] => 'lokaal',
	*												   [summary] => 'samenvatting',
	*												   [description] => 'beschrijving')
	*							  		)
	*				);
	*/
	public function getRooster()
	{
		preg_match_all('#location:(?P<locatie>[^\n]*?).*dtstart:(?P<datum>[0-9]*?)t(?P<start>[^\n]*?).*dtend:[0-9]*t(?P<eind>[^\n]*?).*summary:(?P<summary>[^\n]*?).*description:(?P<description>[^\n]*?)#sUi', $this->roosterData, $matches);

		$roosterArray = array();
		
		for($i = 0; $i < count($matches['datum']); $i++)
		{
			$datum = date('d-m-Y', strtotime($matches['datum'][$i]));
			
			$start = substr($matches['start'][$i], 0, 2).':'.substr($matches['start'][$i], 2, 2);
			
			$eind = substr($matches['eind'][$i], 0, 2).':'.substr($matches['eind'][$i], 2, 2);
			
			$roosterArray[$datum][] = array('start' => $start,
											'eind' => $eind,
											'locatie' => $matches['locatie'][$i],
											'summary' => htmlspecialchars($matches['summary'][$i]),
											'description' => htmlspecialchars($matches['description'][$i]));
		}
		
		return $this->sortRooster($roosterArray);
	}
	
	/** TC
	* Deze methode geeft de chronologisch gesoorteerde rooster-data in array-formaat
	*
	* @return array([dd-mm-YYY] => array( [] => array(
	*												   [start] => 'HH:ii',
	*												   [eind] => 'HH:ii',
	*												   [locatie] => 'lokaal',
	*												   [summary] => 'samenvatting',
	*												   [description] => 'beschrijving')
	*							  		)
	*				);
	*/
	public function sortRooster($roosterArray)
	{
		foreach($roosterArray as $datum => $array)
		{
			$newArray = array();
			$aantal = count($array);
			
			for($i = 0; $i < $aantal; $i++)
			{
				if($i == 0)
				{
					$newArray[0] = $array[0];
				}
				else
				{
					foreach($newArray as $id => $data)
					{
						$timeVorige = strtotime($newArray[$i-1]['start']);
						$timeHuidige = strtotime($array[$i]['start']);
						if($timeHuidige < $timeVorige)
							break;
					}
					
					for($j = count($newArray)-1; $j >= $id; $j--)
						$newArray[$j+1] = $newArray[$j];
					$newArray[$id] = $array[$i];
				}
			}
			
			$roosterArray[$datum] = $newArray;
		}
		return $roosterArray;
	}
	
	/** TC
	* Deze methode geeft de rooster-data in xml-formaat
	*
	* @return XML
	*/
	public function getRoosterXML()
	{
		$roosterArray = $this->getRooster();
		
		$return = '<?xml version="1.0" encoding="utf-8"?>
						<rooster>'."\n";
		
		foreach($roosterArray as $datum => $events)
		{
			$return .= '<events datum="'.$datum.'">'."\n";
			
			foreach($events as $event)
			{
				$return .= '<event>'."\n";
				
				$return .= '<lokaal>'.$event['locatie'].'</lokaal>'."\n";
				
				$return .= '<tijd>'."\n";
				$return .= '<start>'.$event['start'].'</start>'."\n";
				$return .= '<eind>'.$event['eind'].'</eind>'."\n";
				$return .= '</tijd>'."\n";
				
				$return .= '<summary>'.$event['summary'].'</summary>'."\n";
				$return .= '<description>'.$event['description'].'</description>'."\n";
				
				$return .= '</event>'."\n";
			}
			
			$return .= '</events>'."\n";
		}
		
		$return .= '</rooster>';
		
		return $return;
	}
	
	/** TC
	* Deze methode geeft de rooster-data van een lokaal in array-formaat
	*
	* @param $lokaal Het lokaal waarvan het rooster wordt opgehaald
	*
	* @return array([dd-mm-YYY] => array( [] => array(
	*												   [start] => 'HH:ii',
	*												   [eind] => 'HH:ii')
	*							  		)
	*				);
	*/
	public function getRoosterLokaal($lokaal)
	{
		$lokaalData = file_get_contents('http://roosterinfo.hsleiden.nl/kalender/ical/Lokaal_'.$lokaal.'.ics');
		preg_match_all('#dtstart:(?P<datum>[0-9]*?)t(?P<start>[^\n]*?).*dtend:[0-9]*t(?P<eind>[^\n]*?)#sUi', $lokaalData, $matches);

		$lokaalArray = array();
		
		for($i = 0; $i < count($matches['datum']); $i++)
		{
			$datum = date('d-m-Y', strtotime($matches['datum'][$i]));
			
			$start = substr($matches['start'][$i], 0, 2).':'.substr($matches['start'][$i], 2, 2);
			
			$eind = substr($matches['eind'][$i], 0, 2).':'.substr($matches['eind'][$i], 2, 2);
			
			$lokaalArray[$datum][] = array('start' => $start,
											'eind' => $eind);
		}
		
		return $lokaalArray;
	}
	
	/** TC
	* Deze methode geeft de rooster-data van $this->lokalen in array-formaat
	*
	* @param $datum De datum waarvan het rooster wordt opgehaald
	*
	* @return array([lokaal] => array( [] => array(
	*												   [start] => 'HH:ii',
	*												   [eind] => 'HH:ii')
	*							  		)
	*				);
	*/
	public function getVolleLokalen($datum = '')
	{
		$return = array();
		
		if($datum == '')
			$datum = date('d-m-Y');
		
		foreach($this->lokalen as $lokaal)
		{
			$lokaalData = $this->getRoosterLokaal($lokaal);
			
			$return[$lokaal] = $lokaalData[$datum];
		}
		
		
		return $return;
	}
	
	/** TC
	* Deze methode geeft de rooster-data, waarop de lokalen leeg zijn, van $this->lokalen in array-formaat
	*
	* @param $datum De datum waarvan het rooster wordt opgehaald
	*
	* @return array([lokaal] => array( [] => array(
	*												   [start] => 'HH:ii',
	*												   [eind] => 'HH:ii')
	*							  		)
	*				);
	*/
	public function getLegeLokalen($datum = '')
	{
		$return = array();
		
		if($datum == '')
			$datum = date('d-m-Y');
			
		// Haal de tijden op waarop het lokaal vol is
		$volleLokalen = $this->getVolleLokalen($datum);
		
		// Door alle lokalen heen lopen
		foreach($volleLokalen as $lokaal => $tijd)
		{
			// Door alle lessen heen lopen
			for($i = 0; $i < count($tijd); $i++)
			{
				if($i == 0) // Eerste les
				{
					$startTime = strtotime($tijd[$i]['start']); // Starttijd les
					
					if($startTime > $this->dagBeginTime) // Starttijd les > Begin dag
					{
						$return[$lokaal][] = array('start' => date('H:i', $this->dagBeginTime),
												   'eind' => date('H:i', $startTime));
					}
				}
				
				if(isset($tijd[$i+1])) // Rest van de lessen zonder de laatste les
				{
					$nextStartTime = strtotime($tijd[$i+1]['start']); // Starttijd volgende les
					$eindTime = strtotime($tijd[$i]['eind']); // Eindtijd huidige les
					
					if($nextStartTime > $eindTime) // Starttijd volgende les > Eindtijd huidige les
					{
						$return[$lokaal][] = array('start' => date('H:i', $eindTime),
													'eind' => date('H:i', $nextStartTime));
					}
				}
				else // Laatste les
				{
					$eindTime = strtotime($tijd[$i]['eind']); // Eindtijd les
					
					if($eindTime < $this->dagEindTime) // Eindtijd les < Eind dag
					{
						$return[$lokaal][] = array('start' => date('H:i', $eindTime),
												   'eind' => date('H:i', $this->dagEindTime));
					}
				}
			}
		}
		
		return $return;
	}
	
	/** TC
	* Deze methode geeft de rooster-data van de volle lokalen in xml-formaat
	*
	* @return XML
	*/
	public function getVolleLokalenXML()
	{
		$lokalenArray = $this->getVolleLokalen();
		
		return $this->getLokalenXML($lokalenArray);
	}
	
	/** TC
	* Deze methode geeft de rooster-data van de lege lokalen in xml-formaat
	*
	* @return XML
	*/
	public function getLegeLokalenXML()
	{
		$lokalenArray = $this->getLegeLokalen();
		
		return $this->getLokalenXML($lokalenArray);
	}
	
	/** TC
	* Deze methode geeft de rooster-data van de lokalen in xml-formaat
	*
	* @param $lokalenArray Array van lokalen met tijden
	*
	* @return XML
	*/
	public function getLokalenXML($lokalenArray)
	{
		$return = '<?xml version="1.0" encoding="utf-8"?>
						<lokalen>'."\n";
		
		foreach($lokalenArray as $nummer => $tijden)
		{
			$return .= '<lokaal nummer="'.$nummer.'">'."\n";
			
			foreach($tijden as $tijd)
			{
				$return .= '<tijd>'."\n";
				$return .= '<start>'.$tijd['start'].'</start>'."\n";
				$return .= '<eind>'.$tijd['eind'].'</eind>'."\n";
				$return .= '</tijd>'."\n";
			}
			
			$return .= '</lokaal>'."\n";
		}
		
		$return .= '</lokalen>';
		
		return $return;
	}
}

?>