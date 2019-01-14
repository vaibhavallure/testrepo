<?php

/**
 * Created by PhpStorm.
 * User: ybelledent
 * Date: 20/12/2016
 * Time: 09:43
 */
include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';
class Millesima_Image extends Millesima_Abstract
{
    public function getImageByIdMessageData($idMessageData){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll('SELECT * FROM image WHERE idmessagedata = '.$idMessageData.' ORDER BY id');
        if(count($res)>0){
            return $res;
        } else {
            return false;
        }
    }

    public function saveImgBdd($tableau, $idMessage){
        $bddClass = new Millesima_Bdd();

        $attributs = 'idmessagedata';
        $pointInterogation = '(?';
        $values = array();
        $values[] = $idMessage;
        foreach ($tableau as $key => $value){
            $attributs .= ','.$key;
            $pointInterogation .= ',?';

            $values[] = $value;
        }
        $pointInterogation .= ')';

        //insert in bdd de l'image
        $requete =  "INSERT INTO image (".$attributs.")
                    VALUES ".$pointInterogation;

        $result = $bddClass->insert($requete,$values);
        if ($result == "0") {
            $html = 'Les différentes images ne sont pas enregistrés - Problème de sauvegarde';
        }else{
            $html = "";
        }
        return $html;
    }

    public function deleteImgBdd($idMessage){
        $bddClass = new Millesima_Bdd();
        $bddClass->delete("Delete from image WHERE idmessagedata = (?)",array($idMessage));
    }
}