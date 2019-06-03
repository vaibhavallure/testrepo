<?php
/**
 * Données necessaires aux boutons par défauts
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */
 
 switch ($country) {
	case "F" :
	case "B" :
	case "L" :
	case "SF" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 144
															),
								"insc" => array ("width" => 144
															)
						);
		break;

	case "D" :
	case "O" :
	case "SA" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 168
															),
								"insc" => array ("width" => 168
															)
						);
		break;
		
	case "G" :
	case "I" :
	case "H" :
	case "SG" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 127
															),
								"insc" => array ("width" => 127
															)
						);
		break;

	case "Y" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 130
															),
								"insc" => array ("width" => 130
															)
						);
		break;

	case "E" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 130
															),
								"insc" => array ("width" => 130
															)
						);
		break;

	case "P" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 133
															),
								"insc" => array ("width" => 133
															)
						);
		break;

	case "U" :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 127
															),
								"insc" => array ("width" => 127
															)
						);
		break;

	default :
		$btns = array ("jpft" => array ("width" => 156
															),
								"jdcv" => array ("width" => 144
															),
								"insc" => array ("width" => 144
															)
						);
		break;
	
}

$oSmarty->assign('btns', $btns);

/* 
 * Texte bouton Je découvre
 */

$jdcv = array ("F" => "Je&nbsp;<strong>d&eacute;couvre</strong>",
						"B" => "Je&nbsp;<strong>d&eacute;couvre</strong>",
						"L" => "Je&nbsp;<strong>d&eacute;couvre</strong>",
						"D" => "<strong>Mehr </strong>&nbsp;dazu",
						"O" => "<strong>Mehr </strong>&nbsp;dazu",
						"SA" =>"<strong>Mehr </strong>&nbsp;dazu",
						"SF" => "Je&nbsp;<strong>d&eacute;couvre</strong>",
						"G" => "<strong>Discover</strong>",
						"I" => "<strong>Discover</strong>",
						"H" => "<strong>Discover</strong>",
						"SG" => "<strong>Discover</strong>",
						"Y" => "<strong>Scoprite&nbsp;di&nbsp;pi&ugrave;</strong>",
						"E" => "<strong>Descubra</strong>",
						"P" => "<strong>Descobrir</strong>",
						"U" => "Discover&nbsp;<strong>Now</strong>"
						);

/* 
 * Texte bouton J'en profite
 */
$jpft = array ("F" => "J'en&nbsp;<strong>profite</strong>",
						"B" => "J'en&nbsp;<strong>profite</strong>",
						"L" => "J'en&nbsp;<strong>profite</strong>",
						"D" => "Zum&nbsp;<strong>Angebot</strong>",
						"O" => "Zum&nbsp;<strong>Angebot</strong>",
						"SA" =>"Zum&nbsp;<strong>Angebot</strong>",
						"SF" => "J'en&nbsp;<strong>profite</strong>",
						"G" => "Buy&nbsp;<strong>Now</strong>",
						"I" => "Buy&nbsp;<strong>Now</strong>",
						"H" => "Buy&nbsp;<strong>Now</strong>",
						"SG" => "Buy&nbsp;<strong>Now</strong>",
						"Y" => "<strong>Scoprite&nbsp;di&nbsp;pi&ugrave;</strong>",
						"E" => "Comprar&nbsp;<strong>ahora</strong>",
						"P" => "<strong>Aproveito</strong>",
						"U" => "Buy&nbsp;<strong>Now</strong>"
						);
						
/* 
 * Texte bouton Je m'inscris
 */					
$insc = array ("F" => "Je&nbsp;<strong>m'inscris</strong>",
						"B" => "Je&nbsp;<strong>m'inscris</strong>",
						"L" => "Je&nbsp;<strong>m'inscris</strong>",
						"D" => "<strong>Ich&nbsp;melde&nbsp;mich&nbsp;an</strong>",
						"O" => "<strong>Ich&nbsp;melde&nbsp;mich&nbsp;an</strong>",
						"SA" =>"<strong>Ich&nbsp;melde&nbsp;mich&nbsp;an</strong>",
						"SF" => "Je&nbsp;<strong>m'inscris</strong>",
						"G" => "<strong>Register</strong>",
						"I" => "<strong>Register</strong>",
						"H" => "<strong>Register</strong>",
						"SG" => "<strong>Register</strong>",
						"Y" => "Mi&nbsp;<strong>iscrivo</strong>",
						"E" => "Me&nbsp;<strong>inscribo</strong>",
						"P" => "<strong>Subscrevo</strong>",
						"U" => "Sign&nbsp;<strong>up</strong>"
						);
/* 
 * Texte bouton En savoir plus
 */					
$savr = array ("F" => "<strong>En&nbsp;savoir&nbsp;plus<strong>",
						"B" => "<strong>En&nbsp;savoir&nbsp;plus<strong>",
						"L" => "<strong>En&nbsp;savoir&nbsp;plus<strong>",
						"D" => "<strong>Mehr&nbsp;Informationen</strong>",
						"O" => "<strong>Mehr&nbsp;Informationen</strong>",
						"SA" =>"<strong>Mehr&nbsp;Informationen</strong>",
						"SF" => "<strong>En&nbsp;savoir&nbsp;plus<strong>",
						"G" => "<strong>Read&nbsp;more</strong>",
						"I" => "<strong>Read&nbsp;more</strong>",
						"H" => "<strong>Read&nbsp;more</strong>",
						"SG" => "<strong>Read&nbsp;more</strong>",
						"Y" => "<strong>Per&nbsp;saperne&nbsp;di&nbsp;pi&ugrave;</strong>",
						"E" => "<strong>M&aacute;s&nbsp;informaci&oacute;n</strong>",
						"P" => "<strong>Saber&nbsp;mais</strong>",
						"U" => "<strong>Learn&nbsp;more</strong>"
						);

/* 
 * Texte bouton Découvrez la vidéo
 */

$dvid = array ("F" => "<strong>D&eacute;couvrez</strong>&nbsp;la&nbsp;vid&eacute;o",
						"B" => "<strong>D&eacute;couvrez</strong>&nbsp;la&nbsp;vid&eacute;o",
						"L" => "<strong>D&eacute;couvrez</strong>&nbsp;la&nbsp;vid&eacute;o",
						"D" => "<strong>Entdecken&nbsp;Sie&nbsp;mehr</strong>&nbsp;im&nbsp;Video",
						"O" => "<strong>Entdecken&nbsp;Sie&nbsp;mehr</strong>&nbsp;im&nbsp;Video",
						"SA" =>"<strong>Entdecken&nbsp;Sie&nbsp;mehr</strong>&nbsp;im&nbsp;Video",
						"SF" => "<strong>D&eacute;couvrez</strong>&nbsp;la&nbsp;vid&eacute;o",
						"G" => "<strong>Discover</strong>&nbsp;the&nbsp;video",
						"I" => "<strong>Discover</strong>&nbsp;the&nbsp;video",
						"H" => "<strong>Discover</strong>&nbsp;the&nbsp;video",
						"SG" => "<strong>Discover</strong>&nbsp;the&nbsp;video",
						"Y" => "<strong>Scopra</strong>&nbsp;il&nbsp;video",
						"E" => "<strong>Descubra</strong>&nbsp;en&nbsp;video",
						"P" => "<strong>Visite&nbsp;gra&ccedil;as</strong>&nbsp;a&nbsp;v&iacute;deo",
						"U" => "<strong>Discover</strong>&nbsp;the&nbsp;video"
						);

/* 
 * Texte bouton Je réserve
 */

$jrsv = array ("F" => "<strong>D&eacute;couvrez&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2<strong>",
						"B" => "<strong>D&eacute;couvrez&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2<strong>",
						"L" => "<strong>D&eacute;couvrez&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2<strong>",
						"D" => "<strong>Jetzt&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2&nbsp;entdecken</strong>",
						"O" => "<strong>Jetzt&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2&nbsp;entdecken</strong>",
						"SA" =>"<strong>Jetzt&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2&nbsp;entdecken</strong>",
						"SF" => "<strong>D&eacute;couvrez&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2<strong>",
						"G" => "<strong>Discover&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"I" => "<strong>Discover&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"H" => "<strong>Discover&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"SG" => "<strong>Discover&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"Y" => "<strong>Scopri&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"E" => "<strong>Descubra&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"P" => "<strong>Descubra&nbsp;o&nbsp;Vintage&nbsp;2002&nbsp;Pl&eacute;nitude&nbsp;2</strong>",
						"U" => "<strong>Discover&nbsp;Now</strong>"
						);

/* 
 * Texte bouton Découvrir la selection
 */

$dcvslc = array ("F" => "<strong>D&eacute;couvrir</strong>&nbsp;la&nbsp;s&eacute;lection",
						"B" => "<strong>D&eacute;couvrir</strong>&nbsp;la&nbsp;s&eacute;lection",
						"L" => "<strong>D&eacute;couvrir</strong>&nbsp;la&nbsp;s&eacute;lection",
						"D" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"O" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SA" =>"<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SF" => "<strong>D&eacute;couvrir</strong>&nbsp;la&nbsp;s&eacute;lection",
						"G" => "<strong>Discover</strong>&nbsp;our&nbsp;selection",
						"I" => "<strong>Discover</strong>&nbsp;our&nbsp;selection",
						"H" => "<strong>Discover</strong>&nbsp;our&nbsp;selection",
						"SG" => "<strong>Discover</strong>&nbsp;our&nbsp;selection",
						"Y" => "<strong>Scoprite</strong>&nbsp;la&nbsp;nostra selezione",
						"E" => "<strong>Descubrir</strong>&nbsp;nuestra&nbsp;selecci&oacute;n",
						"P" => "<strong>Descubrir</strong>&nbsp;a&nbsp;nossa selec&ccedil;&atilde;o",
						"U" => "<strong>Discover</strong>&nbsp;this&nbsp;selection"
						);

/* 
 * Texte bouton I pick my favorites
 */

$chfav = array ("F" => "Je&nbsp;choisis&nbsp;<strong>mes&nbsp;favoris</strong>",
						"B" => "Je&nbsp;choisis&nbsp;<strong>mes&nbsp;favoris</strong>",
						"L" => "Je&nbsp;choisis&nbsp;<strong>mes&nbsp;favoris</strong>",
						"D" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"O" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SA" =>"<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SF" => "Je&nbsp;choisis&nbsp;<strong>mes&nbsp;favoris</strong>",
						"G" => "I&nbsp;pick&nbsp;<strong>my&nbsp;favorites</strong>",
						"I" => "I&nbsp;pick&nbsp;<strong>my&nbsp;favorites</strong>",
						"H" => "I&nbsp;pick&nbsp;<strong>my&nbsp;favorites</strong>",
						"SG" => "I&nbsp;pick&nbsp;<strong>my&nbsp;favorites</strong>",
						"Y" => "<strong>Scoprite</strong>&nbsp;la&nbsp;nostra&nbsp;selezione",
						"E" => "<strong>Descubrir</strong>&nbsp;nuestra&nbsp;selecci&oacute;n",
						"P" => "<strong>Descubrir</strong>&nbsp;a&nbsp;nossa&nbsp;selec&ccedil;&atilde;o",
						"U" => "I&nbsp;pick&nbsp;<strong>my&nbsp;favorites</strong>"
						);

/* 
 * Texte bouton More special prices
 */

$mrsp = array ("F" => "Plus&nbsp;de&nbsp;<strong>prix&nbsp;spéciaux</strong>",
						"B" => "Plus&nbsp;de&nbsp;<strong>prix&nbsp;spéciaux</strong>",
						"L" => "Plus&nbsp;de&nbsp;<strong>prix&nbsp;spéciaux</strong>",
						"D" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"O" => "<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SA" =>"<strong>Entdecken&nbsp;Sie</strong>&nbsp;unsere&nbsp;Auswahl",
						"SF" => "Plus&nbsp;de&nbsp;<strong>prix&nbsp;spéciaux</strong>",
						"G" => "More&nbsp;<strong>special&nbsp;prices</strong>",
						"I" => "More&nbsp;<strong>special&nbsp;prices</strong>",
						"H" => "More&nbsp;<strong>special&nbsp;prices</strong>",
						"SG" => "More&nbsp;<strong>special&nbsp;prices</strong>",
						"Y" => "<strong>Scoprite</strong>&nbsp;la&nbsp;nostra&nbsp;selezione",
						"E" => "<strong>Descubrir</strong>&nbsp;nuestra&nbsp;selecci&oacute;n",
						"P" => "<strong>Descubrir</strong>&nbsp;a&nbsp;nossa&nbsp;selec&ccedil;&atilde;o",
						"U" => "More&nbsp;<strong>special&nbsp;prices</strong>"
						);

$tradbtns = array ("jdcv" => $jdcv, "jpft" => $jpft, "insc" => $insc, "savr" => $savr, "dvid" => $dvid, "jrsv" => $jrsv, "dcvslc" => $dcvslc, "chfav" => $chfav, "mrsp" => $mrsp);
$oSmarty->assign('tradbtns', $tradbtns);


?>