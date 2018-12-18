<?php
class Allure_MultiCheckout_Model_Paypal_Api_Standard extends Mage_Paypal_Model_Api_Standard
{
	/**
	 * Prepare line items request
	 *
	 * Returns true if there were line items added
	 *
	 * @param array &$request
	 * @param int $i
	 * @return true|bool
	 */
	protected function _exportLineItems(array &$request, $i = 0)
	{
		if (!$this->_cart) {
			return;
		}
		
		$quote = Mage::getSingleton('allure_multicheckout/backordered_session')->getQuote();
		$quote = $quote->collectTotals();
		$paypalCart = Mage::getModel('paypal/cart', array($quote));
		$totals = $paypalCart->getTotals();
		// always add cart totals, even if line items are not requested
		if ($this->_lineItemTotalExportMap) {
			foreach ($this->_cart->getTotals() as $key => $total) {
				if (isset($this->_lineItemTotalExportMap[$key])) { // !empty($total)
					$privateKey = $this->_lineItemTotalExportMap[$key];
					//$request[$privateKey] = $this->_filterAmount($total);
					
					$value = $this->_filterAmount($total);
					if($key=='subtotal'){
						$disscount = 0;
						if(!empty($totals['discount']))
							$disscount = $totals['discount'];
							$request[$privateKey] = $totals[$key] + $value - $disscount;
					}else{
						$request[$privateKey] = $totals[$key]+$value;
					}
					
					Mage::log("cart - ".$privateKey."=".$request[$privateKey],Zend_log::DEBUG,'abc.log',true);
				}
			}
		}
		
		// add cart line items
		$items = $this->_cart->getItems();
		if (empty($items) || !$this->getIsLineItemsEnabled()) {
			return;
		}
		$result = null;
		foreach ($items as $item) {
			foreach ($this->_lineItemExportItemsFormat as $publicKey => $privateFormat) {
				$result = true;
				$value = $item->getDataUsingMethod($publicKey);
				if (isset($this->_lineItemExportItemsFilters[$publicKey])) {
					$callback   = $this->_lineItemExportItemsFilters[$publicKey];
					$value = call_user_func(array($this, $callback), $value);
				}
				if (is_float($value)) {
					$value = $this->_filterAmount($value);
				}
				$request[sprintf($privateFormat, $i)] = $value;
			}
			$i++;
		}
		return $result;
	}
	
}
