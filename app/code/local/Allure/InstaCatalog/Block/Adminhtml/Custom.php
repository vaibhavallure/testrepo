<?php 
class Allure_InstaCatalog_Block_Adminhtml_Custom extends Mage_Page_Block_Html_Pager
{  
    public function __construct()
    {
        $collection = Mage::getModel('allure_instacatalog/feed')
        ->getCollection()
        //->addFieldToFilter('lookbook_mode', 0)
        ->addFieldToFilter('lookbook_mode',array('neq'=>1))
        ->setOrder('created_timestamp','DESC');
        $this->setCollection($collection);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(20=>20,50=>50));
        $pager->setCollection($this->getCollection());
        $pager->setTemplate('inventory/pager.phtml');
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
    
    public function getInstagramCollection(){
        return $this->getCollection();
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
	public function getInstagramShopLookCollection(){
		$collection = Mage::getModel('allure_instacatalog/feed')
		->getCollection()
		->addFieldToFilter('lookbook_mode', 1)
		->setOrder('created_timestamp','DESC');
		return $collection;
	}
}