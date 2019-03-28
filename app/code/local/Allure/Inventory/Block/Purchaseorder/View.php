<?php

class Allure_Inventory_Block_Purchaseorder_view extends Mage_Page_Block_Html_Pager

{
	public function __construct()
	{

		$entityTypeId = Mage::getModel('eav/entity')
		->setType('catalog_product')
		->getTypeId();
		$prodNameAttrId = Mage::getModel('eav/entity_attribute')
		->loadByCode($entityTypeId, 'name')
		->getAttributeId();
		
		$id=Mage::app()->getRequest()->getParam('id');
		
		$collection=Mage::getModel('inventory/orderitems')->getCollection()
		->addFieldToFilter("po_id",$id);
		$collection->getSelect()->joinLeft('catalog_product_entity', 'catalog_product_entity.entity_id = main_table.product_id', array('sku'));
		//$collection->getSelect()->join('catalog_product_entity_varchar', 'main_table.product_id = admin_user.user_id',array('username'));
			
		
		$collection->getSelect()->joinLeft(
				array('prod' => 'catalog_product_entity'),
				'prod.entity_id = main_table.product_id',
				array('sku')
				)
				->joinLeft(
						array('cpev' => 'catalog_product_entity_varchar'),
						'cpev.entity_id = prod.entity_id AND cpev.attribute_id='.$prodNameAttrId.'',
						array('name' => 'value')
						);
				$collection->getSelect()->group('main_table.id');
				$collection->getSelect()->order('main_table.product_id DESC');
				/* echo $collection->getSelect();
				 die; */
				 $this->setCollection($collection);

	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
		$pager->setAvailableLimit(array(1000=>1000));
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
		//$this->getLayout()->createBlock('CLASS_GROUP_NAME/PATH_TO_BLOCK_FILE')->toHtml();
		//$this->setTemplate('inventory/pager.phtml');
		return $this->getChildHtml('pager');
	}
  	 public	function getDate($id,$value=""){
  	 	//	return date('Y-d-m',strtotime($value));
  	 	$shipDate="";
  	 	if($value)
  	 		$shipDate=date('Y-m-d',strtotime($value));
  	 	$form = new Varien_Data_Form(array( 'id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post' ));
  	 	$element = new Varien_Data_Form_Element_Date( array( 'name' => 'order['.$id.'][proposed_delivery_date]', 'label' => Mage::helper('bundle')->__('Date'),
  	 			'tabindex' => 1, 'image' => $this->getSkinUrl('images/grid-cal.gif'),
  	 			'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
  	 			//		'value' => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime($value) ) ) );
  	 			 'value' => $shipDate));
  	 	$element->setForm($form); $element->setId('date_'.$id); return $element->getElementHtml();
  	 }
}