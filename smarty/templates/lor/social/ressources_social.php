<?php
/**
 * Traitement du module réseaux sociaux
 * Inseré dans le traitement général quand le module est activé
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 *
 * Variables déclarées préalablement dans traitement général :
 *		$country
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout des réseaux sociaux<br />";

switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$social = array(    "titre"=>"Suivez notre actualité",
		                    "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaFR",
                            "linkedin" => "https://fr.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;

	case "D" :
	case "O" :
	case "SA" :
		$social = array(    "titre"=>"Millésima infos im sozialen netzwerk",
                            "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaDE",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;

	case "G" :
	case "H" :
	case "I" :
	case "SG" :
		$social = array(    "titre"=>"Follow our latest news",
                            "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaENG",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;

	case "Y" :
		$social = array(    "titre"=>"Seguite le nostre news",
                            "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaIT",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;

	case "E" :
		$social = array(    "titre"=>"Siga toda nuestra actualidad",
                            "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaENG",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;

	case "P" :
		$social = array(    "titre"=>"Siga a actualidade da Millésima",
                            "facebook" => "https://www.facebook.com/millesima",
							"twitter" => "https://twitter.com/MillesimaFR",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
							"youtube" => "http://www.youtube.com/user/Millesima",
                            "instagram" => "https://www.instagram.com/millesima"
						);
		break;
	
	case "U" :
		$social = array(    "titre"=>"Follow our latest news",
                            "facebook" => "https://www.facebook.com/Millesima.USA",
							"twitter" => "https://twitter.com/MillesimaUSA",
                            "linkedin" => "https://www.linkedin.com/company/millesima-sa",
                            "youtube" => "https://www.youtube.com/channel/UCZY1o_kNZID-qf0CyQN4X3w",
                            "instagram" => "https://www.instagram.com/millesimausa"
						);
		break;
	
}

$oSmarty->assign('social', $social);


?>