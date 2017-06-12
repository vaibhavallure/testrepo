<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once ('app/code/local/Ecp/Sales/controllers/OrderController.php');

class Allure_CheckoutStep_OrderController extends Ecp_Sales_OrderController
{
    
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {       
        $action = $this->getRequest()->getActionName(); 
        if($action == "print" || $action == "printorder") 
        	return $this;
        parent::preDispatch();      
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }        
        
    }
    
    
     protected function _canViewOrder($order)
    {
        $action = $this->getRequest()->getActionName(); 
        if($action == 'print' || $action == "printorder" ){
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
    
    
    protected function _loadValidOrders($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $secondOrderId = (int) $this->getRequest()->getParam('second_order_id');
        }
        if (!$orderId && !$secondOrderId) {
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
    public function printorderAction()
    {
    	if (!$this->_loadValidOrders()) {
    		return;
    	}
    	$this->loadLayout('print');
    	$this->renderLayout();
    }
    
}
