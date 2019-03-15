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
		$contacts = array ("conseil" => "Vous avez besoin d'un conseil",
						"noms" => "Contactez <strong>Ariane Bissirier</strong>, <strong>Luce Antunes</strong><br />ou <strong>Pierre Valette</strong>",
						"telephone" => array(array ("label" => "0557 808 808", "href" => "+33557808808")),
						"ouverture" => "Du lundi au vendredi de <strong>9h &agrave; 18h</strong>",
						"emails" => array("conseil@millesima.com")
						);
		break;
		
	case "B" :
		$contacts = array ("conseil" => "Vous avez besoin d'un conseil",
						"noms" => "Contactez <strong>Ariane Bissirier</strong>, <strong>Luce Antunes</strong><br />ou <strong>Pierre Valette</strong>",
						"telephone" => array(array ("label" => "00 800 267 33 289", "href" => "+80026733289")),
						"ouverture" => "Du lundi au vendredi de <strong>9h &agrave; 18h</strong>",
						"emails" => array("conseil@millesima.com")
						);
		break;
		
	case "L" :
		$contacts = array ("conseil" => "Vous avez besoin d'un conseil",
						"noms" => "Contactez <strong>Ariane Bissirier</strong>, <strong>Luce Antunes</strong><br />ou <strong>Pierre Valette</strong>",
						"telephone" => array(array ("label" => "(+352) 20 30 16 31 ", "href" => "+35220301631 ")),
						"ouverture" => "Du lundi au vendredi de <strong>9h &agrave; 18h</strong>",
						"emails" => array("conseil@millesima.com")
						);
		break;
		
	case "SF" :
		$contacts = array ("conseil" => "Vous avez besoin d'un conseil",
						"noms" => "Contactez <strong>Ulrike Treptow</strong> ou <strong>Elodie Kohr</strong>",
						"telephone" => array(array ("label" => "+41 43 550 03 42", "href" => "+41435500342")),
						"ouverture" => "Du lundi au vendredi de <strong>9h &agrave; 17h</strong>",
						"emails" => array("utreptow@millesima.com", "ekohr@millesima.com")
						);
		break;

	case "D" :
		$contacts = array ("conseil" => "Service und Beratung",
						"noms" => "Kontaktieren Sie <strong>Ulrike Treptow</strong> oder <strong>Elodie Kohr</strong>",
						"telephone" => array(array ("label" => "00 800 267 33 289", "href" => "+80026733289")),
						"ouverture" => "Mo-Frei zu B&uuml;rozeiten",
						"emails" => array("utreptow@millesima.com", "ekohr@millesima.com")
						);
		break;
		
	case "O" :
		$contacts = array ("conseil" => "Service und Beratung",
						"noms" => "Kontaktieren Sie <strong>Ulrike Treptow</strong> oder <strong>Elodie Kohr</strong>",
						"telephone" => array(array ("label" => "+43 720 77 59 18", "href" => "+43720775918")),
						"ouverture" => "Mo-Frei zu B&uuml;rozeiten",
						"emails" => array("utreptow@millesima.com", "ekohr@millesima.com")
						);
		break;
		
	case "SA" :
		$contacts = array ("conseil" => "Service und Beratung",
						"noms" => "Kontaktieren Sie <strong>Ulrike Treptow</strong> oder <strong>Elodie Kohr</strong>",
						"telephone" => array(array ("label" => "+41 43 550 03 42", "href" => "+41435500342")),
						"ouverture" => "Mo-Frei zu B&uuml;rozeiten",
						"emails" => array("utreptow@millesima.com", "ekohr@millesima.com")
						);
		break;
		
	case "G" :
		$contacts = array ("conseil" => "Need advice?",
						"noms" => "Feel free to contact <strong>our team</strong>",
						"telephone" => array(array ("label" => "+44 (0) 20 8089 1875", "href" => "+442080891875")),
						"ouverture" => "Monday to Friday from <strong>8 AM to 5&nbsp;PM</strong>",
						"emails" => array("customercare@millesima.com")
						);
		break;
		
	case "I" :
		$contacts = array ("conseil" => "Need advice?",
						"noms" => "Feel free to contact <strong>our team</strong>",
						"telephone" => array(array ("label" => "1 800 55 3393", "href" => "1800553393")),
						"ouverture" => "Monday to Friday from <strong>8 AM to 5&nbsp;PM</strong>",
						"emails" => array("customercare@millesima.com")
						);
		break;
		
	case "H" :
		$contacts = array ("conseil" => "Need advice?",
						"noms" => "Feel free to contact <strong>our team</strong>",
						"telephone" => array(array ("label" => "(852) 5801 0939", "href" => "85258010939")),
						"ouverture" => "Monday to Friday from <strong>3 PM to 11&nbsp;PM</strong>",
						"emails" => array("customercare@millesima.com")
						);
		break;

	case "SG" :
		$contacts = array ("conseil" => "Need advice?",
						"noms" => "Feel free to contact <strong>our team</strong>",
						"telephone" => array(array ("label" => "(65) 3159 1767", "href" => "6531591767")),
						"ouverture" => "Monday to Friday from <strong>3 PM to 11&nbsp;PM</strong>",
						"emails" => array("customercare@millesima.com")
						);
		break;

	case "Y" :
		$contacts = array ("conseil" => "Ha bisogno di un'informazione",
						"noms" => "Chiami <strong>St&eacute;phanie Rocamora</strong>",
						"telephone" => array(array ("label" => "800 781 725", "href" => "+800781725")),
						"ouverture" => "dal luned&igrave; al venerd&igrave; <strong>dalle 9h alle 18h</strong>",
						"emails" => array("srocamora@millesima.com")
						);
		break;

	case "E" :
		$contacts = array ("conseil" => "Necesita un consejo",
						"noms" => "Llame a <strong>St&eacute;phanie Rocamora</strong>",
						"telephone" => array(array ("label" => "900 97 33 42", "href" => "+900973342")),
						"ouverture" => "De lunes a viernes desde las <strong>9h00</strong> <br />hasta las <strong>18h00</strong>",
						"emails" => array("srocamora@millesima.com")
						);
		break;

	case "P" :
		$contacts = array ("conseil" => "Necessita um conselho",
						"noms" => "N&atilde;o hesite em contactar <strong>Luce Antunes</strong>",
						"telephone" => array(array ("label" => "800 833 385", "href" => "+800833385"), 
											 array ("label" => "00 800 267 33 289", "href" => "+80026733289"),
											 array ("label" => "00 33 557 808 846", "href" => "+33557808846")),
						"ouverture" => "Da segunda a sexta-feira <strong>de 9h &agrave;s 17h30</strong>",
						"emails" => array("lantunes@millesima.com")
						);
		break;

	case "U" :
		$contacts = array ("conseil" => "",
						"noms" => "",
						"telephone" => array(array ("label" => "212-639-9463", "href" => "212-639-9463")),
						"ouverture" => "<span class='tel' style='font-size:13px;font-weight:bold;color:#FFFFFF !important;'>1355 2nd Ave, New York, NY 10021</span>",
						"emails" => array("info@millesima.com")
						);
		break;

	default :
		$contacts = array ("conseil" => "Vous avez besoin d'un conseil",
						"noms" => "Contactez <strong>H&eacute;l&egrave;ne Bernard</strong> ou <strong>Ariane Bissirier</strong>",
						"telephone" => array(array ("label" => "0557 808 808", "href" => "+33557808808")),
						"ouverture" => "Du lundi au vendredi de <strong>9h &agrave; 18h</strong>",
						"emails" => array("hbernard@millesima.com","abissirier@millesima.com")
						);
		break;
	
}

$oSmarty->assign('tabcontacts', $contacts);

?>