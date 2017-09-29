<?php

class Allure_Appointments_Block_Adminhtml_Appointments_Print_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("piercingtiming_form",
		    array(
		        "legend" => Mage::helper("appointments")->__("Print Appointments")
		    ));
		
		
		$storeField=$fieldset->addField('store_id', 'select', array(
		        'label'     => Mage::helper("appointments")->__("Store"),
		        'name'    => 'store_id',
		        'values'   => Mage::helper("appointments")->storeOptionArray(),
		 ));

		
		$piercerField=$fieldset->addField('piercer_id', 'select', array(
		    'label'     => Mage::helper("appointments")->__("Piercer"),
		    'name'    => 'piercer_id',
		    'values'   => Mage::helper("appointments")->piercerOptionArray()
		));
		
		$fieldset->addField('from_date', 'date', array(
		    'name'               => 'from_date',
		    'label'              => Mage::helper('appointments')->__('From Date'),
		    'tabindex'           => 1,
		    'image'              => $this->getSkinUrl('images/grid-cal.gif'),
		    "required" => true,
		    'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
		    
		));
		$fieldset->addField('to_date', 'date', array(
		    'name'               => 'to_date',
		    'label'              => Mage::helper('appointments')->__('To Date'),
		    'tabindex'           => 1,
		    "required" => true,
		    'image'              => $this->getSkinUrl('images/grid-cal.gif'),
		    'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
		    
		));
		
		return parent::_prepareForm();
	}
}