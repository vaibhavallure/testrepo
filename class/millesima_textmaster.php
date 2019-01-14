<?php
include_once 'millesima_abstract.php';
include_once 'apitotextmaster.php';

/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 28/06/18
 * Time: 10:29
 * To change this template use File | Settings | File Templates.
 */

class Millesima_Textmaster extends Millesima_Abstract {

    /**
     * @param $brief
     * @param $lang
     * @return json
     */
    public function sendProjectTextMaster($brief,$lang){
        $adapter = new Millesima_Api_To_Textmaster();
        $name = $this->getCode($brief['typebrief']);

        $name = $name.$brief['code'];

        if($lang == 'g'){
            $lang = 'en';
            $langCodes  = array('en-gb');
        } else if ($lang == 'd'){
            $lang = 'de';
            $langCodes  = array('de-de');
        }

        $data = array(
            'name'=>$name,
            'extern_app_name'=>'millesimaemailing',
            'extern_app_id'=>$brief['id'],
            'language_from'=>'fr',
            'language_to'=>$lang,
            //'language_to_codes'=>$langCodes,
            'project_briefing'=>'nouveau projet pour le : '.$name
        );

        $jsonData = json_encode($data);
        //var_dump($jsonData);

        $url = 'project/set';
        $dataProj =  $adapter->requete($jsonData,$url,'POST',true);

        return $dataProj;
    }

    public function actionProjectTextMaster($brief, $action,$lang){
        $adapter = new Millesima_Api_To_Textmaster();
        $name = $this->getCode($brief['typebrief']);
        $name = $name.$brief['code'];

        if($lang == 'g'){
            $lang = 'en';
        } else if ($lang == 'd'){
            $lang = 'de';
        }

        $data = array(
            'name'=>$name,
            'extern_app_name'=>'millesimaemailing',
            'extern_app_id'=>$brief['id'],
            'language_to'=>$lang,
        );

        $jsonData = json_encode($data);
        $url = 'project/action/'.$action;
        $dataProj =  $adapter->requete($jsonData,$url,'POST',true);

        return $dataProj;
    }

    //translation project
    public function translationProjectTextMaster($brief, $lang){
        $adapter = new Millesima_Api_To_Textmaster();
        $name = $this->getCode($brief['typebrief']);
        $name = $name.$brief['code'];

        if($lang == 'g'){
            $lang = 'en';
        } else if ($lang == 'd'){
            $lang = 'de';
        }

        $data = array(
            'name'=>$name,
            'extern_app_name'=>'millesimaemailing',
            'extern_app_id'=>$brief['id'],
            'language_to'=>$lang,
        );

        $jsonData = json_encode($data);
        $url = 'project/translation';
        $dataProj =  $adapter->requete($jsonData,$url,'POST',true);

        return $dataProj;
    }



    /**
     * create  all document
     * @param $data
     * @return json
     */
    public function sendDocumentTextMaster($data){
        $adapter = new Millesima_Api_To_Textmaster();
        $data = array(
            'project_id'=>$data['project_id'],
            'extern_app_title'=>$data['type'],
            'title'=>$data['type'],
            'original_content'=>$data['value'],
        );
        $jsonData = json_encode($data);

        $url = 'document/set';
        $dataDoc =  $adapter->requete($jsonData,$url,'POST',true);

        //var_dump($dataDoc);echo "<br />";
        //die('gfgdfg');
        return $dataDoc;
    }

    /**
     * create  all document
     * @param $brief
     * @param $projectId
     * @return string
     */
    public function sendDocument($brief,$projectId){
        $data = array('project_id' => $projectId, 'type'=> '' , 'value'=> '');

        if($brief['objfr'] != ""){
            $data['type'] = 'objtrad';
            $data['value'] = $brief['objfr'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }
        if($brief['subobj'] != ""){
            $data['type'] = 'subobjtrad';
            $data['value'] = $brief['subobj'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }
        if($brief['wording'] != ""){
            $data['type'] = 'wordingtrad';
            $data['value'] = $brief['wording'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }
        if($brief['titredescsousimg'] != ""){
            $data['type'] = 'titredescsousimgtrad';
            $data['value'] = $brief['titredescsousimg'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }
        if($brief['descsousimg'] != ""){
            $data['type'] = 'descsousimgtrad';
            $data['value'] = $brief['descsousimg'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }
        if ($brief['offsup'] == "1"){
            $tabOsTitle = unserialize($brief['ostitre']);
            $tabOsDesc = unserialize($brief['osdesc']);

            $nboffsup = $brief['nboffsup'];
            for ($i= 1; $i < $nboffsup+1 ;$i++){
                if( $tabOsTitle[$i-1] != ""){
                    //var_dump($brief);
                    $data['type'] = 'article'.$i.'ostitretrad';
                    $data['value'] = $tabOsTitle[$i-1];

                    $dataDoc = $this->sendDocumentTextMaster($data);
                    if(!isset($dataDoc->id)){
                        return 'ko textmaster';
                    }
                }
                if( $tabOsDesc[$i-1] != ""){
                    $data['type'] = 'article'.$i.'osdesctrad';
                    $data['value'] = $tabOsDesc[$i-1];

                    $dataDoc = $this->sendDocumentTextMaster($data);
                    if(!isset($dataDoc->id)){
                        return 'ko textmaster';
                    }
                }
            }
        }
        if($brief['bpinfo'] != ""){
            $data['type'] = 'bpinfotrad';
            $data['value'] = $brief['bpinfo'];

            $dataDoc = $this->sendDocumentTextMaster($data);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }
        }

        return 'ok textmaster';
    }

    /**
     * get all traduction from a brief id
     * @param $briefId
     * @param $lang
     * @return  string
     */
    public function getProjReviewByBrief($briefId,$lang){
        $adapter = new Millesima_Api_To_Textmaster();

        $url = 'project/getreview?externappname=millesimaemailing&externappid='.$briefId.'&languageto='.$lang;
        $dataProj =  $adapter->requete('',$url,'GET',true);

        return $dataProj;
    }

    /**
     * get all traduction from a brief id
     * @param $projectId
     * @return  string
     */
    public function getDocuReviewByBrief($projectId){
        $adapter = new Millesima_Api_To_Textmaster();

        $url = 'document/listreview?projectid='.$projectId;
        $listdoc =  $adapter->requete('',$url,'GET',true);

        return $listdoc;
    }

    /**
     * get all traduction from a brief id
     * @param $data
     * @return  string
     */
    public function validAllTrad($data){
        $adapter = new Millesima_Api_To_Textmaster();

        if($data['lang_id'] == 'g'){
            $langProj = 'en';
        } else if ($data['lang_id'] == 'd'){
            $langProj = 'de';
        }

        $project = $this->getProjReviewByBrief($data['brief_id'],$langProj);
        if(!isset($project->project_id)){
            return 'complete textmaster ko';
        }

        $listDocument = $this->getDocuReviewByBrief($project->project_id);
        foreach($listDocument as $doc){
            $dataSend = array(
                'document_id'=>$doc->document_id,
                'message'=>'well done!',
                'satisfaction'=>'positive',
             );
            $jsonData = json_encode($dataSend);

            $url = 'document/complete';
            $dataDoc =  $adapter->requete($jsonData,$url,'POST',true);
            if(!isset($dataDoc->id)){
                return 'ko textmaster';
            }

        }
        return 'complete textmaster ok';
    }

    /**
     * get all traduction from a brief id
     * @param $data
     * @return  string
     */
    public function invalidAllTrad($data){
        $adapter = new Millesima_Api_To_Textmaster();
        if($data['inv_lang'] == 'g'){
            $langProj = 'en';
        } else if ($data['inv_lang'] == 'd'){
            $langProj = 'de';
        }

        //get project
        $project = $this->getProjReviewByBrief($data['inv_brief_id'],$langProj);
        if(!isset($project->project_id)){
            return 'ko textmaster no project';
        }



        //get document
        $url = 'document/getonedoc?projectid='.$project->project_id.'&externapptitle='.$data['inv_type'];
        $doc =  $adapter->requete('',$url,'GET',true);
        if(!isset($doc->document_id)){
            return 'ko textmaster  no document';
        }

        //send review
        $message = urlencode($data['inv_message']);
        $url = 'document/review?projectid='.$project->project_id.'&documentid='.$doc->document_id.'&message='.$message;
        $dataDoc =  $adapter->requete('',$url,'GET',true);

        if(!isset($dataDoc->id)){
            return 'ko textmaster no invalid' ;
        }

        return 'complete textmaster ok';
    }

}