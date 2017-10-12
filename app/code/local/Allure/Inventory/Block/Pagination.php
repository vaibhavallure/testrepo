<?php
class Allure_Inventory_Block_Pagination extends Mage_Page_Block_Html_Pager

{
	public function __construct()
	{
	    
		//block for receiving and transfer
		
		// Default Website - Main
		$websiteId = 1;

		if (Mage::getSingleton('core/session')->getMyWebsiteId()) {
			$websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
		}

		$website = Mage::getModel( "core/website" )->load($websiteId);
		$storeId = $website->getStoreId();

		// Get Products from "Parent Items" Category
	    $collection=Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
	    $collection->addAttributeToSelect('sku')->addAttributeToSelect('name')->addAttributeToSelect('image')->addAttributeToSelect('qty')->addAttributeToSelect('cost')->setStoreId($storeId);
	    
		if (isset($_GET['search']) && $_GET['search'] != null) {
			$searchString = $_GET['search'];
			$searchString = str_replace("\\", "\\\\",$searchString);
			$collection->addAttributeToFilter(
					array(
							array('attribute' => 'sku', 'like' =>'%'.$searchString.'%'),
							array('attribute' => 'name', 'like' => '%'.$searchString.'%'),
					)
			);
		}
		$collection->addAttributeToFilter('type_id', 'simple');
		$collection->getSelect()->group('e.entity_id');
		$collection->setOrder('sku','ASC');

		$this->setCollection($collection);
	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
		$pager->setAvailableLimit(array(20 => 20, 50 => 50, 100 => 100, 'all' => 'all'));
		$pager->setCollection($this->getCollection());
		$this->setChild('pager', $pager);
		$this->getCollection()->load();
		return $this;
	}
	
	public function getCustomCollection(){
		return $this->getCollection();
	}
	
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
	
}