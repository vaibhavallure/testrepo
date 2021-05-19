<?php
/**
 * Ressources concernant la livraison avant Noel
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
			$fdpofferts = array( "phrase" => "*Hors vins primeurs - En une seule fois à une seule adresse en France métropolitaine. Valable jusqu’au {$datevalide} inclus.",
											"style" => ""
							);
			break;

		case "B" :
			$fdpofferts = array( "phrase" => "*Hors vins primeurs - En une seule fois à une seule adresse en Belgique. Valable jusqu’au {$datevalide} inclus.",
											"style" => ""
							);
			break;

		case "L" :
			$fdpofferts = array( "phrase" => "*Hors vins primeurs - En une seule fois à une seule adresse au Luxembourg. Valable jusqu’au {$datevalide} inclus.",
											"style" => ""
							);
			break;

		case "SF" :
			$fdpofferts = array( "phrase" => "*Hors vins primeurs - En une seule fois à une seule adresse en Suisse. Valable jusqu’au {$datevalide} inclus.",
											"style" => ""
							);
			break;

		case "D" :
			$fdpofferts = array( "phrase" => "*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Deutschland.",
											"style" => ""
							);
			break;

		case "O" :
			$fdpofferts = array( "phrase" => "*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Österreich.",
											"style" => ""
							);
			break;

		case "SA" :
			$fdpofferts = array( "phrase" => "*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in der Schweiz. Gültig bis {$datevalide}.",
											"style" => ""
							);
			break;

		case "G" :
			$fdpofferts = array( "phrase" => "*Excluding en primeur wines. Free delivery on one order to one address - Valid till {$datevalide}.",
											"style" => ""
							);
			break;

		case "I" :
			$fdpofferts = array( "phrase" => "*Excluding en primeur wines. Free delivery on one order to one address - Valid till {$datevalide}.",
											"style" => ""
							);
			break;

		case "H" :
		case "SG" :
			$fdpofferts = array( "phrase" => "*Excluding en primeur wines. Free delivery on one order to one address",
											"style" => ""
							);
			break;

		case "Y" :
			$fdpofferts = array( "phrase" => "*Non riguarda i vini primeur. Per un'unica consegna ad un solo indirizzo.",
											"style" => ""
							);
			break;

		case "E" :
			$fdpofferts = array( "phrase" => "*No incluye los vinos en primeur. Para una sola expedición a una única dirección.",
											"style" => ""
							);
			break;

		case "P" :
			$fdpofferts = array( "phrase" => "*Fora dos vinhos primeurs, entrega numa morada em Portugal Continental. Válida até {$datevalide}.",
											"style" => ""
							);
			break;

		case "U" :
			$fdpofferts = array( "phrase" => "",
											"style" => ""
							);
			break;
	}
switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$conditionvalidite = array( "phrase" => "*valable jusqu’au {$datevalide} inclus",
										"style" => ""
						);
		break;

	case "D" :
	case "O" :
	case "SA" :
		$conditionvalidite = array( "phrase" => "*Angebot gültig für eine Auswahl an Produkten mit limitiertem Lagerbestand von 23.01.16 bis 25.01.16",
										"style" => ""
						);
		break;

	case "G" :
	case "I" :
	case "H" :
	case "SG" :
		$conditionvalidite = array( "phrase" => "*Special offer valid on a selection of product in limited availability from the 23.01.2016 to the 25.01.2016 included.",
										"style" => ""
						);
		break;

	case "Y" :
		$conditionvalidite = array( "phrase" => "*Offerta valida per una selezione di vini in quantità limitata dal 23/01/2016 fino al 25/01/2016",
										"style" => ""
						);
		break;

	case "E" :
		$conditionvalidite = array( "phrase" => "*Oferta válida en una selección de vinos en cantidad limitada desde el 23/01/2016 hasta el 25/01/16",
										"style" => ""
						);
		break;

	case "P" :
		$conditionvalidite = array( "phrase" => "* Promoção válida sobre uma selecção de produtos,  em quantidade limitada do 23/01/16 ao 25/01/16 incluído.",
										"style" => ""
						);
		break;

	case "U" :
		$fdpofferts = array( "phrase" => "",
										"style" => ""
						);
		$conditionvalidite = array( "phrase" => "",
										"style" => ""
						);
		break;

}
switch ($country) {
	case "F" :
	case "B" :
	case "L" :
		$validdefaut = "*Offre valable dans la limite des stocks disponibles";
		$offexc = array( "explications" => "",
									"valid" => "*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.",
									//"valid" => "*Offre valable une fois jusqu'au {$datevalide} inclus sur l'ensemble du site (hors promotions et vins primeurs)",
									//"valid" => "*Hors promotion en cours et hors vins en primeurs. Valable jusqu'au {$datevalide}.",
									//"valid" => "*Chaque oeuvre dispose de son propre certificat d'authenticité.",
									//"valid" => "*Offre valable une seule fois par client à partir de 400€ de vins livrables (hors vins primeurs), dans la limite des stocks disponibles",
									//"valid" => "*Hors vins primeurs - Valable jusqu'au 31/08/2018",
									//"valid" => "*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.",
									//"valid" => "*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.",
									//"valid" => "*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.",
									//"valid" => "*Quantités extrêmement limitées",
									"style" => ""
						);
		
		break;
	case "SF" :
		$validdefaut = "*Offre valable dans la limite des stocks disponibles";
		$offexc = array( "explications" => "",
									"valid" => "*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.",
									//"valid" => "*Offre valable une fois jusqu'au {$datevalide} inclus sur l'ensemble du site (hors promotions et vins primeurs)",
									//"valid" => "*Hors promotion en cours et hors vins en primeurs. Valable jusqu'au {$datevalide}.",
									//"valid" => "*Chaque oeuvre dispose de son propre certificat d'authenticité.",
									//"valid" => "*Offre valable une seule fois par client à partir de CHF 450 de vins livrables (hors vins primeurs), dans la limite des stocks disponibles",
									//"valid" => "*Hors vins primeurs - Valable jusqu'au 31/08/2018",
									//"valid" => "*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.",
									//"valid" => "*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.",
									//"valid" => "*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.",
									//"valid" => "*Quantités extrêmement limitées",
									"style" => ""
						);
		break;

	case "D" :
	case "O" :
		$validdefaut = "*Angebot gültig, solange der Vorrat reicht";
		$offexc = array( "explications" => "",
									"valid" => "*Angebot gültig, solange der Vorrat reicht, nicht kumulierbar mit anderen Aktionen, Subskriptionen ausgeschlossen.",
									//"valid" => "*Angebot gütlig einmalig bis zum {$datevalide} auf unsere vollständige Internetseite, ausgenommen sind bereits rabattierte Produkte und Primeursubskriptionen.",
									//"valid" => "*ausgeschlossen sind Subskriptionsweine und bereits reduzierte Weine. Dieses Angebot ist bis zum {$datevalide} Gültig.",
									//"valid" => "*Jedes Werk hat ein Echtheitszertifikat.",
									//"valid" => "*Angebot gültig für einmalige Anwendung ab 400€ lieferbarem Wein (außer Subskriptionsweine) - solange der Vorrat reicht",
									//"valid" => "*Angebot gültig für Bestellungen ab 300 €, nur lieferbare Weine, Subskriptionen ausgeschlossen und nur für die 50 ersten Bestellungen",
									//"valid" => "*Angebot gültig für Mischkisten, ausgenommen Primeurweine und bereits bestehende Sonderangebote!",
									//"valid" => "*ausgenommen sind bereits reduzierte Weine, Subskriptionen 2016 und 2017.",
									//"valid" => "*Sehr begrenzte Mengen und lieferbar solange der Vorrat reicht. Einige Weine lagern direkt im Weingut. Die Lieferung in unsere Weinkeller erfolgt innerhalb von 45 Tagen, dazu kommt die übliche Lieferzeit zu Ihnen.",
									//"valid" => "*Mengen extrem limitiert",
									"style" => ""
						);
		break;
	case "SA" :
		$validdefaut = "*Angebot gültig, solange der Vorrat reicht";
		$offexc = array( "explications" => "",
									"valid" => "*Angebot gültig, solange der Vorrat reicht, nicht kumulierbar mit anderen Aktionen, Subskriptionen ausgeschlossen.",
									//"valid" => "*Angebot gütlig einmalig bis zum {$datevalide} auf unsere vollständige Internetseite, ausgenommen sind bereits rabattierte Produkte und Primeursubskriptionen.",
									//"valid" => "*ausgeschlossen sind Subskriptionsweine und bereits reduzierte Weine. Dieses Angebot ist bis zum {$datevalide} Gültig.",
									//"valid" => "*Jedes Werk hat ein Echtheitszertifikat.",
									//"valid" => "*Angebot gültig für einmalige Anwendung ab CHF 450 lieferbarem Wein (ausser Subskriptionsweine) - solange der Vorrat reicht",
									//"valid" => "*Primeurweine ausgeschlossen - Gültig bis zum 31/08/2018",
									//"valid" => "*Angebot gültig für Mischkisten, ausgenommen Primeurweine und bereits bestehende Sonderangebote!",
									//"valid" => "*ausgenommen sind bereits reduzierte Weine, Subskriptionen 2016 und 2017.",
									//"valid" => "*Sehr begrenzte Mengen und lieferbar solange der Vorrat reicht. Einige Weine lagern direkt im Weingut. Die Lieferung in unsere Weinkeller erfolgt innerhalb von 45 Tagen, dazu kommt die übliche Lieferzeit zu Ihnen.",
									//"valid" => "*Mengen extrem limitiert",
									"style" => ""
						);

		break;

	case "G" :
		$validdefaut = "*According to availability";
		$offexc = array( "explications" => "",
									"valid" => "*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.",
									//"valid" => "*Offer valid on one purchase placed on or before {$datevalide}. Does not include en primeur or previously discounted wines.",
									//"valid" => "*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.",
									//"valid" => "*Each limited edition case comes with a certificate of authenticity.",
									//"valid" => "*Offer valid once per customer on an order of £ 280.00 or more (not including en primeur wines), according to availability",
									//"valid" => "*Excluding en primeur wines. Valid through 31/08/18",
									//"valid" => '*Valid only on our mixed "my own tasting cases." Not combinable with other promotional offers and exclusive of en primeur wines',
									//"valid" => '* Excluding previously discounted wines, 2016 and 2017 en primeur wines.',
									//"valid" => '*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.',
									//"valid" => '*Extremely limited quantities available',
									"style" => ""
						);
		break;
	case "I" :
		$validdefaut = "*According to availability";
		$offexc = array( "explications" => "",
									"valid" => "*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.",
									//"valid" => "*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.",
									//"valid" => "*Each limited edition case comes with a certificate of authenticity.",
									//"valid" => "*Offer valid once per customer on an order of € 400.00 or more (not including en primeur wines), according to availability",
									//"valid" => "*Excluding en primeur wines. Valid through 31/08/18",
									//"valid" => '*Valid only on our mixed "my own tasting cases." Not combinable with other promotional offers and exclusive of en primeur wines',
									//"valid" => '* Excluding previously discounted wines, 2016 and 2017 en primeur wines.',
									//"valid" => '*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.',
									//"valid" => '*Extremely limited quantities available',
									"style" => ""
						);
		break;
	case "H" :
		$validdefaut = "*According to availability";
		$offexc = array( "explications" => "",
									"valid" => "*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.",
									//"valid" => "*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.",
									//"valid" => "*Each limited edition case comes with a certificate of authenticity.",
									//"valid" => "*Minimum order of HKD 4,000.00. Not cumulative with other promotional codes. Not valid on en primeur or previously discounted wines.",
									//"valid" => "*Offer valid once per customer on an order of HK$ 3000.00 or more (not including en primeur wines), according to availability",
									//"valid" => "*Offer valid on an order of HKD 2800.00 or more (not including en primeur or previously discounted wines. Not combinable with other promotional codes).",
									//"valid" => '*Valid only on our mixed "my own tasting cases." Not combinable with other promotional offers and exclusive of en primeur wines',
									//"valid" => '* Excluding previously discounted wines, 2016 and 2017 en primeur wines.',
									//"valid" => '*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.',
									//"valid" => '*Extremely limited quantities available',
									"style" => ""
						);
		break;
	case "SG" :
		$validdefaut = "*According to availability";
		$offexc = array( "explications" => "",
									"valid" => "*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.",
									//"valid" => "*Offer valid on one purchase placed on or before {$datevalide}. Does not include en primeur or previously discounted wines.",
									//"valid" => "*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.",
									//"valid" => "*Each limited edition case comes with a certificate of authenticity.",
									//"valid" => "*Minimum order of SGD 650.00. Not cumulative with other promotional codes. Not valid on en primeur or previously discounted wines.",
									//"valid" => "*Offer valid once per customer on an order of SGD 600.00 or more (not including en primeur wines), according to availability",
									//"valid" => "*Offer valid on an order of SGD 500.00 or more (not including en primeur or previously discounted wines. Not combinable with other promotional codes).",
									//"valid" => '*Valid only on our mixed "my own tasting cases." Not combinable with other promotional offers and exclusive of en primeur wines',
									//"valid" => '* Excluding previously discounted wines, 2016 and 2017 en primeur wines.',
									//"valid" => '*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.',
									//"valid" => '*Extremely limited quantities available',
									"style" => ""
						);
		break;

	case "Y" :
		$validdefaut = "*Offerta valida nel limite degli stock disponibili";
		$offexc = array( "explications" => "",
									"valid" => "*Offerta a disponibilità limitata, escluse le promozioni in corso e i vini in primeurs.",
									//"valid" => "*Offerta valida una sola volta e fino al {$datevalide} incluso su tutto il sito (salvo vini già in promozione e vini primeurs)",
									//"valid" => "*Codice non valido per i vini già in promozione e per i vini primeurs. Offerta valida fino al {$datevalide}.",
									//"valid" => "*Ogni opera ha il suo proprio certificato di autenticità.",
									//"valid" => "*Offerta valida a partire da 300€ di spesa, esclusi vini Primeurs, nel limite degli stock disponibili.",
									//"valid" => "*Offerta valida a partire da 300 € di vini consegnabili (promozioni e vini in primeurs esclusi)",
									//"valid" => "*Offerta valida solo sulle casse miste, non cumilabile con altre promozioni ed escllusi i vini in primeurs.",
									//"valid" => "*Non riguarda i secondi vin già in offerta, i primeurs 2016 e 2017.",
									//"valid" => "*In quantità molto limitata e secondo disponibilità. Alcuni di questi vini si trovano nelle cantine della proprietà. La consegna sarà effettuta in 45 giorni a partire dalla nostra cantina + il tempo necessario per ogni Paese.",
									//"valid" => "*In quantità estremamente limitata.",
									"style" => ""
						);
		break;

	case "E" :
		$validdefaut = "*Ofertas válidas en el límite de las existencias disponibles";
		$offexc = array( "explications" => "",
									"valid" => "*Oferta válida en el límite de las existencias disponibles, no se refiere a los vinos en oferta especial o en primeurs.",
									//"valid" => "*Oferta válida una sóla vez hasta el {$datevalide} en todo el sito (no se refiere a los vinos en oferta especial o en primeurs).",
									//"valid" => "*No se refiere a las ofertas especiales o los vinos en primeurs. Oferta válida hasta el {$datevalide}.",
									//"valid" => "*Cada obra tiene su propio certificado de autenticidad.",
									//"valid" => "*Oferta válida una sola vez por cliente a partir de 300 € de vinos listos para la entrega (no se refiere a los vinos en primeurs), en el límite de las existencias disponibles.",
									//"valid" => "*Oferta válida a partir de 300€ de vinos disponibles para la entrega (no se refiere a los vinos en primeurs o en oferta especial).",
									//"valid" => "*Sólo válido en las cajas mixtas, no se refiere a los vinos en primeurs.",
									//"valid" => "*No se refiere a vinos en oferta especial, vinos en primeurs 2016 y 2017.",
									//"valid" => "*Cantidades muy limitadas y dependiendo de las existencias disponibles. Ciertos vinos están almacenados en la bodega del château. La entrega en nuestra bodega se efectuará en un plazo de 45 días y tendremos que añadir el plazo de entrega según la dirección de entrega.",
									//"valid" => "*Cantidad muy limitada.",
									"style" => ""
						);
		break;

	case "P" :
		$validdefaut = "*Oferta valida de acordo com o estoque disponível";
		$offexc = array( "explications" => "",
									"valid" => "*Oferta válida limitada aos stocks existentes, e não aplicável aos vinhos em promoção ou em Primeur.",
									//"valid" => "* Oferta válida até {$datevalide}, uma vez em todo o site (excluindo promoções e vinhos jovens)",
									//"valid" => "*Promoção não cumulativa com qualquer outra promoção em vigor e fora dos vinhos em primeurs. Oferta válida até {$datevalide}.",
									//"valid" => "*Cada obra possui o seu próprio certificado de autenticidade.",
									//"valid" => "*Oferta válida para encomenda a partir de 400 euros de vinhos disponíveis (com exclusão dos vinhos em primeurs), de acordo com o stock disponível.",
									//"valid" => "* Oferta válida a partir de 300 € de vinhos disponíveis (excluindo vinhos em promoção e vinhos em primeurs)",
									//"valid" => "*applicàvel unicamente para as caixas personalizadas e fora dos primeurs e dos vinhos já em promoção.",
									//"valid" => "*fora dos vinhos já em promoção e dos primeurs 2016 e 2017.",
									//"valid" => "* Quantidades muito limitadas e sob reserva de disponibilidade. Alguns vinhos são armazenados na adega do Château Yquem, a entrega será prolongada de 45 dias, à confirmar.",
									//"valid" => "* Quantidades extremamente limitadas",
									"style" => ""
						);
		break;

	case "U" :
		$validdefaut = "*Offer valid on select items only";
		$offexc = array( "explications" => "",
									//"valid" => "*Offer valid on select items.",
									"valid" => "*This offer is valid on select items. Please also note that we currently advise against shipping wine in some areas of the country due to freezing temperatures. Exposure to below freezing temperatures could harm the wine during transit. We are happy to hold your order until temperature becomes moderate, but please be aware this may not be until the spring.",
									//"valid" => "*Offer valid on select items. Millesima will hold wines until weather permitted if needed.",
									//"valid" => "*Offer valid from 06/30/2018 to 07/02/2018, 11:59 pm. Excluding items already on sale and futures wines. Offer valid once per user only. Not valid on delivery of wooden cases. Millesima will hold wines until weather permitted, if needed.",
									//"valid" => "*Offer valid until Monday, November 27th, 2017,11:59 pm EST. Offer valid on select items only.",
									//"valid" => "*Offer valid on all items excluding futures and on sale items",
									//"valid" => "*Offer valid on select items only. Because of extreme tempertures during the summer, Millesima may advise to hold shipment until the fall.",
									//"valid" => "*Deliveries in the US are scheduled in Spring 2018.",
									//"valid" => "*$100 discount for a minimum purchase of $700 or more. Excluding futures and on sale items. Not combinable with other promo codes. Offer valid from March 30th to April 2nd midnight. Offer valid once per customer.",
									//"valid" => "*Offer valid on one purchase&nbsp;<strong>totaling $500 or more before tax and/or shipping</strong> placed <strong>between July&nbsp;8th and July&nbsp;12th,&nbsp;2017</strong>. Does not include futures or on sale items. Not valid on previous&nbsp;orders.",
									// "valid" => "*To ensure delivery of in-stock items in time for the holidays, orders must be placed no later than Sunday December 18th. For an estimated arrival date to your shipping address, please call us at 212-639-9463.",
									//"valid" => "*Terms and Conditions for Promotion WELCOMEMAY:<br />The free shipping offer is valid on your orders over $100 or more before taxes, per shipping address. Valid on orders placed this weekend from 04/28/2018 to 04/30/2018. Redeem the offer with promotion code WELCOMEMAY at checkout. Excludes Futures and delivery of original wooden cases. Offer expires at 11:59 p.m. ET, 04/30/18. Offer is not valid on previously purchases. Millesima will hold wines until weather permitted if needed.",
									"style" => ""
						);
		break;

}

$oSmarty->assign('conditionvalidite', $conditionvalidite);
$oSmarty->assign('fdpofferts', $fdpofferts);
$oSmarty->assign('offexc', $offexc);
$oSmarty->assign('validdefaut', $validdefaut);


?>
