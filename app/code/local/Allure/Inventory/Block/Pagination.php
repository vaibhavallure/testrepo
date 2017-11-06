<?php
class Allure_Inventory_Block_Pagination extends Mage_Page_Block_Html_Pager

{
	public function __construct()
	{
	    
		//block for receiving and transfer
		
		// Default Website - Main
		$websiteId = 1;

		if (!empty(Mage::getSingleton('core/session')->getMyWebsiteId())) {
			$websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
		}

		$website = Mage::getModel("core/website")->load($websiteId);
		$storeId = $website->getDefaultGroup()->getDefaultStoreId();
		$stockId = $website->getStockId();
		

		// Get Products from "Parent Items" Category
	    $collection = Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
	    $collection->addAttributeToSelect('sku')
	       ->addAttributeToSelect('name')
	       ->addAttributeToSelect('image')
	       ->addAttributeToSelect('qty')
	       ->addAttributeToSelect('cost')
	       ->addAttributeToSelect('attribute_set_id')
	       ->setStoreId($storeId);
	    
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
		
		if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
		    $collection->joinField('qty',
		        'cataloginventory/stock_item',
		        'qty',
		        'product_id=entity_id',
		        '{{table}}.stock_id='.$stockId,
		        'left');
		}

		$collection->joinAttribute(
		    'cost',
		    'catalog_product/cost',
		    'entity_id',
		    null,
		    'inner',
		    $stockId
		    );
		
		$collection->addAttributeToFilter('type_id', 'simple');
		$collection->getSelect()->group('e.entity_id');
		$collection->setOrder('sku','ASC');
		
		$this->setCollection($collection);
	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
		$pager->setAvailableLimit(array(10 => 10, 20 => 20, 50 => 50));
		$pager->setCollection($this->getCollection());
		$pager->setTemplate('inventory/pager.phtml');
		$this->setChild('pager', $pager);
		//$this->getCollection()->load();
		return $this;
	}
	
	public function getCustomCollection(){
		return $this->getCollection();
	}
	
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
	
	public function getProductAttributeSets(){
	    $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
	       ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
	       ->load()
	       ->toOptionHash();  
	    return $sets;
	}
	
}