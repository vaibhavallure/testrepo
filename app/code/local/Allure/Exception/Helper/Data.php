<?php
class Allure_Exception_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public function notifyExceptionForPayment($checkout, $message){
		
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);
		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */
		$enabled=Mage::getStoreConfig('allure_exception/email/enabled', $checkout->getStoreId());
		Mage::log($message,Zend_log::DEBUG,'notifications',true);
		Mage::log($enabled,Zend_log::DEBUG,'notifications',true);
		try {
			if($enabled){
				$template = Mage::getStoreConfig('checkout/payment_failed/template', $checkout->getStoreId());
			
				$_reciever = Mage::getStoreConfig('allure_exception/email/reciever', $checkout->getStoreId());
				$_reciever=explode(",",$_reciever);
				$sendTo = array(
						array(
								'email' => $_reciever[0],
								'name'  => null
						)
				);
			
				
				$copyTo = Mage::getStoreConfig('allure_exception/email/copy_to', $checkout->getStoreId());
				$copyTo=explode(",",$copyTo);
			//	Mage::log($copyTo,Zend_log::DEBUG,'notifications',true);
				
				if ($copyTo && $copyMethod == 'bcc') {
					$mailTemplate->addBcc($copyTo);
				}
				$copyMethod = Mage::getStoreConfig('allure_exception/email/copy_method', $checkout->getStoreId());
				if ($copyTo && $copyMethod == 'copy') {
					foreach ($copyTo as $email) {
						$sendTo[] = array(
								'email' => $email,
								'name'  => null
						);
					}
				}
			//	Mage::log($sendTo,Zend_log::DEBUG,'notifications',true);
				$shippingMethod = '';
				if ($shippingInfo = $checkout->getShippingAddress()->getShippingMethod()) {
					$data = explode('_', $shippingInfo);
					$shippingMethod = $data[0];
				}
			
				$paymentMethod = '';
				if ($paymentInfo = $checkout->getPayment()) {
					$paymentMethod = $paymentInfo->getMethod();
				}
			
				$items = '';
				foreach ($checkout->getAllVisibleItems() as $_item) {
					/* @var $_item Mage_Sales_Model_Quote_Item */
					$items .= $_item->getProduct()->getName() . '  x '. $_item->getQty() . '  '
							. $checkout->getStoreCurrencyCode() . ' '
									. $_item->getProduct()->getFinalPrice($_item->getQty()) . "\n";
				}
				$total = $checkout->getStoreCurrencyCode() . ' ' . $checkout->getGrandTotal();
				//Mage::log($sendTo,Zend_log::DEBUG,'notifications',true);
				
				foreach ($sendTo as $recipient) {
					Mage::log("coming in Email",Zend_log::DEBUG,'notifications',true);
					$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$checkout->getStoreId()))
					->sendTransactional(
							$template,
							Mage::getStoreConfig('checkout/payment_failed/identity', $checkout->getStoreId()),
							$recipient['email'],
							$recipient['name'],
							array(
									'reason'          => $message,
									'checkoutType'    => $checkoutType,
									'dateAndTime'     => Mage::app()->getLocale()->date(),
									'customer'        => Mage::helper('customer')->getFullCustomerName($checkout),
									'customerEmail'   => $checkout->getCustomerEmail(),
									'billingAddress'  => $checkout->getBillingAddress(),
									'shippingAddress' => $checkout->getShippingAddress(),
									'shippingMethod'  => Mage::getStoreConfig('carriers/' . $shippingMethod . '/title'),
									'paymentMethod'   => Mage::getStoreConfig('payment/' . $paymentMethod . '/title'),
									'items'           => nl2br($items),
									'total'           => $total,
							)
					);
				}
			
				$translate->setTranslateInline(true);
				Mage::log("Done",Zend_log::DEBUG,'notifications',true);
				return $this;
			}
		} catch (Exception $e) {
			Mage::log("Exception".$e->getMessage(),Zend_log::DEBUG,'notifications',true);
		}
	}
}
	 