<?php
class Doddle_Returns_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function queueOrderForSync(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
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
}