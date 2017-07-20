<?php
class Allure_MyAccount_Block_Order_History extends Mage_Core_Block_Template
{
   public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
    }
    
    public function __construct()
    {
    	parent::__construct();
    	$this->setTemplate('allure/myaccount/history.phtml');
    	
    	$request = Mage::app()->getRequest()->getParams();
    	$pageNo=1;
    	$limit = 10;
    	
    	$store = "all";
    	$sortOrder = "desc";
	    if(count($request)>0){
	    	if(!empty($request['m_store'])){
	    		$store = $request['m_store'];
	    	}
	    	
	    	if(!empty($request['m_sort'])){
	    		$sortOrder = $request['m_sort'];
	    	}
	    	
	    	if($request['page'])
	    		$pageNo=$request['page'];
	    		
	    	if($request['limit'])
	    		$limit = $request['limit'];
    	}
    	
    	$orders = Mage::getResourceModel('sales/order_collection')
    	->addFieldToSelect('*')
    	->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
    	->addFieldToFilter('state', array('in' => array('canceled','complete','closed')));  //Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()
    	;//->setOrder('created_at', 'desc');
    	
    	if(!empty($store)){
    		if($store!='all')
    			$orders->addFieldToFilter('main_table.store_id',$store);
    	}
    	
    	$orders->setOrder('main_table.created_at', $sortOrder);
    	
    	$orders->setCurPage($pageNo);
    	$orders->setPageSize($limit);
    	
    	$this->setOrders($orders);
    	
    	//Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
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
    