<?php

include_once 'class/millesima_campaign.php';
include_once 'class/millesima_abstract.php';
include_once 'class/millesima_brief.php';
include_once 'class/millesima_message.php';
include_once 'class/millesima_bdd.php';


$abstract = new Millesima_Abstract();
$campaignClass = new Millesima_Campaign();
$briefClass = new Millesima_Brief();
$messageClass = new Millesima_Message();
$bddClass = new Millesima_Bdd();



//PASSE LES BRIEF AU STATUT 9 (CAMPAGNE ENVOYE) SI TOUTES LES CAMPAGNES DU BRIEF EST AU STATUT 500

//récupère tout les brief ayant un statut sup ou égal à 4
$brief = $briefClass->getBriefIdByStatutNotComplete("4");
$tabBriefComplete = array();

foreach ($brief as $idBrief){
    $nbPays = $briefClass->getNbPaysByBriefId($idBrief["id"]);
    $nbCampaignCreate = $bddClass->selectAll("select count(*) from campaign_selligente
              join message on message.id = campaign_selligente.message_id
              join brief on message.brief_id = brief.id
              where brief_id = ".$idBrief['id']."
              and campaign_selligente.statut = 'reel'");
    if($nbCampaignCreate[0]['count(*)'] == $nbPays){
        $tabBriefComplete[] = $idBrief["id"];
        $briefClass->updateStatus("9",$idBrief["id"]);
    }
}

//$tabBriefComplete[] = array('id' => "1037");

//Récupère les html et les convertis en png de tous  LES BRIEF AU STATUT 9
foreach ($tabBriefComplete as $idBrief){
    $messageIds = $bddClass->selectAll("select campaign_selligente.message_id from campaign_selligente
              join message on message.id = campaign_selligente.message_id
              where brief_id = ".$idBrief['id']."
              and campaign_selligente.statut = 'reel'");

    foreach($messageIds as $messageId){
        $message = $messageClass->getMessageById($messageId['message_id']);
        $message = $message[0];


        //get Info
        $nameMessage = $message['name'];
        $pattern = '/(?=\d)/';
        $array = preg_split($pattern, $nameMessage, 2);
        $name = $array[0];
        $code = $array[1];
        $store = substr($name, 0 ,2 );
        if ($store == 'SA' || $store == 'SF'){
            $name = str_replace('A','',$name);
            $name = str_replace('F','',$name);
        } else if ($store == 'SG'){
            $name = str_replace('SG','N',$name);
        }
        $codePick=$name.' '.$code;
        if (strpos($nameMessage, 'prim') !== false){
            $year = date('Y', strtotime('-1 years'));
            $type = '1-PRIMEURS';
        } else {
            $year = date('Y');
            $type = '2-LIVRABLES';
        }
        $namePng = $nameMessage.'_'.$year;

        //get html
        $html = $message['html'];
        $filename = "fichiers/tmpGeneratePng.html";
        $handle = fopen("$filename", "w");
        fwrite($handle,$html);
        fclose($handle);

        //set image
        $test = shell_exec('library/phantomjs js/millesima/generateImg.js');
        rename('fichiers/tmpGeneratePng.html.png', 'fichiers/'.$namePng.'.png');

        //set Meta
        $test = shell_exec('library/Image-ExifTool-10.96/exiftool -overwrite_original -SubjectCode='.chr(34).$codePick.chr(34).' -ModelAge='.$year.'  fichiers/'.$namePng.'.png');

        //move file
        copy("fichiers/".$namePng.".png","fichiers/archivage/Catalogage_Automatique/Emailings/".$namePng.".png");
        //die('gfdgdfgd');
    }




}
