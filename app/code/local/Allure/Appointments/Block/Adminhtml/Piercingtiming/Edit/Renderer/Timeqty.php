<?php

class Allure_Appointments_Block_Adminhtml_Servicelocations_Edit_Renderer_Timeqty 
extends Mage_Adminhtml_Block_Widget
implements Varien_Data_Form_Element_Renderer_Interface
{
	public function __construct()
	{
		$this->setTemplate('appointments/timeqty.phtml');
	}
	
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);
		return $this->toHtml();
	}
}
?>