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
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauF.jpg"
            );
    break;
	case "B" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/fr-BE/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
               "img" => "https://cdn.millesima.com/templates/wallet/bandeauF.jpg"
            );
    break;
	case "L" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/fr-LU/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
               "img" => "https://cdn.millesima.com/templates/wallet/bandeauF.jpg"
            );
    break;
	case "SF" :
		$wallet = array( "link" => "https://millesima.captainwallet.com/fr-CH/Millesima-fid?user[identifier]=~ID_AVA~",
							"title" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
							"text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile",
                            "img" => "https://cdn.millesima.com/templates/wallet/bandeauF.jpg"
						);
		break;

	case "D" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/de-DE/Millesima-fid?user[identifier]=~ID_AVA~",
            "title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "img" => "https://cdn.millesima.com/templates/wallet/bandeauD.jpg"
          );
    break;
	case "O" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/de-AT/Millesima-fid?user[identifier]=~ID_AVA~",
            "title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
            "img" => "https://cdn.millesima.com/templates/wallet/bandeauD.jpg"
          );
    break;
	case "SA" :
		$wallet = array( "link" => "https://millesima.captainwallet.com/de-CH/Millesima-fid?user[identifier]=~ID_AVA~",
							"title" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
							"text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern",
                            "img" => "https://cdn.millesima.com/templates/wallet/bandeauD.jpg"
						);
		break;

	case "G" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-GB/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauG.jpg"
            );
    break;
	case "H" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-HK/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauG.jpg"
            );
    break;
	case "I" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-IE/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile",
               "img" => "https://cdn.millesima.com/templates/wallet/bandeauG.jpg"
            );
    break;
	case "SG" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-SG/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauG.jpg"
            );
    break;

	case "Y" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/it-IT/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Salva le offerte Mill&eacute;sima sul mio cellulare",
              "text" => "Salva le offerte Mill&eacute;sima sul mio cellulare",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauY.jpg"
            );
    break;

	case "E" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/es-ES/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Guarde las ofertas de Mill&eacute;sima en su m&oacute;vil",
              "text" => "Guarde las ofertas de Mill&eacute;sima en su m&oacute;vil",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauE.jpg"
            );
    break;

	case "P" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/pt-PT/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Registrar as ofertas Mill&eacute;sima no seu telem&oacute;vel",
              "text" => "Registrar as ofertas Mill&eacute;sima no seu telem&oacute;vel",
              "img" => "https://cdn.millesima.com/templates/wallet/bandeauP.jpg"
            );
    break;
	
	case "U" :
    $wallet = array( "link" => "https://millesima.captainwallet.com/en-US/Millesima-fid?user[identifier]=~ID_AVA~",
              "title" => "Save offers from Millesima to my mobile",
              "text" => "Save offers from Millesima to my mobile",
               "img" => "https://cdn.millesima.com/templates/wallet/bandeauU.jpg"
            );
    break;
	
}

$oSmarty->assign('wallet', $wallet);


?>