<?php
/**
 * Ressources concernant la livraison avant Noel et délai de livraison HK & SG
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
		$livraison = array( 
										"phrase" => "<strong>Livraison OFFERTE*</strong>",
										//"phrase" => "Livraison avant No&euml;l : derniers jours *",
										"detail" => "",
										//"detail" => "* Hors vins primeurs",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "31/08/2019";
		break;
	case "B" :
	case "L" :
		$livraison = array( 
										"phrase" => "<strong>Livraison garantie avant Noël pour les commandes reçues avant le 6 décembre 2018</strong>",
										//"phrase" => "Livraison avant No&euml;l : derniers jours *",
										"detail" => "",
										//"detail" => "* Livraison garantie avant No&euml;l pour les commandes reçues avant le <strong>7 d&eacute;cembre 2015</strong>",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "06/12/2018";
		break;
		
	case "SF" :
		$livraison = array( 
										"phrase" => "<strong>Livraison garantie avant Noël pour les commandes reçues avant le 3 décembre 2018</strong>",
										//"phrase" => "Livraison avant No&euml;l : derniers jours *",
										"detail" => "",
										//"detail" => "* Livraison garantie avant No&euml;l pour les commandes reçues avant le <strong>7 d&eacute;cembre 2015</strong>",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "03/12/2018";
		break;

	case "D" :
		$livraison = array( 
										"phrase" => "<strong>Bestellungen, die uns bis zum 4. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>",
										//"phrase" => "Eine Lieferung bis Weihnachten – Nur noch wenige Tage *",
										"detail" => "",
										//"detail" => "* Bestellungen, die uns bis zum <strong>8. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "04/12/2018";
		break;
	case "O" :
		$livraison = array( 
										"phrase" => "<strong>Bestellungen, die uns bis zum 4. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>",
										//"phrase" => "Eine Lieferung bis Weihnachten – Nur noch wenige Tage *",
										"detail" => "",
										//"detail" => "* Bestellungen, die uns bis zum <strong>8. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "04/12/2018";
		break;
		
	case "SA" :
		$livraison = array( 
										"phrase" => "<strong>Bestellungen, die uns bis zum 3. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>",
										//"phrase" => "Eine Lieferung bis Weihnachten – Nur noch wenige Tage *",
										"detail" => "",
										//"detail" => "* Bestellungen, die uns bis zum <strong>7. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "03/12/2018";
		break;

	case "G" :
            $livraison = array(
                "phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>",
                //"phrase" => "Delivery before Christmas: very last days *",
                "detail" => "",
                //"detail" => "* Delivery guaranteed before Christmas for all orders placed before <strong>December the 7<sup>th</sup></strong>",
                "style" => "font-size: 12px;",
                "styledetail" => ""
            );
            $datelivraison = "9/12/2018";
            break;
	case "I" :
		$livraison = array( 
										"phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 2nd of December.</strong>",
										//"phrase" => "Delivery before Christmas: very last days *",
										"detail" => "",
										//"detail" => "* Delivery guaranteed before Christmas for all orders placed before <strong>December the 7<sup>th</sup></strong>",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "02/12/2018";
		break;
		
	case "H" :
		$livraison = array( 
										"phrase" => "<strong>Order before the 3rd of March for a delivery from the 14th of March onwards.</strong>",
										//"phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>",
										//"phrase" => "<strong>Please make sure to order before the 6th of November at the latest for a delivery from the 17th onward!</strong>",
										//"phrase" => "<strong>In order to guarantee the integrity of our wines, the next shipment to Hong Kong will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>",
										"detail" => "",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$livraison2 = array( 
										"phrase" => "<strong>Order before the 31st of March for a delivery from the 10th of April onwards.</strong>",
										//"phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 6th of December.</strong>",
										//"phrase" => "<strong>Please make sure to order before the 6th of November at the latest for a delivery from the 17th onward!</strong>",
										//"phrase" => "<strong>In order to guarantee the integrity of our wines, the next shipment to Hong Kong will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>",
										"detail" => "",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "03/03/2019";
		break;
		
	case "SG" :
		$livraison = array( 
										"phrase" => "<strong>Order before the 3rd of March for a delivery from the 14th of March onwards.</strong>",
										//"phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>",
										//"phrase" => "<strong>Please make sure to order before the 6th of November at the latest for a delivery from the 21st onward!</strong>",
										//"phrase" => "<strong>In order to guarantee the integrity of our wines, the next shipment to Singapore will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>",
										"detail" => "",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$livraison2 = array( 
										"phrase" => "<strong>Order before the 31st of March for a delivery from the 10th of April onwards.</strong>",
										//"phrase" => "<strong>Delivery before Christmas guaranteed for orders received before the 6th of December.</strong>",
										//"phrase" => "<strong>Please make sure to order before the 6th of November at the latest for a delivery from the 17th onward!</strong>",
										//"phrase" => "<strong>In order to guarantee the integrity of our wines, the next shipment to Hong Kong will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>",
										"detail" => "",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "03/03/2019";
		break;

	case "Y" :
		$livraison = array( 
										"phrase" => "<strong>Per gli ordini effettuati prima del 6 dicembre, Millésima garantisce la consegna entro Natale.</strong>",
										//"phrase" => "",
										"detail" => "",
										//"detail" => "Solo gli ordini ricevuti prima del 4 dicembre 2015 saranno assicurati della consegna entro Natale.",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "06/12/2018";
		break;

	case "E" :
		$livraison = array( 
										"phrase" => "<strong>Se entregarán antes de Navidad los pedidos recibidos antes del 6 de diciembre de 2018</strong>",
										//"phrase" => "",
										"detail" => "",
										//"detail" => "Se entregarán antes de Navidad los pedidos recibidos antes del 4 de diciembre de 2015",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "06/12/2018";
		break;

	case "P" :
		$livraison = array( 
										"phrase" => "<strong>As encomendas recebidas antes de 9 de Dezembro de 2018 serão entregues antes do Natal.</strong>",
										//"phrase" => "",
										"detail" => "",
										//"detail" => "* As encomendas recebidas antes de <strong>7 de Dezembro de 2015</strong> ser&atilde;o entregues antes do Natal.",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "09/12/2018";
		break;

	case "U" :
		$livraison = array( 
										"phrase" => "",
										//"phrase" => "",
										"detail" => "",
										//"detail" => "",
										"style" => "font-size: 12px;",
										"styledetail" => ""
						);
		$datelivraison = "01/01/2017";
		break;

}

$oSmarty->assign('livraison', $livraison);
$oSmarty->assign('datelivraison', $datelivraison);
if ($country == 'H' or $country == 'SG'){
	$oSmarty->assign('livraison2', $livraison2);
	
}


?>