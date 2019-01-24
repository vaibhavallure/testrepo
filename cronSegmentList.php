<?php

include_once 'class/apitoselligente.php';
include_once 'class/millesima_abstract.php';
include_once 'class/millesima_bdd.php';

ini_set('soap.wsdl_cache_enabled',0);
ini_set('soap.wsdl_cache_ttl',0);

$bddClass = new Millesima_Bdd();
$html = '';
$return = array();
$apiToSell = new Millesima_Api_To_Selligente();
$client =$apiToSell->getClientIndividual();
$data['listId'] = 657; //base tout clients
$resultListSegment = $client->GetSegments($data);

if($resultListSegment->GetSegmentsResult == 0){
    $segmentList = $resultListSegment->segments->SegmentInfo;
	foreach($segmentList as $segment){
		$segmentBdd = false;
		$memberCount = 0;
        $res= $bddClass->selectAll("SELECT * FROM segment_selligente WHERE name = '".$segment->Name."' and status='local' ORDER BY id DESC");
        if(count($res)>0){
			$segmentBdd = $res[0];
            $bddClass->update("UPDATE segment_selligente SET status = ?, selligente_id = ?, type = ? WHERE id = ?",array('selligente',(int) $segment->ID,$segment->Type,$segmentBdd['id']));
			$resultSegmentCount = $client->GetSegmentRecordCount(array('segmentId'=>$segment->ID));
			if($resultSegmentCount->GetSegmentRecordCountResult == 0){
				$memberCount = $resultSegmentCount->segmentCount;
				$bddClass->update("UPDATE segment_selligente SET nb_contact = ? WHERE selligente_id = ?",array((int) $memberCount,(int) $segment->ID));
			}
		    echo "Le segment ".$segment->Name." a ete mis a jour, nb contact ".$memberCount."\r\n";
		}
	}
}


