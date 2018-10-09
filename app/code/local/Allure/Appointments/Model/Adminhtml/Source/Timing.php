<?php
class Allure_Appointments_Model_Adminhtml_Source_Timing
{
    public function toOptionArray()
    {
    	$array=array();
    	for($i=0;$i<24;$i++){
    		$array[]=array('value' => $i, 'label' => Mage::helper('appointments')->__(sprintf("%02d", $i).':00'));
    		$array[]=array('value' => $i+0.5, 'label' => Mage::helper('appointments')->__(sprintf("%02d", $i).':30'));
    	}
    	return $array;
    }
}
