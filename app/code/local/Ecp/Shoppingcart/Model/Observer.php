<?php

class Ecp_Shoppingcart_Model_Observer
{
    public function storeCustomerQuote(Varien_Event_Observer $observer)
    {
        $lastQid = Mage::getSingleton('checkout/session')->getQuoteId();
        $cookie = Mage::getSingleton('core/cookie');
        Mage::log('subtotal read ' . $cookie->get('subtotal'));
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals['subtotal']->getValue();
        $cookie->set('subtotal', $subtotal, time() + 60 * 60 * 24 * 15);
        Mage::log('subtotal' . $subtotal);
    }

    public function getCustomerQuote(Varien_Event_Observer $observer)
    {
        $quoteId = Mage::app()->getCookie()->get('quote_id_customer');
        $customerQuote = Mage::getModel('sales/quote')->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());
        $customerQuote->setQuoteId($quoteId);
        $customerQuote->save();
    }

    public function storeGiftMessage(Varien_Event_Observer $observer)
    {
    	$quote = Mage::getSingleton('checkout/session')->getQuote();
    	
    	if ($quote && $quote->getId()) {
    		$giftMessage = Mage::getModel('giftmessage/message');
    		$quoteItemId = $observer->getEvent()->getQuoteItem()->getId();
    		$quoteItem = $observer->getEvent()->getQuoteItem();
    		
    		$message = $observer->getEvent()->getProduct()->getSpecialInstruction();
    		
    		if($quoteItem && $quoteItem->getGiftMessageId()) {
    			$giftMessage->load($quoteItem->getGiftMessageId());
    		}
    		
    		if(false && trim($message)=='') {
    			if($giftMessage->getId()) {
    				try{
    					$giftMessage->delete();
    					$quoteItem->setGiftMessageId(0)
    					->save();
    				}
    				catch (Exception $e) { }
    			}
    		}
    		
    		try {
    			if(isset($message) && !empty($message)){
					$giftMessage
					//->setSender('Suresh Shinde')
					//->setRecipient('Sample')
					->setMessage($message)
					->save();
    				$quoteItem->setGiftMessageId($giftMessage->getId())->save();
    			}
    		
    		}
    		catch (Exception $e) { }
    		
    	}
    }
}
