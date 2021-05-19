<?php
/**
 * Traitement du module réseaux sociaux
 * Inseré dans le traitement général quand le module est activé
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 *
 * Variables déclarées préalablement dans traitement général :
 *		$country
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout des réseaux sociaux<br />";

switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaFR",
							"google" => "https://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;

	case "D" :
	case "O" :
	case "SA" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaDE",
							"google" => "http://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;

	case "G" :
	case "H" :
	case "I" :
	case "SG" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaENG",
							"google" => "http://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;

	case "Y" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaIT",
							"google" => "http://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;

	case "E" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaENG",
							"google" => "http://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;

	case "P" :
		$social = array( "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaENG",
							"google" => "http://plus.google.com/+millesima", 
							"youtube" => "http://www.youtube.com/user/Millesima"
						);
		break;
	
	case "U" :
		$social = array( "facebook" => "https://www.facebook.com/Millesima.USA",
							"twitter" => "https://twitter.com/MillesimaUSA",
							"google" => "https://plus.google.com/+Millesima-usa", 
							"youtube" => "https://www.youtube.com/channel/UCZY1o_kNZID-qf0CyQN4X3w"
						);
		break;
	
}

$oSmarty->assign('social', $social);


?>