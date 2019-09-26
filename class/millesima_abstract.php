<?php

/**
 *
 * Millesima dolist token
 *
 * @category      Millesima
 * @author        DGO
 * @version       0.0.1
 * @copyright     millesimaTeam
 * @licence       millesimaLicence
 */

class Millesima_Abstract{

    const DOCTYPE = "<!DOCTYPE html>";

    public function getInfoMessage($name,$objet){
        $store = substr($name, 0 ,2 );

        if ($store == 'Bi' ){
            $return['pays'] =  'Belgique';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'conseil@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Di'){
            $return['pays'] =  'Allemagne';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'kontakt@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Ei'){
            $return['pays'] =  'Espagne';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'srocamora@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Fi'){
            $return['pays'] =  'France';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'conseil@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Gi'){
            $return['pays'] =  'Angleterre';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'customercare@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Hi'){
            $return['pays'] =  'Hong Kong';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'customercare@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'SG'){
            $return['pays'] =  'Singapour';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'customercare@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Ii'){
            $return['pays'] =  'Ireland';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'customercare@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Li'){
            $return['pays'] =  'Luxembourg';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'conseil@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Oi'){
            $return['pays'] =  'Autriche';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'kontakt@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Pi'){
            $return['pays'] =  'Portugal';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'lantunes@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'SA'){
            $return['pays'] =  'Suisse Allemande';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'kontakt@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'SF'){
            $return['pays'] =  'Suisse Française';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'contact@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Yi'){
            $return['pays'] =  'Italie';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'srocamora@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Ui'){
            $return['pays'] =  'USA';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'info@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'UU'){
            $return['pays'] =  'USA';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'info@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } elseif ($store == 'Uu'){
            $return['pays'] =  'USA';
            $return['mail_from'] =  'info@infos.millesima.com';
            $return['name_from'] =  'Millesima';
            $return['mail_reply'] =  'info@millesima.com';
            $return['name_reply'] =  'Millesima';
            $return['subject_camp'] =  $objet;
            $return['name_camp'] =  $name;
        } else {
            $return['pays'] =  'erreur';
            $return['mail_from'] =  'erreur';
            $return['name_from'] =  'erreur';
            $return['mail_reply'] =  'erreur';
            $return['name_reply'] =  'erreur';
            $return['subject_camp'] =  'erreur';
            $return['name_camp'] =  $name;
        }
        return $return;
    }


    function encodeVar($var){
        $html_entities = array (
            "& " => "&amp; ",
            "À" =>  "&Agrave;",	#capital a, grave accent
            "Á" =>  "&Aacute;", 	#capital a, acute accent
            "Â" =>  "&Acirc;", 	#capital a, circumflex accent
            "Ã" =>  "&Atilde;", 	#capital a, tilde
            "Ä" => "&Auml;",	#capital a, umlaut mark
            "Å" => "&Aring;", 	#capital a, ring
            "Æ" => "&AElig;", 	#capital ae
            "Ç" => "&Ccedil;", 	#capital c, cedilla
            "È" => "&Egrave;", 	#capital e, grave accent
            "É" => "&Eacute;", 	#capital e, acute accent
            "Ê" => "&Ecirc;", 	#capital e, circumflex accent
            "Ë" => "&Euml;", 	#capital e, umlaut mark
            "Ì" => "&Igrave;", 	#capital i, grave accent
            "Í" => "&Iacute;", 	#capital i, acute accent
            "Î" => "&Icirc;", 	#capital i, circumflex accent
            "Ï" => "&Iuml;", 	#capital i, umlaut mark
            "Ð" => "&ETH;",		#capital eth, Icelandic
            "Ñ" => "&Ntilde;", 	#capital n, tilde
            "Ò" => "&Ograve;", 	#capital o, grave accent
            "Ó" => "&Oacute;", 	#capital o, acute accent
            "Ô" => "&Ocirc;", 	#capital o, circumflex accent
            "Õ" => "&Otilde;", 	#capital o, tilde
            "Ö" => "&Ouml;", 	#capital o, umlaut mark
            "Ø" => "&Oslash;", 	#capital o, slash
            "Ù" => "&Ugrave;", 	#capital u, grave accent
            "Ú" => "&Uacute;", 	#capital u, acute accent
            "Û" => "&Ucirc;", 	#capital u, circumflex accent
            "Ü" => "&Uuml;", 	#capital u, umlaut mark
            "Ý" => "&Yacute;", 	#capital y, acute accent
            "Þ" => "&THORN;", 	#capital THORN, Icelandic
            "ß" => "&szlig;", 	#small sharp s, German
            "à" => "&agrave;", 	#small a, grave accent
            "á" => "&aacute;", 	#small a, acute accent
            "â" => "&acirc;", 	#small a, circumflex accent
            "ã" => "&atilde;", 	#small a, tilde
            "ä" => "&auml;", 	#small a, umlaut mark
            "å" => "&aring;", 	#small a, ring
            "æ" => "&aelig;", 	#small ae
            "ç" => "&ccedil;", 	#small c, cedilla
            "è" => "&egrave;", 	#small e, grave accent
            "é" => "&eacute;", 	#small e, acute accent
            "ê" => "&ecirc;", 	#small e, circumflex accent
            "ë" => "&euml;", 	#small e, umlaut mark
            "ì" => "&igrave;", 	#small i, grave accent
            "í" => "&iacute;", 	#small i, acute accent
            "î" => "&icirc;", 	#small i, circumflex accent
            "ï" => "&iuml;", 	#small i, umlaut mark
            "ð" => "&eth;",		#small eth, Icelandic
            "ñ" => "&ntilde;", 	#small n, tilde
            "ò" => "&ograve;", 	#small o, grave accent
            "ó" => "&oacute;", 	#small o, acute accent
            "ô" => "&ocirc;", 	#small o, circumflex accent
            "õ" => "&otilde;", 	#small o, tilde
            "ö" => "&ouml;", 	#small o, umlaut mark
            "ø" => "&oslash;", 	#small o, slash
            "ù" => "&ugrave;", 	#small u, grave accent
            "ú" => "&uacute;", 	#small u, acute accent
            "û" => "&ucirc;", 	#small u, circumflex accent
            "ü" => "&uuml;", 	#small u, umlaut mark
            "ý" => "&yacute;", 	#small y, acute accent
            "þ" => "&thorn;", 	#small thorn, Icelandic
            "ÿ" => "&yuml;",	#small y, umlaut mark
            "°" => "&deg;",
            "¡" => "&iexcl;",
            "€" => "&euro;",
            "¿" => "&iquest;",
            "«" => "&laquo;",
            "»" => "&raquo;",
            "…" => "...",
            "–" => "&ndash;"
        );

        foreach ($html_entities as $key => $value) {
            $var = str_replace($key, $value, $var);
        }
        return $var;
    }

    function getUrl($nom_url, $tracking ="",$siteweb, $country){
        $producteur = array(
            'F' => 'producteur-',
            'B' => 'producteur-',
            'L' => 'producteur-',
            'D' => 'produzent-',
            'O' => 'produzent-',
            'SF' => 'producteur-',
            'SA' => 'produzent-',
            'G' => 'producer-',
            'I' => 'producer-',
            'Y' => 'produttore-',
            'E' => 'productor-',
            'P' => 'produtores-',
            'U' => 'producer-',
            'H' => 'producer-',
            'SG' => 'producer-'
        );


        if(isset($_POST[$nom_url."_content"])){
            $content_url = $_POST[$nom_url."_content"];
        }else{
            $content_url = "";
        }

        /* GESTION DU TRACKING */

        switch ($_POST[$nom_url]){
            case 'accueil':
                return $this->getTracking($siteweb, $tracking);
                break;
            case 'categorie':
                return $this->getTracking($siteweb.$content_url, $tracking);
                break;
            case 'produit':
                return $siteweb.$content_url."?".$tracking; // Exception produit : le "?" n'est pas dans content_url mais dans PRODUIT !
                break;
            case 'producteur':
                return $this->getTracking($siteweb.$producteur[$country].$content_url.".html", $tracking);
                break;
            case 'promo':
                return $this->getTracking($siteweb.'promo-'.$content_url, $tracking);
                break;
            case 'landingPage':
                return $this->getTracking($siteweb.$content_url, $tracking);
                break;
            case 'autre':
            default:
                $content_url=str_replace("\r\n",";;",$content_url);
                if($content_url != ""){
                    $urls=explode(";;", $content_url);
                }
                $liste=array();
                foreach ($urls as $value){
                    $assoc=explode("=>", $value);
                    //print_r($assoc);
                    //echo $assoc[0] . " xx " .$assoc[1];
                    $liste[$assoc[0]] = $this->getTracking($assoc[1], $tracking);
                    unset($assoc);
                }
                //print_r($liste);

                return $liste[$country];
                break;
        }

    }

    function getTracking($url, $tracking=""){
        if ($tracking != ""){
            if(substr_count($url, "?") > 0){
                $track = "&".$tracking;
            }else{
                $track = "?".$tracking;
            }
            // Gestion des filtres selon IBM
            if(substr_count($url, "#") > 0){
                $temp_url = explode('#', $url, 2);
                $return = $temp_url[0].$track.'#'.$temp_url[1];
            }else{
                $return = $url.$track;
            }
        }else{
            $return = $url;
        }
        return $return;
    }

    function makeTinyURL($url) {
        $ret= @file_get_contents("http://tinyurl.com/api-create.php?url=".$url);
        if (!$ret) {
            return $url;
        }
        return $ret;
    }

    /**
     * Function to get a value of config
     * @param string $name
     * @return string value
     */
    public function getValueConfig($name){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectone("SELECT * FROM config WHERE name = ?",array($name),'value');

        return $res;
    }

    /**
     * Function to get last value of config
     * @return string value
     */
    public function getLastValueConfig(){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM config ORDER BY updated_at DESC");

        return $res[0];
    }


    /**
     * Function to get code of brief
     * @param string $typebrief
     * @return string value
     */
    public function getCode($typebrief){
        $name = '';
        if($typebrief == 'livrable_eu'){
            $name = 'iosliv';
        }else if($typebrief == 'primeur_eu'){
            $name = 'iosprim';
        }else if($typebrief == 'livrable_us'){
            $name = 'uiosliv';
        }else if($typebrief == 'primeur_us'){
            $name = 'uiosprim';
        }else if($typebrief == 'edv'){
            $name = 'edv';
        }else if($typebrief == 'staff_pick'){
            $name = 'uiospick';
        }else if($typebrief == 'partenaire'){
            $name = 'iospart';
        }
        return $name;
    }

    /**
     * Function to get last value of config
     * @param string $type
     * @param array $brief
     * @param int $id
     * @return string value
     */
    public function sendEmail($type,$brief,$id){
        $name = $this->getCode($brief['typebrief']);
        $codeBrief = $name.$brief['code'];
        $themeBrief = $brief['theme'];
        $dateEnvoiBrief = $brief['dateenvoi'];
        $objet = '';
        $testenvoi = false;

        $pays = '';
        foreach ($brief as $key => $value) {
            if(preg_match('/^pays/',$key)){
                $value = str_replace('pays_','',$key);
                if($pays == '' ){
                    $pays = $value;
                } else {
                    $pays .= ', '.$value;
                }
            }
        }

        $recipiants = array();
        $message = 'Bonjour,'. '<br />';
        $message .= ''. '<br />';
        $message .= 'Le brief '.$codeBrief;
        $headers = 'From: brief@millesima.com' . "\r\n" .
            'Reply-To: lbassagaits@millesima.com' . "\r\n" .
			'Content-Type: text/html; charset="utf-8' . "\r\n" .
			'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        if(!$testenvoi){
            $recipiants[] = 'alopes@millesima.com';
            $replyto = "lbassagaits@millesima.com,alopes@millesima.com,ldeker@millesima.com";
        }else {
            $recipiants = array('dgorski@millesima.com');
        }
        if($type == 'marketing'){
            if(isset($brief['pays_u'])){
                if(!$testenvoi){
                    $recipiants[] = 'imiossec@millesima.com';
                    $recipiants[] = 'hobernard@millesima.com';
                }
            }
            if(isset($brief['pays_g']) || isset($brief['pays_i']) || isset($brief['pays_h']) || isset($brief['pays_sg'])){
                if(!$testenvoi){
                    $recipiants[] = 'pastanislas@millesima.com';
                }
            }
            if(isset($brief['pays_p']) || isset($brief['pays_f']) || isset($brief['pays_l']) || isset($brief['pays_b']) || isset($brief['pays_e']) || isset($brief['pays_y'])){
                if(!$testenvoi){
                    $recipiants[] = 'bgibier@millesima.com';
                }
            }
            if(isset($brief['pays_d']) || isset($brief['pays_o']) || isset($brief['pays_sa']) || isset($brief['pays_sf'])){
                if(!$testenvoi){
                    $recipiants[] = 'pastanislas@millesima.com';
                }
            }
            $objet = 'Création d\'un brief par l\'équipe Marketing';
            $message .=' a été créé par l\'équipe Marketing : <br />';
            $message .= 'http://srv-zend:8000/emailing/view/brief/mail/'.$id.'<br />';
			$message .= ''. '<br />';
            $message .= 'Merci de le valider à votre tour.'. '<br />';
        } else if($type == 'content'){
            if(!$testenvoi){
                $recipiants[] = 'mdutoya@millesima.com';
                $recipiants[] = 'bdejonckheere@millesima.com';
            }
            $objet = 'Validation marketing du brief emailing '.$codeBrief;
            $message .=' a été validé par l\'équipe Marketing :'. '<br />';
            $message .= 'http://srv-zend:8000/emailing/view/brief/mail/'.$id.'<br />';
			$message .= ''. '<br />';
            $message .= 'Merci de le valider à votre tour.'. '<br />';
        } else if($type == 'commerciaux')
		{
            $objet = 'Traduction de l\'emailing '.$codeBrief;
            $message .=' a été validé par l\'équipe Content et Messages.'. '<br />';
            $message .='Thème : '.$themeBrief.'<br />';
            $message .='Date d\'envoi : '.$dateEnvoiBrief. '<br />';
            $message .='Liste des pays : '.$pays. '<br />'. '<br />';
			$liens = "";

            if(isset($brief['pays_p'])){
                $messageP = $message;
                $messageP .= 'http://srv-zend:8000/emailing/view/traduction/check/p-'.$id.'<br />';
                $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/p-'.$id.'<br />';
                $messageP .= '<br />';
                $messageP .= 'Merci'. '<br />';
                //mail('alopes@millesima.com', $objet, $messageP,$headers);
                if(!$testenvoi){
                    mail('lantunes@millesima.com', $objet, $messageP,$headers);
                    mail('bgibier@millesima.com', $objet, $messageP,$headers);
                }
            }
            if(isset($brief['pays_g']) || isset($brief['pays_i']) ||isset($brief['pays_h']) || isset($brief['pays_sg'])){
                $messageG = $message;
                $messageG .= 'http://srv-zend:8000/emailing/view/traduction/check/g-'.$id.'<br />';
                $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/g-'.$id.'<br />';
                $messageG .= ''. '<br />';
                $messageG .= 'Merci'. '<br />';
                //mail('alopes@millesima.com', $objet, $messageG,$headers);
                if(!$testenvoi){
                    mail('hgee@millesima.com', $objet, $messageG,$headers);
                    mail('mrenaud@millesima.com', $objet, $messageG,$headers);
                    mail('lkocsis@millesima.com', $objet, $messageG,$headers);
                    mail('pastanislas@millesima.com', $objet, $messageG,$headers);
                }
            }
            if(isset($brief['pays_e']) || isset($brief['pays_y'])){
                if(isset($brief['pays_e'])){
                    $messageE = $message;
                    $messageE .= 'http://srv-zend:8000/emailing/view/traduction/check/e-'.$id.'<br />';
                    $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/e-'.$id.'<br />';
                    $messageE .= ''. '<br />';
                    $messageE .= 'Merci'. '<br />';
					//mail('alopes@millesima.com', $objet, $messageE,$headers);
                    if(!$testenvoi){
                        mail('srocamora@millesima.com', $objet, $messageE,$headers);
                        mail('bgibier@millesima.com', $objet, $messageE,$headers);
                    }
                }
                if (isset($brief['pays_y'])){
                    $messageY = $message;
                    $messageY .= 'http://srv-zend:8000/emailing/view/traduction/check/y-'.$id.'<br />';
                    $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/y-'.$id.'<br />';
                    $messageY .= ''. '<br />';
                    $messageY .= 'Merci'. '<br />';
					//mail('alopes@millesima.com', $objet, $messageY,$headers);
                    if(!$testenvoi){
                        mail('redacteurit@millesima.com', $objet, $messageY,$headers);
                        mail('srocamora@millesima.com', $objet, $messageY,$headers);
                        mail('bgibier@millesima.com', $objet, $messageY,$headers);
                    }
                }
            }
            if(isset($brief['pays_d']) || isset($brief['pays_o']) || isset($brief['pays_sa'])){
                $messageD = $message;
                $messageD .= 'http://srv-zend:8000/emailing/view/traduction/check/d-'.$id.'<br />';
                $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/d-'.$id.'<br />';
                $messageD .= ''. '<br />';
                $messageD .= 'Merci'. '<br />';
                //mail('alopes@millesima.com', $objet, $messageD,$headers);
                if(!$testenvoi){
                    mail('contact@millesima.com', $objet, $messageD,$headers);
                    mail('pastanislas@millesima.com', $objet, $messageD,$headers);
                    mail('redacteurde@millesima.com', $objet, $messageD,$headers);
                }
            }
            if(isset($brief['pays_u'])){
                $messageU = $message;
                $messageU .= 'http://srv-zend:8000/emailing/view/traduction/check/u-'.$id.'<br />';
                $liens .= 'http://srv-zend:8000/emailing/view/traduction/check/u-'.$id.'<br />';
                $messageU .= ''. '<br />';
                $messageU .= 'Merci'. '<br />';
                //mail('alopes@millesima.com', $objet, $messageU,$headers);
                if(!$testenvoi){
                    mail('imiossec@millesima.com', $objet, $messageU,$headers);
                }
            }
            $message .='Traductions attendues :'. '<br />';
            $message .= $liens. '<br />'. '<br />';

        } else if($type == 'textmaster') {


            $objet = 'Une Traduction de l\'emailing '.$codeBrief;
            $message .=' a été faite par TextMaster.'. '<br />';
            $message .='Thème : '.$themeBrief.'<br />';
            $message .='Date d\'envoi : '.$dateEnvoiBrief. '<br />';
            $message .='Liste des pays : '.$pays. '<br />'. '<br />';

            if(isset($brief['pays_g']) || isset($brief['pays_i']) || isset($brief['pays_h']) || isset($brief['pays_sg'])){
                $message .= 'http://srv-zend:8000/emailing/view/traduction/check/g-'.$id.'<br />';
                if(!$testenvoi){
                    $recipiants[] = 'mrenaud@millesima.com';
                    $recipiants[] = 'hgee@millesima.com';
                    $recipiants[] = 'lkocsis@millesima.com';
                    $recipiants[] = 'pastanislas@millesima.com';
                }

            }
            if(isset($brief['pays_d']) || isset($brief['pays_o']) || isset($brief['pays_sa']) || isset($brief['pays_sf'])){
                $message .= 'http://srv-zend:8000/emailing/view/traduction/check/d-'.$id.'<br />';
                if(!$testenvoi){
                    $recipiants[] = 'utreptow@millesima.com';
                    $recipiants[] = 'sniggl@millesima.com';
                    $recipiants[] = 'pastanislas@millesima.com';
                    $recipiants[] = 'ekohr@millesima.com';
                    $recipiants[] = 'bteurquetil@millesima.com';
                    $recipiants[] = 'redacteurde@millesima.com';
                }
            }

            $message .= ''. '<br />';
            $message .= 'Merci de validé leurs traductions'. '<br />';
        } else if($type == 'alltrad'){
            if(!$testenvoi){
                $recipiants[] = 'lbassagaits@millesima.com';
                $recipiants[] = 'ldeker@millesima.com';
            }
            $objet = 'Traductions complètes pour le brief '.$codeBrief;
            $message .=' a été traduit dans toutes les langues.'. '<br />';
			$message .= ''. '<br />';
            $message .='Vous pouvez créer le message.'. '<br />';
        } else if($type == 'batfr')	{
            if(!$testenvoi){
                $recipiants[] = 'egarnaud@millesima.com, mdutoya@millesima.com, bgibier@millesima.com,pastanislas@millesima.com, vvecchione@millesima.com';
			    $cc = 'lbassagaits@millesima.com, bdejonckheere@millesima.com, aperrin@millesima.com, smonneau@millesima.com, ecandau@millesima.com, alopes@millesima.com, ldeker@millesima.com';
            } else {
                $cc = '';
            }
            $objetBrief = $brief['objfr'];
			$objet = 'Validation BAT FR '.$codeBrief .' : '.$themeBrief;
            $message ='Voici le BAT France : ';
            $message .='<a target="_blank" href="/fichiers/emailings/'.$codeBrief.'/F'.$codeBrief.'.html">F'.$codeBrief.'.html</a>'. '<br />';
            $message .= '<br />';
            $message .='Objet : « <strong>'.$objetBrief .'</strong> »<br />';

			$headers = 'From: brief@millesima.com' . "\r\n" .
            'Reply-To: '.$replyto. "\r\n" .
			'Cc: '.$cc. "\r\n" .
			'Content-Type: text/html; charset="utf-8' . "\r\n" .
			'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
		}

        $message .= ''. '<br />';
        $message .= 'Cordialement.'. '<br />';

		$to="";
        foreach($recipiants as $recipiant){
            $to.=$recipiant.',';
			//$replyto.=$recipiant.';';
        }
		mail($to, $objet, $message,$headers);

	}

    /**
     * Function to get last value of config
     * @param string $type
     * @param array $data
     * @return string value
     */
    public function sendMailMessage($type,$data){
        $objet = $this->getObjectMail($type,$data);

        $message = 'Bonjour,'. '<br />';
        $message .= ''. '<br />';
        $headers = 'From: lbassagaits@millesima.com' . "\r\n" .
            'Reply-To: lbassagaits@millesima.com;bgibier@millesima.com' . "\r\n" .
            'Content-Type: text/html; charset="utf-8' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $message .= $this->getMessageMail($type,$data);

        $message .= ''. '<br />';
        $message .= 'Cordialement.'. '<br />';

        $to="";
        $pays = $data['pays'];
        $recipiants = $this->getRecipiantMail($type,$pays);


        foreach($recipiants as $recipiant){
            $to.=$recipiant.',';
            //$replyto.=$recipiant.';';
        }
        $message = chunk_split(base64_encode($message));
        mail($to, $objet, $message,$headers);
    }

    /**
     * Function to get last value of config
     * @param string $type
     * @param array $pays
     * @return array tabRecipiant
     */
    public function getRecipiantMail($type,$pays){
        $isTest = false;
        $tabRecipiant = array();
        if(!$isTest){
            $tabRecipiant[] = 'alopes@millesima.com';
            $tabRecipiant[] = 'ldeker@millesima.com';
            $tabRecipiant[] = 'bgibier@millesima.com';
            $tabRecipiant[] = 'lbassagaits@millesima.com';
        } else {
            $tabRecipiant[] = 'alopes@millesima.com';
            $tabRecipiant[] = 'dgorski@millesima.com';
        }
		if(($type == 'messagecreate') && array_intersect(array('F','f'),$pays) && !$isTest){
            $tabRecipiant[] = 'gbrun@millesima.com';
        }
        if(($type == 'messagecreate') && array_intersect(array('F','f','L','l','B','b'),$pays) && !$isTest){
            $tabRecipiant[] = 'pvalette@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('D','d','O','o','SA','sa','SF','sf'),$pays) && !$isTest){
            $tabRecipiant[] = 'contact@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('D','d','O','o','SA','sa','SF','sf','G','g','I','i','H','h','SG','sg'),$pays) && !$isTest){
            $tabRecipiant[] = 'pastanislas@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('P','p'),$pays) && !$isTest){
            $tabRecipiant[] = 'lantunes@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('E','e'),$pays) && !$isTest){
            $tabRecipiant[] = 'srocamora@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('Y','y'),$pays) && !$isTest){
            $tabRecipiant[] = 'lspettoli@millesima.com';
        }
        if(($type == 'messagecreate')  && array_intersect(array('U','u'),$pays) && !$isTest){
            $tabRecipiant[] = 'hobernard@millesima.com';
            $tabRecipiant[] = 'lkocsis@millesima.com';
        }
        if(($type == 'commercial' || $type == 'messagecreate')  && array_intersect(array('G','g','I','i','H','h','SG','sg'),$pays) && !$isTest){
            $tabRecipiant[] = 'lkocsis@millesima.com';
            $tabRecipiant[] = 'pdunoyer@millesima.com';
            $tabRecipiant[] = 'npiro@millesima.com';
        }
        if($type == 'marketing' && array_intersect(array('F','f','L','l','B','b','E','e','Y','y','P','p'),$pays) && !$isTest){
            $tabRecipiant[] = 'bgibier@millesima.com';
        }
        if($type == 'marketing' && array_intersect(array('U','u'),$pays) && !$isTest){
            $tabRecipiant[] = 'imiossec@millesima.com';
            $tabRecipiant[] = 'hobernard@millesima.com';
            $tabRecipiant[] = 'ebrancato@millesima.com';
            $tabRecipiant[] = 'marketingus@millesima.com';
            $tabRecipiant[] = 'hobernard@millesima.com';
            $tabRecipiant[] = 'lkocsis@millesima.com';
            $tabRecipiant[] = 'imiossec@millesima.com';
        }
		if($type == 'messagecreate' && array_intersect(array('U','u'),$pays) && !$isTest){
            $tabRecipiant[] = 'imiossec@millesima.com';
            $tabRecipiant[] = 'ebrancato@millesima.com';
        }
        if($type == 'marketing' && array_intersect(array('G','g','I','i','H','h','SG','sg','D','d','O','o','SA','sa','SF','sf'),$pays) && !$isTest){
            $tabRecipiant[] = 'pastanislas@millesima.com';
        }
        if($type == 'master' && !$isTest){
            $tabRecipiant[] = 'pastanislas@millesima.com';
            $tabRecipiant[] = 'lbassagaits@millesima.com';
            $tabRecipiant[] = 'obaldy@millesima.com';
            $tabRecipiant[] = 'fbernard@millesima.com';
            $tabRecipiant[] = 'bdejonckheere@millesima.com';
            $tabRecipiant[] = 'mdutoya@millesima.com';
            $tabRecipiant[] = 'smonneau@millesima.com';
            $tabRecipiant[] = 'pastanislas@millesima.com';
            $tabRecipiant[] = 'vvecchione@millesima.com';
        }

        return $tabRecipiant;
    }

    /**
     * Function to get last value of config
     * @param string $type
     * @param array $data
     * @return string $return
     */
    public function getMessageMail($type,$data){
        $return = '';
        if($type == 'messagecreate' || $type == 'master'){
            $return = $data['content'];
        }
        return $return;
    }

    /**
     * Function to get last value of config
     * @param string $type
     * @param array $data
     * @return string $return
     */
    public function getObjectMail($type,$data){
        $return = '';
        if($type == 'messagecreate'){
            $return = 'BAT '.$data['id'];
        }
        if($type == 'master'){
            //get information mail
            $return = 'BAT '.$data['id'];
        }
        return $return;
    }

    /**
     * Function to get doctype of emails
     * @return string $return
     */
    public function getDoctype(){
      return self::DOCTYPE;  
    }

}