<?php
class Allure_Inventory_Block_Minmax extends Mage_Page_Block_Html_Pager
{
	const  PARENT_ITEMS_CATEGORY_ID=426;
	
    
	public function __construct()
	{
			$websiteId=1;
			if(!empty(Mage::getSingleton('core/session')->getMyWebsiteId()))
				$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
			$website = Mage::getModel( "core/website" )->load($websiteId);
			$storeId = $website->getDefaultGroup()->getDefaultStoreId();
			$stockId = $website->getStockId();
			
			//avoid by desciption
			/* $entityTypeId = Mage::getModel('eav/entity')
			 ->setType('catalog_product')
			 ->getTypeId();
			 $prodDescAttrId = Mage::getModel('eav/entity_attribute')
			 ->loadByCode($entityTypeId, 'description')
			 ->getAttributeId(); */
			$collection=Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
			$collection->addAttributeToSelect('*')->setStoreId($storeId);
			
			if($_GET['search']!=null){
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
			    $collection->getSelect()->joinLeft(array("stock_item" => 'cataloginventory_stock_item'), 
			        "(stock_item.product_id = e.entity_id) and stock_item.stock_id=".$stockId,array('qty','notify_stock_qty'));
			}
			
			$collection->joinAttribute(
			    'cost',
			    'catalog_product/cost',
			    'entity_id',
			    null,
			    'inner',
			    $storeId
			    );
			
			$collection->addAttributeToFilter('type_id', 'simple');
			$collection->getSelect()->group('e.entity_id');
			/* $collection->addAttributeToFilter('sku', array('nlike' => 'c%','nlike' => 'c%'));
			 $collection->addAttributeToFilter('sku', array('nlike' => 's%','nlike' => 's%')); */
			$collection->setOrder('sku','ASC');
			
			$this->setCollection($collection);

	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
		$pager->setAvailableLimit(array(10=>10,20=>20,50=>50));
		$pager->setCollection($this->getCollection());
		$pager->setTemplate('inventory/pager.phtml');
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