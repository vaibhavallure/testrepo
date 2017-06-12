<?php 
class Allure_InstaCatalog_Block_Adminhtml_Custom extends Mage_Adminhtml_Block_Widget
{  
	public function getInstagramCollection(){
		$collection = Mage::getModel('allure_instacatalog/feed')
			->getCollection()
			//->addFieldToFilter('lookbook_mode', 0)
			->addFieldToFilter('lookbook_mode',array('neq'=>1))
			->setOrder('created_timestamp','DESC');
		return $collection;
	}
	
	public function getInstagramShopLookCollection(){
		$collection = Mage::getModel('allure_instacatalog/feed')
		->getCollection()
		->addFieldToFilter('lookbook_mode', 1)
		->setOrder('created_timestamp','DESC');
		return $collection;
	}
}