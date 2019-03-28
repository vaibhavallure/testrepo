<?php
class Allure_SmartAnalytics_Block_New extends Mage_Catalog_Block_Product_New
{
	protected function _construct()
    {
        parent::_construct();
		$this->setTemplate('allure/smartanalytics/new.phtml');
	}
}
