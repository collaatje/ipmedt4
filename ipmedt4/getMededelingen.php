<?php
// Alle benodigde includes - TC
include 'CurlLogin.cls.php';
include 'CurlLoginBlackboard.cls.php';
include 'Rooster.cls.php';

// Als de GET-waarden username en password meegestuurd zijn
if(isset($_GET['username']) && isset($_GET['password']))
{
	$username = $_GET['username'];
	$pass = $_GET['password'];
	
	$curl = new CurlLoginBlackboard($username.'.txt'); // Het CurlLoginBlackboard object creeeren - TC
	
	// De inloggegevens in een array zetten - TC
	$inlogArray = array('user_id' => $username,  'encoded_pw' => base64_encode($pass), 'encoded_pw_unicode' => $curl->b64_unicode($pass));
	
	// Daadwerkelijk inloggen - TC
	$curl->login('http://blackboard.hsleiden.nl/webapps/login/', $inlogArray);
	
	header ("Content-Type: text/xml; charset=utf-8"); // Content-type zetten op text/xml; charset=utf-8 - TC
	echo $curl->getMededelingenXML(); // De xml-data van het rooster weergeven op het scherm - TC
	
	$curl->deleteCookie(); // De cookie van de huidige inlogpoging weer verwijderen - TC
}
else // Fout weergeven - TC
	echo 'Geen waarde opgegeven.';

?>