<?php
class Allure_SmartAnalytics_Adminhtml_SmartAnalytics_UniversalController
            extends Mage_Adminhtml_Controller_Action
{
    public function senddataAction()
    {
        if ($order = $this->_initOrder()) {
            try {
				$storeId = $order->getStoreId();
                Mage::getModel('allure_smartanalytics/observer')->buildData($order, $storeId);
				$this->_getSession()->addSuccess($this->__('The order has been sent successfully to GA.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Failed to send transaction to GA.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

	/**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
}
