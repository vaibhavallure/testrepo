<?php
/**
 * Traitement du module push
 * Inseré dans le traitement général
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du push<br />";

$oSmarty->assign('typepush', $_POST['push']);
$oSmarty->assign('push_type_image', $_POST['push_type_image']);

/* changement, tracking finalement sur tous les pays */
/*if ($country == "U"){
	$url = getUrl('push_url', $tracking);
}else {
	$url = getUrl('push_url');
}*/
$url = $this->getUrl('push_url', $tracking, $siteweb, $country);
$oSmarty->assign('push_url', $url);


if(isset($_POST['push_exceptions']) && $_POST['push_exceptions'] and in_array($country, $_POST['push_exceptions_pays'])){
	if(self::DEBUG){
		echo 'pays exception : '.$country.'<br />'; 
	}
	$oSmarty->assign('push_exception', true);
}else{
	$oSmarty->assign('push_exception', false);
}


switch ($langue) {
	case "F" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Nouveauté';
				break;
			case '113' :
				$libelle ='1+1=3 : Achetez 2 caisses identiques, nous vous offrons la 3e !';
				break;
			case 'roses' :
				$libelle ='Ros&eacute;s';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='Primeurs';
				break;
			case 'livraison' :
				$libelle ='Livraison';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Offres Spéciales';
				break;
			case 'message' :
			default:
				$libelle=$objet_alt_title;
				break;
		}
		break;

	case "D" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Neuheiten';
				break;
			case '113' :
				$libelle ='1+1=3: Kaufen Sie 2 Kisten, wir geben Ihnen die 3. gratis hinzu!';
				break;
			case 'roses' :
				$libelle ='Ros&eacute;weine';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='Subskriptionsweine';
				break;
			case 'livraison' :
				$libelle ='Lieferung';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Sonderangebot';
				break;
			case 'message' :
			default:
				$libelle =$objet_alt_title;
				break;
		}
		break;

	case "G" :
	case "U" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Newcomer';
				break;
			case '113' :
				$libelle ='1+1=3 : Buy 2 identical cases, we offer you the third!';
				break;
			case 'roses' :
				$libelle ='Ros&eacute;s';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='En-primeurs';
				break;
			case 'livraison' :
				$libelle ='Delivery';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Special Offers';
				break;
			case 'message' :
			default:
				$libelle =$objet_alt_title;
				break;
		}
		break;

	case "Y" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Novità';
				break;
			case '113' :
				$libelle ='1+1=3 : Acquistate 2 casse dello stesso vino, noi vi regaliamo la 3a !';
				break;
			case 'roses' :
				$libelle ='Rosati';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='Primeurs';
				break;
			case 'livraison' :
				$libelle ='Consegna';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Offerte Speciali';
				break;
			case 'message' :
			default:
				$libelle =$objet_alt_title;
				break;
		}
		break;

	case "E" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Novedad';
				break;
			case '113' :
				$libelle ='1+1=3 : Compre 2 cajas del mismo vino, le regalamos la tercera caja';
				break;
			case 'roses' :
				$libelle ='Rosados';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='Primeurs';
				break;
			case 'livraison' :
				$libelle ='Gastos de envío';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Ofertas Especiales';
				break;
			case 'message' :
			default:
				$libelle =$objet_alt_title;
				break;
		}
		break;

	case "P" :
		switch ($_POST['push']){
			case 'nouveaute' :
				$libelle ='Novidade';
				break;
			case '113' :
				$libelle =' 1+1=3 : Compre 2 caixas de vinhos, mas leve 3 !';
				break;
			case 'roses' :
				$libelle ='Ros&eacute;s';
				break;
			case 'primeurs' :
			case 'primeurs_sorties' :
			case 'primeurs_pdf' :
				$libelle ='Primeurs';
				break;
			case 'livraison' :
				$libelle ='Transporte';
				break;
			case 'champagne' :
				$libelle ='Champagne';
				break;
			case 'offspe' :
				$libelle ='Ofertas Especiais';
				break;
			case 'message' :
			default:
				$libelle =$objet_alt_title;
				break;
		}

		break;
}
/*cas des US pour les primeurs : Futures */

if ($country == "U" && ($_POST['push'] == "primeurs" OR $_POST['push'] == 'primeurs_sorties' OR $_POST['push'] == 'primeurs_pdf')){
	$libelle ='Futures';
}

$oSmarty->assign('libellepush', $libelle);

?>