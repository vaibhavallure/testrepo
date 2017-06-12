<?php
class Ecp_Sales_Block_Order_History extends Mage_Sales_Block_Order_History
{
   public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
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
    