<?php

class Allure_Inventory_Block_Adminhtml_Stock extends Mage_Adminhtml_Block_Widget
{    
	
	/* public function __construct()
	{
		parent::__construct();
	
		$collection=Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('sku')
		->addAttributeToSelect('name')
		->addAttributeToSelect('attribute_set_id')
		->addAttributeToSelect('type_id');
		$collection->getSelect()->limit();
		
		//inventory search result filter
		if($_GET['search']!=null){
			$collection = Mage::getModel('catalog/product')
			->getCollection()
			->addFieldToFilter('name', array('like' => $_GET['search'].'%'))
			->addAttributeToSelect('*');
		}
		if($_GET['searchById']!=null){
			$collection = Mage::getModel('catalog/product')
			->getCollection()
			->addFieldToFilter('entity_id', array('in' => $_GET['searchById'].'%'))
			->addAttributeToSelect('*');
		}
		$this->setCollection($collection);
	}
	 */
	
	
	public function getPagerHtml()
	{  
		//$this->getLayout()->createBlock('CLASS_GROUP_NAME/PATH_TO_BLOCK_FILE')->toHtml();
		//$this->setTemplate('inventory/pager.phtml');
		return $this->getChildHtml('pager');
	}
	
    
}