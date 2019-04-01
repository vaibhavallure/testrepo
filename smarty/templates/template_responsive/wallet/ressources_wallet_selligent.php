<?php
/**
 * Traitement du module wallet
 * Inser&eacute; dans le traitement g&eacute;n&eacute;ral quand le module est activ&eacute;
 * Faire attention à une cr&eacute;ation de l'objet $oSmarty pr&eacute;alablement dans le traitement g&eacute;n&eacute;ral
 *
 * Variables d&eacute;clar&eacute;es pr&eacute;alablement dans traitement g&eacute;n&eacute;ral :
 *		$country
 * 
 * @author  Aurelie Lopes pour Millesima
 */

switch ($country) {
	case "F" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/fr-FR/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "text" => "T&eacute;l&eacute;charger ma carte Mill&eacute;sima dans mon mobile"
            );
    break;
	case "B" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/fr-BE/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "text" => "T&eacute;l&eacute;charger ma carte Mill&eacute;sima dans mon mobile"
            );
    break;
	case "L" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/fr-LU/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "text" => "T&eacute;l&eacute;charger ma carte Mill&eacute;sima dans mon mobile"
            );
    break;
	case "SF" :
		$wallet = array( "link" => "https://millesima.captainwallet.com/fr-CH/Millesima-fid?user[identifier]=~ID_AVA~",
							"title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
							"text" => "T&eacute;l&eacute;charger ma carte Mill&eacute;sima dans mon mobile"
						);
		break;

	case "D" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/de-DE/Millesima-fid?user[identifier]=~ID_AVA~",
            "title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
          );
    break;
	case "O" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/de-AT/Millesima-fid?user[identifier]=~ID_AVA~",
            "title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
          );
    break;
	case "SA" :
		$wallet = array( "link" => "https://millesima.captainwallet.com/de-CH/Millesima-fid?user[identifier]=~ID_AVA~",
							"title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
							"text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
						);
		break;

	case "G" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-GB/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "H" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-HK/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "I" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-IE/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "SG" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-SG/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;

	case "Y" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/it-IT/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Salva le offerte Mill&eacute;sima sul mio cellulare",
              "text" => "Salva le offerte Mill&eacute;sima sul mio cellulare"
            );
    break;

	case "E" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/es-ES/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Guarde las ofertas de Mill&eacute;sima en su m&oacute;vil",
              "text" => "Guarde las ofertas de Mill&eacute;sima en su m&oacute;vil"
            );
    break;

	case "P" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/pt-PT/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Registrar as ofertas Mill&eacute;sima no seu telem&oacute;vel",
              "text" => "Registrar as ofertas Mill&eacute;sima no seu telem&oacute;vel"
            );
    break;
	
	case "U" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-US/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	
}

$oSmarty->assign('wallet', $wallet);


?>