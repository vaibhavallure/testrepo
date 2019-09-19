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
		$contacts = array ("conseil" => "Besoin de conseil ?","conseil2" => "Contactez-nous !",
						"noms" => "Pierre Valette, Luce Antunes et Ariane Bissirier",
						"telephone" => array(array ("label" => "0557 808 808", "href" => "+33557808808")),
						"ouverture" => "Vos conseillers sont disponibles du lundi au jeudi de 9H à 18H et le vendredi de 9H à 17H au 05 57 808 808.",
						"emails" => array("conseil@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_france.jpg")
						);
		break;
		
	case "B" :
		$contacts = array ("conseil" => "Besoin de conseil ?","conseil2" => "Contactez-nous !",
						"noms" => "Pierre Valette, Luce Antunes et Ariane Bissirier",
						"telephone" => array(array ("label" => "(+32) 57 808 808 ", "href" => "+33557808808")),
						"ouverture" => "Vos conseillers sont disponibles du lundi au jeudi de 9H à 18H et le vendredi de 9H à 17H au 05 57 808 808.",
						"emails" => array("conseil@millesima.com"),
                        "image" => ("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_france.jpg")
						);
		break;
		
	case "L" :
		$contacts = array ("conseil" => "Besoin de conseil ?","conseil2" => "Contactez-nous !",
						"noms" => "Pierre Valette, Luce Antunes et Ariane Bissirier",
						"telephone" => array(array ("label" => "(+352) 57 808 808 ", "href" => "+33557808808")),
						"ouverture" => "Vos conseillers sont disponibles du lundi au jeudi de 9H à 18H et le vendredi de 9H à 17H au 05 57 808 808.",
						"emails" => array("conseil@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_france.jpg")
						);
		break;
		
	case "SF" :
		$contacts = array ("conseil" => "Besoin de conseil ?","conseil2" => "Contactez-nous !",
						"noms" => "Ulrike Treptow et Elodie Kohr",
						"telephone" => array(array ("label" => "00800 267 33 289 (numéro gratuit)<br>0033 5 57 808 809 (Ligne directe Liechtenstein)", "href" => "+0080026733289")),
						"ouverture" => "Vos conseillers sont disponibles du lundi au jeudi de 9H à 17H et le vendredi de 9H à 16H.",
						"emails" => array("contact@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_allemagne.jpg")
						);
		break;

	case "D" :
		$contacts = array ("conseil" => "Wir beraten Sie gerne!","conseil2" => "Rufen sie uns uns an",
						"noms" => "Ulrike Treptow und Elodie Kohr",
						"telephone" => array(array ("label" => "00800 267 33 289<br>(kostenlos)", "href" => "+0080026733289")),
						"ouverture" => "Unser Team steht Ihnen von Montag bis Donnerstag 9 Uhr bis 17 Uhr und Freitags 9 Uhr bis 16 Uhr zur Verfügung!",
						"emails" => array("kontakt@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_allemagne.jpg")
						);
		break;
		
	case "O" :
		$contacts = array ("conseil" => "Wir beraten Sie gerne!","conseil2" => "Rufen sie uns uns an",
						"noms" => "Ulrike Treptow und Elodie Kohr",
						"telephone" => array(array ("label" => "00800 267 33 289<br>(kostenlos)", "href" => "+0080026733289")),
						"ouverture" => "Unser Team steht Ihnen von Montag bis Donnerstag 9 Uhr bis 17 Uhr und Freitags 9 Uhr bis 16 Uhr zur Verfügung!",
						"emails" => array("kontakt@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_allemagne.jpg")
						);
		break;
		
	case "SA" :
		$contacts = array ("conseil" => "Wir beraten Sie gerne!","conseil2" => "Rufen sie uns uns an",
						"noms" => "Ulrike Treptow und Elodie Kohr",
						"telephone" => array(array ("label" => "00800 267 33 289 (kostenlos)<br>0033 5 57 808 809<br>(Durchwahl Liechtenstein)", "href" => "+0080026733289")),
						"ouverture" => "Unser Team steht Ihnen von Montag bis Donnerstag 9 Uhr bis 17 Uhr und Freitags 9 Uhr bis 16 Uhr zur Verfügung!",
						"emails" => array("kontakt@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_allemagne.jpg")
						);
		break;
		
	case "G" :
		$contacts = array ("conseil" => "Have a question?","conseil2" => "Contact us!",
						"noms" => "",
						"telephone" => array(array ("label" => "+44 (0) 20 8089 1875", "href" => "+442080891875")),
						"ouverture" => "Our advisors are available from Monday to Friday from 8am to 4pm.",
						"emails" => array("customercare@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_anglais.jpg")
						);
		break;
		
	case "I" :
		$contacts = array ("conseil" => "Have a question?","conseil2" => "Contact us!",
						"noms" => "",
						"telephone" => array(array ("label" => "1 800 55 3393", "href" => "+1800553393")),
						"ouverture" => "Our advisors are available from Monday to Friday from 8am to 4pm.",
						"emails" => array("customercare@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_anglais.jpg")
						);
		break;
		
	case "H" :
		$contacts = array ("conseil" => "Have a question?","conseil2" => "Contact us!",
						"noms" => "",
						"telephone" => array(array ("label" => "(852) 5801 0939", "href" => "+85258010939")),
						"ouverture" => "Our advisors are available from Monday to Friday from 3pm to 11pm.",
						"emails" => array("customercare@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_anglais.jpg")
						);
		break;

	case "SG" :
		$contacts = array ("conseil" => "Have a question?","conseil2" => "Contact us!",
						"noms" => "",
						"telephone" => array(array ("label" => "(65) 3159 1767", "href" => "6531591767")),
						"ouverture" => "Our advisors are available from Monday to Friday from 3pm to 11pm.",
						"emails" => array("customercare@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_anglais.jpg")
						);
		break;

	case "Y" :
		$contacts = array ("conseil" => "Bisogno d'aiuto?","conseil2" => "Contattaci",
						"noms" => "Stéphanie Rocamora",
						"telephone" => array(array ("label" => "+33557808810", "href" => "+33557808810")),
						"ouverture" => "La tua consulente, Stéphanie Rocamora, è disponibile dal lunedì al giovedì dalle 9 alle 18 e il venerdì dalle 9 alle 17, al numero +33 557 808 810.",
						"emails" => array("srocamora@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_espagne.jpg")
						);
		break;

	case "E" :
		$contacts = array ("conseil" => "¿Necesita usted un consejo?","conseil2" => "¡Contacte con nosotros!",
						"noms" => "Stéphanie Rocamora",
						"telephone" => array(array ("label" => "900 97 33 42", "href" => "+900973342")),
						"ouverture" => "Los consejeros están disponibles del lunes hasta el jueves desde 8h30 hasta 17h30 y el viernes desde 8h30 hasta 16h30 en el 00 33 557 808 810",
						"emails" => array("srocamora@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_espagne.jpg")
						);
		break;

	case "P" :
		$contacts = array ("conseil" => "Necessita um conselho ?","conseil2" => "Contacte-nos",
						"noms" => "Luce Antunes",
						"telephone" => array(array ("label" => "+33(0) 557 808 846", "href" => "+33(0) 557 808 846")),
						"ouverture" => "Conselhos personalizados da segunda a terça-feira, através do +33(0)557 808 846",
						"emails" => array("lantunes@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_portugal.jpg")
						);
		break;

	case "U" :
		$contacts = array ("conseil" => "Have a question?","conseil2" => "Contact us!",
						"noms" => "Millesima Team",
						"telephone" => array(array ("label" => "+1 212-639-9463", "href" => "+1 212-639-9463")),
						"ouverture" => "Our wine team is available Monday through Saturday 11am to 8pm.",
						"emails" => array("info@millesima.com"),
                        "image" =>("http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/contact_usa.jpg")
						);
		break;

}

$oSmarty->assign('tabcontacts', $contacts);

?>