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
	 * Start Express Checkout by requesting initial token and dispatching customer to PayPal
	 */
	public function startAction()
	{
	    try {
	        $this->_initCheckout();
	        
	        if ($this->_getQuote()->getIsMultiShipping()) {
	            
	            /** get all shipping addresses of quote */
	            $shippingAddresses = $this->_getQuote()->getAllShippingAddresses();
	            if(count($shippingAddresses) > 1){
	               $this->_getQuote()->setIsMultiShipping(false);
	               $this->_getQuote()->removeAllAddresses();
	            }
	        }
	        
	        $customer = Mage::getSingleton('customer/session')->getCustomer();
	        $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
	        if ($customer && $customer->getId()) {
	            $this->_checkout->setCustomerWithAddressChange(
	                $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
	                );
	        } elseif ((!$quoteCheckoutMethod
	            || $quoteCheckoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
	            && !Mage::helper('checkout')->isAllowedGuestCheckout(
	                $this->_getQuote(),
	                $this->_getQuote()->getStoreId()
	                )) {
	                    Mage::getSingleton('core/session')->addNotice(
	                        Mage::helper('paypal')->__('To proceed to Checkout, please log in using your email address.')
	                        );
	                    $this->redirectLogin();
	                    Mage::getSingleton('customer/session')
	                    ->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
	                    return;
	        }
	        
	        // billing agreement
	        $isBARequested = (bool)$this->getRequest()
	        ->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
	        if ($customer && $customer->getId()) {
	            $this->_checkout->setIsBillingAgreementRequested($isBARequested);
	        }
	        
	        // Bill Me Later
	        $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));
	        
	        // giropay
	        if ($this->_getQuote()->getIsMultiShipping()) {
	            $this->_checkout->prepareGiropayUrls(
	                Mage::getUrl('checkout/multishipping/success'),
	                Mage::getUrl('paypal/express/cancel'),
	                Mage::getUrl('checkout/multishipping/success')
	                );
	        }else{
	            $this->_checkout->prepareGiropayUrls(
	                Mage::getUrl('checkout/onepage/success'),
	                Mage::getUrl('paypal/express/cancel'),
	                Mage::getUrl('checkout/onepage/success')
	                );
	        }
	        
	        $button = (bool)$this->getRequest()->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_BUTTON);
	        $token = $this->_checkout->start(Mage::getUrl('*/*/return'), Mage::getUrl('*/*/cancel'), $button);
	        if ($token && $url = $this->_checkout->getRedirectUrl()) {
	            $this->_initToken($token);
	            $this->getResponse()->setRedirect($url);
	            return;
	        }
	    } catch (Mage_Core_Exception $e) {
	        $this->_getCheckoutSession()->addError($e->getMessage());
	    } catch (Exception $e) {
	        $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
	        Mage::logException($e);
	    }
	    
	    $this->_redirect('checkout/cart');
	}
	
	/**
	 * Return from PayPal and dispatch customer to order review page
	 */
	public function returnAction()
	{
	    if ($this->getRequest()->getParam('retry_authorization') == 'true'
	        && is_array($this->_getCheckoutSession()->getPaypalTransactionData())
	        ) {
	            $this->_forward('placeOrder');
	            return;
	        }
	        try {
	            $this->_getCheckoutSession()->unsPaypalTransactionData();
	            $this->_checkout = $this->_initCheckout();
	            $this->_checkout->returnFromPaypal($this->_initToken());
	            
	            if ($this->_checkout->canSkipOrderReviewStep()) {
	                $this->_forward('placeOrder');
	            } else {
	                if ($this->_getQuote()->getIsMultiShipping()) {
	                    $this->_redirect('*/*/overview');
	                }else{
	                    $this->_redirect('*/*/review');
	                }
	            }
	            
	            return;
	        } catch (Mage_Core_Exception $e) {
	            Mage::getSingleton('checkout/session')->addError($e->getMessage());
	        }
	        catch (Exception $e) {
	            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
	            Mage::logException($e);
	        }
	        $this->_redirect('checkout/cart');
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
	 * Review order after returning from PayPal
	 */
	public function overviewAction()
	{
	    try {
	        $this->_initCheckout();
	        $this->_checkout->prepareOrderReview($this->_initToken());
	        $this->loadLayout();
	        $this->_initLayoutMessages('paypal/session');
	        $reviewBlock = $this->getLayout()->getBlock('paypal.express.review');
	        $reviewBlock->setQuote($this->_getQuote());
	        $reviewBlock->getChild('details')->setQuote($this->_getQuote());
	        /* if ($reviewBlock->getChild('shipping_method')) {
	            $reviewBlock->getChild('shipping_method')->setQuote($this->_getQuote());
	        } */
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
				$session->setCartCouponCode(null);
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
	
	/**
	 * Submit the order for multishipping checkout
	 */
	public function overviewPostAction()
	{
	    try {
	        $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
	        if ($requiredAgreements) {
	            $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
	            if (array_diff($requiredAgreements, $postedAgreements)) {
	                Mage::throwException(Mage::helper('paypal')->__('Please agree to all the terms and conditions before placing the order.'));
	            }
	        }
	        Mage::dispatchEvent(
	            'checkout_controller_multishipping_overview_giftmessage_post',
	            array('request'=>$this->getRequest(), 'quote'=>$this->_getQuote())
	            );
	        
	        $this->_initCheckout();
	        $this->_getQuote()->setIsMultiShipping(false);
	        //$this->_getQuote()->removeAllAddresses();
	        
	        $this->_checkout->place($this->_initToken());
	        
	        // prepare session to success or cancellation page
	        $session = $this->_getCheckoutSession();
	        //$session->clearHelperData();
	        
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
	        $this->_initToken(false); // no need in token anymore
	        $state = Mage::getSingleton('checkout/type_multishipping_state');
	        Mage::getSingleton('core/session')->setOrderIds(array($order->getId() => $order->getIncrementId()));
	        $state->setCompleteStep(
	            Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
	            );
	        $session->clear();
	        $session->setDisplaySuccess(true);
	        
	        $session->setCartCouponCode(null);
	        
	        $this->_redirect('checkout/multishipping/success');
	        return;
	    } catch (Mage_Paypal_Model_Api_ProcessableException $e) {
	        $this->_processPaypalApiError($e);
	    } catch (Mage_Core_Exception $e) {
	        Mage::helper('checkout')->sendPaymentFailedEmail($this->_getQuote(), $e->getMessage());
	        $this->_getSession()->addError($e->getMessage());
	        $this->_redirect('*/*/overview');
	    } catch (Exception $e) {
	        Mage::helper('checkout')->sendPaymentFailedEmail(
	            $this->_getQuote(),
	            $this->__('Unable to place the order.')
	            );
	        $this->_getSession()->addError($this->__('Unable to place the order.'));
	        Mage::logException($e);
	        $this->_redirect('*/*/overview');
	    }
	}
	
}
	
	