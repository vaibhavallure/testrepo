<?php
class Allure_MyAccount_Block_Order_History extends Mage_Sales_Block_Order_History
{
   public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
    }
    
    public function __construct()
    {
    	parent::__construct();
    	$this->setTemplate('allure/myaccount/history.phtml');
    	
    	$store = 'all';
    	if(!empty($_GET['m_store']))
    		$store = $_GET['m_store'];
    		
    	$sortOrder = 'desc';
    	if(!empty($sortOrder))
    		$sortOrder = $_GET['m_sort'];
    	
    	$orders = Mage::getResourceModel('sales/order_collection')
    	->addFieldToSelect('*')
    	->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
    	->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
    	;//->setOrder('created_at', 'desc');
    	
    	if(!empty($store)){
    		if($store!='all')
    			$orders->addFieldToFilter('main_table.store_id',$store);
    	}
    	
    	if(!empty($sortOrder)){
    		$orders->setOrder('main_table.created_at', $sortOrder);
    	}
    	$orders->setPageSize(10);
    	
    	$this->setOrders($orders);
    	
    	
    	Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
    }
    
    protected function _prepareLayout()
    {
        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager');
        $pager->setAvailableLimit(array(10=>10,20=>20,30=>30,'All'=>'All'));
        $pager->setCollection($this->getOrders());
        $request = Mage::app()->getRequest();
        if($request->getModuleName() == 'customer'){
                $pager->setTemplate('ecp/page/html/pager.phtml');
        }else{
                $pager->setTemplate('ecp/page/html/pagerhistory.phtml');
        }
        //$pager->setLimit(5);
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        return $this;

    }
    
     public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', array('order_id' => $order->getId()));
    }
}    
    