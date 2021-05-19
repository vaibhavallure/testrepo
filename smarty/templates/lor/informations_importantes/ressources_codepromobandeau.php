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
		$phrasecodebd = "avec le code";
		break;
		
	case "D" :
	case "O" :
	case "SA" :
     $phrasecodebd = "mit Ihrem Rabattcode:";
		break;
		
	case "G" :
	case "I" :
	case "H" :
	case "SG" :
     $phrasecodebd = "With the promo code";
		break;
		
	case "Y" :
        $phrasecodebd = "con il codice";
		break;
	
	case "E" :
        $phrasecodebd = "con el c&oacute;digo";
		break;
	
	case "P" :
        $phrasecodebd = "com o c&oacute;digo";
		break;
	
	case "U" :
        $phrasecodebd= "With the promo code";
		break;

     default: $phrasecodebd = '';
     break;
}

	$oSmarty->assign('phrasecodebd', $phrasecodebd);
	
?>