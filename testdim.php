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
include_once 'class/millesima_brief.php';
include_once 'class/millesima_message.php';
include_once 'class/millesima_bdd.php';
include_once 'class/apitoselligente.php';



$abstract = new Millesima_Abstract();
$campaignClass = new Millesima_Campaign();

$apiToSell = new Millesima_Api_To_Selligente();
$client =$apiToSell->getClientBroadcast();

$results = $client->GetCampaignKpi(array('campaignId' => 7738));

var_dump($results);
die('fin');