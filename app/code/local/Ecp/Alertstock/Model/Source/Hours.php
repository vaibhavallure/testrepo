<?php

class Ecp_Alertstock_Model_Source_Hours {

    public function toOptionArray() {
        $retArray = array();
        
        array_push($retArray, array('value' => '*', 'label' => '*'));
        array_push($retArray, array('value' => '00', 'label' => '00'));
        array_push($retArray, array('value' => '01', 'label' => '01'));
        array_push($retArray, array('value' => '02', 'label' => '02'));
        array_push($retArray, array('value' => '03', 'label' => '03'));
        array_push($retArray, array('value' => '04', 'label' => '04'));
        array_push($retArray, array('value' => '05', 'label' => '05'));
        array_push($retArray, array('value' => '06', 'label' => '06'));
        array_push($retArray, array('value' => '07', 'label' => '07'));
        array_push($retArray, array('value' => '08', 'label' => '08'));
        array_push($retArray, array('value' => '09', 'label' => '09'));
        array_push($retArray, array('value' => '10', 'label' => '10'));
        array_push($retArray, array('value' => '11', 'label' => '11'));
        array_push($retArray, array('value' => '12', 'label' => '12'));
        array_push($retArray, array('value' => '13', 'label' => '13'));
        array_push($retArray, array('value' => '14', 'label' => '14'));
        array_push($retArray, array('value' => '15', 'label' => '15'));
        array_push($retArray, array('value' => '16', 'label' => '16'));
        array_push($retArray, array('value' => '17', 'label' => '17'));
        array_push($retArray, array('value' => '18', 'label' => '18'));
        array_push($retArray, array('value' => '19', 'label' => '19'));
        array_push($retArray, array('value' => '20', 'label' => '20'));
        array_push($retArray, array('value' => '21', 'label' => '21'));
        array_push($retArray, array('value' => '22', 'label' => '22'));
        array_push($retArray, array('value' => '23', 'label' => '23'));
        
        return $retArray;
    }

}
