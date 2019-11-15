<?php
//fichier de ressources pour les données specifiques aux emails
		/* Mise à jour le 15 09 2014 */

$is_ibm = array('U', 'SG','I','H','B','L','P','Y','G','E','D','O');
		
//entetes de colonne
switch ($country) {
	
/* --------------- Ressources spécifiques à la FRANCE --------------- */
	case "F" :
		
		$langue="F"; /* a garder : permet de determiner la langue independamment du pays */
		$lettremenu="F"; /* a garder : Non double avec langue - exceptions US et HK */
		
		$siteweb="https://www.millesima.fr/";
		$shorturl="www.millesima.fr";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawine";
		$lienyoutube="http://www.youtube.com/playlist?list=PL87B4BD2E7FD21574";
		
		$validite="Offre valable pendant 72H dans la limite des stocks disponibles.";
		$validitedate="Offre valable jusqu'au $datevalide dans la limite des stocks disponibles.";
		$validitestock="Offre valable dans la limite des stocks disponibles.";
		
		$revendeurs="Réservé à une clientèle particulière, et en aucun cas aux revendeurs professionnels.";
		
		$ht=" HT";
		$ttc=" TTC";
		$fnpx1btlleht="&nbsp;HT/blle";
		$fnpxcaissettc="&nbsp;TTC/caisse*"; 
		$legendepxind="* Prix &euro; TTC indicatif (TVA 20% et frais de port en France m&eacute;tropolitaine inclus).";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Bourgogne",
							"vdr"  => "Vallée du Rhône",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="&agrave; partir de ";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "3",
										),
							"2"  => array(
										  "libelle" => "<strong>1+1=3 <br />panachage possible</strong>",
										   "nbcaisses" => $phrase_APartirDe . "3",
											),
							"123" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-40%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-50%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Prix l&eacute;gers</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> &eacute; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;s</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Blancs</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Frais de port offerts</strong>",
										"ssphrase" => "jusqu'au 31/08/2018",
										"detail" => "*Hors vins primeurs - Livraison en une seule fois à une seule adresse en France M&eacute;tropolitaine - Valable jusqu'au $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		
		/* ------ Primeurs ------ */
		$primeur="Primeur";
		
		$phraseprimeurdirectfrom="Comme chaque ann&Eacute;e, l'int&Eacute;gralit&Eacute; des vins que Mill&Eacute;sima vous propose en primeur a &Eacute;t&Eacute; achet&Eacute;e directement &Agrave; la propri&Eacute;t&Eacute;.";
		
		$titreprimeur="Primeurs 2018";
		$phraseprimeur="Livraison <strong>D&eacute;but&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nouvelles sorties Primeurs Bordeaux";
		$sortiebordeaux="Nouvelles sorties <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="R&eacute;capitulatif des sorties Primeurs Bordeaux";
		$recapsortiebordeaux="R&eacute;capitulatif des sorties <strong>Primeurs Bordeaux</strong>";
		
		/* PDF Primeur */
		$pdf_btleht="Bouteille<br />&euro; ".$ht;
		$pdf_caisht="Caisse<br />&euro; ".$ht;
		$pdf_caisttc="Caisse<br />&euro; ".$ttc." *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - 33050 Bordeaux Cedex.
  T&eacute;l.&nbsp;05&nbsp;57&nbsp;808&nbsp;808 - Fax&nbsp;05&nbsp;57&nbsp;808&nbsp;819 - millesima@millesima.com</small>";
		$site_court="www.millesima.fr";
		$caisse12=" Caisse ou carton de 12 bouteilles";
		$caisse6=" Caisse ou carton de 6 bouteilles";
		$pdf_phraseprimeur="Livraison Début 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Ces prix sont valables jusqu'&agrave; la prochaine offre, dans la limite des stocks disponibles";
		$newsorties="Nouvelles sorties";
		$allsorties="Toutes les sorties";
		
		/* Options supplémentaires */
		break;
		
/* --------------- Ressources spécifiques à la BELGIQUE --------------- */
	case "B" :
		
		$langue="F";
        $lettremenu="F";
		
		$siteweb="https://www.millesima.be/";
		$shorturl="www.millesima.be";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawine";
		$lienyoutube="http://www.youtube.com/playlist?list=PL87B4BD2E7FD21574";
		
		$validite="Offre valable pendant 72H dans la limite des stocks disponibles.";
		$validitedate="Offre valable jusqu'au $datevalide dans la limite des stocks disponibles.";
		$validitestock="Offre valable dans la limite des stocks disponibles.";
		
		$revendeurs="Réservé &agrave; une clientèle particulière, et en aucun cas aux revendeurs professionnels.";
		
		$ht=" HTVA";
		$ttc=" TVAC";
		$fnpx1btlleht="&nbsp;HTVA/blle";
		$fnpxcaissettc="&nbsp;TVAC/caisse*";
		$legendepxind="* Prix &euro; TVAC indicatif (TVA 21% et frais de port Belgique inclus).";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Bourgogne",
							"vdr"  => "Vallée du Rhône",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
							
		$phrase_APartirDe="&agrave; partir de ";
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
											"libelle" => "<strong>1+1=3 <br />panachage possible</strong>",
											"nbcaisses" => $phrase_APartirDe . "3",
							              ),
							"123" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-40%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-50%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Prix l&eacute;gers</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> &eacute; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;s</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Blancs</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							);
		/* = array( "phrase" => "<strong>Frais de port offerts</strong>",
										"ssphrase" => "jusqu'au 31/08/2018",
										"detail" => "*Hors vins primeurs - Livraison en une seule fois à une seule adresse en Belgique - Valable jusqu'au $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		
		
		/* ------ Primeurs ------ */
		$primeur="Primeur";
		
		$phraseprimeurdirectfrom="Comme chaque ann&Eacute;e, l'int&Eacute;gralit&Eacute; des vins que Mill&Eacute;sima vous propose en primeur a &Eacute;t&Eacute; achet&Eacute;e directement &Agrave; la propri&Eacute;t&Eacute;.";
		
		$titreprimeur="Primeurs 2018";
		$phraseprimeur="Livraison <strong>D&eacute;but&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nouvelles sorties Primeurs Bordeaux";
		$sortiebordeaux="Nouvelles sorties <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="R&eacute;capitulatif des sorties Primeurs Bordeaux";
		$recapsortiebordeaux="R&eacute;capitulatif des sorties <strong>Primeurs Bordeaux</strong>";
		
		/* PDF Primeur */
		$pdf_btleht="Bouteille<br />&euro; ".$ht;
		$pdf_caisht="Caisse<br />&euro; ".$ht;
		$pdf_caisttc="Caisse<br />&euro; ".$ttc." *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex. T&eacute;l.&nbsp;gratuit&nbsp;00&nbsp;800&nbsp;267&nbsp;33&nbsp;289 sinon T&eacute;l.&nbsp;05&nbsp;57&nbsp;808&nbsp;808 - millesima@millesima.com</small>";
		$site_court="www.millesima.be";
		$caisse12=" Caisse ou carton de 12 bouteilles";
		$caisse6=" Caisse ou carton de 6 bouteilles";
		$pdf_phraseprimeur="Livraison Début 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Ces prix sont valables jusqu'&agrave; la prochaine offre, dans la limite des stocks disponibles";
		$newsorties="Nouvelles sorties";
		$allsorties="Toutes les sorties";
		
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques au LUXEMBOURG --------------- */
	case "L" :
	
		$langue="F";
        $lettremenu="F";
		
		$siteweb="https://www.millesima.lu/";
		$shorturl="www.millesima.lu";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawine";
		$lienyoutube="http://www.youtube.com/playlist?list=PL87B4BD2E7FD21574";
		
		$validite="Offre valable pendant 72H dans la limite des stocks disponibles.";
		$validitedate="Offre valable jusqu'au $datevalide dans la limite des stocks disponibles.";
		$validitestock="Offre valable dans la limite des stocks disponibles.";
		
		$revendeurs="Réservé &agrave; une clientèle particulière, et en aucun cas aux revendeurs professionnels.";
		
		$ht=" HTVA";
		$ttc=" TVAC";
		$fnpx1btlleht="&nbsp;HTVA/blle";
		$fnpxcaissettc="&nbsp;TVAC/caisse*";
		$legendepxind="* Prix &euro; TVAC indicatif (actuellement TVA 14% pour les vins dont le degr&eacute; d'alcool est <= 13&deg; et 17% pour les vins > 13&deg; et frais de port au Luxembourg inclus).";
		$legendepxindmini="*Px &euro; TVAC indicatif (actuellement TVA 14% si degr&eacute; d'alcool <= 13&deg; et 17% sinon, frais de port au Luxembourg inclus).";
				
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Bourgogne",
							"vdr"  => "Vallée du Rhône",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="&agrave; partir de ";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
							"libelle" => "<strong>1+1=3 <br />panachage possible</strong>",
							"nbcaisses" => $phrase_APartirDe . "3",
										),
							"123" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-40%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-50%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Prix l&eacute;gers</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> &eacute; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;s</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Blancs</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Frais de port offerts</strong>",
										"ssphrase" => "jusqu'au 31/08/2018",
										"detail" => "*Hors vins primeurs - Livraison en une seule fois à une seule adresse au Luxembourg - Valable jusqu'au $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/

		
		/* ------ Primeurs ------ */
		$primeur="Primeur";
		
		$phraseprimeurdirectfrom="Comme chaque ann&Eacute;e, l'int&Eacute;gralit&Eacute; des vins que Mill&Eacute;sima vous propose en primeur a &Eacute;t&Eacute; achet&Eacute;e directement &Agrave; la propri&Eacute;t&Eacute;.";
		
		$titreprimeur="Primeurs 2018";
		$phraseprimeur="Livraison <strong>D&eacute;but&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nouvelles sorties Primeurs Bordeaux";
		$sortiebordeaux="Nouvelles sorties <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="R&eacute;capitulatif des sorties Primeurs Bordeaux";
		$recapsortiebordeaux="R&eacute;capitulatif des sorties <strong>Primeurs Bordeaux</strong>";
		
		/* PDF Primeur */
		$pdf_btleht="Bouteille<br />&euro; ".$ht;
		$pdf_caisht="Caisse<br />&euro; ".$ht;
		$pdf_caisttc="Caisse<br />&euro; ".$ttc." *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex. T&eacute;l.&nbsp;gratuit&nbsp;00 800 267 33 289 sinon T&eacute;l.&nbsp;05&nbsp;57&nbsp;808&nbsp;808 - millesima@millesima.com</small>";
		$site_court="www.millesima.lu";
		$caisse12=" Caisse/carton de 12 blles";
		$caisse6=" Caisse/carton de 6 blles";
		$pdf_phraseprimeur="Livraison Début 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Ces prix sont valables jusqu'&agrave; la prochaine offre, dans la limite des stocks disponibles";
		$newsorties="Nouvelles sorties";
		$allsorties="Toutes les sorties";

		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'ALLEMAGNE --------------- */
	case "D" :
	
		$langue="D";
        $lettremenu="D";
		
		$siteweb="https://www.millesima.de/";
		$shorturl="www.millesima.de";
		$lienfacebook="https://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawein";
		$lienyoutube="http://www.youtube.com/playlist?list=PL4BEB86E15F78970F";
		
		$validite="Dieses Angebot ist 72 Stunden g&uuml;ltig, solange der Vorrat reicht.";
		$validitedate="Dieses Angebot ist bis zum $datevalide g&uuml;ltig solange der Vorrat reicht.";
		$validitestock="Angebot g&uuml;ltig, solange der Vorrat reicht.";
		
		$revendeurs="DIESE ANGEBOT IST AUSSCHLIESSLICH AN ENDVERBRAUCHER GERICHTET.";
		
		$ttc=" inkl.19% MwSt.";
		$fnpx1btllettc="/Flasche inkl.19% MwSt.";
		$fnpxcaissettc="/Kiste inkl.19% MwSt.";
		$legendepxind=""; //*Preisangabe pro Kiste inklusive 19% Mehrwertsteuer
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgund",
							"vdr"  => "Rhône-Tal",
							"alsace"  => "Elsass",
							"champagne"  => "Champagner",
							"loire" => "Loire",
							);
							
		$phrase_APartirDe="Ab";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
											"libelle" => "<strong>1+1=3</strong>",
											"nbcaisses" => $phrase_APartirDe . "3",
										),
							"123" => array(
										"libelle" => "<strong>40%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>50%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Die leichten Preise</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagner</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Weissweine</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Lieferung gratis</strong>",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="Primeurweine";
		
		$phraseprimeurdirectfrom="Wie jedes Jahr wurde die Gesamtheit der von Mill&eacute;sima als Primeurs angebotenen Weine direkt beim Hersteller eingekauft.";
		
		$titreprimeur="Primeurweine 2018";
		$phraseprimeur="Auslieferung <strong>Fr&uuml;hjahr&nbsp;2021</strong>";
		
		$titresortiebordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$sortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$recapsortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";
		

		/* PDF Primeur */
		$pdf_btleht="&euro; / Flasche<br /> ".$ttc;
		$pdf_caisht="&euro; / Kiste<br /> ".$ttc;
		$pdf_caisttc="&euro; / L<br /> ".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex.  Tel. 00 800 267 33 289 oder 0 800 181 30 83 - millesima@millesima.com</small>";
		$site_court="www.millesima.de";
		$caisse12=" Kiste oder Karton mit 12 Fl.";
		$caisse6=" Kiste oder Karton mit 6 Fl.";
		$pdf_phraseprimeur="Auslieferung Frühjahr 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Diese Preise sind bis zum Erscheinen eines neuen Angebots g&uuml;ltig, solange der Vorrat reicht.";
		$newsorties="Neuzugänge Primeurs";
		$allsorties="Alle verfügbaren Primeurweine ";
	
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'AUTRICHE --------------- */
	case "O" :
	
		$langue="D";
        $lettremenu="D";
		
		$siteweb="https://www.millesima.at/";
		$shorturl="www.millesima.at";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawein";
		$lienyoutube="http://www.youtube.com/playlist?list=PL4BEB86E15F78970F";
		
		$validite="Dieses Angebot ist 72 Stunden g&uuml;ltig, solange der Vorrat reicht.";
		$validitedate="Dieses Angebot ist bis zum $datevalide g&uuml;ltig solange der Vorrat reicht.";
		$validitestock="Angebot g&uuml;ltig, solange der Vorrat reicht.";
		
		$revendeurs="DIESE ANGEBOT IST AUSSCHLIESSLICH AN ENDVERBRAUCHER GERICHTET.";
		
		$ttc=" inkl.20% MwSt.";
		$fnpx1btllettc="/Flasche inkl.20% MwSt.";
		$fnpxcaissettc="/Kiste inkl.20% MwSt.";
		$legendepxind=""; //*Preisangabe pro Kiste inklusive 20% Mehrwertsteuer
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgund",
							"vdr"  => "Rhône-Tal",
							"alsace"  => "Elsass",
							"champagne"  => "Champagner",
							"loire" => "Loire",
							);
							
		$phrase_APartirDe="Ab";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
							"libelle" => "<strong>1+1=3</strong>",
							"nbcaisses" => $phrase_APartirDe . "3",
											),
							"123" => array(
										"libelle" => "<strong>40%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>50%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Die leichten Preise</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagner</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Weissweine</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Lieferung gratis</strong>",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="Primeurweine";
		
		$phraseprimeurdirectfrom="Wie jedes Jahr wurde die Gesamtheit der von Mill&Eacute;sima als Primeurs angebotenen Weine direkt beim Hersteller eingekauft.";
		
		$titreprimeur="Primeurweine 2018";
		$phraseprimeur="Auslieferung <strong>Fr&uuml;hjahr&nbsp;2021</strong>";
		
		$titresortiebordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$sortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$recapsortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";

		/* PDF Primeur */
		$pdf_btleht="&euro; / Flasche<br /> ".$ttc;
		$pdf_caisht="&euro; / Kiste<br /> ".$ttc;
		$pdf_caisttc="&euro; / L<br /> ".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex.  Tel. 00 800 267 33 289 oder 0 800 29 84 61 - millesima@millesima.com</small>";
		$site_court="www.millesima.at";
		$caisse12=" Kiste oder Karton mit 12 Fl.";
		$caisse6=" Kiste oder Karton mit 6 Fl.";
		$pdf_phraseprimeur="Auslieferung Frühjahr 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Diese Preise sind bis zum Erscheinen eines neuen Angebots gültig, solange der Vorrat reicht.";
		$newsorties="Neuzugänge Primeurs";
		$allsorties="Alle verfügbaren Primeurweine ";
		
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à la SUISSE ALLEMANDE --------------- */
	case "SA" :
		
		/* Ressources spécifiques à la Suisse Allemande */
		$langue="D";
        $lettremenu="D";
		
		$siteweb="https://de.millesima.ch/";
		$shorturl="de.millesima.ch";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawein";
		$lienyoutube="http://www.youtube.com/playlist?list=PL4BEB86E15F78970F";
		
		$validite="Dieses Angebot ist 72 Stunden g&uuml;ltig, solange der Vorrat reicht.";
		$validitedate="Dieses Angebot ist bis zum $datevalide g&uuml;ltig solange der Vorrat reicht.";
		$validitestock="Angebot g&uuml;ltig, solange der Vorrat reicht.";
		
		$revendeurs="DIESE ANGEBOT IST AUSSCHLIESSLICH AN ENDVERBRAUCHER GERICHTET.";

		$ttc=" inkl.MwSt.";
		$fnpx1btllettc="/Flasche";
		$fnpxcaissettc="/Kiste";
		$legendepxind="";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgund",
							"vdr"  => "Rhône-Tal",
							"alsace"  => "Elsass",
							"champagne"  => "Champagner",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="Ab";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
							"libelle" => "<strong>1+1=3</strong>",
							"nbcaisses" => $phrase_APartirDe . "3",
										),
							"123" => array(
										"libelle" => "<strong>20%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>50%</strong> Rabatt auf die 2. Kiste",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Die leichten Preise</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagner</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Weissweine</strong> zu leichten Preisen",
										"nbcaisses" => "",
										),
							);
							
		/*$fdpo = array( "phrase" => "<strong>Lieferung gratis</strong>",
										"ssphrase" => "G&uuml;ltig bis 31/08/2018",
										"detail" => "*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in der Schweiz - G&uuml;ltig bis $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
						
						
		/* ------ Primeurs ------ */
		$primeur="Primeurweine";
		
		$phraseprimeurdirectfrom="Wie jedes Jahr wurde die Gesamtheit der von Mill&Eacute;sima als Primeurs angebotenen Weine direkt beim Hersteller eingekauft.";
		
		$titreprimeur="Primeurweine 2018";
		$phraseprimeur="Auslieferung <strong>Fr&uuml;hjahr&nbsp;2021</strong>";
		
		$titresortiebordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$sortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="Neuzug&auml;nge Primeurs Bordeaux";
		$recapsortiebordeaux="Neuzug&auml;nge <strong>Primeurs Bordeaux</strong>";
		
		/* PDF Primeur */
		$pdf_btleht="<br /> ";
		$pdf_caisht="CHF<br />Flasche<br /> ".$ttc;
		$pdf_caisttc="CHF<br />Kiste<br /> ".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex.  Tel. 00 800 267 33 289 oder 0 800 83 71 67 - millesima@millesima.com</small>";
		$site_court="de.millesima.ch";
		$caisse12=" Kiste oder Karton mit 12 Fl.";
		$caisse6=" Kiste oder Karton mit 6 Fl.";
		$pdf_phraseprimeur="Auslieferung Frühjahr 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Diese Preise sind bis zum Erscheinen eines neuen Angebots gültig, solange der Vorrat reicht.";
		$newsorties="Neuzugänge Primeurs";
		$allsorties="Alle verfügbaren Primeurweine ";
		
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à la SUISSE FRANCAISE --------------- */
	case "SF" :
		$langue="F";
        $lettremenu="F";
		
		$siteweb="https://fr.millesima.ch/";
		$shorturl="fr.millesima.ch";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimawine";
		$lienyoutube="http://www.youtube.com/playlist?list=PL87B4BD2E7FD21574";
		
		$validite="Offre valable pendant 72H dans la limite des stocks disponibles.";
		$validitedate="Offre valable jusqu'au $datevalide dans la limite des stocks disponibles.";
		$validitestock="Offre valable dans la limite des stocks disponibles.";
		
		$revendeurs="Réservé &agrave; une clientèle particulière, et en aucun cas aux revendeurs professionnels.";
		
		$ttc=" TVAC";
		$fnpx1btllettc="&nbsp;la bouteille";
		$fnpxcaissettc="&nbsp;la caisse";
		$legendepxind="";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Bourgogne",
							"vdr"  => "Vallée du Rhône",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
							
		$phrase_APartirDe="&agrave; partir de ";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
							"libelle" => "<strong>1+1=3 <br />panachage possible</strong>",
							"nbcaisses" => $phrase_APartirDe . "3",
											),
							"123" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-40%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "La 2e caisse &agrave; <strong>-50%</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Prix l&eacute;gers</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> &eacute; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Ros&eacute;s</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Blancs</strong> &agrave; prix l&eacute;gers",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Frais de port offerts</strong>",
										"ssphrase" => "jusqu'au 31/08/2018",
										"detail" => "*Hors vins primeurs - Livraison en une seule fois à une seule adresse en Suisse - Valable jusqu'au $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="Primeur";
		
		$phraseprimeurdirectfrom="Comme chaque ann&Eacute;e, l'int&Eacute;gralit&Eacute; des vins que Mill&Eacute;sima vous propose en primeur a &Eacute;t&Eacute; achet&Eacute;e directement &Agrave; la propri&Eacute;t&Eacute;.";
		
		$titreprimeur="Primeurs 2018";	
		$phraseprimeur="Livraison <strong>D&eacute;but&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nouvelles sorties Primeurs Bordeaux";
		$sortiebordeaux="Nouvelles sorties <strong>Primeurs Bordeaux</strong>";
		$titrerecapbordeaux="R&eacute;capitulatif des sorties Primeurs Bordeaux";
		$recapsortiebordeaux="R&eacute;capitulatif des sorties <strong>Primeurs Bordeaux</strong>";
	
		/* PDF Primeur */
		$pdf_btleht="<br /> ";
		$pdf_caisht="CHF<br />Bouteille<br /> ".$ttc;
		$pdf_caisttc="CHF<br />Caisse<br /> ".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex.  Tel. 00 800 267 33 289 ou 0 800 83 71 67 - millesima@millesima.com</small>";
		$site_court="fr.millesima.ch";
		$caisse12=" Caisse ou carton de 12 bouteilles";
		$caisse6=" Caisse ou carton de 6 bouteilles";
		$pdf_phraseprimeur="Livraison Début 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Ces prix sont valables jusqu'&agrave; la prochaine offre, dans la limite des stocks disponibles";
		$newsorties="Nouvelles sorties";
		$allsorties="Toutes les sorties";
	
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'ANGLETERRE --------------- */
	case "G" :
		$langue="G";
        $lettremenu="G";
		
		$siteweb="https://www.millesima.co.uk/";
		$shorturl="www.millesima.co.uk";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://twitter.com/finewinefutures";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231";
		
		$validite="Special offer valid for 72 hours according to availability";
		$validitedate="Valid till $datevalide according to availability";
		$validitestock="According to availability.";
		
		$revendeurs="This offer is only available to private individuals; it is not available to importers or distributors.";
		
		$ht=" in bond ex VAT";
		$ttc="inc. VAT and duties";
		$fnpx1btlleht="/btl in bond ex VAT";
		$fnpxcaissettc="/case inc. VAT and DP*";
		$legendepxind="* Estimation of price including current VAT & duties (free delivery).";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgundy",
							"vdr"  => "Rhone Valley",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="from ";
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
										"libelle" => "<strong>1+1=3 <br /> -33% from 3 different cases</strong>",
										"nbcaisses" => $phrase_APartirDe . "3",
							),
							"123" => array(
										"libelle" => "<strong>-40%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Special prices</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> at light prices",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> at light prices",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>White wines</strong> at light prices",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Free delivery</strong>",
										"ssphrase" => "until the 31/08/2018",
										"detail" => "*Excluding en primeur wines. Free delivery on one order to one address – valid until the $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="En-Primeur";
		
		$phraseprimeurdirectfrom="This year, as every year, all of Mill&Eacute;sima&acute;s en primeur wines are bought exclusively from each property.";
		
		$titreprimeur="2018 En-Primeur wine";
		$phraseprimeur="Delivery <strong>by early&nbsp;2021</strong>";
		
		$titresortiebordeaux="En-Primeur Bordeaux New releases";
		$sortiebordeaux=" <strong>En-Primeur Bordeaux</strong> New releases";
		$titrerecapbordeaux="Summary: En-Primeur Bordeaux New releases";
		$recapsortiebordeaux="<strong>Summary: En-Primeur Bordeaux</strong> New releases";
		
		/* PDF Primeur */
		$pdf_btleht="Bottle &pound;<br />".$ht;
		$pdf_caisht="Case &pound;<br />".$ht;
		$pdf_caisttc="Case &pound; *<br />".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex. Tel. 0800 917 03 52 - millesima@millesima.com</small>";
		$site_court="www.millesima.co.uk";
		$caisse12=" 12 bottles case or box";
		$caisse6="  6 bottles case or box";
		$pdf_phraseprimeur="Delivery by early 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="These prices are valid till the next offer, according to availability";
		$newsorties="New releases";
		$allsorties="All the releases";

		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'IRLANDE --------------- */
	case "I" :
		$langue="G";
        $lettremenu="G";
		
		$siteweb="https://www.millesima.ie/";
		$shorturl="www.millesima.ie";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://twitter.com/finewinefutures";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231";
		
		$validite="Special offer valid for 72 hours according to availability";
		$validitedate="Valid till $datevalide according to availability";
		$validitestock="According to availability.";
		
		$revendeurs="This offer is only available to private individuals; it is not available to importers or distributors.";
		
		$ht=" in bond ex VAT";
		$ttc="inc. VAT and duties";
		$fnpx1btlleht="/btl in bond ex VAT";
		$fnpxcaissettc="/case inc. VAT and DP*";
		$legendepxind="* Estimation of price including current VAT & duties (free delivery).";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgundy",
							"vdr"  => "Rhone Valley",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="from";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
											"libelle" => "<strong>1+1=3 <br /> -33% from 3 different cases</strong>",
											"nbcaisses" => $phrase_APartirDe . "3",
											),
							"123" => array(
										"libelle" => "<strong>-40%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Special prices</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> at light prices",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> at light prices",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>White wines</strong> at light prices",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Free delivery</strong>",
										"ssphrase" => "until the 31/08/2018",
										"detail" => "*Excluding en primeur wines. Free delivery on one order to one address – valid until the $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="En-Primeur";
		
		$phraseprimeurdirectfrom="This year, as every year, all of Mill&Eacute;sima&acute;s en primeur wines are bought exclusively from each property.";
		
		$titreprimeur="2018 En-Primeur wine";
		$phraseprimeur="Delivery <strong>by early&nbsp;2021</strong>";
		
		$titresortiebordeaux="En-Primeur Bordeaux New releases";
		$sortiebordeaux=" <strong>En-Primeur Bordeaux</strong> New releases";
		$titrerecapbordeaux="Summary: En-Primeur Bordeaux New releases";
		$recapsortiebordeaux="<strong>Summary: En-Primeur Bordeaux</strong> New releases";			
		
		/* PDF Primeur */
		$pdf_btleht="Bottle &euro;<br />".$ht;
		$pdf_caisht="Case &euro;<br />".$ht;
		$pdf_caisttc="Case &euro; *<br />".$ttc;
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex. Tel. 1 800 55 33 93 - millesima@millesima.com</small>";
		$site_court="www.millesima.ie";
		$caisse12=" 12 bottles case or box";
		$caisse6="  6 bottles case or box";
		$pdf_phraseprimeur="Delivery by early 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="These prices are valid till the next offer, according to availability";
		$newsorties="New releases";
		$allsorties="All the releases";
		
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'ITALIE --------------- */
	case "Y" :
		$langue="Y";
        $lettremenu="Y";
		
		$siteweb="https://www.millesima.it/";
		$shorturl="www.millesima.it";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimavini";
		$lienyoutube="http://www.youtube.com/playlist?list=PLF85D49EF6F1C22A0";
		
		$validite="Offerta valida per 72 ore nel limite degli stock disponibili";
		$validitedate="Offerta valida fino al $datevalide nel limite degli stock disponibili";
		$validitestock="Offerta valida nel limite degli stock disponibili.";
		
		$revendeurs="Questa offerta è rivolta ad una clientela di privati ed in nessun caso ai rivendidori professionisti.";
		
		$ht="IVA escl.";
		$ttc="IVA incl.";
		$fnpx1btlleht="/bott.&nbsp;IVA&nbsp;escl.";
		$fnpxcaissettc="/cassa&nbsp;IVA&nbsp;incl.*";
		$legendepxind="* Contare l'IVA al 22%. L'iva sar&agrave; da saldare dopo la messa a disposizione dei vini da parte della propriet&agrave; con le spese di trasporto";
		$legendepxindmini="*L'IVA (al 22% attualmente) sar&agrave; da saldare dopo la messa a disposizione dei vini con le spese di trasporto.";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Borgogna",
							"vdr"  => "Valle del Rodano",
							"alsace"  => "Alsazia",
							"champagne"  => "Champagne",
							"loire" => "Loira",
							);
		
		$phrase_APartirDe="a partire da";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "3",
							),
							"123" => array(
										"libelle" => "<strong>-40%</strong> per la 2a cassa",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> per la 2a cassa",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Prezzi scontati</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagne</strong> a prezzi scontati",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosati</strong> con prezzi scontati",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Bianchi</strong> a prezzi scontati",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Spedizione gratuita</strong>",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);	*/
		/* ------ Primeurs ------ */
		$primeur="In-Primeur";
		
		$phraseprimeurdirectfrom="Come sempre tutti i vini che Mill&eacute;sima vi propone provengono direttamente dalla propriet&agrave;";
		
		$titreprimeur="Vini in-primeur 2018";
		$phraseprimeur="Consegna <strong>inizio&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nuovi In-Primeur di Bordeaux";
		$sortiebordeaux=" <strong>Nuovi In-Primeur</strong> di Bordeaux";
		$titrerecapbordeaux="Elenco riepilogativo: Nuovi In-Primeur di Bordeaux";
		$recapsortiebordeaux="<strong>Elenco riepilogativo: Nuovi In-Primeur</strong> di Bordeaux";
		
		/* PDF Primeur */
		$pdf_btleht="Bottiglia &euro;<br />".$ht;
		$pdf_caisht="Cassa &euro;<br />".$ht;
		$pdf_caisttc="Cassa &euro;<br />".$ttc."*";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex.
  Tel. 800 781 725 oppure 00 800 267 33 289 - millesima@millesima.com</small>";
		$site_court="www.millesima.it";
		$caisse12=" Cassa/cartone di 12 bott.";
		$caisse6=" Cassa/cartone di 6 bott.";
		$pdf_phraseprimeur="Consegna inizio 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Questi prezzi sono validi fino alla prossima offerta, nel limite degli stock disponibili";
		$newsorties="Nuovi In-Primeur";
		$allsorties="Tutti i In-Primeur";
	
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à l'ESPAGNE --------------- */
	case "E" :
		$langue="E";
        $lettremenu="E";
		
		$siteweb="https://www.millesima.es/";
		$shorturl="www.millesima.es";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimavinos";
		$lienyoutube="http://www.youtube.com/playlist?list=PL13D0964AFEEDBBB7";
		
		$validite="Oferta v&aacute;lida durante 72 horas en el l&iacute;mite de nuestras existencias disponibles";
		$validitedate="Oferta v&aacute;lida hasta el $datevalide en el l&iacute;mite de nuestras existencias disponibles.";
		$validitestock="Oferta válida en el límite de las existencias disponibles.";
		
		$revendeurs="Se dirige únicamente a la clientela particular y en ningún caso a revendedores profesionales.";
		
		$ht="sin IVA";
		$ttc="IVA incl.";
		$fnpx1btlleht="&nbsp;sin&nbsp;IVA/bot.";
		$fnpxcaissettc="&nbsp;IVA&nbsp;incl./caja*";
		$legendepxind="*Precios indicados con IVA (el 21% actualmente en vigor) y gastos de porte uncluidos.";
		
		$regions=array(
							"bordeaux"  => "Burdeos",
							"bourgogne"  => "Borgoña",
							"vdr"  => "Valle del Ródano",
							"alsace"  => "Alsacia",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="a partir de";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "3",
							),
							"123" => array(
										"libelle" => "un <strong>-40%</strong> en la 2a caja",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "un <strong>-50%</strong> en la 2a caja",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Precios rebajados</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagne</strong> con precios rebajados",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosados</strong> con precios rebajados",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Blancos</strong> con precios rebajados",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Entrega gratuita</strong>",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="En-Primeur";
		
		$phraseprimeurdirectfrom="Como siempre, todos los vinos que Mill&Eacute;sima le ofrece provienen directamente de la propiedad.";
		
		$titreprimeur="En-Primeur 2018";
		$phraseprimeur="Entrega <strong>inicios&nbsp;de&nbsp;2021</strong>";
		
		$titresortiebordeaux="Nuevos En-Primeur de Burdeos";
		$sortiebordeaux=" <strong>Nuevos En-Primeur</strong> de Burdeos";
		$titrerecapbordeaux="Recapitulativo: Nuevos En-Primeur de Burdeos";
		$recapsortiebordeaux="<strong>Recapitulativo: Nuevos En-Primeur</strong> de Burdeos";
		
		/* PDF Primeur */
		$pdf_btleht="Botella &euro;<br />".$ht;
		$pdf_caisht="Caja &euro;<br />".$ht;
		$pdf_caisttc="Caja &euro;<br />".$ttc." *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex. Francia - Tlfn gratuito: 900 97 33 42 (o  00 800 267 33 289) - millesima@millesima.com</small>";
		$site_court="www.millesima.es";
		$caisse12=" Caja de 12 bot.";
		$caisse6=" Caja de 6 bot.";
		$pdf_phraseprimeur="Entrega inicios de 2021 "; // Doublon non encodé + sans balises
		$pdf_phraseprix="Estos precios están válidos hasta la próxima oferta, en el límite de las existencias disponibles";
		$newsorties="Nuevos En-Primeur";
		$allsorties="Todos los nuevos En-Primeur";
		
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques au PORTUGAL --------------- */
	case "P" :
		$langue="P";
        $lettremenu="P";
		
		$siteweb="https://www.millesima.pt/";
		$shorturl="www.millesima.pt";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://www.twitter.com/millesimavinho";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231";
		
		$validite="Proposta v&aacute;lida durante 72 horas";
		$validitedate="Oferta v&aacute;lida at&eacute; $datevalide (de acordo com o stock dispon&iacute;vel)";
		$validitestock="Oferta valida de acordo com o stock disponível.";
		
		$revendeurs="Oferta unicamente valida para clientes particulares, não disponível para profissionais.";
		
		$ht=" s/IVA";
		$ttc="c/IVA";
		$fnpx1btlleht="&nbsp;s/IVA&nbsp;a&nbsp;gar.";
		$fnpxcaissettc="&nbsp;c/IVA&nbsp;a&nbsp;caixa*";
		$legendepxind="*Pre&ccedil;os indicativos em euros com IVA (actualmente taxa em vigor de 13%).";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Bourgogne",
							"vdr"  => "Vallée du Rhône",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="a partir de ";
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "3",
							),
							"123" => array(
										"libelle" => "<strong>-40%</strong> sobre a 2a caixa",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> sobre a 2a caixa",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Preços leves</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagne</strong> com preços leves",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> com preços leves",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>Brancos</strong> com preços leves",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Transporte gr&aacute;tis</strong>",
										"ssphrase" => "at&eacute; 31/08/2018",
										"detail" => "*Fora dos vinhos primeurs – Entrega numa morada em Portugal Continental - At&eacute; $datefdpo",
										"style" => "",
										"styledetail" => ""
						);*/
		/* ------ Primeurs ------ */
		$primeur="Primeur";
		
		$phraseprimeurdirectfrom="Como todos os anos, os vinhos que lhe propomos em primeur foram integralmente comprados &Aacute; propriedade.";
		$avantageacheterenprimeur=
			"<strong>As vantagens de comprar em primeur:</strong> Fazer um bom neg&oacute;cio. Tem a garantia de ter comprado os melhores vinhos ao melhor pre&ccedil;o, 
			sem se preocupar em n&atilde;o conseguir encontrar, quando estiverem esgotados ou com pre&ccedil;os mais altos. Tem a certeza de os ter comprado em condi&ccedil;&otilde;es de pre&ccedil;o mais favor&agrave;veis.</br>";
		$acheterenprimeur=
			"<strong>Comprar em primeur:</strong> S&atilde;o grandes vinhos que se encontram durante o seu per&iacute;odo de estagio, que corresponde a 18 meses, entre a vindima e o engarrafamento. 
			Os vinhos Primeurs, s&atilde;o comercializados antes de serem postos no mercado e ser&atilde;o entregues no principio de 2021.</br></br>";
			
		$titreprimeur="Primeurs 2018";
		$phraseprimeur="Entrega <strong>in&iacute;cio&nbsp;2021</strong>";
		
		$titrerecapbordeaux="Recapitulativo Primeurs Bordeaux 2018";
		$titresortiebordeaux="Estreia Primeurs Bordeaux";
		$sortiebordeaux="Estreia <strong>Primeurs Bordeaux</strong>";
		$recapsortiebordeaux="Recapitulativo das sa&iacute;das <strong>Primeurs Bordeaux</strong>";

		/* PDF Primeur */
		$pdf_btleht="Garrafa &euro;<br />".$ht;
		$pdf_caisht="Caixa &euro;<br />".$ht;
		$pdf_caisttc="Caixa &euro;<br />".$ttc." *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex - Tel. 800 833 385 ou 00800 267 33 289 - Linha directa: 00 33 557 808 846 - millesima@millesima.com</small>";
		$site_court="www.millesima.pt";
		$caisse12=" Caixa ou cartão de 12 gar.";
		$caisse6=" Caixa ou cartão de 6 gar.";
		$pdf_phraseprimeur="Entrega início 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="Estes preços são válidos até a próxima oferta, de acordo com o stock disponível";
		$newsorties="Últimas saídas";
		$allsorties="Todos os Primeurs";
	
		/* Options supplémentaires */
		
		break;

/* --------------- Ressources spécifiques à HONG KONG --------------- */
	case "H" :
		$langue="G";
        $lettremenu="H";
		
		$siteweb="https://www.millesima.com.hk/";
		$shorturl="www.millesima.com.hk";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://twitter.com/finewinefutures";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231";
		
		$validite="Special offer valid for 72 hours according to availability";
		$validitedate="Valid till $datevalide according to availability";
		$validitestock="According to availability.";
		
		$revendeurs="This offer is only available to private individuals; it is not available to importers or distributors.";
		
		$ht=" in bond ex VAT";
		$ttc=" inc. VAT and DP*";
		$fnpx1btlleht=" per bottle";
		$fnpxcaissettc=" per case inc. VAT and DP*";
		$legendepxind="Shipping charge of approx. HKD 400.00 to be billed when wines are ready for delivery";
		$legendepxindmini="";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgundy",
							"vdr"  => "Rhone Valley",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="from";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
											"libelle" => "<strong>1+1=3 <br /> -33% from 3 different cases</strong>",
											"nbcaisses" => $phrase_APartirDe . "3",
										),
							"123" => array(
										"libelle" => "<strong>-40%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Special prices</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> at light prices",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> at light prices",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>White wines</strong> at light prices",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "<strong>Free delivery</strong>",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);	*/
		/* ------ Primeurs ------ */
		$primeur="En-Primeur";
		
		$phraseprimeurdirectfrom="This year, as every year, all of Mill&Eacute;sima&acute;s en primeur wines are bought exclusively from each property.";
		
		$titreprimeur="2018 En-Primeur wine";
		$phraseprimeur="Delivery <strong>by early&nbsp;2021</strong>";
		
		$titresortiebordeaux="En-Primeur Bordeaux New releases";
		$sortiebordeaux=" <strong>En-Primeur Bordeaux</strong> New releases";
		$titrerecapbordeaux="Summary: En-Primeur Bordeaux New releases";
		$recapsortiebordeaux="<strong>Summary: En-Primeur Bordeaux</strong> New releases";			

		/* PDF Primeur */
		$pdf_btleht=" ";
		$pdf_caisht="Bottle<br />HK$";
		$pdf_caisttc="Case<br />HK$ *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex - France. Phone: 00 33 557 808 813 Fax: 00 33 557 808  819 - millesima@millesima.com</small>";
		$site_court="www.millesima.com.hk";
		$caisse12=" 12 bottles case or box";
		$caisse6="  6 bottles case or box";
		$pdf_phraseprimeur="Delivery by early 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="These prices are valid till the next offer, according to availability";
		$newsorties="New releases";
		$allsorties="All the releases";
		
		/* Options supplémentaires */
		
		break;
		
/* --------------- Ressources spécifiques à SINGAPOUR --------------- */
	case "SG" :
		$langue="G";
        $lettremenu="H";
		
		$siteweb="https://www.millesima.sg/";
		$shorturl="www.millesima.sg";
		$lienfacebook="http://www.facebook.com/millesima";
		$lientwitter="http://twitter.com/finewinefutures";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231";
		
		$validite="Special offer valid for 72 hours according to availability";
		$validitedate="Valid till $datevalide according to availability";
		$validitestock="According to availability.";
		
		$revendeurs="This offer is only available to private individuals; it is not available to importers or distributors.";
		
		$ht=" in bond ex GST";
		$ttc=" inc. GST and DP*";
		$fnpx1btlleht="/btl in bond ex GST";
		$fnpxcaissettc="/case inc. GST and DP*";
		$legendepxind="* Estimation of price including current GST & duties (To be invoiced with a shipping charge of approx. SGD 65.00 to be billed when wines are ready for delivery).";
		$legendepxindmini="";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgundy",
							"vdr"  => "Rhone Valley",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="from";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
											"libelle" => "<strong>1+1=3 <br /> -33% from 3 different cases</strong>",
											"nbcaisses" => $phrase_APartirDe . "3",
										),
							"123" => array(
										"libelle" => "<strong>-40%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Special Prices</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> at light prices",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> at light prices",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>White wines</strong> at light prices",
										"nbcaisses" => "",
										),
							);
		/* = array( "phrase" => "<strong>Free delivery</strong>",
										"ssphrase" => "until the 31/08/2018",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);					*/
		/* ------ Primeurs ------ */
		$primeur="En-Primeur";
		
		$phraseprimeurdirectfrom="This year, as every year, all of Mill&Eacute;sima&acute;s en primeur wines are bought exclusively from each property.";
		
		$titreprimeur="2018 En-Primeur wine";
		$phraseprimeur="Delivery <strong>by early&nbsp;2021</strong>";
		
		$titresortiebordeaux="En-Primeur Bordeaux New releases";
		$sortiebordeaux=" <strong>En-Primeur Bordeaux</strong> New releases";
		$titrerecapbordeaux="Summary: En-Primeur Bordeaux New releases";
		$recapsortiebordeaux="<strong>Summary: En-Primeur Bordeaux</strong> New releases";			

		/* PDF Primeur */
		$pdf_btleht=" ";
		$pdf_caisht="Bottle<br />HK$";
		$pdf_caisttc="Case<br />HK$ *";
		$coordonnees="Mill&eacute;sima SA <small>- 87, quai de Paludate - CS 11691 - F33050 Bordeaux Cedex - France. Phone: 00 33 557 808 813 Fax: 00 33 557 808  819 - millesima@millesima.com</small>";
		$site_court="www.millesima.com.hk";
		$caisse12=" 12 bottles case or box";
		$caisse6="  6 bottles case or box";
		$pdf_phraseprimeur="Delivery by early 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="These prices are valid till the next offer, according to availability";
		$newsorties="New releases";
		$allsorties="All the releases";
		
		/* Options supplémentaires */
		
		break;
		
/* --------------- Ressources spécifiques aux USA --------------- */		
	case "U" :
		$langue="U";
        $lettremenu="U";
		
		$siteweb="https://www.millesima-usa.com/";
		$shorturl="www.millesima-usa.com";
		$lienfacebook="http://www.facebook.com/#!/Millesima.USA";
		$lientwitter="https://twitter.com/#!/millesimausa";
		$lienyoutube="http://www.youtube.com/playlist?list=PLB7FAFA756FC5A231&feature=plcp";
		
		$validite="Valid according to availability.";
		$validitedate="Valid according to availability.";
		$validitestock="According to availability.";
		
		$revendeurs="This offer is only available to private individuals; it is not available to importers or distributors.";
	
		$ht="";
		$ttc="";
		$fnpx1btlleht=" per bottle";
		$fnpxcaissettc=" per case";
		$legendepxind="";
		$legendepxindmini="";
		
		$regions=array(
							"bordeaux"  => "Bordeaux",
							"bourgogne"  => "Burgundy",
							"vdr"  => "Rhone Valley",
							"alsace"  => "Alsace",
							"champagne"  => "Champagne",
							"loire" => "Loire",
							);
		
		$phrase_APartirDe="from";					
		$promo_courtes=array(
							"1"  => array(
										"libelle" => "<strong>1+1=3</strong>",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"2"  => array(
								"libelle" => "<strong>1+1=3</strong>",
								"nbcaisses" => $phrase_APartirDe . "2",
							),
							"123" => array(
										"libelle" => "<strong>-40%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"127" => array(
										"libelle" => "<strong>-50%</strong> on the 2nd case",
										"nbcaisses" => $phrase_APartirDe . "2",
										),
							"702" => array(
										"libelle" => "<strong>Special prices</strong>",
										"nbcaisses" => "",
										),
							"703" => array(
										"libelle" => "<strong>Champagnes</strong> at light prices",
										"nbcaisses" => "",
										),
							"704" => array(
										"libelle" => "<strong>Rosés</strong> at light prices",
										"nbcaisses" => "",
										),
							"705" => array(
										"libelle" => "<strong>White wines</strong> at light prices",
										"nbcaisses" => "",
										),
							);
		/*$fdpo = array( "phrase" => "",
										"ssphrase" => "",
										"detail" => "",
										"style" => "",
										"styledetail" => ""
						);	*/
							
		/* ------ Primeurs ------ */
		$primeur="Future";
		$phraseprimeurdirectfrom="This year, as every year, all of Millesima&acute;s en primeur wines are bought exclusively from each property.";
		
		$titreprimeur="2018 Futures wine";
		$phraseprimeur="Delivery <strong>early&nbsp;2021</strong>";
		
		$titresortiebordeaux="Bordeaux Futures New releases";
		$sortiebordeaux=" <strong>Bordeaux Futures</strong> New releases";
		$titrerecapbordeaux="Summary: Bordeaux Futures New releases";
		$recapsortiebordeaux="<strong>Summary: Bordeaux Futures</strong> New releases";
		
		/* PDF Primeur */
		$pdf_btleht=" ";
		$pdf_caisht="$ / Bottle";
		$pdf_caisttc="$ / Case";
		$coordonnees="Millesima USA <small>1355 2nd Ave. New York, NY 10021 Tel: (212) 639-9463 - info@millesima.com</small>";
		$site_court="www.millesima-usa.com";
		$caisse12=" 12 bottles case or box";
		$caisse6="  6 bottles case or box";
		$pdf_phraseprimeur="Delivery early 2021"; // Doublon non encodé + sans balises
		$pdf_phraseprix="These prices are valid till the next offer, according to availability";
		$newsorties="New releases";
		$allsorties="All the releases";
	
		/* Options supplémentaires */
		
		break;
}
?>
