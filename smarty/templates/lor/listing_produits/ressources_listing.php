<?php
/**
 * Données fixes necessaires aux listings promo 
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */
 
 switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
	case "D" :
	case "O" :
	case "SA" :
	case "G" :
	case "I" :
	case "H" :
	case "SG" :
	case "Y" :
	case "E" :
	case "P" :
	case "U" :
	default :
		$promos_listing = array ("5" => array (
																"rep" => "promo_5",
																"titre" => "33% Off Any 3 Cases Or More",
																"desc" => "Pick <strong>any 3 cases</strong> or more in this curated selection and get <strong>33% off*</strong>. This special offer is limited in time. The Millesima team has selected several legendary wines from <strong>".$appellationshtml."</strong>, including <strong>".$nomshtml."</strong> and a lot more! All the select wines are Pre-Arrivals which means that they are currently stored in pristine conditions in our Parent company cellar in Bordeaux, France and the delivery of those wines will be in three to six months.",
																"url" => "promo-5.html",
																"btn" => "dcvslc",
																"ast" => "*Offer valid on select items"
															),
								"125" => array (
																"rep" => "promo_125",
																"titre" => "20% Off Any 2 Cases Or More",
																"desc" => "The Millesima team has worked on a Bordeaux cases special offer that grants 20% off* any 2 cases bought among a beautiful selection of legendary Bordeaux wines, such as <strong>".$nomshtml."</strong> and a lot more. These wines are Pre-Arrivals, which means that they are sourced directly from states and are currently stored in pristine conditions in our parent company cellar in Bordeaux, France and the delivery of those wines will be in three to six months. So, what are your favorites ?",
																"url" => "promo-125",
																"btn" => "chfav",
																"ast" => "*Offer valid on select items"
															),
								"pre-arrivals" => array (
																"rep" => "pre-arrivals",
																"titre" => "Very Special Prices",
																"desc" => "All the select wines are Pre-Arrivals which means that they are currently stored in pristine conditions in our Parent company cellar in Bordeaux, France and the delivery of those wines will be in three to six months.",
																"url" => "",
																"btn" => "mrsp",
																"ast" => "*Offer valid on select items"
															),
								"instock" => array (
																"rep" => "instock",
																"titre" => "Very Special Prices",
																"desc" => "We have worked on an extensive <strong>selection of ready for delivery wines,</strong> currently stored in our <strong>New York shop,</strong> so that you can <strong>enjoy right now some of the best ".$appellationshtml."...</strong> in a <strong>special offer!</strong>",
																"url" => "",
																"btn" => "mrsp",
																"ast" => "*Offer valid on select items"
															)
						);
		break;
	
}
/* traitement effectue ensuite dans millesima_message_template si listing promo choisi */
 
 ?>