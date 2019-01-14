<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 11/07/18
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

include_once 'class/millesima_abstract.php';
include_once 'class/apitotextmaster.php';
include_once 'class/millesima_textmaster.php';
include_once 'class/millesima_brief.php';
include_once 'class/millesima_traduction.php';


$briefClass = new Millesima_Brief();
$tradClass = new Millesima_Traduction();
$briefList = $briefClass->getBriefStatutList("3");

foreach ($briefList as $brief){
	var_dump($brief['id']);
    $lang = explode('|', $brief['pays']);
    if(in_array('d',$lang) || in_array('o',$lang) || in_array('sa',$lang)){
        $langProj = "de";
        var_dump('lang de');
        getProjectTraduction($brief['id'],$langProj,$brief);
    }
    if(in_array('g',$lang) || in_array('i',$lang) || in_array('h',$lang) || in_array('sg',$lang)){
        $langProj = "en";
        var_dump("lang en");
        getProjectTraduction($brief['id'],$langProj,$brief);
    }
}

function getProjectTraduction($id, $langProj,$brief){
    $textMasterClass = new Millesima_Textmaster();
    $tradClass = new Millesima_Traduction();
    $project = $textMasterClass->getProjReviewByBrief($id,$langProj);
	//var_dump($project->project_id);
	//die('gfdgdf');
    if(isset($project->id)){
        var_dump($project->project_id);
        $listDocument = $textMasterClass->getDocuReviewByBrief($project->project_id);
        foreach($listDocument as $doc){
            var_dump($doc->document_id);
            var_dump($doc->extern_app_title);
            if($doc->status == "in_review") {
                var_dump($doc->status);
                if($langProj == 'de'){
                    $lang = 'd';
                } else if($langProj == 'en'){
                    $lang = 'g';
                }
                $value = $doc->traduction_content;
                $type = $doc->extern_app_title;
                $isExist = $tradClass->getTrad($id,$lang,$type);
                if(!$isExist){
                    var_dump('insert');
                    $result = $tradClass->insertTrad($id,$lang,$type,$value,1);
                    $tradClass->sendEmail('textmaster',$brief,$id);
                } else {
                    if (is_null($isExist['value']) || $isExist['value'] == "" ) {
                        var_dump('update');
                        $result = $tradClass->updateTradText($id,$lang,$type,$value,0);
                        $tradClass->sendEmail('textmaster',$brief,$brief['id']);
                    } else if ( $isExist['value'] == 2 ) {
                        var_dump('update 2');
                        $result = $tradClass->updateTradText($id,$lang,$type,$value,0);
                        $tradClass->sendEmail('textmaster',$brief,$brief['id']);
                    }
                }
            }
        }
    }
}







