<?php

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once ('Ecp/Sales/controllers/OrderController.php');

class Allure_MultiCheckout_OrderController extends Ecp_Sales_OrderController
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch ()
    {
        $action = $this->getRequest()->getActionName();
        if ($action == "print" || $action == "printorder")
            return $this;
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        
        if (! Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    protected function _canViewOrder ($order)
    {
        $action = $this->getRequest()->getActionName();
        if ($action == 'print' || $action == "printorder") {
            return true;
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId) &&
                 in_array($order->getState(), $availableStates, $strict = true)) {
            return true;
        }
        return false;
    }

    protected function _loadValidOrders ($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $secondOrderId = (int) $this->getRequest()->getParam('second_order_id');
        }
        if (! $orderId && ! $secondOrderId) {
            $this->_forward('noRoute');
            return false;
        }
        
        $order = Mage::getModel('sales/order')->load($orderId);
        $secondOrder = Mage::getModel('sales/order')->load($secondOrderId);
        
        if ($this->_canViewOrder($order) && $this->_canViewOrder($secondOrder)) {
            Mage::register('current_order', $order);
            Mage::register('second_current_order', $secondOrder);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Print Order Action
     */
    public function printorderAction ()
    {
        if (! $this->_loadValidOrders()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }
}
