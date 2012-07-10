<?php
/** TC
 * Met de klasse CurlLogin kan er ingelogd worden op websites
 * en kunnen er pagina's opgevraagd worden van websites
 * 
 * @author Tim Colla
 * @version 1.0
 */
class CurlLogin
{	
	protected $cookieFile;
	protected $userAgent;
	protected $loginCheckString;
	protected $loginCheckUrl;
	
	public function __construct($cookie)
	{
		$this->userAgent = "Mozilla/5.0 (Windows NT 6.0; rv:11.0) Gecko/20100101 Firefox/11.0";
		
		$this->loginCheckString = "document.location.replace('/webapps/login?new_loc=%2fwebapps%2fportal%2ftab')";
		$this->loginCheckUrl = "http://blackboard.hsleiden.nl/webapps/portal/tab";
		
		if(empty($cookie))
			$cookie = 'cookie.txt';
			
		$cookie = substr(md5(rand(0,99999)), rand(0, 10), rand(15, 20)).$cookie;
			
		$this->cookieFile = $cookie;
	}
	
	public function deleteCookie()
	{
		unlink($this->cookieFile);
	}
	
	/** TC
	* Deze methode stuurt een POST request naar de opgegeven url om zo in te loggen
	* @param $url De url uit het action-attribuut de <form> tag
	* @param $data Een array met alle POST-data in de vorm van
	* 			array('veldnaam1' => 'waarde',
	*				  ...
	*				  'veldnaam n' => 'waarde n');
	*
	* @return Html-code
	*/
	public function login($url, $data)
	{
		if(!$this->isLoggedIn())
		{
			$fp = fopen($this->cookieFile, "w");
			fclose($fp);
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			
			$source = curl_exec($ch);
			
			curl_close($ch);
			unset($ch);
		}
		
		return true;
	}
	
	/** TC
	* Deze methode stuurt een GET request naar de opgegeven url om zo de pagina op te halen
	* @param $url De url uit het action-attribuut de <form> tag
	* @return Html-code van de opgegeven url
	*/
	public function getPage($url)
	{
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		$source = curl_exec($ch);
		
		curl_close($ch);
		unset($ch);
		
		return $source;
	}
	
	/** TC
	* Deze methode stuurt een POST request naar de opgegeven url om zo een pagina op te halen
	* @param $url De url uit het action-attribuut de <form> tag
	* @param $data Een array met alle POST-data in de vorm van
	* 			array('veldnaam1' => 'waarde',
	*				  ...
	*				  'veldnaam n' => 'waarde n');
	*
	* @return Html-code
	*/
	public function getPostPage($url, $data)
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		$source = curl_exec($ch);
		
		curl_close($ch);
		unset($ch);
		
		return $source;
	}
	
	/** TC
	* Deze methode kijkt of de gebruiker al is ingelogd
	*
	* @return boolean
	*/
	public function isLoggedIn()
	{
		$source = $this->getPage($this->loginCheckUrl);
		
		if(!stristr($source, $this->loginCheckString))
			return true;
			
		return false;
	}
}

?>