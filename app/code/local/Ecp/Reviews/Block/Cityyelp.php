<?php
class Ecp_Reviews_Block_Cityyelp extends Mage_Core_Block_Template
{
	public function _construct()
	{
		parent::_construct();
		$this->setTemplate('reviews/cityyelp.phtml');
	}
}