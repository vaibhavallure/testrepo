<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 09/01/19
 * Time: 16:27
 * To change this template use File | Settings | File Templates.
 */

include_once 'class/millesima_campaign.php';
include_once 'class/millesima_abstract.php';
include_once 'class/millesima_bdd.php';
include_once 'class/apitoselligente.php';



$abstract = new Millesima_Abstract();
$campaignClass = new Millesima_Campaign();
$apiToSell = new Millesima_Api_To_Selligente();

$client =$apiToSell->getClientBroadcast();
$campaignList = $campaignClass->getCampaignList();

foreach ($campaignList as $campaign){
    $idSell = $campaign['selligente_id'];
    if (is_null($campaign['target_count'])){
        $results = $client->GetCampaignKpi(array('campaignId' => (int)$idSell));
        if($results->GetCampaignKpiResult == 0){
            $bddClass = new Millesima_Bdd();
            $bddClass->update("UPDATE campaign_selligente SET target_count = ?, sent_count = ?, view_count = ? , click_count = ? WHERE selligente_id = ?",array($results->campaignKpi->TargetCount,$results->campaignKpi->SentCount,$results->campaignKpi->ViewCount,$results->campaignKpi->ClickCount,$idSell));
        }
    }
}
die('fin');