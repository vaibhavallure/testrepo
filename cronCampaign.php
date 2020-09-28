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
//$brief = $briefClass->getBriefIdByStatutNotComplete("4");
/*
$brief = $briefClass->getBriefIdByStatut("4");

$tabBriefComplete = array();

foreach ($brief as $idBrief){
    $nbPays = $briefClass->getNbPaysByBriefId($idBrief["id"]);
	echo("id : " . $idBrief["id"]);
	echo(", nbPays : " . $nbPays);
    $nbCampaignCreate = $bddClass->selectAll("select count(*) from campaign_selligente
              join message on message.id = campaign_selligente.message_id
              join brief on message.brief_id = brief.id
              where brief_id = ".$idBrief['id']."
              and campaign_selligente.statut = 'reel'");
	echo(", nbCampaignCreate  : " . $nbCampaignCreate[0]['count(*)']); 
    if($nbCampaignCreate[0]['count(*)'] == $nbPays){
        $tabBriefComplete[] = $idBrief["id"];
        //$briefClass->updateStatus("9",$idBrief["id"]);
    }
	echo "\r\n";
}
//var_dump($tabBriefComplete);
*/
$tabBriefComplete[] = '1430';

//Récupère les html et les convertis en png de tous  LES BRIEF AU STATUT 9

foreach ($tabBriefComplete as $idBrief){
    $briefClass->createPng($idBrief);
    //die('die fin for 1');
}

