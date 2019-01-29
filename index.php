<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'library/Slim/Slim.php';
include 'class/millesima_abstract.php';
include 'class/millesima_brief.php';
include 'class/millesima_message.php';
include 'class/millesima_message_template.php';
include 'class/millesima_segment.php';
include 'class/millesima_campaign.php';
include 'class/millesima_traduction.php';
include 'class/millesima_messagedata.php';
include 'class/millesima_tinyclues.php';
include 'class/millesima_textmaster.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim(array(
    'templates.path' => './templates'
));

/////////////////////  Route Page  ////////////////////////////////////////
$app->get('/view/home','getHomeView');

$app->get('/view/brief','getGestionBriefView');
$app->get('/view/brief/create','getActionBriefView');
$app->post('/view/brief/create','getActionBriefView');
$app->get('/view/brief/action','actionBrief');
$app->post('/view/brief/action','actionBrief');
$app->get('/view/brief/mail/:param','getActionMailView');
$app->get('/view/brief/check/:param','getBriefCheckView');

$app->get('/view/traduction','getTraductionBriefView');
$app->post('/view/traduction','getTraductionBriefView');
$app->get('/view/traduction/action','actionTrad');
$app->post('/view/traduction/action','actionTrad');
$app->get('/view/traduction/check/:param','getTradCheckView');
$app->get('/view/traduction/checkbybrief/:param','getTradCheckViewByBrief');

$app->get('/view/message','getMessageView');
$app->post('/view/message/create','createMessage');
$app->get('/view/message_action','actionMessage');
$app->post('/view/message_action','actionMessage');

$app->get('/view/segment','getSegmentView');
$app->post('/view/segment/create','createSegment');
$app->post('/view/segment/update','updateSegment');
$app->post('/view/segment/tinyclues', 'traitementTinyclues');

$app->get('/view/campaign_create','createCampaign');
$app->post('/view/campaign_create','createCampaign');
$app->get('/view/campaign_stat','statCampaign');
$app->post('/view/campaign_stat','statCampaign');



/////////////////////  Action AJAX Campaign ////////////////////////////////////////
$app->get('/view/ajax/campaign/:message','getInfoMessage');
$app->get('/view/ajax/message_getlocalhtml/:message','getLocalHtml');
$app->get('/view/ajax/segment_count/:data','countSegment');
$app->get('/view/ajax/brief_info/:name','getConfigDataIndex');
$app->get('/view/ajax/message_getBriefInfo/:brief','getBriefInfo');
$app->get('/view/ajax/message_getMessageSaveInfo/:brief','getMessageSaveInfo');
$app->get('/view/ajax/trad_getExist/:data','getTradInfo');
$app->post('/view/ajax/trad_save/','saveTradInfo');
$app->post('/view/ajax/trad_inv/','invalidTraduction');
$app->post('/view/ajax/send_reel/','sendCampaignReelAjax');
$app->get('/view/ajax/campaign_search/:name','searchCampaign');
/////////////////////  Function Route Page  ////////////////////////////////////////
function getView($content,$url = '../') {
    //die('ggfdgdfgd');
    $app = \Slim\Slim::getInstance();

    $app->view()->setData(array('content' => $content, 'url'=> $url));
    $app->render('view.php');
}

function getHomeView() {
    //die('ggfdgdfgd');
    $app = \Slim\Slim::getInstance();
    $content =$app->view()->fetch('home.php');
    getView($content);
}

/////////////////////  Function action brief   ////////////////////////////////////////

function getGestionBriefView($html = '',$url = '../') {
    $app = \Slim\Slim::getInstance();

    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }
    $briefClass = new Millesima_Brief();
    $briefAllList = $briefClass->getBriefAllList();
    $briefModifList = $briefClass->getBriefModifList();
    $briefValidMarketList = $briefClass->getBriefStatutList(1);
    $briefValidMarianneList = $briefClass->getBriefStatutList(2);
    $app->view()->appendData(array( 'brief_all_list' => $briefAllList));
    $app->view()->appendData(array( 'brief_modif_list' => $briefModifList));
    $app->view()->appendData(array( 'brief_valid_market_list' => $briefValidMarketList));
    $app->view()->appendData(array( 'brief_valid_marianne_list' => $briefValidMarianneList));


    $content = $app->view()->fetch('brief.php');
    getView($content,$url);
}

function getActionBriefView($html = '',$url = '../../', $brief = array(),$button = 'Envoyer') {
    $app = \Slim\Slim::getInstance();

    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }
    $app->view()->appendData(array( 'brief' => $brief));
    $app->view()->appendData(array( 'button' => $button));
    $content = $app->view()->fetch('brief_action.php');
    getView($content,$url);
}

function actionBrief(){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();
    $briefClass = new Millesima_Brief();
    $tradClass = new Millesima_Traduction();
    $bddClass = new Millesima_Bdd();

    if(!isset($data['code'])){
        $html = "c'est pas bien d'aller direct sur cette url";

    } else {
        //var_dump($data);
        //die('titi2');
        $html = '';
        $id = array_shift($data);
        $code = $data["code"];
        $button = array_shift($data);
        $btnaction = array_pop($data);
        if((int)$id > 0){
            if ($btnaction == 'mark') {
                $briefClass->sendEmail('content', $data, $id);
                $briefClass->updateStatus(2,$id);
                $html = 'Validation Marketing faite. Code brief : '.$code;
                $html .= $briefClass->update($data,$id, 2);
            } else if ($btnaction == 'mar') {
                $alltrad = $tradClass->testAllTradGood($id);
                $tabFrance = Array();
                $tabAllem = Array();
                $tabAnglais = Array();
                $tabAutre = Array();
                //boucle pour savoir si il y a qu'un seul pays
                foreach ($data as $key => $value) {
                    if(preg_match('/^pays_f/',$key)){
                        $tabFrance[] = "F";
                    }
                    if(preg_match('/^pays_l/',$key)){
                        $tabFrance[] = "L";
                    }
                    if(preg_match('/^pays_b/',$key)){
                        $tabFrance[] = "B";
                    }
                    if(preg_match('/^pays_sf/',$key)){
                        $tabFrance[] = "SF";
                    }
                    if(preg_match('/^pays_d/',$key)){
                        $tabAllem[] = "D";
                    }
                    if(preg_match('/^pays_o/',$key)){
                        $tabAllem[] = "O";
                    }
                    if(preg_match('/^pays_sa/',$key)){
                        $tabAllem[] = "SA";
                    }
                    if(preg_match('/^pays_g/',$key)){
                        $tabAnglais[] = "G";
                    }
                    if(preg_match('/^pays_i/',$key)){
                        $tabAnglais[] = "I";
                    }
                    if(preg_match('/^pays_h/',$key)){
                        $tabAnglais[] = "H";
                    }
                    if(preg_match('/^pays_sg/',$key)){
                        $tabAnglais[] = "SG";
                    }
                    if(preg_match('/^pays_y/',$key)){
                        $tabAutre[] = "Y";
                    }
                    if(preg_match('/^pays_e/',$key)){
                        $tabAutre[] = "E";
                    }
                    if(preg_match('/^pays_p/',$key)){
                        $tabAutre[] = "P";
                    }
                    if(preg_match('/^pays_u/',$key)){
                        $tabAutre[] = "U";
                    }
                }

                $html = 'Validation Contenu faite. Code brief : '.$code;
                $message_mounted = $briefClass->getBriefMessageMounted($id);
                if(($tabFrance != null && $tabAllem == null && $tabAnglais == null && $tabAutre == null) ||
                    ($tabFrance == null && $tabAllem != null && $tabAnglais == null && $tabAutre == null) ||
                    ($tabFrance == null && $tabAllem == null && $tabAnglais != null && $tabAutre == null)){
                    $briefClass->updateStatus("4",$id);
                    $html .= $briefClass->update($data,$id, 4);
                    $briefClass->sendEmail('alltrad', $data, $id);
                    if($message_mounted == "1" && $message_create != "1"){
                        $briefClass->updateStatus("5",$id);
                    }
                }else if($alltrad){
                    $brief = $briefClass->getBrief($id);
                    $date = date("Y-m-d H:i:s");

                    $briefClass->updateStatus(4,$id);
                    $html .= $briefClass->update($data,$id, 4);
                    $bddClass->update("UPDATE brief SET validtrad_at = (?) where  id = (?)", array($date, $id));
                    $briefClass->sendEmail('alltrad', $brief,$brief['code']);
                    if($message_mounted == "1" && $message_create != "1"){
                        $briefClass->updateStatus("5",$id);
                    }elseif ($message_mounted == "1" && $message_create == "1"){
                        $briefClass->updateStatus("5",$id);
                    }
                }else{
                    $prob = false;
                    $briefClass->update($data,$id);
                    if(isset($data['tm_g'])){
                        $textMasterClass = new Millesima_Textmaster();
                        $brief = $briefClass->getBrief($id);

                        $dataProj = $textMasterClass->sendProjectTextMaster($brief,'g');
                        if(isset($dataProj->id)){
                            $dataDoc = $textMasterClass->sendDocument($brief,$dataProj->id);
                            //$dataProj = $textMasterClass->translationProjectTextMaster($brief,'g');
                            //$dataProj = $textMasterClass->actionProjectTextMaster($brief,'launch','g');
                            if($dataDoc == 'ok textmaster' && isset($dataProj->id)){
                                $html .= 'Creation text master ok ';
                            } else {
                                $prob = true;
                                $html = 'Problème textmaster doc lang g, contact suportweb@millesima.com avec le brief id : '.$brief['id'];
                            }
                        }else {
                            $prob = true;
                            $html = 'Problème textmaster doc lang g, contact suportweb@millesima.com avec le brief id : '.$brief['id'];
                        }

                    }
                    if(isset($data['tm_d'])){
                        $textMasterClass = new Millesima_Textmaster();
                        $brief = $briefClass->getBrief($id);

                        $dataProj = $textMasterClass->sendProjectTextMaster($brief,'d');
                        if(isset($dataProj->id)){
                            $dataDoc = $textMasterClass->sendDocument($brief,$dataProj->id);
                            //$dataProj = $textMasterClass->translationProjectTextMaster($brief,'d');
                            $dataProj = $textMasterClass->actionProjectTextMaster($brief,'launch','d');
                            if($dataDoc == 'ok textmaster' && isset($dataProj->id)){
                                $html .= 'Creation text master ok ';
                            } else {
                                $prob = true;
                                $html = 'Problème textmaster doc lang a, contact suportweb@millesima.com avec le brief id : '.$brief['id'];
                            }
                        }else {
                            $prob = true;
                            $html = 'Problème textmaster proj lang a, contact suportweb@millesima.com avec le brief id : '.$brief['id'];
                        }

                    }
                    if(!$prob){
                        $briefClass->sendEmail('commerciaux', $data, $id);
                        $briefClass->updateStatus("3",$id);
                        $html .= $briefClass->update($data,$id, 3);
                    }
                }
            } else if ($btnaction == 'mod') {
                $html = 'Modification faite. Code brief : '.$code;
                $html .= $briefClass->update($data,$id);
            } else if ($btnaction == 'sup') {
                $html = 'Suppression du brief : '.$code;
                $html .= $briefClass->delete($id);
            } else if($btnaction == 'copier'){
                $data["dateenvoi"] = date("d/m/Y");
                $return = $briefClass->create($data, true);
                $html .= $return['html'];
                if($return['id'] != '0'){
                    $briefClass->sendEmail('marketing', $data,$return['id']);
                    $res = $tradClass->getAllTrad($id);
                    if($res){
                        foreach($res as $key=>$trad) {
                            unset($res[$key]["id"]);
                            unset($res[$key]["idmessagedata"]);
                            unset($res[$key]["created_at"]);
                            unset($res[$key]["updated_at"]);
                            $res[$key]["brief_id"] = $return['id'];
                            $tradClass->insertTrad($res[$key]["brief_id"],$res[$key]["lang"],$res[$key]["type"],$res[$key]["value"]);
                        }
                    }
                }
            } else if ($btnaction == 'batfr') {
				$briefClass->sendEmail('batfr', $data, $id);
                $html = 'Validation BAT Fr envoyé. Code brief : '.$code;
			}
        } else {
            $return = $briefClass->create($data);
            $html .= $return['html'];
            if($return['id'] != '0'){
                $briefClass->sendEmail('marketing', $data,$return['id']);
            }
        }
    }
    getGestionBriefView($html,'../../');
}

function getActionMailView ($param){
    if($param != 'undefined'){
        $briefClass = new Millesima_Brief();

        $brief = $briefClass->getBrief($param);
        if(!$brief){
            $html = 'pas de brief pour cet id';
            getGestionBriefView($html,'../../../');
        } else {
            $statut = $brief['statut'];
            $button = 'Retour';
            if($statut == '1'){
                $button = 'Validation Marketing';
            } else if($statut == '2'){
                $button = 'Validation Contenu';
            } else if($statut == '5'){
                $button = 'Envoi BAT Fr';
            }
            getActionBriefView('','../../../',$brief,$button);
        }
    } else {
        getGestionBriefView('','../../../');
    }

}
function getBriefCheckView ($param){
    if($param != 'undefined'){
        $briefClass = new Millesima_Brief();
        $brief = $briefClass->getBrief($param);
        if(!$brief){
            $html = 'pas de brief pour cet id';
            getGestionBriefView($html,'../../../');
        } else {
            $statut = $brief['statut'];
            $button = 'Retour';
            getActionBriefView('','../../../',$brief,$button);
        }
    } else {
        getGestionBriefView('','../../../');
    }

}

/////////////////////  Function action traduction   ////////////////////////////////////////

function getTraductionBriefView($html = '',$url = '../', $trad = array(), $brief = array()){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();

    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }

    $briefClass = new Millesima_Brief();
    $briefList = $briefClass->getBriefAllList();
    $app->view()->appendData(array( 'brief_list' => $briefList));
    $app->view()->appendData(array( 'trad' => $trad));
    $app->view()->appendData(array( 'brief_info' => $brief));

    $content = $app->view()->fetch('traduction.php');
    getView($content,$url);
}

function actionTrad(){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();
    $tradClass = new Millesima_Traduction();
    $bddClass = new Millesima_Bdd();
    $id = $data['brief_id'];
    $date = date("Y-m-d H:i:s");
    $alltrad = false;
    $html = '';
    $bddClass->update("UPDATE brief SET updated_at = (?) where  id = (?)", array($date, $id));

    //complete all doc for project textmaster
    if($data['is_textmaster'] == 1 && $data['is_valid'] == 0){
        $textMasterClass = new Millesima_Textmaster();
        $return = $textMasterClass->validAllTrad($data);
        $html .= $return;
    }

    //save new trad
    $html .= $tradClass->saveTraduction($data);

    //test all trad send mail
    $alltrad = $tradClass->testAllTradGood($id);

    if($alltrad){
        $briefClass = new Millesima_Brief();
        $brief = $briefClass->getBrief($id);
        $briefClass->updateStatus(4,$data['brief_id']);
        $bddClass->update("UPDATE brief SET validtrad_at = (?) where  id = (?)", array($date, $id));

        $briefClass->sendEmail('alltrad', $brief,$brief['code']);
        $message_mounted = $briefClass->getBriefMessageMounted($id);
        if($message_mounted == "1"){
            $briefClass->updateStatus("5",$id);
        }
    }

    getTraductionBriefView($html,'../../');
}

function getTradCheckView ($param){

    if($param != 'undefined'){
        $tmp = explode('-',$param);
        $pays = $tmp[0];
        $id = $tmp[1];
        $TradClass = new Millesima_Traduction();
        $briefClass = new Millesima_Brief();
        $return = $TradClass->getAllTradLang($id,$pays);
        $brief = $briefClass->getBrief($id);
        $is_valid = 0;

        foreach($return as $trad){
            $tabTrad[$trad['type']] = $trad['value'];
            $tabTrad['is_textmaster'] =  $trad['is_textmaster'];
            if($trad['is_valid'] == 2){
                $is_valid = 2;
            } else if ($trad['is_valid'] == 1 && $is_valid != 2){
                $is_valid = 1;
            }
        }
        $tabTrad['is_valid'] =  $is_valid;
        $tabTrad['id'] = $id;
        $tabTrad['pays'] = $pays;

        getTraductionBriefView('' ,'../../../', $tabTrad, $brief);
    } else {
        getGestionBriefView('','../../../');
    }

}

function getTradCheckViewByBrief ($param){
    if($param != 'undefined'){
        $briefClass = new Millesima_Brief();
        $brief = $briefClass->getBrief($param);
        $tabTrad = array();
        getTraductionBriefView('' ,'../../../', $tabTrad, $brief);
    } else {
        getGestionBriefView('','../../../');
    }
}

function invalidTraduction (){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();

    $textMasterClass = new Millesima_Textmaster();
    $html = $textMasterClass->invalidAllTrad($data);

    var_dump($html);
    die('gfdgdf');

    if($html == "complete textmaster ok"){
        $tradClass = new Millesima_Traduction();
        $tradClass->validTradTextMaster($data['inv_brief_id'],$data['inv_lang'],$data['inv_type'],2);
    }
    echo $html;
}


/////////////////////  Function action message   ////////////////////////////////////////

function getMessageView($html = '',$url = '../') {
    $app = \Slim\Slim::getInstance();
    $briefClass = new Millesima_Brief();
    $briefList = $briefClass->getBriefMessageList();
    $app->view()->appendData(array( 'brief_list' => $briefList));
    $messageDataClass = new Millesima_Messagedata();
    $messageDataSave = $messageDataClass->getMessageDataList();
    $app->view()->appendData(array( 'messagedata_save' => $messageDataSave));
    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }
    $content = $app->view()->fetch('message.php');
    getView($content,$url);
}


function createMessage(){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();
    $messageDataClass = new Millesima_Messagedata();
    $messageClass = new Millesima_Message_Template();
    //$file = $app->request->file();
    $btnAction = $data['btnaction'];
    unset($data['btnaction']);
    if($btnAction == 'envoyer'){
        $html = $messageDataClass->saveMessageData($data);
        $html .= $messageClass->createMessage($data);
    }elseif ($btnAction == 'sauvegarder'){
        $html = $messageDataClass->saveMessageData($data);
    }else{
        $html = "Erreur";
    }

    getMessageView($html,'../../');
}

function actionMessage() {
    $html = '';
    $url = '../';
    $app = \Slim\Slim::getInstance();

    //recupération des messages
    $messageClass = new Millesima_Message();
    $briefClass = new Millesima_Brief();
    $bddClass = new Millesima_Bdd();
    $messageList = $messageClass->getMessageList();
    $app->view()->appendData(array( 'messageList' => $messageList));

    //verification param for action
    $data = $app->request->post();

    if(isset($data) && isset($data["suppression"]) && is_array($data["messageid"]) && count($data["messageid"]) > 0 ){

        //suppression des message
        $messageIds = $data["messageid"];
        if (isset($data['suppression'])){
            foreach ($messageIds as $messageId){
                $brief_id = $messageClass->getBriefIdByMessageId($messageId);
                $messageResponse = $messageClass->delete($messageId);
                if($messageResponse[$messageId]['success']){
                    $briefClass->updateStatus("4",$brief_id);
                    $html .= "<b>La demande de suppresion de message a été prise en compte.</b><br/>";
                    $html .= "MessageID  = ".$messageId."<br/>";

                } else {
                    $html =  " <h3>Error</h3>";
                    $html .=  "Message : ".$messageResponse[$messageId]['value'];
                    $html .=  "<br>";
                    $html .=  "La demande n'a pas été prise en compte :(";
                }
            }
        }
    }

    //affichage page
    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }
    $content =$app->view()->fetch('message_action.php');
    getView($content,$url);
}

/////////////////////  Function action segment   ////////////////////////////////////////
function getSegmentView($html = '',$url = '../') {
    $app = \Slim\Slim::getInstance();
    $segmentClass = new Millesima_Segment();

    $segmentList = $segmentClass->getSegmentList();

    $app->view()->appendData(array( 'segmentList' => $segmentList));
    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }

    $content =$app->view()->fetch('segment.php');

    getView($content,$url);
}

function createSegment(){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();

    $rep="dolist";
    $nomfichiersdolist=array();
    $nomdusegment=$data["nomdusegment"];
    $countries=$data["pays"];
    $segmentClass = new Millesima_Segment();
    $html = '';

    //verification non fichier est le meme que le nom du segment
    if($_FILES["selectfile"]["name"] != 'extraction_'.$nomdusegment.'.csv'){
        $html =  "<h3>Error</h3>";
        $html .=  "Message : ";
        $html .=  "<br>";
        $html .=  "La demande n'a pas été prise en compte, le fichier d'extraction n'a pas le même nom que le nom de segment :(";
		$html .=  "<br>";
		$html .=  $_FILES["selectfile"]["name"];
		$html .=  "<br>";
		$html .=  "le nom doit être extraction_+nom segment+.csv";
        getSegmentView($html,'../../');
    }

    //copier le fichier selection au bon endroit
    if(isset($_FILES["selectfile"]["tmp_name"]) && isset($_FILES["selectfile"]["name"])){
        move_uploaded_file($_FILES["selectfile"]["tmp_name"],'fichiers/segment-file/'.$_FILES["selectfile"]["name"]);
    }

    //etape 1 charger en base les emails
    $segmentClass->chargeEmailBdd($_FILES["selectfile"]["name"],false);

    //ETAPE 3 CREER UN FICHIER AU FORMAT DML CONTENANT EMAIL + PAYSCOM pour chaque pays
    foreach($countries as $country) {
        $nomFile = $segmentClass->createPickFile($country,$nomdusegment);
        $segmentClass->sendFileSegmentFtp($nomdusegment,$nomFile);
        $segmentClass->createInBdd($nomFile);
        $html .= "<b>La demande de création d'import a été prise en compte.</b><br/>";
        $html .= "<b>".$nomFile."</b><br/>";
    }
    //die('gdfgdfgd');
    getSegmentView($html,'../../');
}

function updateSegment(){

    $segmentClass = new Millesima_Segment();
    $segmentResponse = $segmentClass->updateSegment();

    $html = "<b>La demande de mise à jour a été prise en compte voici les segment mis a jour.</b><br/>";
    $html .= $segmentResponse;

    getSegmentView($html,'../../');
}

function traitementTinyclues(){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();

    $tinycluesClass = new Millesima_Tinyclues();
    $date=$data["dateFichier"];
    $date = explode('/', $date);
    $date = $date[2].'-'.$date[1].'-'.$date[0];

    //CONNEXION SFTP TINYCLUES
    $connection = ssh2_connect('sftp2.tinyclues.com', 22, array('hostkey'=>'ssh-rsa'));
    if(ssh2_auth_pubkey_file($connection, 'millesima', 'sftp/cle_tinyclues.pub', 'sftp/cle_tinyclues')){
    }else{
        die('la connection au sftp ne marche pas');
    }
    $sftp = ssh2_sftp($connection);
    $dir = "ssh2.sftp://$sftp/data/from_tc"; //spécifie le répertoire distant

    //RECUPERATION DES FICHIERS SUR LE SFTP TINYCLUES
    $fichierTinyclues = $tinycluesClass->ListeFichierSFTP($dir, $date);

    //supression fichier residuel

    //DEPLACEMENT DU SFTP VERS DOSSIER TINYCLUES DOLIST
    foreach ($fichierTinyclues as $fichier){
        $contents = file_get_contents("ssh2.sftp://$sftp/data/from_tc/$fichier");
        file_put_contents ("/var/www/emailing/tinyclues/$fichier", $contents);
    }

    //Dedoublonnage des fichiers non US (S'il y en a)
    $fichierNonUS = $tinycluesClass->recupFichier("non_us");


    if($fichierNonUS){

        //TRIE DES FICHIERS (DECROISSANT)
        $tabFichierNonUS = $tinycluesClass->sortBySize($fichierNonUS);
        $tinycluesClass->dedoublonneEtOrganise($tabFichierNonUS);
    }


    //Dedoublonnage des fichiers US (S'il y en a)
    $fichierUS = $tinycluesClass->recupFichier("us");

    if($fichierUS){
        //TRIE DES FICHIERS (DECROISSANT)
        $tabFichierUS = $tinycluesClass->sortBySize($fichierUS);
        $tinycluesClass->dedoublonneEtOrganise($tabFichierUS);
    }

    //RECUPERATION DES FICHIERS DEDOUBONNE POUR ARCHIVAGE
    $fichier = $tinycluesClass->recupFichier("dedoublon");


    $html = '';
    foreach ($fichier as $newFichier){
        $html .= $tinycluesClass->archivageEtEnvoiTinyclues($newFichier);
        unlink($newFichier);
        $oldfichier = explode("/", $newFichier);
        $oldfichier = "$oldfichier[0]/$oldfichier[2]";
        unlink($oldfichier);
    }
    rmdir("tinyclues/dedoublon");

    getSegmentView($html,'../../');
}
/////////////////////  Function action Campaign   ////////////////////////////////////////
function createCampaign(){
    $html = '';
    $url = '../';
    $app = \Slim\Slim::getInstance();

    //recupération des messages pour la création des campagnes
    $messageClass = new Millesima_Message();
    $segmentClass = new Millesima_Segment();
    $briefClass = new Millesima_Brief();
    $bddClass = new Millesima_Bdd();
    $messageList = $messageClass->getMessageOrderByDate();
    $segmentList = $segmentClass->getSegmentList();


    foreach ($messageList as $key=>$message){
        $briefId = $messageClass->getInfoById($message['id'],'brief_id');
        $brief = $briefClass->getBrief($briefId);
        $store = substr($message['name'], 0 ,2 );
        if(date('l', strtotime($brief['dateenvoi'])) == 'Sunday' || date('l', strtotime($brief['dateenvoi'])) == 'Saturday'){
            $heure = $briefClass->getValueConfig($store.'-S');
        } else {
            $heure = $briefClass->getValueConfig($store.'-L');
        }
        $nbContact = 0;
        foreach($segmentList as $segment){
            if($message['name'] == $segment['name']){
                $nbContact = $segment['nb_contact'];
                break;
            }
        }

        $messageList[$key]['nb_contact'] = $nbContact;
        $messageList[$key]['date_send'] = $brief['dateenvoi'];
        $messageList[$key]['heure_send'] = $heure;
    }

    $app->view()->appendData(array('segmentList' => $segmentList, 'messageList' => $messageList));

    //verification param for creation
    $data = $app->request->post();



    if(isset($data) && isset($data["namecampaign"]) && is_array($data["checkbox-message"]) && count($data["checkbox-message"]) > 0 ){
        //var_dump($data);

        //creation des message
        $campaignClass = new Millesima_Campaign();
        $messageIds = $data["checkbox-message"];
        $name = $data["namecampaign"];
        $fromMail = $data["frommailcampaign"];
        $fromName = $data["fromnamecampaign"];
        $replyMail = $data["replymailcampaign"];
        $replyName = $data["replynamecampaign"];
        $subject = $data["subjectcampaign"];
        $type = $data['creation'];

        foreach ($messageIds as $messageId){
            $tmp = explode('-',$messageId);
            $messageId = $tmp[0];
            $message = $messageClass->getMessageById($messageId);
            $brief = $briefClass->getBrief($message[0]['brief_id']);
            foreach($segmentList as $segment){
                if($segment['name'] == $message[0]['name']){
                    $segment = $segment['selligente_id'];
                    break;
                }
            }

            if($type == 'reel'){
                $startDate = $data["dateenvoi-".$messageId].' '.$data["heureenvoi-".$messageId];
                $format = "d/m/Y H:i:s";
                $dateObj = DateTime::createFromFormat($format, $startDate);
				$segment = (int) $segment;
            } else{
                $dateObj = new DateTime('NOW');
				$segment = 7748;
            }

            //echo"<br />";var_dump($segment);
            //die('gfdgdfgd');

            $campaignResponse = $campaignClass->create($message[0],$name,$fromMail,$fromName,$replyMail,$replyName,$subject,$brief['theme'],$segment,$dateObj,$type);
            if($campaignResponse['success']){
                $html .= "<b>La demande d'envoi ".$data['creation']." de campagne a été prise en compte.</b><br/>";
                $html .= $campaignResponse['value']."<br/>";
            } else {
                $html =  " <h3>Error</h3>";
                $html .=  "Message : ".$campaignResponse['value'];
                $html .=  "<br>";
                $html .=  "La demande n'a pas été prise en compte :(";
            }
        }
    }

    //affichage page
    if($html != ''){
        $app->view()->appendData(array( 'html' => $html));
    }

    $content =$app->view()->fetch('campaign_create.php');
    getView($content,$url);
}


function statCampaign(){
    $url = '../';
    $app = \Slim\Slim::getInstance();
    $campaignClass = new Millesima_Campaign();
    $segmentClass = new Millesima_Segment();
    $messageClass = new Millesima_Message();

    $campaignList = $campaignClass->getCampaignOrderByDate();

    $listCampaign = array();
    foreach ($campaignList as $key => $campaign){
        $listCampaign[$key]['idCampagne'] = $campaign['selligente_id'];
        $listCampaign[$key]['name'] = $messageClass->getInfoById($campaign['message_id'],'name');
        $listCampaign[$key]['statut'] = $campaign['statut'];
        $listCampaign[$key]['sendDateSelligente'] = $campaign['send_date'];
        $listCampaign[$key]['nbTarget'] = $campaign['target_count'];
        $listCampaign[$key]['nbSent'] = $campaign['sent_count'];
        $listCampaign[$key]['nbView'] = $campaign['view_count'];
        $listCampaign[$key]['nbClick'] = $campaign['click_count'];
    }

    $app->view()->appendData(array('campaignList' => $listCampaign));

    $content =$app->view()->fetch('campaign_stat.php');
    getView($content,$url);
}

/////////////////////  Function Action AJAX Campaign ////////////////////////////////////////
/////////////////////  Message  ////////////////////////////////////////
function getInfoMessage($message) {
    $return = '';
    if($message != 'undefined'){
        $messageClass = new Millesima_Message();
        $tmp = explode('-',$message, 2);
        $store = substr($tmp[1], 0 ,2 );

        if( $store == 'Di' || $store == 'Oi' || $store == 'SA'){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'d');
        } else if( $store == 'Ei' ){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'e');
        } else if( $store == 'Pi' ){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'p');
        } else if( $store == 'Yi' ){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'y');
        } else if( $store == 'UU' || $store == 'Ui' || $store == 'Uu'){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'u');
        } else if( $store == 'Gi' || $store == 'Hi' || $store == 'SG' || $store == 'Ii'){
            $objet = $messageClass->getTradObjetSendFromId($tmp[0],'g');
        } else {
            $briefId = $messageClass->getInfoById($message,'brief_id');
            $briefClass = new Millesima_Brief();
            $brief = $briefClass->getBrief($briefId);
            $objet = $brief['objfr'];
        }
        $return = $messageClass->getInfoMessage($tmp[1],$objet);
        echo json_encode($return);
    }
}

function getLocalHtml($message) {
    $return = '';
    if($message != 'undefined'){
        $messageClass = new Millesima_Message();
        $return = $messageClass->getInfoById($message,'file_html_link');
    }
    echo json_encode($return);
}

/////////////////////  Segement  ////////////////////////////////////////
function countSegment($data) {
    $return = '';
    if($data != 'undefined'){
        $segmentClass = new Millesima_Segment();
        $tabData = explode(',',$data);
        foreach($tabData as $tabSeg){
            $tmp = explode('_',$tabSeg);
            $segmentName = $tmp[0];
            $segmentId = $tmp[1];
            $return[$segmentName]['nb_contact'] = $segmentClass->segmentCount($segmentId);
        }
    }
    echo json_encode($return);
}

/////////////////////  config  ////////////////////////////////////////
function getConfigDataIndex($name) {
    $return = '';
    if($name != 'undefined'){
        $bddClass = new Millesima_Bdd();
        $return= $bddClass->selectone("SELECT * FROM config Where name = ?", array($name),'value');
        $return = date('y').'-'.$return;
    }
    echo json_encode($return);
}

/////////////////////  brief  ////////////////////////////////////////
function getBriefInfo($idBrief) {
    $return = '';
    $tabTrad = array();
    if($idBrief != 'undefined'){
        $briefClass = new Millesima_Brief();
        $tradClass = new Millesima_Traduction();
        $returnTrad = $tradClass->getAllTrad($idBrief);
        foreach($returnTrad as $trad){
            $lang = $trad['lang'];
            $tabTrad[$lang][$trad['type']] = $trad['value'];
        }
        $return['trad'] = $tabTrad;
        $return['brief'] = $briefClass->getBrief($idBrief);
        $return['brief']['osurl'] = unserialize($return['brief']['osurl']);
        $return['brief']['ostitre'] = unserialize($return['brief']['ostitre']);
        $return['brief']['osdesc'] = unserialize($return['brief']['osdesc']);
    }
    echo json_encode($return);
}

function getMessageSaveInfo($idBrief) {
    $return = '';
    if($idBrief != 'undefined'){
        $messageDataClass = new Millesima_Messagedata();
        $tradClass = new Millesima_Traduction();
        $imageClass = new Millesima_Image();
        $return['messageData'] = $messageDataClass->getMessageDataByIdBrief($idBrief);
        $return['langue'] = $tradClass->getTradByIdMessageData($return['messageData']['id']);
        $image = $imageClass->getImageByIdMessageData($return['messageData']['id']);
        if($image){
            $return['image'] = $image;
        }
    }
    echo json_encode($return);
}

/////////////////////  Traduction  ////////////////////////////////////////
function getTradInfo($data) {
    $return = '';
    if($data != 'undefined'){
        $TradClass = new Millesima_Traduction();
        $tabData = explode('-',$data);
        $return = $TradClass->getAllTradLang($tabData[0],$tabData[1]);
    }
    echo json_encode($return);
}

function saveTradInfo(){
    $app = \Slim\Slim::getInstance();
    //verification param for creation
    $data = $app->request->post();
    $tradClass = new Millesima_Traduction();
    $bddClass = new Millesima_Bdd();
    $html = $tradClass->saveTraduction($data);
    $id = $data['brief_id'];
    $date = date("Y-m-d H:i:s");
    $alltrad = false;
    $bddClass->update("UPDATE brief SET updated_at = (?) where  id = (?)", array($date, $id));

    echo json_encode($html);
}

/////////////////////  campaigne  ////////////////////////////////////////
function sendCampaignReelAjax (){
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();
    $return = '';

    foreach($data as $campaign => $info){

        $campaignClass = new Millesima_Campaign();
        $campaignId = $campaign;
        $segmentId = $info['segmentid'];
        //$segmentId = 9452;
        $dateRecup = $info['date'].' '.$info['heure'];
        $date = date_parse_from_format("d/m/Y H:i:s", $dateRecup);
        $date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
        $date = date('Y-m-d H:i:s',$date);
        $bat = false;

        $return .= sendCampaign($campaignClass,$bat,$segmentId,$campaignId,$date);
        $return .= "<br />";
    }

    echo $return;
}

function  searchCampaign($name){
    $return = "";
    if ($name != 'undefined') {
        $campaignClass = new Millesima_Campaign();
        $return['campaign'] = $campaignClass->getStatCampaign($name);
    }
    echo json_encode($return);
}
$app->run();