<?php
/**
 * Traitement du module contacts
 * Inseré dans le traitement général quand le module est activé /TO DO/
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du bloc de contact<br />";


switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$reassurance = array (1 => array( "titre" => "Choix exceptionnel !", 
											"description" => "12 000 r&eacute;f&eacute;rences disponibles en plusieurs formats et mill&eacute;simes en livrable et en primeur."), 
							2 => array( "titre" => "Qualit&eacute; &amp; provenance !", 
											"description" => "2.5 millions de bouteilles en stock dans nos chais de Bordeaux en provenance directe des propri&eacute;t&eacute;s."),
							3 => array( "titre" => "Conseils &amp; services", 
											"description" => "Nos conseillers partagent leur passion avec plus de 150 000 clients en Europe, aux USA et en Asie."),
							4 => array( "titre" => "Livraison soign&eacute;e", 
											"description" => "D&eacute;j&agrave; 350 000 livraisons par transporteurs sp&eacute;cialis&eacute;s dans plus de 120 pays.")
						);
		break;

	case "D" :
	case "O" :
		$reassurance = array (1 => array( "titre" => "Eine einmalige Auswahl !", 
											"description" => "Mehr als 12 000 sorgsam ausgew&auml;hlte Referenzen ergeben eines der sch&ouml;nsten und breitesten Angebote am Markt."), 
							2 => array( "titre" => "Qualit&auml;t &amp; Authentizit&auml;t !", 
											"description" => "Unsere Weine kaufen wir ausschlie&szlig;lich direkt beim Produzenten ein und garantieren so Qualit&auml;t und Authentizit&auml;t jeder Flasche."),
							3 => array( "titre" => "Beratung &amp; Service", 
											"description" => "Unsere Kundenberater teilen ihre Leidenschaft f&uuml;r grosse Weine mit mehr als 150 000 Kunden in Europa, den USA und Asien."),
							4 => array( "titre" => "Professionnelle Lieferung", 
											"description" => "30 Jahre Erfahrung mit 350 000 Lieferungen an Kunden in 120 L&auml;ndern garantieren eine problemlose und professionelle Abwicklung Ihrer Bestellung.")
						);
		break;
	case "SA" :
		$reassurance = array (1 => array( "titre" => "Eine einmalige Auswahl !", 
											"description" => "Mehr als 12 000 sorgsam ausgew&auml;hlte Referenzen ergeben eines der sch&ouml;nsten und breitesten Angebote am Markt."), 
							2 => array( "titre" => "Qualit&auml;t &amp; Authentizit&auml;t !", 
											"description" => "Unsere Weine kaufen wir ausschliesslich direkt beim Produzenten ein und garantieren so Qualit&auml;t und Authentizit&auml;t jeder Flasche."),
							3 => array( "titre" => "Beratung &amp; Service", 
											"description" => "Unsere Kundenberater teilen ihre Leidenschaft f&uuml;r grosse Weine mit mehr als 150 000 Kunden in Europa, den USA und Asien."),
							4 => array( "titre" => "Professionnelle Lieferung", 
											"description" => "30 Jahre Erfahrung mit 350 000 Lieferungen an Kunden in 120 L&auml;ndern garantieren eine problemlose und professionelle Abwicklung Ihrer Bestellung.")
						);
		break;
		
	case "G" :
	case "I" :
	case "H" :
	case "SG" :
		$reassurance = array (1 => array( "titre" => "Exceptional choice!", 
											"description" => "12,000 references available in several formats and vintages, both ready for shipment and en-primeur."), 
							2 => array( "titre" => "Quality and provenance!", 
											"description" => "2.5 million bottles in stock in our Bordeaux cellars, coming straight from their producing estates."),
							3 => array( "titre" => "Advice and Assistance", 
											"description" => "Our advisors share their passion with over 150 000 customers across Europe, USA and Asia."),
							4 => array( "titre" => "Careful delivery", 
											"description" => "Over 350,000 deliveries made by specialized carriers throughout more than 120 countries.")
						);
		break;

	case "Y" :
		$reassurance = array (1 => array( "titre" => "Scelta eccezionale", 
											"description" => "Pi&ugrave; di 12 000 prodotti disponibili in diversi formati e annate pronti per la consegna o in primeurs."), 
							2 => array( "titre" => "Qualit&agrave; ed Autenticit&agrave;", 
											"description" => "2.5 milioni di bottiglie nello stock nella nostra cantina di Bordeaux, tutti i vini provengono direttamente delle tenute."),
							3 => array( "titre" => "Consigili e servizi", 
											"description" => "Il nostro staff condivide la sua passione con pi&ugrave; di 150 000 clienti in Europa, Stati Uniti ed Asia."),
							4 => array( "titre" => "Consegna accurata", 
											"description" => "Pi&ugrave; di 350 000 consegne effettuate da corrieri specializzati in pi&ugrave; di 120 paesi.")
						);
		break;

	case "E" :
		$reassurance = array (1 => array( "titre" => "Surtido excepcional", 
											"description" => "M&aacute;s de 12 000 vinos disponibles en diversos formatos y a&ntilde;adas listos para la entrega o en primeurs."), 
							2 => array( "titre" => "Calidad y autenticidad", 
											"description" => "2.5 miliones de botellas en nuestra bodega de Burdeos que provienen directamente de los ch&acirc;teaux."),
							3 => array( "titre" => "Consejos y servicios", 
											"description" => "Nuestros consejeros comparten su pasi&oacute;n con m&aacute;s de 150 000 clientes en toda Europa, Asia y EEUU."),
							4 => array( "titre" => "Entrega cuidada", 
											"description" => "350 000 entregadas efectuadas por transportistas especializados en m&aacute;s de 120 pa&iacute;ses.")
						);
		break;

	case "P" :
		$reassurance = array (1 => array( "titre" => "Escolha excepcional", 
											"description" => "12 000 referencias seleccionadas com a maior severidade, dispon&iacute;veis em varias colheitas e em grandes garrafas."), 
							2 => array( "titre" => "Qualidade et autenticidade", 
											"description" => "2 500 000 garrafas compradas exclusivamente nas propriedades, envelhecem nas nossas caves que garantem uma conserva&ccedil;&atilde;o &oacute;ptima"),
							3 => array( "titre" => "Servi&ccedil;o personalizado", 
											"description" => "Os nossos conselheiros partilham com mais de 150 000 clientes as suas notas de prova e informa&ccedil;&otilde;es sobre cada vinho."),
							4 => array( "titre" => "Entrega cuidada", 
											"description" => "Mais de 350 000 entregas efectuadas em mais de 120 pa&iacute;ses na Europa, EUA e Asia.")
						);
		break;

	case "U" :
		$reassurance = array (1 => array( "titre" => "Exceptional choice!", 
											"description" => "12,000 references available in several formats and vintages, both ready for shipment and en-primeur."), 
							2 => array( "titre" => "Quality and provenance!", 
											"description" => "2.5 million bottles in stock in our Bordeaux cellars, sourced directly from producer estates."),
							3 => array( "titre" => "Advice and Services", 
											"description" => "Our advisors share their passion with over 150 000 customers across Europe, USA and Asia."),
							4 => array( "titre" => "Careful delivery", 
											"description" => "Already 350,000 deliveries made by specialized carriers throughout more than 120 countries.")
						);
		break;

	default :
		$reassurance = array (1 => array( "titre" => "Choix exceptionnel !", 
											"description" => "12 000 r&eacute;f&eacute;rences disponibles en plusieurs formats et mill&eacute;simes en livrable et en primeur."), 
							2 => array( "titre" => "Qualit&eacute; &amp; provenance !", 
											"description" => "2.5 millions de bouteilles en stock dans nos chais de Bordeaux en provenance directe des propri&eacute;t&eacute;s."),
							3 => array( "titre" => "Conseils &amp; services", 
											"description" => "Nos conseillers partagent leur passion avec plus de 150 000 clients en Europe, aux USA et en Asie."),
							4 => array( "titre" => "Livraison soign&eacute;e", 
											"description" => "D&eacute;j&agrave; 350 000 livraisons par transporteurs sp&eacute;cialis&eacute;s dans plus de 120 pays.")
						);
		break;
	
}

$oSmarty->assign('tabreassurance', $reassurance);

?>