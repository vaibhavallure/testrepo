<?php
class Allure_MyAccount_Block_Order_Openorder extends Mage_Core_Block_Template
{
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', 
            array('order_id' => $order->getId(),'order_type'=>'open'));
    }
    
    public function __construct()
    {
    	parent::__construct();
    	$helper    = Mage::helper("myaccount");
    	$openOrder = $helper::OPEN_ORDER;
    	$orders    = $helper->getOrdersHistory($openOrder);
    	$this->setOrders($orders);
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
    