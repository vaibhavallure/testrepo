<?php
/**
 * Traductions "Avec le code"
 * Inseré dans le traitement général quand le module est activé
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 *
 * Variables déclarées préalablement dans traitement général :
 *		$country
 * 
 * @author  Aurelie Lopes pour Millesima
 */
 
 switch ($country) {
	case "F" :	
	case "B" :
	case "L" :
	case "SF" :
		$phrasecode = "avec le code";
		break;
		
	case "D" :
	case "O" :
	case "SA" :
		$phrasecode = "mit Ihrem Rabattcode:";
		break;
		
	case "G" :
	case "I" :
	case "H" :
	case "SG" :
		$phrasecode = "With the promo code";
		break;
		
	case "Y" :
		$phrasecode = "con il codice";		
		break;
	
	case "E" :
		$phrasecode = "con el c&oacute;digo";		
		break;
	
	case "P" :
		$phrasecode = "com o c&oacute;digo";		
		break;
	
	case "U" :
		$phrasecode = "With the promo code";		
		break;	
}

	$oSmarty->assign('phrasecode', $phrasecode)
	
?>