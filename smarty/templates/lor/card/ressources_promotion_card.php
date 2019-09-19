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
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_F.jpg",
              "title" => "Frais de port offert",
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile"
            );
    break;
	case "B" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_B.jpg",
              "title" => "Frais de port offert",
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile"
            );
    break;
	case "L" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_L.jpg",
              "title" => "Frais de port offert",
              "text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile"
            );
    break;
	case "SF" :
		$promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_SF.jpg",
							"title" => "Frais de port offert",
							"text" => "Sauvegarder les offres Mill&eacute;sima dans mon mobile"
						);
		break;

	case "D" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_D.jpg",
            "title" => "Lieferung Gratis",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
          );
    break;
	case "O" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_O.jpg",
            "title" => "Lieferung Gratis",
            "text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
          );
    break;
	case "SA" :
		$promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_SA.jpg",
							"title" => "Lieferung Gratis",
							"text" => "Die Angebote von Mill&eacute;sima in meinem Handy speichern"
						);
		break;

	case "G" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_G.jpg",
              "title" => "Free shipping",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "H" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_H.jpg",
              "title" => "Free shipping",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "I" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_I.jpg",
              "title" => "Free shipping",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;
	case "SG" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_SG.jpg",
              "title" => "Free shipping",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;

	case "Y" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_Y.jpg",
              "title" => "Spedizione gratuita",
              "text" => "Salva le offerte Mill&eacute;sima sul mio cellulare"
            );
    break;

	case "E" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_E.jpg",
              "title" => "Envío gratis",
              "text" => "Guarde las ofertas de Mill&eacute;sima en su m&oacute;vil"
            );
    break;

	case "P" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_P.jpg",
              "title" => "Transporte gratis",
              "text" => "Registrar as ofertas Mill&eacute;sima no seu telem&oacute;vel"
            );
    break;

	case "U" :
    $promotioncard = array( "link" => "https://static.millesima.com/s3/contrib/common/promotion-card/promotion_card_U.jpg",
              "title" => "Free shipping",
              "text" => "Save offers from Millesima to my mobile"
            );
    break;

}

$oSmarty->assign('promotion_card', $promotioncard);


?>