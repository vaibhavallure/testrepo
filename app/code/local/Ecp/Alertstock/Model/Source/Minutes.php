<?php

class Ecp_Alertstock_Model_Source_Minutes {


    public function toOptionArray() {
        $retArray = array();
        
        array_push($retArray, array('value' => '00', 'label' => '00'));
        array_push($retArray, array('value' => '05', 'label' => '05'));
        array_push($retArray, array('value' => '10', 'label' => '10'));
        array_push($retArray, array('value' => '15', 'label' => '15'));
        array_push($retArray, array('value' => '20', 'label' => '20'));
        array_push($retArray, array('value' => '25', 'label' => '25'));
        array_push($retArray, array('value' => '30', 'label' => '30'));
        array_push($retArray, array('value' => '35', 'label' => '35'));
        array_push($retArray, array('value' => '40', 'label' => '40'));
        array_push($retArray, array('value' => '45', 'label' => '45'));
        array_push($retArray, array('value' => '50', 'label' => '50'));
        array_push($retArray, array('value' => '55', 'label' => '55'));
        
        return $retArray;
    }    

}
