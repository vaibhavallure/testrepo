<?php
class FME_Restrictcustomergroup_Model_Config_Restrictiontype
{
	public function toOptionArray()
    {
		return array(
			array(
				'label' => Mage::helper('restrictcustomergroup')->__('Basic'),
				'value' => 'basic'
			),
			array(
				'label' => Mage::helper('restrictcustomergroup')->__('Manual'),
				'value' => 'manual'
			),
		);
	}
}