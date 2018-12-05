<?php
require_once ('app/code/core/Mage/Paypal/controllers/ExpressController.php');
class Allure_MultiCheckout_ExpressController extends Mage_Paypal_ExpressController
{
	private function _getQuote()
	{
		if (!$this->_quote) {
			$this->_quote = $this->_getCheckoutSession()->getQuote();
		}
		return $this->_quote;
	}
	
	
	private function getQuoteOrdered(){
		return Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote();
	}
	
	private function getQuteoteBackOrdered(){
		return Mage::getSingleton('allure_multicheckout/backordered_session')->getQuote();
	}
	
	private function _getSession()
	{
		return Mage::getSingleton('paypal/session');
	}
	
	/**
	 * Update shipping method (combined action for ajax and regular request)
	 */
	public function saveShippingMethodAction()
	{
		try {
			$isAjax = $this->getRequest()->getParam('isAjax');
			$this->_initCheckout();
			if ($isAjax) {
				$quoteObj = $this->_getQuote();
				$_checkoutstepHelper = Mage::helper('allure_multicheckout');
				if($_checkoutstepHelper->isTwoShipment()){
					$quote1 = $this->getQuoteOrdered();
					$quote2 = $this->getQuteoteBackOrdered();
					
					$params = $this->getRequest()->getPost();
					$shippingMethod = $params['shipping_method'];
					$responseShipping = '';
					if(!empty($params['type']) && $params['type']==1){
						if (!$quote1->getIsVirtual() && $shippingAddress = $quote1->getShippingAddress()) {
							if ($shippingMethod!= $shippingAddress->getShippingMethod()) {
								$this->getQuoteOrdered()->getShippingAddress()->setShippingMethod($shippingMethod);
								$this->getQuoteOrdered()->collectTotals()->save();
							}
						}
					}
					
					if(!empty($params['type']) && $params['type']==2){
						if (!$quote2->getIsVirtual() && $shippingAddress = $quote2->getShippingAddress()) {
							if ($shippingMethod!= $shippingAddress->getShippingMethod()) {
								$this->getQuteoteBackOrdered()->getShippingAddress()->setShippingMethod($shippingMethod);
								$this->getQuteoteBackOrdered()->collectTotals()->save();
							}
						}
					}
					
					$this->loadLayout('paypal_express_review_details_two');
					$html = $this->getLayout()->getBlock('two_ship_block')->toHtml();
					$data = array('html'=>$html);
					$jsonData = json_encode(compact('success', 'message', 'data'));
					$this->getResponse()->setHeader('Content-type', 'application/json');
					$this->getResponse()->setBody($jsonData);
				}else{
					$this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
					$this->loadLayout('paypal_express_review_details');
					$this->getResponse()->setBody($this->getLayout()->getBlock('root')
							->setQuote($this->_getQuote())
							->toHtml());
				}
				return;
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$this->_getSession()->addError($this->__('Unable to update shipping method.'));
			Mage::logException($e);
		}
		if ($isAjax) {
			$this->getResponse()->setBody('<script type="text/javascript">window.location.href = '
					. Mage::getUrl('*/*/review') . ';</script>');
		} else {
			$this->_redirect('*/*/review');
		}
	}
	
	
	/**
	 * Review order after returning from PayPal
	 */
	public function reviewAction()
	{
		try {
			$this->_initCheckout();
			$this->_checkout->prepareOrderReview($this->_initToken());
			$this->loadLayout();
			$this->_initLayoutMessages('paypal/session');
			$reviewBlock = $this->getLayout()->getBlock('paypal.express.review');
			$reviewBlock->setQuote($this->_getQuote());
			$reviewBlock->getChild('details')->setQuote($this->_getQuote());
			if ($reviewBlock->getChild('shipping_method')) {
				$reviewBlock->getChild('shipping_method')->setQuote($this->_getQuote());
			}
			$this->renderLayout();
			return;
		}
		catch (Mage_Core_Exception $e) {
			Mage::getSingleton('checkout/session')->addError($e->getMessage());
		}
		catch (Exception $e) {
			Mage::getSingleton('checkout/session')->addError(
					$this->__('Unable to initialize Express Checkout review.')
					);
			Mage::logException($e);
		}
		$this->_redirect('checkout/cart');
	}
	
	
	
	
	/**
	 * Submit the order
	 */
	public function placeOrderAction()
	{
		try {
			$requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
			if ($requiredAgreements) {
				$postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
				if (array_diff($requiredAgreements, $postedAgreements)) {
					Mage::throwException(Mage::helper('paypal')->__('Please agree to all the terms and conditions before placing the order.'));
				}
			}
			
			$this->_initCheckout();
			$this->_checkout->placeCustom($this->_initToken());
			
			
			$_checkoutstepHelper = Mage::helper('allure_multicheckout');
			if($_checkoutstepHelper->isTwoShipment()){ //two shipment order
				
				$this->_getQuote()->setIsActive(false)->save();
				if($this->getQuoteOrdered()){
					$this->getQuoteOrdered()->setIsActive(false)->save();
				}
				if($this->getQuteoteBackOrdered()){
					$this->getQuteoteBackOrdered()->setIsActive(false)->save();
				}
				
				$this->_initToken(false); // no need in token anymore
				$this->_redirect('checkout/onepage/successorder');
				
			}else{
				
				// prepare session to success or cancellation page
				$session = $this->_getCheckoutSession();
				$session->clearHelperData();
				
				// "last successful quote"
				$quoteId = $this->_getQuote()->getId();
				$session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
				
				// an order may be created
				$order = $this->_checkout->getOrder();
				if ($order) {
					$session->setLastOrderId($order->getId())
					->setLastRealOrderId($order->getIncrementId());
					// as well a billing agreement can be created
					$agreement = $this->_checkout->getBillingAgreement();
					if ($agreement) {
						$session->setLastBillingAgreementId($agreement->getId());
					}
				}
				
				// recurring profiles may be created along with the order or without it
				$profiles = $this->_checkout->getRecurringPaymentProfiles();
				if ($profiles) {
					$ids = array();
					foreach($profiles as $profile) {
						$ids[] = $profile->getId();
					}
					$session->setLastRecurringProfileIds($ids);
				}
				
				// redirect if PayPal specified some URL (for example, to Giropay bank)
				$url = $this->_checkout->getRedirectUrl();
				if ($url) {
					$this->getResponse()->setRedirect($url);
					return;
				}
				
				
				if($this->getQuoteOrdered()){
					$this->getQuoteOrdered()->setIsActive(false)->save();
				}
				if($this->getQuteoteBackOrdered()){
					$this->getQuteoteBackOrdered()->setIsActive(false)->save();
				}
				
				$this->_initToken(false); // no need in token anymore
				$this->_redirect('checkout/onepage/success');
			}
			
			return;
		} catch (Mage_Paypal_Model_Api_ProcessableException $e) {
			$this->_processPaypalApiError($e);
		} catch (Mage_Core_Exception $e) {
			Mage::helper('checkout')->sendPaymentFailedEmail($this->_getQuote(), $e->getMessage());
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/review');
		} catch (Exception $e) {
			Mage::helper('checkout')->sendPaymentFailedEmail(
					$this->_getQuote(),
					$this->__('Unable to place the order.')
					);
			$this->_getSession()->addError($this->__('Unable to place the order.'));
			Mage::logException($e);
			$this->_redirect('*/*/review');
		}
	}
	
}
	
	