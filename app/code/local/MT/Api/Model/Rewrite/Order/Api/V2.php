<?php
class MT_Api_Model_Rewrite_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2
{
	/**
	 * Retrieve full order information
	 *
	 * @param string $orderIncrementId
	 * @return array
	 */
	public function info($orderIncrementId)
	{
		$order = $this->_initOrder($orderIncrementId);
		
		if ($order->getGiftMessageId() > 0) {
			$order->setGiftMessage(
					Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
					);
		}
		
		$result = $this->_getAttributes($order, 'order');
		
		$result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
		$result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
		$result['items'] = array();
		
		foreach ($order->getAllVisibleItems() as $item) {  //add getAllVisibleItems by allure 
			if ($item->getGiftMessageId() > 0) {
				$item->setGiftMessage(
						Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
						);
			}
			
			$result['items'][] = $this->_getAttributes($item, 'order_item');
		}
		
		$result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');
		
		$result['status_history'] = array();
		
		foreach ($order->getAllStatusHistory() as $history) {
			$result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
		}
		
		return $result;
	}
}
