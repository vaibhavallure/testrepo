
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 18/12/13
 * Time: 17:27
 * To change this template use File | Settings | File Templates.
 */
include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';
include_once 'millesima_brief.php';

class Millesima_Traduction extends Millesima_Abstract
{
    public function deleteTradByIdMessageData($idMessageData){
        $bddClass = new Millesima_Bdd();
        $bddClass->delete("Delete from traduction WHERE idmessagedata = (?)",array($idMessageData));
    }

    public function deleteTradByBriefIdAndLangAndType($briefId,$langue,$type){
        $bddClass = new Millesima_Bdd();
        $bddClass->delete("Delete from traduction WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($briefId,$langue,$type));
    }

    public function getTradByIdMessageData($idMessageData){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll('SELECT * FROM traduction WHERE idmessagedata = '.$idMessageData.' ORDER BY id DESC');
        if(count($res)>0){
            return $res;
        } else {
            return false;
        }
    }

    /**
     * Function to get all trad brief
     * @param int $briefId
     * @return mixed
     */
    public function getAllTrad ($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("Select * from traduction where brief_id = (?)",array($briefId));
        return $res;
    }

    /**
     * Function to get all trad brief
     * @param int $briefId
     * @param string $lang
     * @return mixed
     */
    public function getAllTradLang ($briefId,$lang){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("Select * from traduction where brief_id = (?) AND lang = ?",array($briefId,$lang));
        return $res;
    }

    /**
     * Function to get nb trad brief
     * @param int $briefId
     * @return int
     */
    public function getNbTradByBriefId($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("select * from traduction where brief_id = (?) and is_textmaster = 0",array($briefId));
        return $res;
    }



    /**
     * Function to get nb trad brief
     * @param string $lang
     * @param int $briefId
     * @return int
     */
    public function getNbTradByBriefIdbyLang($briefId,$lang){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("select * from traduction where brief_id = (?) and is_textmaster = 0 AND lang = ?",array($briefId,$lang));
        return $res;
    }

    /**
     * Function to get nb trad brief
     * @param int $briefId
     * @return int
     */
    public function getNbTradByBriefIdTextMaster($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("select * from traduction where brief_id = (?) and is_textmaster = 1 and is_valid = 1",array($briefId));
        return $res;
    }

    /**
     * Function to get nb trad brief
     * @param int $briefId
     * @param string $lang
     * @return int
     */
    public function getNbTradByBriefIdTextMasterBylang($briefId,$lang){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("select * from traduction where brief_id = (?) and is_textmaster = 1 and is_valid = 1 AND lang = ?",array($briefId,$lang));
        return $res;
    }

    /**
     * Function to test une lang is traduction
     * @param int $briefId
     * @param int $lang
     * @return string
     */
    public function getNbTradLang($briefId,$lang){
        $nb = $this->getAllTradLang($briefId,$lang);
        $nbTextMaster = $this->getNbTradByBriefIdTextMasterBylang($briefId,$lang);
        $nbtrad = count($nb) + count($nbTextMaster);
        return $nbtrad;
    }

    /**
     * Function to get value trad
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @return mixed
     */
    public function getValueTrad($briefId,$lang,$type){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM traduction WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($briefId,$lang,$type),'value');
        if(count($res)>0){
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to get value trad
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @return mixed
     */
    public function getTrad($briefId,$lang,$type){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM traduction WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($briefId,$lang,$type));
        if(count($res)>0){
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to uptdate trad
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @param string $value
     * @return mixed
     */
    public function updateTrad($briefId,$lang,$type,$value){
        $bddClass = new Millesima_Bdd();
        $bddClass->update("UPDATE traduction SET value = (?) WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($value,$briefId,$lang,$type));
    }

    /**
     * Function to uptdate trad
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @param string $value
     * @param string $isValid
     * @return mixed
     */
    public function updateTradText($briefId,$lang,$type,$value,$isValid){
        $bddClass = new Millesima_Bdd();
        $bddClass->update("UPDATE traduction SET value = (?),is_valid = (?) WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($value,$isValid,$briefId,$lang,$type));
    }

    /**
     * Function to insert trad
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @param string $value
     * @param int $is_textmaster
     * @return mixed
     */
    public function insertTrad($briefId,$lang,$type,$value,$is_textmaster = 0){
        $bddClass = new Millesima_Bdd();
        $result = $bddClass->insert("INSERT INTO traduction (brief_id,lang,type,value,is_textmaster) VALUES (?,?,?,?,?)",array($briefId,$lang,$type,$value,$is_textmaster));
        if ($result == "0") {
            //text de retour de la non création du brief
            $html = '';
            $html .="La trad n'a pas été créé, une erreur est survenue.";
            return $html;
        }
        return false;
    }

    /**
     * Function to valid trad text master
     * @param int $briefId
     * @param string $lang
     * @param string $type
     * @param int $is_valid
     */
    public function validTradTextMaster($briefId,$lang,$type,$is_valid){
        $bddClass = new Millesima_Bdd();
        $bddClass->update("UPDATE traduction SET is_valid = (?) WHERE brief_id = (?) AND lang = (?) AND type = (?)",array($is_valid,$briefId,$lang,$type));
    }

    /**
     * Function to save traduction in emailing
     *
     * @param array $data
     * @return string
     */
    public function saveTraduction($data){
        $briefClass = new Millesima_Brief();
        $textMasterClass = new Millesima_Textmaster();

        $briefId = $data['brief_id'];
        $statut = $briefClass->getStatutBrief($briefId);
        $lang = $data['lang_id'];
        if(isset($data['is_textmaster'])){
            $isTextmaster = $data['is_textmaster'];
        } else {
            $isTextmaster = 0;
        }


        $result = "";
        foreach ($data as  $type => $value){
            if($statut < 3){
                if(strstr($type,'trad')){
                    $isExist = $this->getValueTrad($briefId,$lang,$type);
                    if($isExist && $value != ""){
                        $result = $this->updateTrad($briefId,$lang,$type,$value);
                    } else if ($isExist && $value == "") {
                        $result = $this->deleteTradByBriefIdAndLangAndType($briefId,$lang,$type);
                    }else if(!$isExist && $value != ""){
                        $result = $this->insertTrad($briefId,$lang,$type,$value,$isTextmaster);
                    }
                     if($result){
                        return $result;
                    }
                }
            }else{
                if(strstr($type,'trad') && $value != ""){
                    $isExist = $this->getValueTrad($briefId,$lang,$type);
                    if($isExist){
                        $result = $this->updateTrad($briefId,$lang,$type,$value);
                    } else {
                        $result = $this->insertTrad($briefId,$lang,$type,$value,$isTextmaster);
                    }
                    if($result){
                        return $result;
                    }
                }
            }
        }
        $brief = $briefClass->getBrief($briefId);
        $html = '';
        $html .="Traductions prises en comptes. Identifiant brief : ".$brief['code'];
        return $html;
    }

    /**
     * Function to save traduction in emailing
     *
     * @param int $id
     * @return string
     */
    public function testAllTradGood($id){
        $briefId = $id;

        $bddClass = new Millesima_Bdd();
        $briefClass = new Millesima_Brief();

        $nbChampTrad = $briefClass->getNbChampTradBrief($briefId);
        $nbChampTrad = (int) $nbChampTrad['nbChampTrad'];

        //get lang brief
        $brief = $briefClass->getBrief($briefId);
        $pays = $brief['pays'];
        $listPays = explode('|',$pays);

        if(in_array("g",$listPays) || in_array("i",$listPays) || in_array("h",$listPays) || in_array("sg",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"g");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        if(in_array("o",$listPays) || in_array("sa",$listPays) || in_array("d",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"d");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        if(in_array("y",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"y");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        if(in_array("e",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"e");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        if(in_array("p",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"p");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        if(in_array("u",$listPays)){
            $nbtrad = $this->getNbTradLang($briefId,"u");
            if($nbChampTrad != $nbtrad ){
                return false;
            }
        }
        return true;
    }


}