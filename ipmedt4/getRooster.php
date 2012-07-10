<?php
// Alle benodigde includes - TC
include 'CurlLogin.cls.php';
include 'CurlLoginBlackboard.cls.php';
include 'Rooster.cls.php';

// Als de GET-waarde klas meegestuurd is
if(isset($_GET['klas']))
{
	$rooster = new Rooster('http://roosterinfo.hsleiden.nl/kalender/ical/Klas_'.$_GET['klas'].'.ics'); // Rooster data ophalen - TC

	header ("Content-Type:text/xml"); // Content-type zetten op text/xml - TC

	echo $rooster->getRoosterXML(); // De xml-data van het rooster weergeven op het scherm - TC
}
else // Fout weergeven - TC
	echo 'Geen waarde opgegeven';
?>