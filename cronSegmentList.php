<?php

include_once 'class/millesima_segment.php';
include_once 'class/apitoselligente.php';
include_once 'class/millesima_abstract.php';
include_once 'class/millesima_bdd.php';


$html = '';
$return = array();
$apiToSell = new Millesima_Api_To_Selligente();
$client =$apiToSell->getClientIndividual();
$data['listId'] = 657; //base tout clients
$results = $client->GetSegments($data);

if($results->GetSegmentsResult == 0){
    $segmentList = $results->segments->SegmentInfo;
    foreach($segmentList as $segment){
        $bddClass = new Millesima_Bdd();
        $segmentBdd = $this->getSegmentByName($segment->Name);
        if($segmentBdd){
            $bddClass->update("UPDATE segment_selligente SET status = ?, selligente_id = ?, type = ? WHERE id = ?",array('selligente',(int) $segment->ID,$segment->Type,$segmentBdd['id']));
            $return[] = $segment->Name;
            $memberCount = $this->segmentCount($segment->ID);
            $html .= "Le segment ".$segment->Name." a ete mis a jour, nb contact ".$memberCount."<br />";
        }

    }
}
echo $html;

