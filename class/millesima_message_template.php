
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 18/12/13
 * Time: 17:27
 * To change this template use File | Settings | File Templates.
 */
use voku\helper\HtmlMin;
require 'vendor/autoload.php';

include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';
include_once 'article.php';
class Millesima_Message_Template extends Millesima_Abstract
{

    const DEBUG = false;

    public function createMessage($data, $compress = false){
        $appPath = dirname(__DIR__, 1)."/";
        $ressourceClass = new Millesima_Ressource();

        //param default
        $titre = "Ceci est un email automatique";
        $html = '';
        $htmlfr = '';
        $htmlde = '';
        $htmluk = '';
        $htmlit = '';
        $htmles = '';
        $htmlpt = '';
        $htmlus = '';

        //param onglet 1
        $codemessage = $data["codemessage"];
        /*if (! is_dir(self::REPAPPLI."emailings/".$codemessage)){
            mkdir(self::REPAPPLI."emailings/".$codemessage, 0777);
        }*/
        //die($appPath."fichiers/emailings/".$codemessage);
        if (! is_dir($appPath."fichiers/emailings/".$codemessage)){
            mkdir($appPath."fichiers/emailings/".$codemessage, 0777);
        }

        $datevalide = $data["datevalide"];
        $dateenvoi = $data["dateenvoi"];
        $datefdpo = $data["datefdpo"];
        $vardates = array('datevalide'=>$datevalide,'datefdpo'=>$datefdpo, 'dateenvoi'=>$dateenvoi);
        $tpl = $data["tpl"];
        $type_message = "ios";

        $tracking_omn=$data["tracking"];
        $tracking_ibm=$data["tracking_ibm"];

        //recuperation produit onglet 2
        $listing = $data["listing"];
        $type_listing = $data["type_listing"];
        $type_listing_promo = $data["type_listing_promo"];
        $articleACharger=trim($data["articles"]);
        $articleACharger=str_replace("\r\n",";",$articleACharger);
        $articleACharger=str_replace(" ","",$articleACharger);
        $typeref=$data["type_ref"];
        if($articleACharger != ""){
            $idtoload=explode(";", $articleACharger);
        }

        $cgv = $data["cgv"];
		if(isset($data["other_cgv"]) && $data["other_cgv"]){
			$cgv2 = $data["cgv2"];
			$cgvexceptions = $data["cgv_exceptions"];
		}
		
        if($data["block_image"]){
			$filename = $appPath.'smarty/templates/'.$tpl."/block_image/fonctions_images.php";
			if (file_exists($filename)){
                require_once($filename);
            }
        }
		
        //recuperation pays onglet 4
        $mespays=$data["pays"];
        $listepays='';
        foreach ($mespays as $onepays) {
            $listepays .= $onepays.";";
        }
        $listepays = substr($listepays, 0,-1);
        $countrytoload=explode(";", $listepays);


        /**
         * GENERATION DES FICHIERS E-MAILING
         * pour chaque pays on effectue une boucle qui genere le fichier .html
         */
        foreach ($countrytoload as $country) {
            require('ressources.php');
            //$html .=  "<h3>Traitement de ".$country."</h3>";

            $maliste = array();
			$noms=array();
			$appellations=array();
            if(isset($idtoload)){
                foreach ($idtoload as $loaded) {
                    if ($typeref == "sku"){
                        $article = $this->load($loaded,$country,$typeref);
                    }else{
                        $article = $this->load($loaded,$country);
                    }
                    if($article == false) {
                        $html .= " Produit non trouvé : ".$loaded."</br>";

                    } else {
                        if (!in_array($article->libelle_internet, $noms)) {
                            $noms[] = $article->libelle_internet . " " . $article->millesime;
                        }
                        if (!in_array($article->appellation, $appellations)) {
                            $appellations[] = $article->appellation;
                        }
                        //print_r($article);
                        if ($article->prix_ttc != "") {
                            $maliste[] = $article;
                        }
                        unset($article);
                    }
                }
            }
            $listeproduit = $maliste;
			
            /**
             * @var Smarty
             * on cree un objet smarty pour chaque pays auxquel on assigne le tableau d'ojet Article
             * et les ressources spécifiques emailing
             * see /config/ressources.php
             */
            $oSmarty = new Smarty();
            $oSmarty->template_dir = $appPath.'smarty/templates/';
            $oSmarty->compile_dir = $appPath.'smarty/templates_c/';
            $oSmarty->config_dir = $appPath.'smarty/configs/';
            $oSmarty->cache_dir = $appPath.'smarty/cache/';
            $oSmarty->debugging = false;
            $oSmarty->error_reporting = E_ALL & ~E_NOTICE;

			//if(in_array($country, $is_ibm)){
				$tracking = $tracking_ibm;
			/*}else{
				$tracking = $tracking_omn;
			}*/
			$oSmarty->assign('tracking', $tracking);
			
            $oSmarty->assign('type_message', $type_message);

            
            $oSmarty->assign('tpl', $tpl);
            $oSmarty->assign('dateenvoi', $dateenvoi);
            $oSmarty->assign('region', $regions);
            /*$oSmarty->assign('entetecol', $entetecol);
            $oSmarty->assign('dtentetecol', $dtentetecol);
            $oSmarty->assign('fnentetecol', $fnentetecol);
            $oSmarty->assign('dtentetecolT2', $dtentetecolT2);
            $oSmarty->assign('fnentetecolT2', $fnentetecolT2);
            $oSmarty->assign('dtentcolpxindic', $dtentcolpxindic);
            $oSmarty->assign('fnentcolpxindic', $fnentcolpxindic);
            $oSmarty->assign('dtentcolpxht', $dtentcolpxht);
            $oSmarty->assign('fnentcolpxht', $fnentcolpxht);*/
            $oSmarty->assign('ht', $ht);
            $oSmarty->assign('ttc', $ttc);
            $oSmarty->assign('fnpx1btlleht', $fnpx1btlleht);
            if(isset($fnpx1btllettc)){
                $oSmarty->assign('fnpx1btllettc', $fnpx1btllettc);
            }
            $oSmarty->assign('fnpxcaissettc', $fnpxcaissettc);
            $oSmarty->assign('legendepxind', $legendepxind);
            $oSmarty->assign('codemessagegeneral', $codemessage);
            $oSmarty->assign('codemessage', $country.$codemessage);
            $oSmarty->assign('titre', $titre);
            $oSmarty->assign('country', $country);
            $oSmarty->assign('datevalide', $datevalide);
            if(isset($lienmail)){
                $oSmarty->assign('lienmail', $lienmail);
            }
            $oSmarty->assign('siteweb', $siteweb);
            $oSmarty->assign('shorturl', $shorturl);
            //$oSmarty->assign('lienfacebook',$lienfacebook);
            //$oSmarty->assign('lientwitter',$lientwitter);
            //$oSmarty->assign('lienyoutube',$lienyoutube);
            $oSmarty->assign('liste_produits', $listeproduit);
            $oSmarty->assign('validite',$validite);
            $oSmarty->assign('validitedate',$validitedate);
            $oSmarty->assign('langue',$langue);
            $oSmarty->assign('lettremenu',$lettremenu);
            $oSmarty->assign('codecouleur',$_POST["codecouleur"]);
            $oSmarty->assign('couleurtxtbtn',$_POST["couleurtxtbtn"]);
            $oSmarty->assign('promos',$promo_courtes);
            $oSmarty->assign('phraseprimeur', $phraseprimeur);
            $oSmarty->assign('primeur', $primeur);


            //faire le tableau avec les sujects
            $objet_alt_title = $data['objet-'.$country];
            $oSmarty->assign('objet_alt_title',$objet_alt_title);
            //$html .= $objet_alt_title."<br />";

            /*
            * Header
            * versions en ligne/smartphone, thème, vcard desabonnement
            *
		    */
			$filename = $appPath.'smarty/templates/'.$tpl."/entete/ressources_entetes.php";
			if (file_exists($filename)){
                require($filename);
            }
			
            /* Conditions pour le menu */
            $filename = $appPath.'smarty/templates/'.$tpl."/menus/ressources_menus.php";
            if (file_exists($filename)){
                require($filename);
            }
            if(isset($data['menu_sans_primeurs'])){
                $oSmarty->assign('sans_primeurs', $data['menu_sans_primeurs']);
            }

            if(isset($_POST["titregen"]) && $_POST["titregen"]){
                $oSmarty->assign('titregen', $_POST["titregen"]);
                $oSmarty->assign('titre', $this->encodeVar($_POST["titre_".$country]));
            }
			
			/* MODULE BLOCK_IMAGE */
            if($data["block_image"]){
				$filename = $appPath.'smarty/templates/'.$tpl."/block_image/traitement_images.php";
				if (file_exists($filename)){
					require($filename);
				}
				$oSmarty->assign('blockimg', $data["block_image"]);
            }

			
			$filename = $appPath.'smarty/templates/'.$tpl."/boutons/ressources_btns.php";
            if (file_exists($filename)){
                require($filename);
            }

            /*
			$filename = self::REPAPPLI.'smarty/templates/'.$tpl."/informations_importantes/ressources_livraison.php";
            if (file_exists($filename)){
                require($filename);
            }
			$filename = self::REPAPPLI.'smarty/templates/'.$tpl."/informations_importantes/ressources_conditionsvalidite.php";
            if (file_exists($filename)){
                require($filename);
            }*/
            $bdheader = array('title'=>$ressourceClass->getRessourceValue($country,'bd_header_title', $vardates),'detail'=>$ressourceClass->getRessourceValue($country,'bd_header_detail', $vardates),'asterisque'=>$ressourceClass->getRessourceValue($country,'bd_header_asterisque', $vardates));

            $oSmarty->assign('bdheader', $bdheader);

            /* Section Description gÃ©nÃ©rale sous l'image */

            if(isset($_POST["descgen"]) && $_POST["descgen"]){
                $oSmarty->assign('descgen', $_POST["descgen"]);
                $oSmarty->assign('desctypebtn', $_POST["desctypebtn"]);
                //$oSmarty->assign('descbtnwidth', $btns[$_POST["desctypebtn"]]["width"]);
				/*urlgen  est le lien de la première image dans traitement image */
				$url = $urlgen;
				$titre = $this->encodeVar($_POST["desctitre".$country]);
				$text = $_POST["desctext".$country];
				$titreupper = "";
                $textalign = "";
                $astdesc = "";
                $asterisquedesc = "";
				if(isset($_POST["desctitreupper"])){
                    $titreupper = $_POST["desctitreupper"];
				}
				if(isset($_POST["astdesc"])){
                    $astdesc = $_POST["astdesc"];
                    $asterisquedesc = $ressourceClass->getRessourceValue($country,'ast_description',$vardates);
                }

                $filename = $appPath.'smarty/templates/'.$tpl."/informations_importantes/ressources_codepromobandeau.php";
                if (file_exists($filename)){
                    require($filename);
                }

                $textalign = $_POST["align_desc"];
				$btn = $_POST["desctypebtn"];
				$proprietesDesc = array("url" => $url,
												"titre" => $titre,
												"text" => $text,
												"titreupper" => $titreupper,
												"textalign" => $textalign,
												"astdesc" => $asterisquedesc,
												"btn" => $btn);
                $oSmarty->assign('desc', $proprietesDesc);
				
				if(isset($_POST["iscodepromo"])){
					$iscodepromo = $_POST["iscodepromo"];
					$codepromo = $_POST["codepromo"];
					$oSmarty->assign('iscodepromo', $iscodepromo);
					$oSmarty->assign('codepromo', $codepromo);
					
					$filename = $appPath.'smarty/templates/'.$tpl."/codepromo/ressources_codepromo.php";
					if (file_exists($filename)){
						require($filename);
					}
				}
            }
			/* Listing */
			
			if($listing){
                $oSmarty->assign('listing', $listing);
            }
            if($type_listing == 'ssprix'){
                $oSmarty->assign('type_listing', 'defaut');
                $oSmarty->assign('ssprix', 1);
			}elseif($type_listing){
				$oSmarty->assign('type_listing', $type_listing);
                $oSmarty->assign('ssprix', 0);
            }
            if($listing && $type_listing == "promo"){
				if($type_listing_promo != 'defaut'){
					$lstprmodesc = true;
					
					/*création des listes noms et appellations des produits */
					$nbnoms = count($noms);
					$nomshtml="";
					for($i = 0; $i < $nbnoms; $i++){
						$nomshtml.=$noms[$i];
						if($i < $nbnoms-1){
							$nomshtml.=", ";
						}
					}
					$nomshtml.="...";
					$oSmarty->assign('nomsvins', $nomshtml);
					
					$nbappellations = count($appellations);
					$appellationshtml="";
					for($i = 0; $i < $nbappellations; $i++){
					$appellationshtml.=$appellations[$i];
						if($i < $nbappellations -2){
							$appellationshtml.=", ";
						}else if ($i == $nbappellations-2){
							$appellationshtml.=" or ";
						}
					}
					$oSmarty->assign('appvins', $appellationshtml);
					
					$filename = $appPath.'smarty/templates/'.$tpl."/listing_produits/ressources_listing.php";
					if (file_exists($filename)){
						require($filename);
					}
					$oSmarty->assign('type_listing_promo', $type_listing_promo);
					if($promos_listing[$type_listing_promo]["url"] != ""){
						$promos_listing[$type_listing_promo]["url"] = $this->getTracking($siteweb.$promos_listing[$type_listing_promo]["url"], $tracking);
					}else{
						$promos_listing[$type_listing_promo]["url"] = $urlgen;
					}
					$oSmarty->assign('lstpromotab', $promos_listing[$type_listing_promo]);
				}else{
					$lstprmodesc = false;
				}
				$oSmarty->assign('lstprmodesc', $lstprmodesc);
            }
			
            /* Section articles supplementaires */

            if(isset($_POST["section_article"]) && $_POST["section_article"]){
				$oSmarty->assign('section_article', $_POST["section_article"]);
                $oSmarty->assign('articles_nb', $_POST["articles_nb"]);
				//Creation d'un tableau contenant les variables de chaque article
				$proprietesArticles = array();
				$article = "";
				$url = "";
				$titre = "";
				$text = "";
				$titreupper = "";
				$artimgprim = "";
				$btn = "";
				$btnwidth = "";
				$exception = "";
                $nb = "00";
				$articles_ast = $ressourceClass->getRessourceValue($country,'ast_articles', $vardates);
                
				for ($i = 1; $i <= intval($_POST["articles_nb"]); $i++){
					$article = "article".$i;
					$url = "";
					if(!isset($_POST[$article."_nourl"]) || !$_POST[$article."_nourl"]){
						$url=$this->getUrl($article.'_url', $tracking, $siteweb, $country);
					}
					if(isset($_POST[$article.'_exceptions']) && $_POST[$article.'_exceptions'] and in_array($country, $_POST[$article.'_exceptions_pays'])){
						if(self::DEBUG){
							echo 'pays exception : '.$country.'<br />'; 
						}
						$exception=true;
					}else{
						$exception=false;
					}
					if($i<10){
						$nb="0".$i;
					}else{
						$nb=$i;
					};
					$titre = $this->encodeVar($_POST[$article."titre".$country]);
					$text = $_POST[$article."text".$country];
					if(isset($_POST[$article."titreupper"])){
						$titreupper = $_POST[$article."titreupper"];
                    }
                    $ast_art = '';
					if(isset($_POST[$article."_astart"])){
                        $astart = $_POST[$article."_astart"];
                        $ast_art = $ressourceClass->getRessourceValue($country,'ast_article'.$i, $vardates);
                        if($ast_art == '' && $articles_ast != ''){
                            $ast_art = $articles_ast;
                        }
                    }
					if(isset($_POST[$article."_artimgprim"])){
						$artimgprim = $_POST[$article."_artimgprim"];
					}
                    $btn = $_POST[$article."typebtn"];

					$proprietesArticles[$article] = array("url" => $url,
												"imgnb" => $nb,
												"titre" => $titre,
												"text" => $text,
												"titreupper" => $titreupper,
												"astart" => $ast_art,
												"artimgprim" => $artimgprim,
												"btn" => $btn,
												/*"btnwidth" => $btns[$btn]["width"],*/
												/*"btnheight" => $btns[$btn]["height"],*/
												"exception" => $exception);
					
				}
				$oSmarty->assign('articles', $proprietesArticles);
            }

            /* Contact */
            $filename = $appPath.'smarty/templates/'.$tpl."/contact/ressources_contact.php";
            if (file_exists($filename)){
                require($filename);
            }
            /* Reassurance */
            $filename = $appPath.'smarty/templates/'.$tpl."/reassurance/ressources_reassurance.php";
            if (file_exists($filename)){
                require($filename);
            }

            /* Desabo */
            $filename = $appPath.'smarty/templates/'.$tpl."/desabo/ressources_desabo.php";
            if (file_exists($filename)){
                require($filename);
            }
			
			/* mentions légales */
            $filename = $appPath.'smarty/templates/'.$tpl."/mentions/ressources_mentions.php";
            if (file_exists($filename)){
                require($filename);
            }

            /* Push */
			$filename = $appPath.'smarty/templates/'.$tpl."/push/traitement_push.php";
            if (file_exists($filename)){
                require($filename);
            }

            /* Reseaux sociaux */
            $filename = $appPath.'smarty/templates/'.$tpl."/social/ressources_social.php";
            if (file_exists($filename)){
                require($filename);
            }

            /* Wallet Widget */
            if(isset($_POST["w_wallet"]) && $_POST["w_wallet"]){
                $filename = $appPath.'smarty/templates/'.$tpl."/wallet/ressources_wallet_selligent.php";
                if (file_exists($filename)){
                    require($filename);
                }
                $oSmarty->assign('w_wallet', $_POST["w_wallet"]);
            }

            /* Promotion card */

            if(isset($_POST["isPromotionCard"]) && $_POST["isPromotionCard"]) {
                $isPromotionCard = $data['isPromotionCard'];
                $promotionCardDescription = $data['promotionCardDescription-' . $country];

                $promotionCardDiscountCode = $data['promotionCardDiscountCode'];
                $promotionCardImageLink = $data['promotionCardImageLink'];


                //mise au format des dates pour Gmail
                $promotionCardDateStart = new DateTime('NOW');
                $promotionCardDateStart = $promotionCardDateStart->format('c');
                $promotionCardDateEnd = DateTime::createFromFormat('d/m/Y',  $data['datevalide']);
                $promotionCardDateEnd =  $promotionCardDateEnd->format('c');

                $oSmarty->assign('isPromotionCard', $isPromotionCard);
                $oSmarty->assign('promotionCardDescription', $promotionCardDescription);
                $oSmarty->assign('promotionCardDiscountCode', $promotionCardDiscountCode);
                $oSmarty->assign('promotionCardImageLink', $promotionCardImageLink);
                $oSmarty->assign('promotionCardDateStart', $promotionCardDateStart);
                $oSmarty->assign('promotionCardDateEnd', $promotionCardDateEnd);

            }

            /* Gestion CGV */
			if(isset($cgv2) && $cgv2 && in_array($country, $cgvexceptions)){
				$oSmarty->assign('typecgv', $cgv2);	
			}
			else{
				 $oSmarty->assign('typecgv', $cgv);
			}
            if($cgv == 'primeurs' || (isset($cgv2) && $cgv2 == 'primeurs')){
                if(isset($data['cgv_prim_actuelle']) && $data['cgv_prim_actuelle']){
                    $oSmarty->assign('prim_act', $data['cgv_prim_actuelle']);
                }
                if(isset($data['cgv_prim_prec']) && $data['cgv_prim_prec']){
                    $oSmarty->assign('prim_prec', $data['cgv_prim_prec']);
                }
            }
			
            /* FDPO */

                $fdpo = array(
                    'titre'=>$ressourceClass->getRessourceValue($country,'bdf_fdpo', $vardates),
                    'ssphrase'=>$ressourceClass->getRessourceValue($country,'bdf_fdpo_ssphrase', $vardates),
                    'detail'=>$ressourceClass->getRessourceValue($country,'bdf_fdpo_detail',$vardates)
                );
                $oSmarty->assign('fdpo', $fdpo);
                if(isset($data["fdpo_bandeau"]) && $data["fdpo_bandeau"]){
                    $oSmarty->assign("fdpo_bandeau",$data["fdpo_bandeau"]);
                }
                if(isset($data["fdpo_conditions"]) && $data["fdpo_conditions"]){
                    $oSmarty->assign("fdpo_conditions",$data["fdpo_conditions"]);
                }


			
			/* Gestion Trigger */ 
			/*if($data["tpl"] == "trigger_responsive"){
				$oSmarty->assign("trig",$data["trig"]);
				
				$filename = self::REPAPPLI.'smarty/templates/'.$tpl."/ressources_trigger.php";
				if (file_exists($filename)){
					require($filename);
				}
				
				$filename = self::REPAPPLI.'smarty/templates/'.$tpl."/content/ressources_".$data["trig"].".php";
				if (file_exists($filename)){
					require($filename);
				}
			}*/

            /**
             * Choix du modèle de message(s) créé(s)
             * @choix Normal
             * @choix responsive (attention Android & iphone)
             * @choix responsive version mobile (exclusion de outlook & lotus notes)
             *
             * Choix unique en v 1.0 - idéalement, plusieurs versions.
             */
            if ($data["modele"]){
                $output = $oSmarty->fetch($tpl.'/'.$data['modele'].'.tpl');
            } else {
                $output = $oSmarty->fetch($tpl.'/normal.tpl'); /* Version par défaut */
            }
            /**
             * Minifier le Html pour éviter les messages tronqués dans Gmail
             * CF : https://github.com/voku/HtmlMin
             */
            if ($compress == true) {
                $htmlMin = new HtmlMin();
                $htmlMin->doOptimizeViaHtmlDomParser();
                $htmlMin->doRemoveComments();
                $htmlMin->doSumUpWhitespace();
                $htmlMin->doRemoveWhitespaceAroundTags();
                $htmlMin->doRemoveSpacesBetweenTags();
                $output = $htmlMin->minify($output);
            }
            /**
             * Ecriture des fichiers html depuis le return $output de l'objet smarty
             * @var unknown_type
             */
            $filename = $appPath."fichiers/emailings/".$codemessage."/".$country.$codemessage.".html";
            //echo $filename;exit;
            $handle = fopen("$filename", "w");
            fwrite($handle,$this->encodeVar($output));
            $linkFileHtml = '/fichiers/emailings/'.$codemessage.'/'.$country.$codemessage.'.html';


            $traductionClass = new Millesima_Traduction();
			if($country == 'F' || $country == 'B' || $country == 'L' || $country == 'SF'){
                $briefClass = new Millesima_Brief();
                $brief = $briefClass->getBrief($data['brief_id']);
				$htmlfr .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$brief['objfr']."<br />";
			}elseif($country == 'D' || $country == 'O' || $country == 'SA'){
				$objet = $traductionClass->getValueTrad($data['brief_id'],'d','objtrad');
                $htmlde .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}elseif($country == 'G' || $country == 'I' || $country == 'H' || $country == 'SG'){
                $objet = $traductionClass->getValueTrad($data['brief_id'],'g','objtrad');
				$htmluk .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}elseif($country == 'Y'){
                $objet = $traductionClass->getValueTrad($data['brief_id'],'y','objtrad');
				$htmlit .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}elseif($country == 'E'){
                $objet = $traductionClass->getValueTrad($data['brief_id'],'e','objtrad');
				$htmles .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}elseif($country == 'P'){
                $objet = $traductionClass->getValueTrad($data['brief_id'],'p','objtrad');
				$htmlpt .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}elseif($country == 'U'){
                $objet = $traductionClass->getValueTrad($data['brief_id'],'u','objtrad');
				$htmlus .= "<a target='_blank' href='http://" . self::DOCKER_HOST_IP . ":" . self::DOCKER_HOST_PORT . "/fichiers/emailings/".$codemessage."/".$country.$codemessage.".html'>".$country.$codemessage.".html"."</a> | objet : ".$objet['value']."<br />";
			}

            //get version text of mail
            $versionText = $output;
            $regex='/<style[^>]*>.*?<\/style>/s';
            $versionText = preg_replace($regex,' ',$versionText);
            $versionText = strip_tags($versionText);
            $versionText = trim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $versionText));

            $bddClass = new Millesima_Bdd();
            $messageClass = new Millesima_Message();
            $abtest=$data["abtest"];
            $dateenvoi = str_replace("/","-",$dateenvoi);
            $dateenvoi = date("Y-m-d", strtotime($dateenvoi));
            if ($abtest == "true") {
                $vartest=array("a", "b", "d");
                foreach($vartest as $var){
                    $nameMessage = $country.$codemessage."-".$var;
                    $res = $messageClass->getMessageByName($nameMessage);
                    if(count($res)>0){
                        $bddClass->update("UPDATE message SET brief_id = ?,created_at = ?, file_html_link = ?,html = ? ,text = ? where name = ?",array($data['brief_id'],date('Y-m-d H:i:s'),$linkFileHtml,$output,$versionText,$nameMessage));
                    } else {
                        $bddClass->insert("INSERT INTO message (brief_id,name,created_at,file_html_link,html,text) VALUES (?,?,?,?,?,?)",array($data['brief_id'],$nameMessage,date('Y-m-d H:i:s'),$linkFileHtml,$output,$versionText));
                    }
                    $bddClass->update("UPDATE message SET dateenvoi = (?) where brief_id = (?)", array($dateenvoi, $data['brief_id']));
                    $html .="Le message ".$nameMessage." a été créé <br />";
                }
            }else{

                $nameMessage = $country.$codemessage;
                $res = $messageClass->getMessageByName($nameMessage);
                if(count($res)>0){
                    $bddClass->update("UPDATE message SET brief_id = ?,created_at = ?, file_html_link = ?,html = ? ,text = ? where name = ?",array($data['brief_id'],date('Y-m-d H:i:s'),$linkFileHtml,$output,$versionText,$nameMessage));
                } else {
                    $bddClass->insert("INSERT INTO message (brief_id,name,created_at,file_html_link,html,text) VALUES (?,?,?,?,?,?)",array($data['brief_id'],$nameMessage,date('Y-m-d H:i:s'),$linkFileHtml,$output,$versionText));
                }
                $bddClass->update("UPDATE message SET dateenvoi = (?) where brief_id = (?)", array($dateenvoi, $data['brief_id']));
                $html .="Le message ".$nameMessage." a été créé <br />";
            }
        }




		// affichage des liens vers les HTMl par groupement de pays de validation
        $html .=  $htmlfr."<br />";
        $html .=  $htmlde."<br />";
        $html .=  $htmluk."<br />";
        $html .=  $htmlit."<br />";
        $html .=  $htmles."<br />";
        $html .=  $htmlpt."<br />";
        $html .=  $htmlus."<br />";


        $briefClass = new Millesima_Brief();
        $bddClass->update("UPDATE brief SET messagemounted = 1 where  id = (?)", array($data['brief_id']));
        $tradOk = $briefClass->getStatutBrief($data['brief_id']);
        if($tradOk == "4"){
            $briefClass->updateStatus("5",$data['brief_id']);
        }

        return $html;
    }


    public function getCollection($codemessage) {
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT distinct(Code_article),Classement_cata,Classement_Appellation,Millesime,Lettre_Format,quantite FROM baseok WHERE Lettre_Format = 'c' AND Doc_prix LIKE ? ORDER BY  Classement_Appellation ASC, quantite ASC, Classement_cata ASC, Millesime DESC",array('_'.$codemessage));
        foreach ($res as $item) {
            $col .=$item["Code_article"].";";
        }
        $col = substr($col, 0,-1);
        //print_r($col);exit;
        return $col;
    }


    /**
     * Charge les propri�t�s principales de l'objet en se basant sur sa Refpick et sa lettre Pays
     * ex: $idtoload = 1001_2005_CB_C_6 ou 1001/05/C
     * ex: $country = "F"
     * @param $idtoload
     * @param $country
     * @param $type
     * @return unknown_type
     */
    public function load($idtoload,$country,$type = 'Code_article') {
        $bddClass = new Millesima_Bdd();
        $data = $bddClass->selectAll("SELECT * FROM baseok WHERE ".$type." = '".$idtoload."' AND Lettre_pays = '".$country."'");
        if (is_array($data) && isset($data[0])) {
        $data = $data[0];

        $article = new Article;

        //preparation obj
        $article->pays=$data["Lettre_pays"];
        $article->refpick=$data["Code_article"];
        $article->sku=$data["sku"];

        // utile pour les noms images produits dans les listings
        $ref = explode('/', $data["sku"]);
        $article->shortref=strtoupper($ref[0]);
        $article->shortmill = $ref[1];
        $article->leformatorig=strtoupper($ref[2]);

		$expl=explode('&lt;br/&gt;', $data["Libelle_Internet"]);
		/* --- Rajout d'une valeur formatée du libellé internet --- */
		if (isset($expl[1])){
			$article->libelle_internet=utf8_encode($expl[0]." ".$expl[1]);
			$article->libelle_internet_html=utf8_encode("<strong>".$expl[0]."</strong><br />".$expl[1]);
		}else{
			$article->libelle_internet=utf8_encode($expl[0]);
			$article->libelle_internet_html=utf8_encode("<strong>".$expl[0]."</strong>");
		}
		
        $article->millesime=$data["Millesime"];
        $article->classement=utf8_encode($data["Libelle_Classement"]);
        $article->cru=utf8_encode($data["Libelle_Cru"]);
        $article->marque=utf8_encode($data["Libelle_Marque"]);
        $article->appellation=utf8_encode($data["Libelle_appellation"]);
        $article->region=utf8_encode($data["Libelle_region"]);
        $article->typedevin=$data["Libelle_Typeproduit"];
        $article->couleur=$data["Libelle_couleur"];
        $article->LibelleCouleur=utf8_encode($data["Libelle_couleur"]); // DOUBLON ! VERIFIER LEQUEL EST UTILISE
        $article->idcouleur=$data["Code_Couleur"];
        $article->primeur=$data["_1Prim0Liv"];
        $article->image=$data["Image"];
        $article->url_image_full=$data["URL_Image_Full"];
        $article->url_image_thumb=$data["URL_Image_Thumb"];

        $article->quantite=$data["quantite"];
        $article->boiscarton=$data["BoisCarton"];
		if($article->pays == 'F' || $article->pays == 'B' || $article->pays == 'L' || $article->pays == 'SF' ){
            $article->boiscarton = str_replace('Une', '', $article->boiscarton);
            $article->boiscarton = str_replace('Un', '', $article->boiscarton);
            $article->boiscarton = str_replace('carton', 'Carton', $article->boiscarton);
            $article->boiscarton = str_replace('caisse', 'Caisse', $article->boiscarton);
        }
        if($article->pays == 'Y'){
            $article->boiscarton = str_replace('Una', '', $article->boiscarton);
            $article->boiscarton = str_replace('Un', '', $article->boiscarton);
            $article->boiscarton = str_replace('cassa', 'Cassa', $article->boiscarton);
            $article->boiscarton = str_replace('cartone', 'Cartone', $article->boiscarton);
        }
		if($article->pays == 'P'){
            $article->boiscarton = str_replace('cartao', 'cart&atilde;o', $article->boiscarton);
        }

        if($article->pays == 'E'){
            $article->boiscarton = str_replace('Una', '', $article->boiscarton);
            $article->boiscarton = str_replace('caja', 'Caja', $article->boiscarton);
        }



        $article->conditionnementpluriel=utf8_encode($data["Libelle_Cond_pluriel"]);
        $article->conditionnementsingulier=utf8_encode($data["Libelle_Cond_singulier"]);
        if($article->quantite == 1){
            $article->conditionnement = $article->conditionnementsingulier;
        }else{
            $article->conditionnement = $article->conditionnementpluriel;
        }

        if($article->pays != 'D' && $article->pays != 'O' && $article->pays != 'SA' ){
                $article->conditionnement = strtolower($article->conditionnement);
         }

        $article->Packaging=$this->encodeVar(utf8_encode($data["Packaging"]));
        $article->refcond=substr($data["Code_article"],10,2);

        $article->ordrecat=$data["Classement_cata"];
        $article->ordreapp=$data["Classement_Appellation"];

        // Traitement de l'encepagement pour les produits USA
        if($article->pays == 'U'){
            $article->encepagement=explode("/", $data["encepagement"]);
            $article->encepagement_principal=$article->encepagement[0];
        }

        // recuperation de l'url produit
        $article->url_produit=$data["URL_IBM"];

        $article->codedevise=$data["Code_Devise"];
        $article->prix_ht=$data["prix_ht"];
        $article->prix_ttc=$data["prix_ttc"];
        $article->prix_remise=$data["prix_remise"];
        if($article->quantite != 0){
            $article->prixhtblle=number_format(str_replace(',', '.', $article->prix_ht)/$article->quantite, 2, ',', ' ');
            $article->prixttcblle=number_format(str_replace(',', '.', $article->prix_ttc)/$article->quantite, 2, ',', ' ');
            if ($article->prix_remise != ''){
				$article->prixremblle=number_format(str_replace(',', '.', $article->prix_remise)/$article->quantite, 2, ',', ' ');
			}
            // str_replace pour mettre la chaine de caractère au format float, pour qu'elle soit bien converti
            // et que la division soit juste ! Sinon, légères différences de décimales (19,92 au lieu de 19,95)...
        }
        $article->prixlitrettc=$data["Prix_au_Litre"]; // Prix au litre en TTC ! Ne plus calculer avec la tva

        $article->code_promo=$data["code_promo"];
        $article->type_promo=$data["type_promo"]; // Libelle de la promo si connu

        if($article->primeur){
            // Arrondi du prix ttc indicatif en primeur pour certains pays
            switch($article->pays){
                case 'F':
                case 'B':
                case 'L':
                case 'G':
                case 'I':
                case 'Y':
                case 'E':
                case 'P':
                $article->prix_ttc=number_format(ceil(str_replace(',', '.', $article->prix_ttc)),2, ',', '');
                    // str_replace pour mettre la chaine de caractère au format float, pour qu'elle soit bien converti
                    // et que l'arrondi soit juste !
                    break;
                default:
                    break;
            }
        }
        if($article->pays == 'U' OR $article->pays == 'G' OR $article->pays == 'I' OR $article->pays == 'H'  OR $article->pays == 'SG'){ // Une fois les prix calculés, on reformate au format anglais pour tous les pays anglophones (décimales séparées par des points)
            $article->prix_ht=str_replace(',', '.',$article->prix_ht);
            $article->prix_ttc=str_replace(',', '.',$article->prix_ttc);
            $article->prixhtblle=str_replace(',', '.',$article->prixhtblle);
            $article->prixttcblle=str_replace(',', '.',$article->prixttcblle);
            $article->prixlitrettc=str_replace(',', '.',$article->prixlitrettc);
            $article->prix_remise=str_replace(',', '.',$article->prix_remise);
        }

        /**
         * On calcule la propri�t� devise pour avoir un sigle car elle n'est pas stock�e en base
         */
        switch ($data["Code_Devise"]) {
            case 3 : $article->devise="&euro;";break;
            case 5 : $article->devise="CHF";break;
            case 8 : $article->devise="&pound;";break;
            case 11 : $article->devise="\$";break;
            case 15 : $article->devise="HK\$";break;
            case 16 : $article->devise="SGD";break;
            default : $article->devise="&euro;";break;
        };


        if($article->code_promo != ''){
            switch ($article->code_promo) {
                case "1" /* 1+1=3 */ :
                    $article->prix_promo = ((double)$article->prix_ttc * 2)/3 ;
                    break;
                case "2" /* 1+1=3 */ :
                   $article->prix_promo = ( (double)$article->prix_ttc * 2)/3 ;
                   // $article->prix_promo = $article->prix_ttc ;
                    break;
                case "127" /* La 2e caisse à -50% */ :
                    $article->prix_promo = ((double)$article->prix_ttc + (double)$article->prix_ttc/2)/2 ;
                    break;
                case "123" /* La 2e caisse à 40% */ :
                    $article->prix_promo = ((double)$article->prix_ttc + ((double)$article->prix_ttc - (double)$article->prix_ttc*40/100))/2 ;
                    break;
                case "702" /* Prix légers */:
                case "703" /* Champagnes à prix légers */:
                case "704" /* Rosés à prix légers */:
                case "709" /* Instant Millésima */:
				case "705" /* Up to 20% off on 2010 */:
                    $article->prix_promo = $article->prix_remise;
                    break;
                default:
                    $article->prix_promo = $article->prix_remise;
                    break;

            }

            if(isset($article->prix_promo)){
                $article->prix_promo = round($article->prix_promo, 2, PHP_ROUND_HALF_EVEN);
                $article->prix_promo = number_format($article->prix_promo, 2, ',', '');
            }

        }
            return $article;
    } else {
            $article = false;
            return $article;
        }
    }
}