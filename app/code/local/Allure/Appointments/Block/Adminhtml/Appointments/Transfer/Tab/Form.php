<?php

class Allure_Appointments_Block_Adminhtml_Appointments_Transfer_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("piercingtiming_form",
		    array(
		        "legend" => Mage::helper("appointments")->__("Transfer Appointments")
		    ));
		
		$storeField=$fieldset->addField('source_piercer', 'select', array(
		    'label'     => Mage::helper("appointments")->__("Source Piercer"),
		    'name'    => 'source_piercer',
		    'values'   => $this->getPiercersOptionsArray(),
		));
		$storeField=$fieldset->addField('destination_piercer', 'select', array(
		    'label'     => Mage::helper("appointments")->__("Desination Piercer"),
		    'name'    => 'destination_piercer',
		    'values'   => $this->getPiercersOptionsArray(),
		));
		
		$fieldset->addField('date', 'date', array(
		    'name'               => 'date',
		    'label'              => Mage::helper('appointments')->__('Date'),
		    'tabindex'           => 1,
		    'image'              => $this->getSkinUrl('images/grid-cal.gif'),
		    "required" => true,
		    'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
		    
		));
		return parent::_prepareForm();
	}
	public function getPiercersOptionsArray(){
	    $piercerArray=array();
	    $collection=Mage::getModel('appointments/piercers')->getCollection();
	    foreach ($collection as $percer){
	        $piercerArray[$percer->getId()]=$percer->getFirstname()." ".$percer->getLastname();
	    }
	    return $piercerArray;
	}
}