<?php
class Allure_Inventory_Block_Purchaseorder_New extends Mage_Page_Block_Html_Pager

{
	public function __construct()
	{
	    $helper = Mage::helper("inventory");
	    $childCategoryId = $helper->getChildCategoryId();
	    if(empty($childCategoryId)){
	        $childCategoryId = $helper->getParentCategoryId();
	    }
	    
		$websiteId=1;
		if(Mage::getSingleton('core/session')->getMyWebsiteId())
			$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
		
		$website = Mage::getModel( "core/website" )->load($websiteId);
		$storeId = $website->getDefaultGroup()->getDefaultStoreId();
		$stockId = $website->getStockId();
		
		/* $entityTypeId = Mage::getModel('eav/entity')
		->setType('catalog_product')
		->getTypeId();
		$prodDescAttrId = Mage::getModel('eav/entity_attribute')
		->loadByCode($entityTypeId, 'description')
		->getAttributeId(); */
		
	/*	$category=Mage::getModel('catalog/category')->load(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
		$collection = Mage::getResourceModel('reports/product_lowstock_collection')
		->addAttributeToSelect('*')
		->addAttributeToSelect('description')
		->setStoreId($storeId)
		->joinInventoryItem('qty')
		->joinInventoryItem('stock_id')
		->useManageStockFilter($storeId)
		->useNotifyStockQtyFilter($storeId)
		->setOrder('sku', Varien_Data_Collection::SORT_ORDER_ASC);
		$collection->addAttributeToFilter('stock_id', array('eq' => $stockId));
		$collection->addAttributeToFilter('type_id', 'simple');*/
		//$collection->addCategoryFilter($category);
		
		//$collection=Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
		$collection = Mage::getResourceModel('catalog/product_collection')
		->addAttributeToFilter('type_id', array('eq' => 'simple'));
		
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
		/* $collection->addAttributeToFilter(
				array(
						array('attribute' => 'sku', 'nlike' => 's%'),
				)
				); */
		/* $collection->getSelect()
		->joinLeft(
				array('cpev' => 'catalog_product_entity_text'),
				'cpev.entity_id = e.entity_id AND cpev.attribute_id='.$prodDescAttrId.'',
				array('description' => 'value')
				);
		if($_GET['search']!=null)
			$collection->getSelect()->orWhere('cpev.value like \'%'.$_GET['search'].'%\'');
		 */
		
		if( $storeId ) {
			$collection->addStoreFilter($storeId);
		}
		
		//Temperory commented as CPMAGENTO Parent category issue
		
		$collection->getSelect()->join(
		    array('category_product' => 'catalog_category_product'),
		    'category_product.product_id = e.entity_id',
		    array('category_id')
		    );
		
		$collection->getSelect()->where('category_product.category_id = '.$childCategoryId);
		
		if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
		    $collection->joinField('qty',
		        'cataloginventory/stock_item',
		        'qty',
		        'product_id=entity_id',
		        '{{table}}.stock_id='.$stockId,
		        'left');
		}
		$collection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
	  //  $collection->getSelect()->where('lowstock_inventory_item.po_sent=0');
	    $collection->getSelect()->group('e.entity_id');
		$collection->addAttributeToFilter('sku', array('nlike' => 'c%','nlike' => 'c%'));
		$collection->addAttributeToFilter('sku', array('nlike' => 's%','nlike' => 's%'));
		$this->setCollection($collection);
		
		
		/* if($_GET['search']!=null){
			
			$searchText=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $_GET['search']);
			$collection->addAttributeToFilter(
					array(
							array('attribute' => 'sku', 'like' => $searchText.'%'),
							array('attribute' => 'name', 'like' => $searchText.'%'),
					)
					);
			
		}
		$collection->getSelect()->where('lowstock_inventory_item.po_sent=0');
		$collection->getSelect()->group('e.entity_id');
		$collection->setOrder('sku','ASC');
		
		$this->setCollection($collection); */

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