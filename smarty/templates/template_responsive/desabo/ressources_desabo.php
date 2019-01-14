<?php
/**
 * Traitement du module desabo
 * Inseré dans le traitement général quand le module est activé /TO DO/
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du bloc de desabo<br />";


switch ($country) {
	case "F" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "[DL:UNSUBSCRIBE-1]"
						);
		break;
						
	case "B" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "[DL:UNSUBSCRIBE-12]"
						);
		break;
						
	case "L" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "[DL:UNSUBSCRIBE-13]"
						);
		break;
						
	case "SF" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "[DL:UNSUBSCRIBE-14]"
						);
		break;

	case "D" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "[DL:UNSUBSCRIBE-6]"
						);
		break;

	case "O" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "[DL:UNSUBSCRIBE-10]"
						);
		break;
		
	case "SA" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "[DL:UNSUBSCRIBE-9]"
						);
		break;
		
	case "G" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "[DL:UNSUBSCRIBE-3]"
						);
		break;

	case "I" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "[DL:UNSUBSCRIBE-11]"
						);
		break;

	case "Y" :
		$desabo = array ("title" => "Essere cancellato dal nostro elenco",
						"lien" => "[DL:UNSUBSCRIBE-5]"
						);
		break;

	case "E" :
		$desabo = array ("title" => "Ser eliminado de la lista de difusi&oacute;n",
						"lien" => "[DL:UNSUBSCRIBE-4]"
						);
		break;

	case "P" :
		$desabo = array ("title" => "Desejo ser retirado da lista de difus&atilde;o",
						"lien" => "[DL:UNSUBSCRIBE-15]"
						);
		break;
	
	case "H" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "[DL:UNSUBSCRIBE-19]"
						);
		break;

	case "SG" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "[DL:UNSUBSCRIBE-19]"
						);
		break;

	case "U" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "[DL:UNSUBSCRIBE-18]"
						);
		break;

	default :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "[DL:UNSUBSCRIBE-1]"
						);
		break;
	
}

$oSmarty->assign('desabo', $desabo);

?>