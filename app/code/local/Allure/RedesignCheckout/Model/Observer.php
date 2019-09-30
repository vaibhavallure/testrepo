<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Observer extends Varien_Object
{
    /**
     * Set address item gift to order
     * @param Varien_Object $observer
     */
    public function salesEventConvertQuoteItemToOrderItem($observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $orderItem->setIsGiftItem($quoteItem->getIsGiftItem());
        $orderItem->setGiftItemQty($quoteItem->getGiftItemQty());
    }
    
    /**
     * Set signature delivery method to quote address
     * @param Varien_Object $observer
     */
    public function checkoutEventSaveSignature($observer)
    {
        $signatureDelivery = $observer->getEvent()->getRequest()->getParam('no_signature_delivery');
        Mage::log($signatureDelivery,Zend_Log::DEBUG,'abc.log',true);
        $quote = $observer->getEvent()->getQuote();
        if(is_array($signatureDelivery)){
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address){
                try{
                    $address->setNoSignatureDelivery(0);
                    if (isset($signatureDelivery[$address->getId()])) {
                        if($signatureDelivery[$address->getId()]){
                            $address->setNoSignatureDelivery(1);
                        }
                    } 
                }catch (Exception $e){}
            }
        }
    }
    
    public function salesEventConvertQuoteAddressToOrder($observer){
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();
        if($address->getNoSignatureDelivery()) {
            $order->setNoSignatureDelivery($address->getNoSignatureDelivery());
        }
        if($address->getIsContainBackorder()){
            $order->setIsContainBackorder($address->getIsContainBackorder());
        }
        return $this;
    }
}

