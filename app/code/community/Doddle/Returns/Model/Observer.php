<?php
class Doddle_Returns_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function queueOrderForSync(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return;
        }

        // Only add to order queue if store config is enabled
        if ($this->getHelper()->getOrderSyncEnabled($order->getStoreId()) == false) {
            return;
        }

        $this->getOrderSyncQueue()->queueOrder($order->getId());
    }

    /**
     * @return Doddle_Returns_Model_Order_Sync_Queue
     */
    protected function getOrderSyncQueue()
    {
        return Mage::getModel('doddle_returns/order_sync_queue');
    }

    /**
     * @return Doddle_Returns_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('doddle_returns');
    }
}