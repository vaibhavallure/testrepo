<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 27/02/19
 * Time: 11:45
 * To change this template use File | Settings | File Templates.
 */

include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';

class Millesima_Ressource extends Millesima_Abstract
{
    /**
     * Function to get one ressource
     * @param string store
     * @param string name
     * @param array info
     * @return mixed
     */
    public function getRessourceValue($store,$name,$info = array()){
        $bddClass = new Millesima_Bdd();
        $return = '';
        $res= $bddClass->selectAll("SELECT * FROM ressource WHERE store = ? AND name = ? ORDER BY id DESC",array($store,$name));
        if(count($res)>1){
            $now = date("Y-m-d H:i:s");
            foreach($res as $line){
                if( ($line['start_date'] < $now) && ($line['end_date'] > $now || is_null($line['end_date'])) ){
                    $return =  $line['value'];
                }
            }
        } else if (count($res)>0){
            $return =  $res[0]['value'];
        }

        foreach($info as $key=>$value ){
            $return = str_replace('{$'.$key.'}',$value,$return);
        }
       return $return;
    }

    /**
     * Function to get all ressource
     * @return mixed
     */
    public function getRessourceList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM ressource ORDER BY id DESC");
        return $res;
    }

    /**
     * Function to get all ressource
     * @param filter
     * @return mixed
     */
    public function getRessourceListFilter($filter){
        $bddClass = new Millesima_Bdd();
        $req = "SELECT * FROM ressource WHERE";
        $now = date("Y-m-d H:i:s");
        $value = array();

        if((isset($filter['store_filter']) && $filter['store_filter'] != '')){
            $req = $req.' store = ?';
            $value[] = $filter['store_filter'];
        }
        if((isset($filter['name_filter']) && $filter['name_filter'] != '')) {
            if(count($value)> 0){
                $req = $req.' AND';
            }
            $req = $req.' name = ?';
            $value[] = $filter['name_filter'];
        }
        if((isset($filter['actif_filter']) && $filter['actif_filter'] == '1')) {
            if(count($value)> 0){
                $req = $req.' AND';
            }
            $req = $req.' start_date <= \''.$now.'\' AND (end_date >=\''.$now.'\' OR end_date is null ) ';
        }else  if((isset($filter['actif_filter']) && $filter['actif_filter'] == '2')) {
            if(count($value)> 0){
                $req = $req.' AND';
            }
            $req = $req.' (start_date > \''.$now.'\' OR end_date < \''.$now.'\')';
        }
        $req = $req.'ORDER BY id DESC';

        //var_dump('aaaaaaaaaaaaaaaaaaaaaaaaaaaa');var_dump($req);
        $res = $bddClass->selectAll($req, $value);
        return $res;
    }

    /**
     * Function to get ressource by id
     * @param id
     * @return mixed
     */
    public function getRessourceById($id){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM ressource WHERE id = ? ORDER BY id DESC",array($id));
        return $res;
    }

    /**
     * Function to get ressource for submit or change data
     * @param $store
     * @param $name
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
     public function getRessourceSubmit($store,$name,$startDate,$endDate) {
         $bddClass = new Millesima_Bdd();
         $now = date("Y-m-d H:i:s");
         if(is_null($endDate)){
             //return 'titi';
             $res = $bddClass->selectAll("SELECT * FROM ressource WHERE store = ? AND name = ? AND (end_date >= ? OR end_date IS NULL) ORDER BY id DESC",array($store,$name,$startDate));
         } else {
             //return 'toto';
             $res = $bddClass->selectAll("SELECT * FROM ressource WHERE store = ? AND name = ? AND start_date <= ? AND (end_date >= ?  Or end_date is null)  ORDER BY id DESC",array($store,$name,$endDate,$startDate));
         }
         return $res;
    }
}