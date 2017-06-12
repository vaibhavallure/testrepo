<?php
require_once 'Mage/Sales/controllers/OrderController.php';
class Ecp_Sales_OrderController extends Mage_Sales_OrderController
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {       
        $action = $this->getRequest()->getActionName(); 
        if($action == 'print') return $this;
        parent::preDispatch();      
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }        
        
    }
    protected function _canViewOrder($order)
    {
        $action = $this->getRequest()->getActionName(); 
        if($action == 'print'){
            return true;
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
    /**
     * Print Order Action
     */
    public function printAction()
    {       
        if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }
}
