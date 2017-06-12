<?php
class Allure_Appointments_Model_Adminhtml_Source_Piercer
{
    public function toOptionArray()
    {        
        $array = array(
            array('value' => '0', 'label' => Mage::helper('appointments')->__('00:00')),
            array('value' => '1', 'label' => Mage::helper('appointments')->__('01:00')),
            array('value' => '2', 'label' => Mage::helper('appointments')->__('02:00')),
            array('value' => '3', 'label' => Mage::helper('appointments')->__('03:00')),      
        	array('value' => '4', 'label' => Mage::helper('appointments')->__('04:00')),
        	array('value' => '5', 'label' => Mage::helper('appointments')->__('05:00')),
        	array('value' => '6', 'label' => Mage::helper('appointments')->__('06:00')),
        	array('value' => '7', 'label' => Mage::helper('appointments')->__('07:00')),
        	array('value' => '8', 'label' => Mage::helper('appointments')->__('08:00')),
        	array('value' => '9', 'label' => Mage::helper('appointments')->__('09:00')),
        	array('value' => '10', 'label' => Mage::helper('appointments')->__('10:00')),
       		array('value' => '11', 'label' => Mage::helper('appointments')->__('11:00')),
       		array('value' => '12', 'label' => Mage::helper('appointments')->__('12:00')),
       		array('value' => '13', 'label' => Mage::helper('appointments')->__('13:00')),
       		array('value' => '14', 'label' => Mage::helper('appointments')->__('14:00')),
       		array('value' => '15', 'label' => Mage::helper('appointments')->__('15:00')),
       		array('value' => '16', 'label' => Mage::helper('appointments')->__('16:00')),
       		array('value' => '17', 'label' => Mage::helper('appointments')->__('17:00')),
       		array('value' => '18', 'label' => Mage::helper('appointments')->__('18:00')),
        	array('value' => '19', 'label' => Mage::helper('appointments')->__('19:00')),
        	array('value' => '20', 'label' => Mage::helper('appointments')->__('20:00')),
        	array('value' => '21', 'label' => Mage::helper('appointments')->__('21:00')),
        	array('value' => '22', 'label' => Mage::helper('appointments')->__('22:00')),
        	array('value' => '23', 'label' => Mage::helper('appointments')->__('23:00')),
        		
        );       
    	return $array;
    }
    public function toArray()
    {
    	$piercers = array();
    	$collection = Mage::getModel('appointments/piercers')->getCollection();
    	foreach ($collection as $model)
    	{
    		$piercers[$model->getId()]=$model->getFirstname()." ".$model->getLastname(); 
    	}
    	
    	return $piercers;
    }
}