<?php

class Allure_Appointments_Block_Adminhtml_Appointments_Edit_Renderer_Piercername
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		
		$value = $row->getPiercerId();
		if($value)
		{
			$model = Mage::getModel('appointments/piercers')->load($value);
			if($model)
			{
				$name = $model->getFirstname() . " ". $model->getLastname();
			}
			return $name;
		}
		else{
			return "NOT ASSIGNED";
		}
	}
}
?>