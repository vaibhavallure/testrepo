<?php
/**
 * Traductions "Avec le code"
 * Inser� dans le traitement g�n�ral quand le module est activ�
 * Faire attention � une cr�ation de l'objet $oSmarty pr�alablement dans le traitement g�n�ral
 *
 * Variables d�clar�es pr�alablement dans traitement g�n�ral :
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