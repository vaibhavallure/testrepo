<?php
class IWD_OrderManager_Model_Order_Info extends IWD_OrderManager_Model_Order
{
    protected $params;

    public function updateOrderInfo($params)
    {
        $this->init($params);

        if (isset($params['confirm_edit']) && !empty($params['confirm_edit'])) {
            $this->addChangesToConfirm();
        } else {
            $this->editInfo();
            $this->updateOrderAmounts();
            $this->addChangesToLog();
            $this->notifyEmail();
        }
    }

    public function execUpdateOrderInfo($params)
    {
        $this->init($params);
        $this->editInfo();
        $this->updateOrderAmounts();
        $this->notifyEmail();
    }

    protected function init($params){
        $this->params = $params;

        if (empty($this->params) || !isset($this->params['order_id'])){
            throw new Exception("Order id is not defined");
        }

        $this->load($this->params['order_id']);
    }

    protected function addChangesToLog()
    {
        $logger = Mage::getSingleton('iwd_ordermanager/logger');
        $order_id = $this->params['order_id'];

        $logger->addCommentToOrderHistory($order_id);
        $logger->addLogToLogTable(IWD_OrderManager_Model_Confirm_Options_Type::ORDER_INFO, $order_id);
    }

    protected function editInfo()
    {
        $this->load($this->params['order_id']);

        $this->updateOrderState();
        $this->updateOrderStatus();
        $this->updateOrderStoreId();
    }

    protected function notifyEmail(){
        $notify = isset($this->params['notify']) ? $this->params['notify'] : null;
        $order_id = $this->params['order_id'];

        if ($notify) {
            $message = isset($this->params['comment_text']) ? $this->params['comment_text'] : null;
            $email = isset($this->params['comment_email']) ? $this->params['comment_email'] : null;
            $result['notify'] = Mage::getModel('iwd_ordermanager/notify_notification')->sendNotifyEmail($order_id, $email, $message);
        }
    }

    protected function addChangesToConfirm()
    {
        $logger = Mage::getSingleton('iwd_ordermanager/logger');
        $order_id = $this->params['order_id'];

        $this->estimateOrderInfoChanges();
        $this->estimateOrderAmounts();

        $logger->addCommentToOrderHistory($order_id, 'wait');
        $logger->addLogToLogTable(IWD_OrderManager_Model_Confirm_Options_Type::ORDER_INFO, $order_id, $this->params);

        $message = Mage::helper('iwd_ordermanager')
            ->__('Updates was not applied now! Customer get email with confirm link. Order will be updated after confirm.');

        Mage::getSingleton('adminhtml/session')->addNotice($message);
    }

    protected function estimateOrderInfoChanges(){
        $logger = Mage::getSingleton('iwd_ordermanager/logger');

        if (isset($this->params['status']) && !empty($this->params['status']) && $this->getStatus() != $this->params['status']) {
            $logger->addChangesToLog('order_status', $this->getStatus(), $this->params['status']);
        }

        $allow_change_state = $this->isAllowChangeOrderState();
        if (isset($this->params['state']) && !empty($this->params['state']) && $this->getState() != $this->params['state'] && $allow_change_state) {
            $logger->addChangesToLog('order_state', $this->getState(), $this->params['state']);
        }

        if (isset($this->params['store_id']) && !empty($this->params['store_id']) && $this->getStoreId() != $this->params['store_id']) {
            $new_store = Mage::app()->getStore($this->params['store_id']);
            $old_store = Mage::app()->getStore($this->getStoreId());
            $logger->addChangesToLog('order_store_name', $old_store->getName(), $new_store->getName());
        }
    }

    protected function updateOrderStatus()
    {
        $status_id = $this->params['status'];

        if (!empty($status_id) && $this->getStatus() != $status_id && $status_id !== 'false' && $status_id != false) {
            Mage::getSingleton('iwd_ordermanager/logger')->addChangesToLog('order_status', $this->getStatus(), $status_id);

            $this->setData('status', $status_id)->save();
        }
    }

    protected function updateOrderStoreId()
    {
        $store_id = $this->params['store_id'];

        $new_store = Mage::app()->getStore($store_id);
        $old_store = Mage::app()->getStore($this->getStoreId());

        if (!empty($store_id) && $this->getStoreId() != $store_id) {
            Mage::getSingleton('iwd_ordermanager/logger')->addChangesToLog('order_store_name', $old_store->getName(), $new_store->getName());
            $this->setData('store_id', $store_id)->save();
        }
    }

    protected function updateOrderState()
    {
        if(isset($this->params['state'])) {
            $state_id = $this->params['state'];

            $allow_change_state = $this->isAllowChangeOrderState();

            if (!empty($state_id) && $this->getState() != $state_id && $allow_change_state) {

                Mage::getSingleton('iwd_ordermanager/logger')->addChangesToLog('order_state', $this->getState(), $state_id);
                $this->setData('state', $state_id)->save();
            }
        }
    }

    protected function updateOrderAmounts()
    {
        if (isset($this->params['recalculate_amount']) && !empty($this->params['recalculate_amount'])) {
            //TODO: add!!!
        }
    }

    protected function estimateOrderAmounts()
    {
        if (isset($this->params['recalculate_amount']) && !empty($this->params['recalculate_amount']))
        {
            $order_id = $this->params['order_id'];
            $order = Mage::getModel('sales/order')->load($order_id);
            Mage::getSingleton('adminhtml/session_quote')->clear();

            $sales_order_create = Mage::getModel('adminhtml/sales_order_create')->initFromOrder($order);
            $quote = $sales_order_create->getQuote();

            $quote->setData('store_id', $this->params['store_id'])->save();

            $quote = $quote->setTotalsCollectedFlag(false)->collectTotals();

            $totals = array(
                'grand_total' => $quote->getGrandTotal(),
                'base_grand_total' => $quote->getBaseGrandTotal(),
            );

            Mage::getSingleton('iwd_ordermanager/logger')->addNewTotalsToLog($totals);
        }
    }
}