<?php
/**
 * @author allure
 *
 */
class Allure_Orderdate_Model_Adminhtml_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Create new order
     *
     * @return Mage_Sales_Model_Order
     */
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();
        
        $params      = Mage::app()->getRequest()->getPost('order');
        $createdDate = $params['created_at'];
        
        $service = Mage::getModel('sales/service_quote', $quote);
        /** @var Mage_Sales_Model_Order $oldOrder */
        $oldOrder = $this->getSession()->getOrder();
        if ($oldOrder->getId()) {
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = array(
                'original_increment_id'     => $originalId,
                'relation_parent_id'        => $oldOrder->getId(),
                'relation_parent_real_id'   => $oldOrder->getIncrementId(),
                'edit_increment'            => $oldOrder->getEditIncrement()+1,
                'increment_id'              => $originalId.'-'.($oldOrder->getEditIncrement()+1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
            
            $oldOrder->cancel();
        }
        
        /** @var Mage_Sales_Model_Order $order */
        $order = $service->submit();
        if(!empty($createdDate)){
            $todayDate = date('Y-m-d');
            $createdDateOnly = Mage::getModel("core/date")->gmtDate("Y-m-d", $createdDate);
            $createdDate = Mage::getModel("core/date")->gmtDate("Y-m-d H:i:s", $createdDate);
            if($todayDate != $createdDateOnly){
                $order->setCreatedAt($createdDate)->save();
            }
        }
        $customer = $quote->getCustomer();
        if ((!$customer->getId() || !$customer->isInStore($this->getSession()->getStore()))
            && !$quote->getCustomerIsGuest()
            ) {
                $customer->setCreatedAt($order->getCreatedAt());
                $customer
                ->save()
                ->sendNewAccountEmail('registered', '', $quote->getStoreId());;
            }
            if ($oldOrder->getId()) {
                $oldOrder->setRelationChildId($order->getId());
                $oldOrder->setRelationChildRealId($order->getIncrementId());
                $oldOrder->save();
                $order->save();
            }
            if ($this->getSendConfirmation()) {
                $order->queueNewOrderEmail();
            }
            
            Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
            
            return $order;
    }
    
}