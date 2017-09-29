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
			//Mage::log(json_encode($this->_getAttributes($item, 'order_item')),Zend_log::DEBUG,'api_orders.log',true);
			
			//added for Parent child
			$itemNew=$this->_getAttributes($item, 'order_item');
			if($itemNew['product_type']=='configurable'){
			    $productId = Mage::getModel("catalog/product")->getIdBySku($itemNew['sku']);
			    $simpleProduct=Mage::getModel('catalog/product')->load($productId);
			    $itemNew['product_type']='simple';
			    $itemNew['name']=$simpleProduct->getName();
			    $cpid=$itemNew['product_id'];
			    if(isset($productId) && !empty($productId))
			        $itemNew['product_id']=$productId;
			    $product_options = unserialize($itemNew['product_options']);
			    $oldObj=$product_options['info_buyRequest'];
			    $oldObj['cpid']=$cpid;
			    //Mage::log(json_encode($oldObj),Zend_log::DEBUG,'api_orders.log',true);
			    $product_options['info_buyRequest']=($oldObj);
			    $itemNew['product_options']=serialize($product_options);
			    
			}
			$result['items'][] = $itemNew;
		}
		$result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');
		$result['status_history'] = array();
		foreach ($order->getAllStatusHistory() as $history) {
			$result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
		}
		
		return $result;
	}
}
