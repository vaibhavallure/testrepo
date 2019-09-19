<?php
/**
 * Traitement du module menu
 * Inseré dans le traitement général quand le module est activé /TO DO/
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du menu du message<br />";


switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$menu = array ("tous" => array ("nom" => "Tous nos vins", 
											"url" => $this->getTracking($siteweb."tous-nos-vins.html", $tracking)
										),
						"primeurs" => array ("nom" => "Primeurs", 
											"url" => $this->getTracking($siteweb."tous-nos-primeurs.html", $tracking)
											),
						"offspe" => array ("nom" => "Offres Speciales", 
											"url" => $this->getTracking($siteweb."offres-speciales.html", $tracking)
											)
						);
		break;

	case "D" :
	case "O" :
	case "SA" :
		$menu = array ("tous" => array ("nom" => "Unsere Weine", 
											"url" => $this->getTracking($siteweb."unsere-weine.html", $tracking)),
						"primeurs" => array ("nom" => "Subskriptionsweine", 
											"url" => $this->getTracking($siteweb."subskriptionsweine.html", $tracking)),
						"offspe" => array ("nom" => "Sonderangebote", 
											"url" => $this->getTracking($siteweb."sonderangebote.html", $tracking))
						);
		break;
		
	case "G" :
	case "I" :
	case "H" :
		$menu = array ("tous" => array ("nom" => "All our fine wines", 
											"url" => $this->getTracking($siteweb."our-fine-wines.html", $tracking)),
						"primeurs" => array ("nom" => "En-Primeur", 
											"url" => $this->getTracking($siteweb."en-primeur-wines.html", $tracking)),
						"offspe" => array ("nom" => "Special offers", 
											"url" => $this->getTracking($siteweb."special-offers.html", $tracking))
						);
		break;

	case "Y" :
		$menu = array ("tous" => array ("nom" => "Tutti i nostri vini", 
											"url" => $this->getTracking($siteweb."grandi-vini.html", $tracking)),
						"primeurs" => array ("nom" => "In-primeur", 
											"url" => $this->getTracking($siteweb."vini-in-primeurs.html", $tracking)),
						"offspe" => array ("nom" => "Offerte speciali", 
											"url" => $this->getTracking($siteweb."offerte-speciali.html", $tracking))
						);
		break;

	case "E" :
		$menu = array ("tous" => array ("nom" => "Todos los vinos", 
											"url" => $this->getTracking($siteweb."grandes-vinos.html", $tracking)),
						"primeurs" => array ("nom" => "En-primeur", 
											"url" => $this->getTracking($siteweb."vinos-en-primeurs.html", $tracking)),
						"offspe" => array ("nom" => "Ofertas especiales", 
											"url" => $this->getTracking($siteweb."ofertas-especiales.html", $tracking))
						);
		break;

	case "P" :
		$menu = array ("tous" => array ("nom" => "Todos os vinhos", 
											"url" => $this->getTracking($siteweb."grandes-vinhos.html", $tracking)),
						"primeurs" => array ("nom" => "primeurs", 
											"url" => $this->getTracking($siteweb."vinho-em-primeurs.html", $tracking)),
						"offspe" => array ("nom" => "Ofertas especiais", 
											"url" => $this->getTracking($siteweb."em-destaque.html", $tracking))
						);
		break;

	case "U" :
		$menu = array ("tous" => array ("nom" => "All our fine wines", 
											"url" => $this->getTracking($siteweb."our-fine-wines.html", $tracking)),
						"primeurs" => array ("nom" => "Futures", 
											"url" => $this->getTracking($siteweb."futures.html", $tracking)),
						"offspe" => array ("nom" => "About", 
											"url" => $this->getTracking($siteweb."about-us.html", $tracking))
						);
		break;
		
	case "SG" :
		$menu = array ("tous" => array ("nom" => "All our fine wines", 
											"url" => $this->getTracking($siteweb."our-fine-wines.html", $tracking)),
						"primeurs" => array ("nom" => "En-Primeur", 
											"url" => $this->getTracking($siteweb."en-primeur-wines.html", $tracking)),
						"offspe" => array ("nom" => "Special offers", 
											"url" => $this->getTracking($siteweb."special-offers.html", $tracking))
						);
		break;

	default :
		$menu = array ("tous" => array ("nom" => "Tous nos vins", 
											"url" => $this->getTracking($siteweb."tous-nos-vins.html", $tracking)),
						"primeurs" => array ("nom" => "Primeurs", 
											"url" => $this->getTracking($siteweb."tous-nos-primeurs.html", $tracking)),
						"offspe" => array ("nom" => "Offres Speciales", 
											"url" => $this->getTracking($siteweb."offres-speciales.html", $tracking))
						);
		break;
	
}

$oSmarty->assign('tabmenu', $menu);

?>